@extends('layouts.app')
@section('content')
    @include('components.hero')

    <div class="trip-booking-container container">
        <div class="row align-items-stretch">

            <!-- Left Side: Available Voyages -->
            <div class="col-lg-6">
                <div class="table-container">
                    <h4 class="text-center text-primary">Available Voyages (Next 8 Days)</h4>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead class="table-dark text-center">
                                <tr>
                                    <th>Date</th>
                                    <th>Route</th>
                                    <th>Departure</th>
                                    <th>Vessel</th>
                                </tr>
                            </thead>
                            <tbody class="text-center">
                                {{-- @var array $voyage --}}
                                @forelse ($voyages as $voyage)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($voyage['departure_date'])->format('M d') }}</td>
                                        <td>{{ $voyage['route_from'] }} - {{ $voyage['route_to'] }}</td>
                                        <td>{{ \Carbon\Carbon::parse($voyage['departure_time'])->format('h:i A') }}</td>
                                        <td>{{ $voyage['vessel_name'] }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center">
                                            <div class="text-muted">
                                                <i class="bi bi-calendar-x fs-1 d-block"></i>
                                                No voyages available in the next 8 days
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>

            <!-- Right Side: Booking Selection -->
            <div class="col-lg-6">
                <div class="booking-selection-content d-flex flex-column">

                    <div class="text-center">
                        <h4 class="text-primary">Select Route & Schedule</h4>
                    </div>

                    <!-- Booking Type -->
                    <div class="booking-type-section">
                        <h6 class="text-secondary">Booking Type</h6>

                        <div class="booking-type-options">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="bookingType" id="passengerType"
                                    value="passenger" checked>
                                <label class="form-check-label" for="passengerType">Passenger</label>
                            </div>

                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="bookingType" id="cargoType"
                                    value="cargo">
                                <label class="form-check-label" for="cargoType">Cargo</label>
                            </div>
                        </div>
                    </div>

                    <!-- Route Selection -->
                    <div class="route-selection">
                        <div class="route-inputs">
                            <div class="route-input-group">
                                <label class="form-label">From</label>
                                <select id="routeFrom" class="form-select">
                                    <option value="">Select Origin</option>
                                    @foreach (collect($voyages)->pluck('route_from')->unique() as $origin)
                                        <option value="{{ $origin }}">{{ $origin }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="route-arrow">
                                <i class="bi bi-arrow-right fs-4 text-primary"></i>
                            </div>

                            <div class="route-input-group">
                                <label class="form-label">To</label>
                                <select id="routeTo" class="form-select" disabled>
                                    <option value="">Select Destination</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Date and Time Selection -->
                    <div class="date-time-selection">
                        <div class="datetime-inputs">
                            <div class="datetime-input-group">
                                <label class="form-label">Departure Date</label>
                                <div class="calendar-input-wrapper">
                                    <span class="calendar-icon"><i class="bi bi-calendar-event"></i></span>
                                    <input type="date" id="tripDate" class="calendar-input form-control" disabled>
                                </div>
                            </div>

                            <div class="datetime-input-group">
                                <label class="form-label">Departure Time</label>
                                <select id="departureTime" class="form-select" disabled>
                                    <option value="">Select Time</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Spacer to push button to bottom -->
                    <div class="spacer"></div>

                    <!-- Proceed Button -->
                    <button id="proceedBtn" class="proceed-btn btn btn-primary btn-lg" type="button"
                        data-passenger-url="{{ route('passengerbooking') }}" data-cargo-url="{{ route('cargobooking') }}"
                        disabled>
                        <i class="bi bi-arrow-right-circle me-2"></i>PROCEED
                    </button>

                </div>
            </div>

        </div>
    </div>

    <script type="application/json" id="voyages-data">{!! json_encode($voyages) !!}</script>
    <script src="{{ asset('js/bookingtype.js') }}"></script>
@endsection
