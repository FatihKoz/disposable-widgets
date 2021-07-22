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
class WhazzUpVATSIM extends Widget
{
  // Prepare Guzzle
  public function __construct(GuzzleClient $httpClient)
  {
    $this->httpClient = $httpClient;
  }

  // Main Widget Code
  public function run()
  {
    // Network Specific Definitions
    $network_selection = 'VATSIM';
    $user_field = 'cid';
    $server_address = 'https://data.vatsim.net/v3/vatsim-data.json';

    // Get Settings From DB
    if (Dispo_Settings('dtools.whazzup_vatsim_fieldname')) {
      $field_name = Dispo_Settings('dtools.whazzup_vatsim_fieldname');
    } else {
      $field_name = 'VATSIM';
    }
    if (Dispo_Settings('dtools.whazzup_vatsim_refresh')) {
      $refresh_interval = Dispo_Settings('dtools.whazzup_vatsim_refresh');
      if ($refresh_interval < 15) { $refresh_interval = 15; }
    } else {
      $refresh_interval = 60;
    }

    // Define Basic Variables as NULL
    $error = null;
    $pilots = null;
    $dltime = null;
    $widgetdata = null;

    $whazzup = Disposable_WhazzUp::where('network', $network_selection)->first();

    if (!$whazzup || $whazzup->updated_at->diffInSeconds() > $refresh_interval) {
      $refresh_check = true;
      $error = 'No Valid Data Found';
      $this->DownloadWhazzUp($network_selection, $server_address);
    }

    if($whazzup) {
      if (isset($refresh_check)) {
        $whazzup->refresh();
      }
      $pilots = collect(json_decode($whazzup->pilots));
      $pilots = $pilots->whereIn($user_field, $this->NetworkUsersArray($field_name));
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
          'network_id'   => $pilot->cid,
          'callsign'     => $pilot->callsign,
          'server_name'  => $pilot->server,
          'online_time'  => Carbon::parse($pilot->logon_time)->diffInMinutes(),
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

  // Get Network Users
  public function NetworkUsersArray($field_name = null)
  {
    if (!$field_name) { return null; }
    $userfield = UserField::where('name', $field_name)->first();
    if (!$userfield) { return null; }
    $networkusers = UserFieldValue::where('user_field_id', $userfield->id)->whereNotNull('value')->get();
    if (!$networkusers) { return null; }
    $networkusers = $networkusers->pluck('value')->all();
    return $networkusers;
  }

  // Find The User
  public function FindUser($network_id = null)
  {
    if (!$network_id) { return null; }
    $user = UserFieldValue::where('value', $network_id)->first();
    $user = $user->user;
    return $user;
  }

  // Find User's Active Pirep
  public function FindActivePirep($user_id = null)
  {
    if (!$user_id) { return null; }
    $pirep = Pirep::where('user_id', $user_id)->where('state', 0)->orderby('updated_at', 'desc')->first();
    return $pirep;
  }

  // Get Airline Codes
  public function AirlinesArray()
  {
    $airlines = Airline::where('active', 1)->get();
    if (!$airlines) { return null; }
    $airlines = $airlines->pluck('icao')->all();
    return $airlines;
  }

  // Download and Update WhazzUp Data with IVAO Specific Sections
  public function DownloadWhazzUp($network_selection = null, $server_address = null)
  {
    if (!$network_selection || !$server_address) { return null; }

    try {
      $response = $this->httpClient->request('GET', $server_address);
      if ($response->getStatusCode() !== 200) {
        Log::error('Disposable Tools: HTTP '.$response->getStatusCode().' Error Occured During WhazzUp Download !');
      }
    } catch (GuzzleException $e) {
      Log::error('Disposable Tools: WhazzUp Download Error | '.$e->getMessage());
    }

    $whazzupdata = json_decode($response->getBody());
    $whazzup_sections = array(
      'network'      => $network_selection,
      'pilots'       => json_encode($whazzupdata->pilots),
      'atcos'        => json_encode($whazzupdata->controllers),
      'servers'      => json_encode($whazzupdata->servers),
      'rawdata'      => json_encode($whazzupdata),
    );

    return Disposable_WhazzUp::updateOrCreate(['network' => $network_selection], $whazzup_sections);
  }

}
