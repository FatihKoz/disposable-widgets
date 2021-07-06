<?php
use \App\Models\Enums\AircraftState;
use \App\Models\Enums\AircraftStatus;
use \App\Models\Enums\PirepState;
use \App\Models\Enums\UserState;
use \App\Models\SimBrief;
use \Nwidart\Modules\Facades\Module;

// Check Disposable Modules
// Return boolean
if (!function_exists('Dispo_Modules')) {
  function Dispo_Modules($module)
  {
    $module_enabled = false;
    $dispo_module = Module::find($module);
    if($dispo_module) { $module_enabled = $dispo_module->isEnabled(); }
    return $module_enabled;
  }
}

// Decode Day Of Flights
// Return string
if (!function_exists('Dispo_FlightDays')) {
  function Dispo_FlightDays($flight_days)
  {
    $days = array();
    for($i=0;$i<7;$i++) {
      if($flight_days & pow(2,$i)) {
        $days[]=jddayofweek($i,1);
      }
    }
    $result = implode(",",$days);
    return $result;
  }
}

// Format Pirep State Badge
// Return formatted string (with html tags)
if (!function_exists('Dispo_PirepBadge')) {
  function Dispo_PirepBadge($pirepstate)
  {
    $color = 'primary';
    if($pirepstate === 0 || $pirepstate === 5) { $color = 'info'; }
    elseif($pirepstate === 1) { $color = 'secondary'; }
    elseif($pirepstate === 2) { $color = 'success'; }
    elseif($pirepstate === 3) { $color = 'warning'; }
    elseif($pirepstate === 4 || $pirepstate === 6) { $color = 'danger'; }

    $result = "<span class='badge badge-".$color."'>".PirepState::label($pirepstate)."</span>";
    return $result;
  }
}

// Format User State Badge
// Return formatted string (with html tags)
if (!function_exists('Dispo_UserStateBadge')) {
  function Dispo_UserStateBadge($state)
  {
    $color = 'primary';
    if($state === 0) { $color = 'secondary'; }
    elseif($state === 1) { $color = 'success'; }
    elseif($state === 3) { $color = 'warning'; }
    elseif($state === 2 || $state === 4 || $state === 5) { $color = 'danger'; }

    $result = "<span class='badge badge-".$color."'>".UserState::label($state)."</span>";
    return $result;
  }
}

// Format Aircraft State Badge
// return formatted string (with html tags)
if (!function_exists('Dispo_AcStateBadge')) {
  function Dispo_AcStateBadge($state, $aircraft_id = null)
  {
    $color = 'primary';
    if($state === 0) { $color = 'success'; }
    elseif($state === 1) { $color = 'info'; }
    elseif($state === 2) { $color = 'warning'; }

    $result = "<span class='badge badge-".$color."'>".AircraftState::label($state)."</span>";

    // See if this aircraft is being used by some user's active simbrief ofp
    if (setting('simbrief.block_aircraft') && $aircraft_id && $state === 0) {
      $simbrief_book = SimBrief::where('aircraft_id', $aircraft_id)->whereNotNull('flight_id')->whereNull('pirep_id')->orderby('created_at', 'desc')->first();
      if (!empty($simbrief_book)) {
        $result = "<span class='badge badge-secondary' title='Booked By: ".$simbrief_book->user->name_private."'>Booked</span>";
      }
    }

    return $result;
  }
}

// Format Aircraft Status Badge
// return formatted string (with html tags)
if (!function_exists('Dispo_AcStatusBadge')) {
  function Dispo_AcStatusBadge($status)
  {
    $color = 'primary';
    if($status === 'A') { $color = 'success'; }
    elseif($status === 'S' || $status === 'R') { $color = 'warning'; }
    elseif($status === 'C' || $status === 'W') { $color = 'danger'; }

    $result = "<span class='badge badge-".$color."'>".AircraftStatus::label($status)."</span>";
    return $result;
  }
}

// Format RouteCode
// Return string
if (!function_exists('Dispo_RouteCode')) {
  function Dispo_RouteCode($route_code)
  {
    if(Dispo_Modules('DisposableTours'))
    {
      $route_code = Dispo_TourName($route_code);
      return $route_code;
    }
    if($route_code === 'H'){ $route_code = 'Historic' ;}
    // You can add more text for your own codes like below
    // elseif($route_code === 'AJ'){ $route_code = 'AnadoluJet' ;}
    return $route_code;
  }
}

// Format Runway Data
// Return string (with html tags)
if (!function_exists('Dispo_Runway')) {
  function Dispo_Runway($runway)
  {
    $unit = setting('units.distance');
    $runway_lenght = number_format($runway->lenght)." m";
    if($unit === 'mi'|| $unit === 'nmi')
    {
      $runway_lenght = number_format($runway->lenght * 3.28084)." ft";
    }

    $runway_data = $runway->runway_ident." : ".$runway_lenght;

    if($runway->ils_freq && $runway->loc_course)
    {
      $runway_data = $runway_data." > ".$runway->ils_freq." Mhz ".$runway->loc_course."&deg;";
    }

    return $runway_data;
  }
}

// Generic Value Rounder with Weight Converter
// Conversion part designed for SimBrief Briefing Blade
// Return Numeric String
if (!function_exists('Dispo_Round')) {
  function Dispo_Round($value, $round, $conv = null)
  {
    if($conv === 'lbs') { $value = $value * 2.20462262185; }
    elseif($conv === 'kg') { $value = $value / 2.20462262185; }
    elseif($conv === 'kgs') { $value = $value / 2.20462262185; }
    $value = floatval($value);
    $rmv = fmod($value,$round);
    $rmv = $round - $rmv;
    $rounded = $value + $rmv;
    return $rounded;
  }
}

// Generic Fuel Cost Converter
// Return formatted string
if (!function_exists('Dispo_FuelCost')) {
  function Dispo_FuelCost($cost)
  {
    $unit = setting('units.fuel');
    $currency = setting('units.currency');
    if($unit === 'kg') { $cost = $cost / 2.20462262185; }
    $cost = number_format($cost,3)." ".$currency."/".$unit;
    return $cost;
  }
}

// Generic Fuel Weight Converter
// Return formatted string
if (!function_exists('Dispo_Fuel')) {
  function Dispo_Fuel($fuel)
  {
    $unit = setting('units.fuel');
    if($unit === 'kg') { $fuel = $fuel / 2.20462262185; }
    $fuel = number_format($fuel)." ".setting('units.fuel');
    return $fuel;
  }
}

// Generic Weight Converter
// Return formatted string
if (!function_exists('Dispo_Weight')) {
  function Dispo_Weight($weight)
  {
    $unit = setting('units.weight');
    if($unit === 'kg') { $weight = $weight / 2.20462262185; }
    $weight = number_format($weight)." ".setting('units.weight');
    return $weight;
  }
}

// Generic Distance Converter
// Return formatted string
if (!function_exists('Dispo_Distance')) {
  function Dispo_Distance($distance)
  {
    $unit = setting('units.distance');
    if($unit === 'km') { $distance = $distance * 1.852; }
    elseif($unit === 'mi') { $distance = $distance * 1.15078; }
    $distance = number_format($distance)." ".$unit;
    return $distance;
  }
}

// Generic Altitude Converter
// Return formatted string
if (!function_exists('Dispo_Altitude')) {
  function Dispo_Altitude($altitude)
  {
    $unit = setting('units.altitude');
    if($unit === 'm') { $altitude = $altitude / 3.280840; }
    $altitude = number_format($altitude)." ".$unit;
    return $altitude;
  }
}

// Tankering Possibility Check
// Return formatted string (with html tags)
if (!function_exists('Dispo_Tankering')) {
  function Dispo_Tankering($flight,$aircraft,$diff = 0.85)
  {
    $fueltype = $aircraft->subfleet->fuel_type;

    $result_ok = "<h6 class='mt-1 p-1'><span class='badge badge-success float-left'>".__('disposable.tankerok')."</span></h6>";
    $result_not = "<h6 class='mt-1 p-1'><span class='badge badge-danger float-left'>".__('disposable.tankernot')."</span></h6>";
    $result_skip = "<h6 class='mt-1 p-1'><span class='badge badge-warning float-left'>".__('disposable.tankerskip')."</span></h6>";

    if($fueltype !== 1) {
      return $result_skip;
    }

    if($fueltype === 1) {
      if($flight->dpt_airport && $flight->dpt_airport->fuel_jeta_cost) {
        $depcost = $flight->dpt_airport->fuel_jeta_cost;
      } else {
        $depcost = setting('airports.default_jet_a_fuel_cost');
      }
      if($flight->arr_airport && $flight->arr_airport->fuel_jeta_cost) {
        $arrcost = $flight->arr_airport->fuel_jeta_cost;
      } else {
        $arrcost = setting('airports.default_jet_a_fuel_cost');
      }
    }

    if($depcost < ($arrcost * $diff)) {
      return $result_ok;
    } else {
      return $result_not;
    }
  }
}

// Check Pirep Field Values And Format Them
// Return formatted string (with html tags)
if (!function_exists('Dispo_PirepFields')) {
  function Dispo_PirepFields($field_slug, $field_value, $aircraft = null)
  {
    $error = null;
    if(is_numeric($field_value)) {
      // Landing Rate
      if($field_slug === 'landing-rate') {
        if($field_value > 0) { $error = " <i class='fas fa-exclamation-triangle ml-2' style='color:firebrick;' title='Positive Landing Rate !'></i>" ;}
        $field_value = number_format($field_value,2)." ft/min".$error;
      }
      // Threshold Distance
      elseif($field_slug === 'threshold-distance') {
        if(setting('units.distance') === 'km' ) {
          $field_value = number_format($field_value / 3.2808)." m".$error;
        } else {
          $field_value = number_format($field_value)." ft".$error;
        }
      }
      // Landing G-Force
      elseif($field_slug === 'landing-g-force') {
        $field_value = number_format($field_value,2)." g".$error;
      }
      // Fuel Values
      elseif(strpos($field_slug, '-fuel') !== false) {
        if(setting('units.fuel') === 'kg') { $field_value = $field_value / 2.204622621; }
        if($field_value < 0) { $error = " <i class='fas fa-exclamation-triangle ml-2' style='color:firebrick;' title='Negative Fuel !'></i>" ;}
        if($field_value <= 10) {
          $field_value = number_format($field_value, 2) ." ". setting('units.fuel').$error;
        } else {
          $field_value = number_format($field_value) ." ". setting('units.fuel').$error;
        }
      }
      // Weight Values
      elseif(strpos($field_slug, '-weight') !== false) {
        if(setting('units.weight') === 'kg') { $field_value = $field_value / 2.204622621; }
        $field_value = number_format($field_value) ." ". setting('units.weight').$error;
      }
      // Pitch, Roll, Heading : Angle
      elseif(strpos($field_slug, 'roll') !== false || strpos($field_slug, 'pitch') !== false || strpos($field_slug, 'heading') !== false) {
        // $field_value = number_format($field_value,2)."&deg;".$error;
        $field_value = $field_value."&deg;".$error;
      }
      // Centerline Deviation : Distance
      elseif(strpos($field_slug, 'centerline-dev') !== false) {
        if(setting('units.distance') === 'km' ) {
          $field_value = number_format(($field_value / 3.2808),2)." m".$error;
        } else {
          $field_value = number_format($field_value,2)." ft".$error;
        }
      }
      // TakeOff and Landing Flaps
      elseif($aircraft && Dispo_Modules('DisposableTech') && strpos($field_slug, '-flaps') !== false) {
        $field_value = Dispo_Flap($aircraft, $field_value);
      }
      // TakeOff and Landing Speeds
      elseif(strpos($field_slug, '-speed') !== false) {
        $field_value = $field_value.' kts';
      }
    }
    // Date/Time Values (not displaying full date on purpose)
    elseif(strpos($field_slug, 'off-time') !== false || strpos($field_slug, 'ing-time') !== false || strpos($field_slug, 'on-time') !== false) {
      $field_value = Carbon::parse($field_value)->format('H:i')." UTC";
    }
    return $field_value;
  }
}

// Array Unique For Multidimensional Arrays
// Return Array
if(!function_exists('Dispo_Array_Unique_MD')) {
  function Dispo_Array_Unique_MD($array, $key) {
    $temp_array = array();
    $i = 0;
    $key_array = array();
    foreach($array as $val) {
      if (!in_array($val[$key], $key_array)) {
        $key_array[$i] = $val[$key];
        $temp_array[$i] = $val;
      }
      $i++;
    }
    return $temp_array;
  }
}

// In Array Function For Multidimensional Arrays
// Return Boolean
if(!function_exists('Dispo_In_Array_MD')) {
  function Dispo_In_Array_MD($needle, $haystack, $strict = false) {
    foreach($haystack as $item) {
      if (($strict ? $item === $needle : $item == $needle) || (is_array($item) && Dispo_In_Array_MD($needle, $item, $strict))) {
        return true;
      }
    }
    return false;
  }
}
