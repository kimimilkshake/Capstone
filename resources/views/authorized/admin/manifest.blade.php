@extends('layouts.app')
@section('page-title', 'MANIFEST')
@section('content')
    @include('components.authHeader')
    @include('components.admin_nav') {{--NAVBAR--}}

    <div class="admin-body">
<div class="manifest-header text-center">
    <h2 class="manifest-title">{{ $voyage->route->route_origin }} to {{ $voyage->route->route_destination }}</h2>
    <div class="d-flex justify-content-center flex-wrap gap-5 mt-3">
        <div class="text-start">
            <p><strong>Schedule :</strong>{{ \Carbon\Carbon::parse($voyage->voyage_departure_date)->format('F j, Y, D') }}</p>
            <p><strong>Vessel :</strong> {{ $voyage->vessel->vessel_name}}</p>
        </div>
        <div class="text-start">
            <p><strong>Voyage no :</strong>{{ $voyage->voyage_code }}</p>
            <p><strong>Status :</strong> {{ $voyage->voyage_status }}</p>
        </div>
    </div>
</div>


    <!-- Passenger Manifest -->
    <div class="passenger-manifest mt-5">
        <h4 class="manifest-section-title">Passenger Manifest</h4>
        <table class="manifest-table">
            <thead>
                <tr>
                    <th>Ticket No</th>
                    <th>Passenger Name</th>
                    <th>Age/Gen</th>
                    <th>Category</th>
                    <th>Accommodation</th>
                    <th>Cabin No</th>
                    <th>Departure</th>
                    <th>Fare</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="8" class="text-center">No data available</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Cargo Manifest -->
    <div class="cargo-manifest mt-5">
        <h4 class="manifest-section-title">Cargo Manifest</h4>
        <table class="manifest-table">
            <thead>
                <tr>
                    <th>B/L No</th>
                    <th>Qty</th>
                    <th>Classification</th>
                    <th>Description</th>
                    <th>Shippers</th>
                    <th>TIN Number</th>
                    <th>Consignees</th>
                    <th>Freight Charge</th>
                    <th>VAT</th>
                    <th>Stamp</th>
                    <th>Total</th>
                    <th>Receipt No.</th>
                    <th>Net Arrastre</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="13" class="text-center">No data available</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
</div>
@endsection
