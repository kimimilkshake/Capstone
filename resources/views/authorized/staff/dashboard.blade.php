@extends('layouts.app')
@section('page-title', 'DASHBOARD')
@section('content')
    @include('components.authHeader')
    @include('components.staff_nav')

    <!-- Centered Dropdown Success Alert -->
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show text-center" role="alert"
            style="position: fixed; z-index: 9999; top: 80px; left: 0; right: 0; margin-left: auto; margin-right: auto; width: 90%; max-width: 800px; box-shadow: 0 8px 16px rgba(0,0,0,0.2); font-size: 1.1rem; padding: 1.5rem;">
            <button type="button" class="btn-close position-absolute top-0 end-0 m-2" data-bs-dismiss="alert"
                aria-label="Close"></button>
            <i class="fas fa-check-circle mb-2" style="font-size: 3rem; color: #198754;"></i>
            <h5 class="mb-2"><strong>Success!</strong></h5>
            <p class="mb-0">{{ session('success') }}</p>
        </div>
    @endif

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
                <span class="anumberStat">PHP {{ number_format($totalSales, 2) }}</span>
                <p>Total Sales</p>
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
                            <td>{{ $voyage->passenger_tickets_count }}/{{ $voyage->vessel->vessel_total_passenger_capacity }}</td>
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
