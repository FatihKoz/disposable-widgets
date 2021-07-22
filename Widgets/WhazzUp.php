<?php

namespace Modules\DisposableTools\Widgets;

use App\Contracts\Widget;
use App\Models\Airline;
use App\Models\Pirep;
use App\Models\UserField;
use App\Models\UserFieldValue;
use Carbon\Carbon;
use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Support\Facades\Log;
use Modules\DisposableTools\Models\Disposable_WhazzUp;

// WhazzUp Data Retrieval
class WhazzUp extends Widget
{
  // Define Default Config
  protected $config = ['selection' => 'IVAO'];

  // Prepare Guzzle
  public function __construct(GuzzleClient $httpClient)
  {
    $this->httpClient = $httpClient;
  }

  // Get Network Selection
  public function NetworkSelection()
  {
    $selection = $this->config['selection'];
    if(strpos($selection, 'IVAO') !== false) {
      $result = 'IVAO';
    }
    if(strpos($selection, 'VATSIM') !== false) {
      $result = 'VATSIM';
    }
    return $result;
  }

  // Download and Save/Update WhazzUp Data
  public function DownloadWhazzUp($network_selection = 'IVAO')
  {
    if ($network_selection === 'VATSIM') {
      $server_address = 'https://data.vatsim.net/v3/vatsim-data.json';
    } else {
      $server_address = 'https://api.ivao.aero/v2/tracker/whazzup';
    }

    try {
      $response = $this->httpClient->request('GET', $server_address);
      if ($response->getStatusCode() !== 200) {
        Log::error('Disposable Tools: HTTP '.$response->getStatusCode().' Error Occured During WhazzUp Download !');
      }
    } catch (GuzzleException $e) {
      Log::error('Disposable Tools: WhazzUp Download Error | '.$e->getMessage());
    }

    $whazzupdata = json_decode($response->getBody());

    if ($network_selection === 'VATSIM') {
      $whazzup_sections = array(
        'network'      => $network_selection,
        'pilots'       => serialize($whazzupdata->pilots),
        'atcos'        => serialize($whazzupdata->controllers),
        'servers'      => serialize($whazzupdata->servers),
        'rawdata'      => serialize($whazzupdata),
      );
    } else {
      $whazzup_sections = array(
        'network'      => $network_selection,
        'pilots'       => serialize($whazzupdata->clients->pilots),
        'atcos'        => serialize($whazzupdata->clients->atcs),
        'observers'    => serialize($whazzupdata->clients->observers),
        'servers'      => serialize($whazzupdata->servers),
        'voiceservers' => serialize($whazzupdata->voiceServers),
        'rawdata'      => serialize($whazzupdata),
      );
    }

    return Disposable_WhazzUp::updateOrCreate(['network' => $network_selection], $whazzup_sections);
  }

  // Get Network Users
  public function NetworkUsersArray()
  {
    $userfield = UserField::where('name', $this->config['selection'])->first();
    if (!$userfield) {
      return null;
    }
    $networkusers = UserFieldValue::where('user_field_id', $userfield->id)->whereNotNull('value')->get();
    if (!$networkusers) {
      return null;
    }
    $networkusers = $networkusers->pluck('value')->all();
    return $networkusers;
  }

  // Get Airline Codes
  public function AirlinesArray()
  {
    $airlines = Airline::where('active', 1)->get();
    $airlines = $airlines->pluck('icao')->all();
    return $airlines;
  }

  // Find The User
  public function FindUser($networkid = null)
  {
    if($networkid) {
      $user = UserFieldValue::where('value', $networkid)->first();
      $user = $user->user;
      return $user;
    }
  }

  // Find User's Active Pirep
  public function FindActivePirep($userid = null)
  {
    if($userid) {
      $pirep = Pirep::where('user_id', $userid)->where('state', 0)->orderby('updated_at', 'desc')->first();
      return $pirep;
    }
  }

  // Main Widget Code
  public function run()
  {
    $widget_selection = $this->NetworkSelection();

    if ($widget_selection === 'VATSIM') {
      $network_selection = 'VATSIM';
      $refresh_interval = 120;
      $user_field = 'cid';
    } else {
      $network_selection = 'IVAO';
      $refresh_interval = 60;
      $user_field = 'userId';
    }
    $error = null;
    $pilots = null;
    $dltime = null;
    $widgetdata = null;

    $whazzup = Disposable_WhazzUp::where('network', $network_selection)->first();

    if (!$whazzup || $whazzup->updated_at->diffInSeconds() > $refresh_interval) {
      $refresh_check = true;
      $error = 'No Valid Data Found';
      $this->DownloadWhazzUp($network_selection);
    }

    if($whazzup) {
      if (isset($refresh_check)) {
        $whazzup->refresh();
      }
      $pilots = collect(unserialize($whazzup->pilots));
      $pilots = $pilots->whereIn($user_field, $this->NetworkUsersArray());
      $dltime = isset($whazzup->updated_at) ? $whazzup->updated_at : null;

      $widgetdata = collect();
      foreach ($pilots as $pilot) {
        $user = $this->FindUser($pilot->$user_field);
        $pirep = $this->FindActivePirep($user->id);
        $airline_icao = substr($pilot->callsign,0,3);
        $airline = in_array($airline_icao, $this->AirlinesArray());
        $widgetdata[] = array(
          'user_id'      => $user->id,
          'name'         => $user->name,
          'name_private' => $user->name_private,
          'network_id'   => isset($pilot->userId) ? $pilot->userId : $pilot->cid,
          'callsign'     => $pilot->callsign,
          'server_name'  => isset($pilot->serverId) ? $pilot->serverId : $pilot->server,
          'online_time'  => isset($pilot->time) ? ceil($pilot->time/60): Carbon::parse($pilot->logon_time)->diffInMinutes(),
          'pirep'        => $pirep,
          'airline'      => $airline,
        );
      }
    }

    return view('DisposableTools::whazzup',[
      'pilots'  => $widgetdata,
      'error'   => $error,
      'network' => $network_selection,
      'dltime'  => $dltime,
      ]
    );
  }
}
