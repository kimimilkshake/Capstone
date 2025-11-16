@extends('layouts.app')
@section('page-title', 'DASHBOARD')
@section('content')
    @include('components.authHeader')
    @include('components.admin_nav') {{--NAVBAR--}}
    <div class="admin-body">
        <div class="dashbord-titles">
            <h3 id="dashboard-datetoday">{{ \Carbon\Carbon::now()->format('F d, Y, l') }}</h3>
        </div>
        <div class="astat-boxes-row">
            <div class="astat-boxes-col">
                <span class="anumberStat">XX</span>
                <p>Passengers</p>
            </div>
            <div class="astat-boxes-col">
                <span class="anumberStat">XX</span>
                <p>Cargo Bookings</p>
            </div>
            <div class="astat-boxes-col">
                <span class="anumberStat">PHP XXXX</span>
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
                        <th>Arrival Date</th>
                        <th>ETD</th>
                        <th>ETA</th>
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
                        <td>{{ \Carbon\Carbon::parse($voyage->voyage_departure_date)->format('F j, Y, D') }}</td>
                        <td>{{ \Carbon\Carbon::parse($voyage->voyage_arrival_date)->format('F j, Y, D') }}</td>
                        <td>{{ \Carbon\Carbon::parse($voyage->voyage_estimated_TD)->format('g:iA') }}</td>
                        <td>{{ \Carbon\Carbon::parse($voyage->voyage_estimated_TA)->format('g:iA') }}</td>
                        <td>{{ $voyage->vessel->vessel_name}}</td>
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
    
    
    
@endsection
