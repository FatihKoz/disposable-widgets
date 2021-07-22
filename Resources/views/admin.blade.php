@extends('admin.app')
@section('title', 'Disposable Tools and Widgets Module')

@section('content')
  <div class="card border-blue-bottom">
    <div class="content">
      <p>This module is designed to provide multiple widgets and some tools for phpVms with basic language support.</p>
      <p><b>Possible options and usage examples explained in the readme and at phpVms forum</b></p>
      <p>&bull; Basic list of the widgets are below;</p>
      <table class="table table-striped text-left" style="width: 96%; margin: 5px;">
        <tr>
          <th>@@widget('Modules\DisposableTools\Widgets\ActiveUsers')</th>
          <td>Provides an Active Users list (Requires sessions to be handled by database)</td>
        </tr>
        <tr>
          <th>@@widget('Modules\DisposableTools\Widgets\AircraftLists', ['type' => 'location'])</th>
          <td>Lists your aircraft counts by their locations or ICAO type codes</td>
        </tr>
        <tr>
          <th>@@widget('Modules\DisposableTools\Widgets\AircraftStats', ['id' => $aircraft->id])</th>
          <td>Provides simple stats for an aircraft (like pirep count, fuel usage, milage etc)</td>
        </tr>
        <tr>
          <th>@@widget('Modules\DisposableTools\Widgets\AirlineStats')</th>
          <td>Provides simple stats for your phpVms (or a specific airline if you have many)</td>
        </tr>
        <tr>
          <th>@@widget('Modules\DisposableTools\Widgets\AirportAircrafts', ['location' => $airport->id])</th>
          <td>Provides a list of the aircrafts at given location</td>
        </tr>
        <tr>
          <th>@@widget('Modules\DisposableTools\Widgets\AirportPireps', ['location' => $airport->id])</th>
          <td>Provides a list of the pireps for given location</td>
        </tr>
        <tr>
          <th>@@widget('Modules\DisposableTools\Widgets\AirportInfo')</th>
          <td>Provides a list of your airports and a link to visit Airport Details page (by Maco / included with his permission)</td>
        </tr>
        <tr>
          <th>@@widget('Modules\DisposableTools\Widgets\FlightTimeMultiplier')</th>
          <td>A JavaScript time multiplier, some VA's give bonus time for events etc.</td>
        </tr>
        <tr>
          <th>@@widget('Modules\DisposableTools\Widgets\PersonalStats', ['disp' => 'full', 'user' => $user->id])</th>
          <td>Provides personal stats for given user, mainly designed for Acars users</td>
        </tr>
        <tr>
          <th>@@widget('Modules\DisposableTools\Widgets\TopAirlines', ['count' => 3, 'type' => 'flights'])</th>
          <td>As the name says it, if you have more than one airline, may be usefull (Supports Period Selection)</td>
        </tr>
        <tr>
          <th>@@widget('Modules\DisposableTools\Widgets\TopAirports', ['count' => 5, 'type' => 'dep'])</th>
          <td>Provides a list of most used airports by departure or arrival</td>
        </tr>
        <tr>
          <th>@@widget('Modules\DisposableTools\Widgets\TopPilots', ['type' => 'landingrate'])</th>
          <td>List your top pilots or best one, now includes Average Landing Rate support (Supports Period Selection)</td>
        </tr>
        <tr>
          <th>@@widget('Modules\DisposableTools\Widgets\SunriseSunset', ['location' => $airport->id])</th>
          <td>Gives times for Sunrise - Sunset and aviation applicable twilight in UTC for given airport</td>
        </tr>
        <tr>
          <th>@@widget('Modules\DisposableTools\Widgets\FlightsMap', ['source' => $hub->id])</th>
          <td>Displays a leaflet map from given flights or user pireps</td>
        </tr>
        <tr>
          <th>@@widget('Modules\DisposableTools\Widgets\ActiveBookings')</th>
          <td>Displays current flight & aircraft bookings done via SimBrief Planning</td>
        </tr>
        <tr>
          <th>@@widget('Modules\DisposableTools\Widgets\WhazzUpIVAO')</th>
          <td>Displays current online IVAO users (with Airline and Pirep Checks)</td>
        </tr>
        <tr>
          <th>@@widget('Modules\DisposableTools\Widgets\WhazzUpVATSIM')</th>
          <td>Displays current online VATSIM users (with Airline and Pirep Checks)</td>
        </tr>
      </table>
      <hr>
      <p><b>Optional Theming</b></p>
      <p>
        Widgets are visually compatible with my themes (Disposable v1-v2 / Bootstrap 4.6 & FontAwesome) by default, but if you wish you can copy the widget blade files to your own theme folder and make visual changes there.
        To do this please create a folder inside your theme folder called <b>modules</b> and another one under it called <b>DisposableTools</b> (case sensetive) then copy blade files 
        you want to edit from <b>phpvms root: modules/DisposableTools/Resources/views/</b> to this new folder.<br>
        Final path will like <b>phpvms root: resources/views/layouts/Your Theme Folder/modules/DisposableTools/name_of_the_file_you_coppied.blade.php</b>
      </p>
      <p>&bull; You can repeat this step for every theme you have if you want customized blades for each of them.</p>
      <hr>
      <p>By <a href="https://github.com/FatihKoz" target="_blank">B.Fatih KOZ</a> &copy; @php echo date('Y'); @endphp</p>
    </div>
  </div>

  <div class="row text-center" style="margin-left:5px; margin-right:5px;">
    <div class="col-sm-12">
      <h5 style="margin:5px; padding:5px;"><b>Admin Functions & Settings</b></h5>
    </div>
  </div>

  <div class="row text-center" style="margin-left:5px; margin-right:5px;">
    <div class="col-sm-4">
      <div class="card border-blue-bottom" style="padding:10px;">
        <b>IVAO WhazzUp Widget Settings</b>
        <br><br>
        <form action="/admin/disposabletools" id="whazzupivao">
          <input type="hidden" name="action" value="whazzup">
          <input type="hidden" name="network" value="IVAO">
          <div class="row text-center">
            <div class="col-sm-12">
              <label for="field_name">IVAO ID Field Name</label>
              <input class="form-control" type="text" id="field_name" name="field_name" placeholder="IVAO" maxlength="20" value="{{ Dispo_Settings('dtools.whazzup_ivao_fieldname') }}">
            </div>
          </div>
          <div class="row text-center">
            <div class="col-sm-12">
              <label for="refresh_interval">Refresh Interval (seconds)</label>
              <input class="form-control" type="number" id="refresh_interval" name="refresh_interval" placeholder="60" min="15" max="1200" value="{{ Dispo_Settings('dtools.whazzup_ivao_refresh') }}">
            </div>
          </div>
          <input type="submit" value="Save Widget Settings">
        </form>
        <br>
        <span class="text-danger"><b>Defaults are IVAO and 60 seconds</b></span>
      </div>
    </div>

    <div class="col-sm-4">   
      <div class="card border-blue-bottom" style="padding:10px;">
        <b>VATSIM WhazzUp Widget Settings</b>
        <br><br>
        <form action="/admin/disposabletools" id="whazzupvatsim">
          <input type="hidden" name="action" value="whazzup">
          <input type="hidden" name="network" value="VATSIM">
          <div class="row text-center">
            <div class="col-sm-12">
              <label for="field_name">VATSIM CID Field Name</label>
              <input class="form-control" type="text" id="field_name" name="field_name" placeholder="VATSIM" maxlength="20" value="{{ Dispo_Settings('dtools.whazzup_vatsim_fieldname') }}">
            </div>
          </div>
          <div class="row text-center">
            <div class="col-sm-12">
              <label for="refresh_interval">Refresh Interval (seconds)</label>
              <input class="form-control" type="number" id="refresh_interval" name="refresh_interval" placeholder="60" min="15" max="1200" value="{{ Dispo_Settings('dtools.whazzup_vatsim_refresh') }}">
            </div>
          </div>
          <input type="submit" value="Save Widget Settings">
        </form>
        <br>
        <span class="text-danger"><b>Defaults are VATSIM and 60 seconds</b></span>
      </div>
    </div>
  </div>
@endsection
