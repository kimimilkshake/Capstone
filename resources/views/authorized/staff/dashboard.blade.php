@extends('layouts.app')
@section('page-title', 'DASHBOARD')
@section('content')
    @include('components.authHeader')
    @include('components.staff_nav')

    <div class="staff-body">
        <div class="dashbord-titles">
            <h3 id="dashboard-datetoday">{{ \Carbon\Carbon::now()->format('F d, Y, l') }}</h3>
        </div>
        <div class="astat-boxes-row">
            <div class="astat-boxes-col">
                <span class="anumberStat">{{ $passengerBookings }}</span>
                <p>Passenger Bookings</p>
            </div>
            <div class="astat-boxes-col">
                <span class="anumberStat">{{ $cargoBookings }}</span>
                <p>Cargo Bookings</p>
            </div>
            <div class="astat-boxes-col">
                <span class="anumberStat">₱ XX,XXX.XX</span>
                <p>Passenger Revenue</p>
            </div>
            <div class="astat-boxes-col">
                <span class="anumberStat">₱ XX,XXX.XX</span>
                <p>Cargo Revenue</p>
            </div>
            <div class="astat-boxes-col">
                <span class="anumberStat">₱{{ number_format($totalSales, 2) }}</span>
                <p>Total Revenue</p>
            </div>
        </div>
        <div class="astat-boxes-row">
            <div class="astat-boxes-col">
                <ul>
                    <li>the pie chart of the payment methods will go here</li>
                    <li>it will be multicolored, with each color representing a different payment method</li>
                    <li>green for cash and orange for gcash</li>
                    <li>this portion will also show amount received in cash and amount received in gcash and the total amount</li>
                </ul>
            </div>
            <div class="astat-boxes-col">
                <ul>
                    <li>the bar chart showing the total number of bookings per route will go here</li>
                    <li>x-axis will be the routes, per route so one red and blue for CEBBAY, another red and blue for another route, etc.</li>
                    <li>y-axis will be for the total number of bookings</li>
                    <li>it will be multicolored, red for cargo bookings and blue for passenger bookings</li>
                </ul>
            </div>
        </div>
        <br>
        <div class="astat-voyage">
            <h4>Today's Voyages</h4>
            <table class="voyage-table">
                <thead>
                    <tr>
                        <th>Voyage Code</th>
                        <th>Route</th>
                        <th>Departure Date</th>
                        <th>ETD</th>
                        <th>Arrival Date</th>
                        <th>ETA</th>
                        <th>PAX/CAP</th>
                        <th>Vessel</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($voyages as $voyage)
                        <tr>
                            <td>{{ $voyage->voyage_code }}</td>
                            <td>
                                {{ $voyage->routePort->route_origin }} →
                                {{ $voyage->routePort->route_destination }}
                            </td>
                            <td>{{ \Carbon\Carbon::parse($voyage->voyage_departure_date)->format('M j, Y, D') }}</td>
                            <td>{{ \Carbon\Carbon::parse($voyage->voyage_estimated_TD)->format('g:iA') }}</td>
                            <td>{{ \Carbon\Carbon::parse($voyage->voyage_arrival_date)->format('M j, Y, D') }}</td>
                            <td>{{ \Carbon\Carbon::parse($voyage->voyage_estimated_TA)->format('g:iA') }}</td>
                            <td>{{ $voyage->passenger_tickets_count }}/{{ $voyage->vessel->vessel_total_passenger_capacity }}
                            </td>
                            <td>{{ $voyage->vessel->vessel_name }}</td>
                            <td>{{ $voyage->voyage_status }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center">No voyages for today.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>

    @if (session('success'))
        <script>
            // Auto-dismiss alert after 8 seconds (increased from 5)
            setTimeout(function() {
                var alert = document.querySelector('.alert-success');
                if (alert) {
                    var bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }
            }, 8000);
        </script>
    @endif
@endsection
