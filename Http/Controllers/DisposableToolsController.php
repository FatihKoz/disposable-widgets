<?php

namespace Modules\DisposableTools\Http\Controllers;

use App\Contracts\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Laracasts\Flash\Flash;

class DisposableToolsController extends Controller
{
  // Admin Page
  public function admin(Request $request) { 

    if($request->input('action') === 'whazzup') {
      $network_selection = $request->input('network');
      if ($network_selection === 'IVAO') {
        $key_fn = 'dtools.whazzup_ivao_fieldname';
        $key_ri = 'dtools.whazzup_ivao_refresh'; 
      } elseif ($network_selection === 'VATSIM') {
        $key_fn = 'dtools.whazzup_vatsim_fieldname';
        $key_ri = 'dtools.whazzup_vatsim_refresh'; 
      } 
      $field_name = $request->input('field_name');
      $refresh_interval = $request->input('refresh_interval');
      $this->ChangeDisposableSettings($key_fn, $field_name);
      $this->ChangeDisposableSettings($key_ri, $refresh_interval);
      Flash::success($network_selection.' WhazzUp Widget Settings Updated');
    }

    return view('DisposableTools::admin'); 
  }

  public function ChangeDisposableSettings($key_name, $key_value) {
    DB::table('disposable_settings')->upsert([['key' => $key_name, 'value'=> $key_value]],['key'], ['value']);
  }

}
