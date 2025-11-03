@extends('layouts.app')
@section('page-title', 'MANIFEST')
@section('content')
    @include('components.authHeader')
    @include('components.admin_nav') {{--NAVBAR--}}

<div class="manifest-header text-center">
    <h2 class="manifest-title">Cebu to Talibon, Bohol</h2>
    <div class="d-flex justify-content-center flex-wrap gap-5 mt-3">
        <div class="text-start">
            <p><strong>Schedule :</strong> August 1, 2025 Friday 9pm</p>
            <p><strong>Vessel :</strong> M/V LAPULAPU FERRY 8</p>
        </div>
        <div class="text-start">
            <p><strong>Voyage no :</strong> 6721.05821</p>
            <p><strong>Status :</strong> Pending</p>
        </div>
    </div>
</div>


    <!-- Passenger Manifest -->
    <div class="passenger-manifest mt-5">
        <h4 class="manifest-section-title">Passenger Manifest</h4>
        <table class="manifest-table table table-bordered">
            <thead class="table-primary">
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
        <table class="manifest-table table table-bordered">
            <thead class="table-secondary">
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
@endsection
