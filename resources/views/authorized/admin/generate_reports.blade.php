@extends('layouts.app')
@section('page-title', 'GENERATE REPORTS')

@section('content')
@include('components.authHeader')
@include('components.admin_nav')

<div class="admin-body">

    <!-- FILTERS -->
    <div class="reports-filter-row">
        <form method="GET" class="reports-filter-form">

            <!-- DATE FROM --> 
            <div class="reports-filter-group"> 
                <label class="reports-filter-label">From</label> 
                <input type="date" name="start_date" value="{{ request('start_date') }}" class="reports-filter-input"> 
            </div> 
            <!-- DATE TO --> 
            <div class="reports-filter-group"> 
                <label class="reports-filter-label">To</label> 
                <input type="date" name="end_date" value="{{ request('end_date') }}" class="reports-filter-input"> 
            </div>

            <!-- ROUTE --> 
            <div class="reports-filter-group"> 
                <label class="reports-filter-label">Route</label> 
                <select name="route" class="reports-filter-select"> 
                    <option value="" {{ $selectedRoute === null || $selectedRoute === '' ? 'selected' : '' }}> All Routes </option> 
                    @foreach($routes as $route) 
                    <option value="{{ $route->route_port_id }}" {{ $selectedRoute == $route->route_port_id ? 'selected' : '' }}> {{ $route->route_origin }} - {{ $route->route_destination }} </option> 
                    @endforeach 
                    </select> 
                </div> 
                <!-- BOOKING TYPE --> 
                <div class="reports-filter-group"> 
                    <label class="reports-filter-label">Booking Type</label> 
                    <select name="booking_type" class="reports-filter-select"> 
                        <option value="" {{ $selectedBookingType === null || $selectedBookingType === '' ? 'selected' : '' }}>All Booking Types</option> 
                        <option value="cargo" {{ $selectedBookingType == 'cargo' ? 'selected' : '' }}>Cargo</option> 
                        <option value="passenger" {{ $selectedBookingType == 'passenger' ? 'selected' : '' }}>Passenger</option> 
                    </select> 
                </div>

            <!-- GENERATE BUTTON --> 
            <div class="reports-filter-actions"> 
                <button type="submit" class="reports-btn-generate"> 
                    <i class="fa-solid fa-file-lines"></i> Generate 
                </button> 
            </div> 
            
            <!-- PRINT BUTTON --> 
            <div class="reports-filter-actions"> 
                <button type="button" class="reports-btn-print"> 
                    <i class="fa-solid fa-print"></i> 
                </button> 
            </div>
        </form>
    </div>

    <!-- TITLE -->
    @php
        $bookingLabel = $isPassenger ? 'Passenger Booking Type' :
                        ($isCargo ? 'Cargo Booking Type' : 'All Booking Type');

        if ($selectedRoute) {
            $routeObj = $routes->firstWhere('route_port_id', $selectedRoute);
            $routeLabel = $routeObj
                ? $routeObj->route_origin . ' - ' . $routeObj->route_destination
                : 'Unknown Route';
        } else {
            $routeLabel = 'All Routes';
        }

        $fromDate = $startDate ? \Carbon\Carbon::parse($startDate)->format('m/d/Y') : 'Start Date';
        $toDate = $endDate ? \Carbon\Carbon::parse($endDate)->format('m/d/Y') : 'End Date';
    @endphp

    <h3 class="report-title">
        {{ $bookingLabel }} Data for {{ $routeLabel }} from {{ $fromDate }} to {{ $toDate }}
    </h3>

    <!-- FIRST ROW -->
    <div class="reports-row">

        @if(!$isPassenger && !$isCargo)
            <div class="stat-box">
                <span class="anumberStat">XXX</span>
                Passenger Bookings
            </div>
            <div class="stat-box">
                <span class="anumberStat">XXX</span>
                Cargo Bookings
            </div>
            <div class="stat-box">
                <span class="anumberStat">₱ XX,XXX.XX</span>
                Passenger Revenue
            </div>
            <div class="stat-box">
                <span class="anumberStat">₱ XX,XXX.XX</span>
                Cargo Revenue
            </div>
            <div class="stat-box">
                <span class="anumberStat">₱ XXX,XXX.XX</span>
                Total Revenue
            </div>

        @elseif($isPassenger)
            <div class="stat-box">
                <span class="anumberStat">XXX</span>
                Passenger Bookings
            </div>
            <div class="stat-box">
                <span class="anumberStat">₱ XX,XXX.XX</span>
                Passenger Revenue
            </div>

        @elseif($isCargo)
            <div class="stat-box">
                <span class="anumberStat">XXX</span>
                Cargo Bookings
            </div>
            <div class="stat-box">
                <span class="anumberStat">₱ XX,XXX.XX</span>
                Cargo Revenue
            </div>
        @endif

    </div>

    <!-- SECOND ROW -->
    <div class="reports-row">

        <!-- PAYMENT -->
        <div class="report-box">
            Payment Methods (Pie Chart)
        </div>

        <!-- CHART -->
        <div class="report-box">
            <div class="chart-header">
                <select>
                    <option>Bookings</option>
                    <option>Revenue</option>
                </select>
            </div>

            @if($isAllRoutes)
                <p>Bar Chart (per route)</p>
            @else
                <p>Line Chart (past N days)</p>
            @endif
        </div>

        <!-- TOP CARGO -->
        @if(!$isPassenger)
            <div class="report-box">
                Top 10 Cargo Items
            </div>
        @endif

    </div>
    
    <!-- VOYAGE TABLE -->
    <div class="astat-voyage mt-4">
        <h4>Voyages</h4>
        <table class="reports-table">
            <thead>
                <tr>
                    <th>Voyage Code</th>
                    <th>Route</th>
                    <th>Vessel</th>
                    <th>Departure Date</th>
                    <th>ETD</th>
                    <th>Arrival Date</th>
                    <th>ETA</th>
                    @if(!$isCargo) <th>PAX/CAP</th> @endif
                    @if(!$isPassenger) <th>SKS</th><th>VAR</th><th>MC</th> @endif
                    @if($isPassenger) <th>Passenger Revenue</th> @endif
                    @if($isCargo) <th>Cargo Revenue</th> @endif
                    @if(!$isPassenger && !$isCargo) 
                        <th>Passenger Revenue</th>
                        <th>Cargo Revenue</th>
                    @endif
                    <th>Total Revenue</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($voyages as $voyage)
                    <tr>
                        <td>{{ $voyage->voyage_code }}</td>
                        <td>{{ $voyage->routePort->route_origin }} → {{ $voyage->routePort->route_destination }}</td>
                        <td>{{ $voyage->vessel->vessel_code }}</td>
                        <td>{{ $voyage->voyage_actual_departure_date ? \Carbon\Carbon::parse($voyage->voyage_actual_departure_date)->format('M j, Y, D') : '-' }}</td>
                        <td>{{ $voyage->voyage_actual_TD ? \Carbon\Carbon::parse($voyage->voyage_actual_TD)->format('g:iA') : '-' }}</td>
                        <td>{{ $voyage->voyage_actual_arrival_date ? \Carbon\Carbon::parse($voyage->voyage_actual_arrival_date)->format('M j, Y, D') : '-' }}</td>
                        <td>{{ $voyage->voyage_actual_TA ? \Carbon\Carbon::parse($voyage->voyage_actual_TA)->format('g:iA') : '-' }}</td>

                        @if(!$isCargo)
                            <td>{{ $voyage->passenger_tickets_count }}/{{ $voyage->vessel->vessel_total_passenger_capacity }}</td> <!`-- PAX/CAP -->
                        @endif

                        @if(!$isPassenger)
                            <td>-</td>  <!-- SKS -->
                            <td>-</td>  <!-- VAR -->
                            <td>-</td>  <!-- MC -->
                        @endif

                        @if($isPassenger)
                            <td>-</td> <!-- Passenger Revenue -->
                        @endif

                        @if($isCargo)
                            <td>-</td> <!-- Cargo Revenue -->
                        @endif

                        @if(!$isPassenger && !$isCargo)
                            <td>-</td> <!-- Passenger Revenue -->
                            <td>-</td> <!-- Cargo Revenue -->
                        @endif

                        <td>-</td>
                        <td>{{ $voyage->voyage_status }}</td> <!- Status -->
                    </tr>
                @empty
                    <tr>
                        <td colspan="14" class="text-center">No voyages for selected filters.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="mt-3">
            {{ $voyages->links('pagination::bootstrap-5') }}
        </div>
    </div>

</div>
@endsection