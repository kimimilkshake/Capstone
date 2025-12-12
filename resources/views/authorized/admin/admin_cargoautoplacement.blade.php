@extends('layouts.app')
@section('page-title', 'Cargo Auto Placement')
@section('content')
    @include('components.authHeader')
    @include('components.admin_nav')

<div class="admin-body">
    <h3 class="text-center mb-4">Cargo Auto Placement</h3>

    <div class="scs-form_container">
        <form method="GET" action="{{ route('admin.cargo.placement') }}" class="mb-4">
            <div class="form-group">
                <label for="voyage_id">Select Voyage:</label>
                <select name="voyage_id" id="voyage_id" class="form-control" onchange="this.form.submit()">
                    <option value="">-- Select a Voyage --</option>
                    @foreach($voyages as $voyage)
                        <option value="{{ $voyage->voyage_id }}" {{ $selectedVoyageId == $voyage->voyage_id ? 'selected' : '' }}>
                            {{ $voyage->voyage_code }} - 
                            {{ $voyage->routePort->route_origin }} → {{ $voyage->routePort->route_destination }} 
                            ({{ \Carbon\Carbon::parse($voyage->voyage_departure_date)->format('M j, Y') }})
                        </option>
                    @endforeach
                </select>
            </div>
        </form>

        @if($errors->any())
            <div class="alert alert-danger">
                @foreach($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        @if($selectedVoyageId && $placementData)
            @if(isset($placementData['error']))
                <div class="alert alert-warning">{{ $placementData['error'] }}</div>
            @else
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5>Voyage Information</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Voyage Code:</strong> {{ $placementData['voyage']->voyage_code }}</p>
                        <p><strong>Vessel:</strong> {{ $placementData['voyage']->vessel->vessel_name }}</p>
                        <p><strong>Route:</strong> {{ $placementData['voyage']->routePort->route_origin }} → {{ $placementData['voyage']->routePort->route_destination }}</p>
                        <p><strong>Total Hatches:</strong> {{ $placementData['hatches']->count() }}</p>
                        <p><strong>Total Cargo Items:</strong> {{ $placementData['cargoReceipts']->count() }}</p>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header bg-secondary text-white">
                        <h5>Hatch Specifications</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Hatch</th>
                                    <th>Length (m)</th>
                                    <th>Width (m)</th>
                                    <th>Height (m)</th>
                                    <th>Weight Capacity (kg)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($placementData['hatches'] as $hatch)
                                    <tr>
                                        <td>{{ $hatch->hatch_label }}</td>
                                        <td>{{ $hatch->hatch_length }}</td>
                                        <td>{{ $hatch->hatch_width }}</td>
                                        <td>{{ $hatch->hatch_height }}</td>
                                        <td>{{ $hatch->hatch_weight_capacity }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header bg-info text-white">
                        <h5>Cargo Items</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Receipt ID</th>
                                    <th>Booking Ref</th>
                                    <th>Item Description</th>
                                    <th>Qty</th>
                                    <th>L × W × H (m)</th>
                                    <th>Weight (kg)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($placementData['cargoReceipts'] as $receipt)
                                    @php
                                        $booking = \App\Models\CargoBooking::where('booking_ref_no', $receipt->booking_ref_no)->first();
                                    @endphp
                                    <tr>
                                        <td>{{ $receipt->cargo_receipt_id }}</td>
                                        <td>{{ $receipt->booking_ref_no }}</td>
                                        <td>{{ $receipt->cargoItem->cargo_item_description ?? 'N/A' }}</td>
                                        <td>{{ $receipt->cargo_item_qty ?? 1 }}</td>
                                        <td>
                                            @if($booking)
                                                {{ $booking->length }} × {{ $booking->width }} × {{ $booking->height }}
                                            @else
                                                No dimensions
                                            @endif
                                        </td>
                                        <td>{{ $booking->weight ?? 'N/A' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.cargo.place') }}">
                    @csrf
                    <input type="hidden" name="voyage_id" value="{{ $selectedVoyageId }}">
                    <button type="submit" class="btn btn-success btn-lg btn-block">
                        <i class="fas fa-box-open"></i> Calculate Auto Placement
                    </button>
                </form>
            @endif
        @endif

        @if(session('placement_results'))
            <div class="card mt-4">
                <div class="card-header bg-success text-white">
                    <h5>Placement Results</h5>
                </div>
                <div class="card-body">
                    @foreach(session('placement_results') as $index => $hatchResult)
                        <div class="mb-4">
                            <h6>{{ $hatchResult['hatch']->hatch_label }}</h6>
                            
                            @if(isset($hatchResult['error']))
                                <div class="alert alert-danger">{{ $hatchResult['error'] }}</div>
                            @else
                                @php
                                    $result = $hatchResult['result'];
                                    $packedItems = $result['response']['packed_items'] ?? [];
                                    $unpackedItems = $result['response']['unpacked_items'] ?? [];
                                @endphp
                                
                                <p><strong>Packed Items:</strong> {{ count($packedItems) }}</p>
                                <p><strong>Unpacked Items:</strong> {{ count($unpackedItems) }}</p>
                                
                                @if(!empty($packedItems))
                                    <table class="table table-sm table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Item ID</th>
                                                <th>Position (x, y, z)</th>
                                                <th>Dimensions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($packedItems as $item)
                                                <tr>
                                                    <td>{{ $item['id'] ?? 'N/A' }}</td>
                                                    <td>{{ $item['x'] ?? 0 }}, {{ $item['y'] ?? 0 }}, {{ $item['z'] ?? 0 }}</td>
                                                    <td>{{ $item['w'] ?? 0 }} × {{ $item['h'] ?? 0 }} × {{ $item['d'] ?? 0 }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                @endif
                            @endif
                        </div>
                    @endforeach

                    @if(session('remaining_items') && count(session('remaining_items')) > 0)
                        <div class="alert alert-warning">
                            <strong>Warning:</strong> {{ count(session('remaining_items')) }} items could not be placed in any hatch.
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
