@if($bookings->count())
  <div class="card mb-2">
    <div class="card-header p-1">
      <h5 class="m-1 p-0">
        @lang('DisposableTools::common.activeb')
        <i class="fas fa-file-signature float-right"></i>
      </h5>
    </div>
    <div class="card-body p-0">
      <table class="table table-borderless table-sm table-striped mb-0 text-left">
        <tr>
          <th>@lang('common.aircraft')</th>
          <th>@lang('DisposableTools::common.flight')</th>
          <th>@lang('DisposableTools::common.orig')</th>
          <th>@lang('DisposableTools::common.dest')</th>
          <th class="text-center">@lang('DisposableTools::common.pilot')</th>
          {{-- <th class="text-right">@lang('DisposableTools::common.create')</th> --}}
          <th class="text-right">@lang('DisposableTools::common.expire')</th>
        </tr>
        @foreach($bookings as $booking)
          <tr>
            <th>
              <a href="{{ route('DisposableAirlines.daircraft', [$booking->aircraft->registration]) }}" title="{{ $booking->aircraft->icao }}">
                {{ $booking->aircraft->registration }}
              </a>
            </th>
            <th>
              <a href="{{ route('frontend.flights.show', [$booking->flight_id]) }}" title="{{ $booking->flight->ident }}">
                {{ $booking->flight->airline->code.' '.$booking->flight->flight_number }}
              </a>
            </th>
            <td>
              <a href="{{ route('frontend.airports.show', [$booking->flight->dpt_airport_id]) }}" title="{{ $booking->flight->dpt_airport->name ?? '' }}">
                {{ $booking->flight->dpt_airport_id }}
              </a>
            </th>
            <td>
              <a href="{{ route('frontend.airports.show', [$booking->flight->arr_airport_id]) }}" title="{{ $booking->flight->arr_airport->name ?? ''}}">
                {{ $booking->flight->arr_airport_id }}
              </a>
            </th>
            <th class="text-center"><a href="{{ route('frontend.profile.show', [$booking->user_id]) }}">{{ $booking->user->name_private }}</a></th>
            {{-- <td class="text-right">{{ $booking->created_at->diffForHumans() }}</td> --}}
            <td class="text-right">{{ $booking->created_at->addHours(setting('simbrief.expire_hours'))->diffForHumans() }}</td>
          </tr>
        @endforeach
      </table>
    </div>
  </div>
@endif
