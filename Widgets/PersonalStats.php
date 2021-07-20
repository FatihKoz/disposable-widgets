<?php

namespace Modules\DisposableTools\Widgets;

use App\Contracts\Widget;
use App\Models\Pirep;
use App\Models\Enums\PirepState;
use App\Models\Enums\PirepSource;
use Illuminate\Support\Facades\Auth;
use Carbon;
use Lang;

class PersonalStats extends Widget
{
  protected $config = ['disp' => null, 'user' => null, 'period' => null, 'type' => 'avglanding'];

  public function run() {

    $userid = $this->config['user'] ?? Auth::user()->id;
    $periodtext = null;
    $statname = null;
    $currtime = Carbon::now();
    $curryear = $currtime->format('Y');

    // Define Quarters Of Current Year
    if($this->config['period'] === 'q1') {
      $quarter_start = $curryear.'-01-01 00:00:01';
      $quarter_end = $curryear.'-03-31 23:59:59';
    }
    elseif($this->config['period'] === 'q2') {
      $quarter_start = $curryear.'-04-01 00:00:01';
      $quarter_end = $curryear.'-06-30 23:59:59';
    }
    elseif($this->config['period'] === 'q3') {
      $quarter_start = $curryear.'-07-01 00:00:01';
      $quarter_end = $curryear.'-09-30 23:59:59';
    }
    elseif($this->config['period'] === 'q4') {
      $quarter_start = $curryear.'-10-01 00:00:01';
      $quarter_end = $curryear.'-12-31 23:59:59';
    }

    // Define Main Queries
    $manualq = Pirep::where('user_id', $userid)->where('state', PirepState::ACCEPTED);
    $acarsq = Pirep::where('user_id', $userid)->where('state', PirepState::ACCEPTED)->where('source', PirepSource::ACARS);
    // Define Day Queries
    if (is_numeric($this->config['period'])) {
      $currtime = $currtime->subdays($this->config['period']);
      $manualq = $manualq->where('submitted_at','>=', $currtime);
      $acarsq = $acarsq->where('submitted_at','>=', $currtime);
      $periodtext = Lang::get('DisposableTools::common.lastndays', ['period' => $this->config['period']]);
    }
    // Define Quarter, Month And Year Queries
    elseif ($this->config['period'] === 'q1' || $this->config['period'] === 'q2' || $this->config['period'] === 'q3' || $this->config['period'] === 'q4') {
      $manualq = $manualq->whereBetween('submitted_at', [$quarter_start, $quarter_end]);
      $acarsq = $acarsq->whereBetween('submitted_at', [$quarter_start, $quarter_end]);
      $periodtext = $curryear.'/'.strtoupper($this->config['period']);
    }
    elseif ($this->config['period'] === 'currentm') {
      $manualq = $manualq->whereMonth('submitted_at', '=', $currtime->month);
      $acarsq = $acarsq->whereMonth('submitted_at', '=', $currtime->month);
      $periodtext = $currtime->format('F');
    }
    elseif ($this->config['period'] === 'lastm') {
      $currtime = $currtime->subMonthNoOverflow();
      $manualq = $manualq->whereMonth('submitted_at', '=', $currtime->month);
      $acarsq = $acarsq->whereMonth('submitted_at', '=', $currtime->month);
      $periodtext = $currtime->format('F');
    }
    elseif ($this->config['period'] === 'prevm') {
      $currtime = $currtime->subMonthsNoOverflow(2);
      $manualq = $manualq->whereMonth('submitted_at', '=', $currtime->month);
      $acarsq = $acarsq->whereMonth('submitted_at', '=', $currtime->month);
      $periodtext = $currtime->format('F');
    }
    elseif ($this->config['period'] === 'currenty') {
      $manualq = $manualq->whereYear('submitted_at', '=', $currtime->year);
      $acarsq = $acarsq->whereYear('submitted_at', '=', $currtime->year);
      $periodtext = $currtime->format('Y');
    }
    elseif ($this->config['period'] === 'lasty') {
      $currtime = $currtime->subYearNoOverflow();
      $manualq = $manualq->whereYear('submitted_at', '=', $currtime->year);
      $acarsq = $acarsq->whereYear('submitted_at', '=', $currtime->year);
      $periodtext = $currtime->format('Y');
    }

    // Execute Query
    // Average Landing Rate - Acars Only
    if ($this->config['type'] === 'avglanding') {
      $PersonalStats = $acarsq->avg('landing_rate');
    }
    // Average Score - Acars Only
    elseif ($this->config['type'] === 'avgscore') {
      $PersonalStats = $acarsq->avg('score');
    }
    // Average Distance - Acars Only
    elseif ($this->config['type'] === 'avgdistance') {
      $PersonalStats = $acarsq->avg('distance');
    }
    // Total Distance - Acars Only
    elseif ($this->config['type'] === 'totdistance') {
      $PersonalStats = $acarsq->sum('distance');
    }
    // Average Time
    elseif ($this->config['type'] === 'avgtime') {
      $PersonalStats = $manualq->avg('flight_time');
    }
    // Total Time
    elseif ($this->config['type'] === 'tottime') {
      $PersonalStats = $manualq->sum('flight_time');
    }
    // Average Fuel
    elseif ($this->config['type'] === 'avgfuel') {
      $PersonalStats = $manualq->avg('fuel_used');
    }
    // Total Fuel
    elseif ($this->config['type'] === 'totfuel') {
      $PersonalStats = $manualq->sum('fuel_used');
    }
    // Total Flights
    elseif ($this->config['type'] === 'totflight') {
      $PersonalStats = $manualq->count('score');
    }

    // Add Unit Type If Necessary and Format The Result
    if ($this->config['type'] === 'avglanding') {
      $PersonalStats = number_format(round($PersonalStats)) . ' ft/min';
      $statname = Lang::get('DisposableTools::common.avglanding');
    }
    elseif ($this->config['type'] === 'avgscore') {
      $PersonalStats = number_format(round($PersonalStats));
      $statname = Lang::get('DisposableTools::common.avgscore');
    }
    elseif ($this->config['type'] === 'avgtime' || $this->config['type'] === 'tottime') {
      round($PersonalStats);
    }
    elseif ($this->config['type'] === 'avgdistance' || $this->config['type'] === 'totdistance') {
      if(setting('units.distance') === 'km') {
        $PersonalStats = number_format(round($PersonalStats * 1.852)) . ' km';
      } else {
        $PersonalStats = number_format(round($PersonalStats)) . ' nm';
      }
    }
    elseif ($this->config['type'] === 'avgfuel' || $this->config['type'] === 'totfuel') {
      if(setting('units.fuel') === 'kg') {
        $PersonalStats = number_format(round($PersonalStats / 2.20462262185)) . ' kg';
      } else {
        $PersonalStats = number_format(round($PersonalStats)) . ' lbs';
      }
    }

    // Define Text
    if($this->config['type'] === 'avgdistance') {
      $statname = Lang::get('DisposableTools::common.avgdistance');
    }
    elseif($this->config['type'] === 'totdistance') {
      $statname = Lang::get('DisposableTools::common.totdistance');
    }
    elseif($this->config['type'] === 'avgtime') {
      $statname = Lang::get('DisposableTools::common.avgtime');
    }
    elseif($this->config['type'] === 'tottime') {
      $statname = Lang::get('DisposableTools::common.tottime');
    }
    elseif($this->config['type'] === 'avgfuel') {
      $statname = Lang::get('DisposableTools::common.avgfuel');
    }
    elseif($this->config['type'] === 'totfuel') {
      $statname = Lang::get('DisposableTools::common.totfuel');
    }
    elseif($this->config['type'] === 'totflight') {
      $statname = Lang::get('DisposableTools::common.totflight');
    }

    // Return Data To Blade
    return view('DisposableTools::personal_stats', [
      'pstat'   => $PersonalStats,
      'sname'   => $statname,
      'speriod' => $periodtext,
      'config'  => $this->config,
      ]);
  }
}
