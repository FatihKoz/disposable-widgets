<?php

namespace Modules\DisposableTools\Widgets;

use App\Contracts\Widget;
use App\Models\Pirep;
use App\Models\Enums\PirepState;
use Illuminate\Support\Facades\DB;
use Carbon;
use Lang;

class TopAirlines extends Widget
{
  protected $config = ['count' => 3, 'type' => 'flights', 'period' => null];

  public function run()
  {
    $period = null;
    if($this->config['period'])
    {
      $wheretype = 'whereMonth';
      $repdate = Carbon::now();

      if ($this->config['period'] === 'currentm')
      {
        $repsql = $repdate->month;
        $period = $repdate->format('F');
      }

      elseif ($this->config['period'] === 'lastm')
      {
        $repdate = $repdate->subMonthNoOverflow();
        $repsql = $repdate->month;
        $period = $repdate->format('F');
      }

      elseif ($this->config['period'] === 'prevm')
      {
        $repdate = $repdate->subMonthNoOverflow(2);
        $repsql = $repdate->month;
        $period = $repdate->format('F');
      }

      elseif ($this->config['period'] === 'currenty')
      {
        $wheretype = 'whereYear';
        $repsql = $repdate->year;
        $period = $repdate->format('Y');
      }

      elseif ($this->config['period'] === 'lasty')
      {
        $wheretype = 'whereYear';
        $repdate = $repdate->subYearNoOverflow();
        $repsql = $repdate->year;
        $period = $repdate->format('Y');
      }
    }

    if ($this->config['type'] === 'flights')
    {
      $rawsql = DB::raw('count(airline_id) as totals');
      $rtype = trans_choice('common.flight',2);
    }
    elseif ($this->config['type'] === 'distance')
    {
      $rawsql = DB::raw('sum(distance) as totals');
      $rtype = Lang::get('common.distance');
    }
    elseif ($this->config['type'] === 'time')
    {
      $rawsql = DB::raw('sum(flight_time) as totals');
      $rtype = Lang::get('pireps.flighttime');
    }

    if ($this->config['period'])
    {
      $tairlines = Pirep::select('airline_id', $rawsql)
        ->where('state', PirepState::ACCEPTED)
        ->$wheretype('created_at', '=', $repsql)
        ->orderBy('totals', 'desc')
        ->groupBy('airline_id')
        ->take($this->config['count'])
        ->get();
    } else {
      $tairlines = Pirep::select('airline_id', $rawsql)
        ->where('state', PirepState::ACCEPTED)
        ->orderBy('totals', 'desc')
        ->groupBy('airline_id')
        ->take($this->config['count'])
        ->get();
    }

    return view('DisposableTools::top_airlines', [
        'tairlines' => $tairlines,
        'config'    => $this->config,
        'rtype'     => $rtype,
        'rperiod'   => $period,
        ]);
  }
}
