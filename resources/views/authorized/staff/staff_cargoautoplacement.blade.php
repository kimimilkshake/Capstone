@extends('layouts.app')
@section('page-title', 'Cargo Auto Placement')
@section('content')
    @include('components.authHeader')
    @include('components.staff_nav')

    <div class="staff-body">
        <h3 class="text-center mb-4">Cargo Auto Placement</h3>

        <div class="scs-form_container">
            <form method="GET" action="{{ route('staff.cargo.placement') }}" class="mb-4">
                <div class="form-group">
                    <label for="voyage_id">Select Voyage:</label>
                    <select name="voyage_id" id="voyage_id" class="form-control" onchange="this.form.submit()">
                        <option value="">-- Select a Voyage --</option>
                        @foreach ($voyages as $voyage)
                            <option value="{{ $voyage->voyage_id }}"
                                {{ $selectedVoyageId == $voyage->voyage_id ? 'selected' : '' }}>
                                {{ $voyage->voyage_code }} -
                                {{ $voyage->routePort->route_origin }} → {{ $voyage->routePort->route_destination }}
                                ({{ \Carbon\Carbon::parse($voyage->voyage_departure_date)->format('M j, Y') }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </form>

            @if ($errors->any())
                <div class="alert alert-danger">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            @if ($selectedVoyageId && $placementData)
                @if (isset($placementData['error']))
                    <div class="alert alert-warning">{{ $placementData['error'] }}</div>
                @else
                    <div class="card mb-4">
                        <div class="card-header text-white" style="background-color: #485b8c;">
                            <h5>Voyage Information</h5>
                        </div>
                        <div class="card-body">
                            <p><strong>Voyage Code:</strong> {{ $placementData['voyage']->voyage_code }}</p>
                            <p><strong>Vessel:</strong> {{ $placementData['voyage']->vessel->vessel_name }}</p>
                            <p><strong>Route:</strong> {{ $placementData['voyage']->routePort->route_origin }} →
                                {{ $placementData['voyage']->routePort->route_destination }}</p>
                            <p><strong>Total Hatches:</strong> {{ $placementData['hatches']->count() }}</p>
                            <p><strong>Total Cargo Items:</strong> {{ $placementData['cargoReceipts']->count() }}</p>
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header text-white" style="background-color: #485b8c;">
                            <h5>Hatch Specifications</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered">
                                <thead style="background-color: #485b8c; color: white;">
                                    <tr>
                                        <th
                                            style="background-color: #485b8c; color: white; text-align: center; padding: 12px;">
                                            Hatch</th>
                                        <th
                                            style="background-color: #485b8c; color: white; text-align: center; padding: 12px;">
                                            Length (m)</th>
                                        <th
                                            style="background-color: #485b8c; color: white; text-align: center; padding: 12px;">
                                            Width (m)</th>
                                        <th
                                            style="background-color: #485b8c; color: white; text-align: center; padding: 12px;">
                                            Height (m)</th>
                                        <th
                                            style="background-color: #485b8c; color: white; text-align: center; padding: 12px;">
                                            Weight Capacity (kg)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($placementData['hatches'] as $hatch)
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
                        <div class="card-header text-white" style="background-color: #485b8c;">
                            <h5>Cargo Items</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered">
                                <thead style="background-color: #485b8c; color: white;">
                                    <tr>
                                        <th
                                            style="background-color: #485b8c; color: white; text-align: center; padding: 12px;">
                                            Receipt ID</th>
                                        <th
                                            style="background-color: #485b8c; color: white; text-align: center; padding: 12px;">
                                            Booking Ref</th>
                                        <th
                                            style="background-color: #485b8c; color: white; text-align: center; padding: 12px;">
                                            Item Description</th>
                                        <th
                                            style="background-color: #485b8c; color: white; text-align: center; padding: 12px;">
                                            Qty</th>
                                        <th
                                            style="background-color: #485b8c; color: white; text-align: center; padding: 12px;">
                                            L × W × H (m)</th>
                                        <th
                                            style="background-color: #485b8c; color: white; text-align: center; padding: 12px;">
                                            Weight (kg)</th>
                                        <th
                                            style="background-color: #485b8c; color: white; text-align: center; padding: 12px;">
                                            Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($placementData['cargoReceipts'] as $receipt)
                                        @php
                                            $booking = \App\Models\CargoBooking::with('measurementUnit')
                                                ->where('cargo_booking_id', $receipt->cargo_booking_id)
                                                ->first();

                                            // Convert dimensions to meters for display
                                            $unitName =
                                                $booking?->measurementUnit?->measurement_unit_abbreviation ?? 'cm';
                                            $conversionFactor =
                                                stripos($unitName, 'cm') !== false
                                                    ? 0.01
                                                    : (stripos($unitName, 'in') !== false
                                                        ? 0.0254
                                                        : 1);

                                            $lengthM = ($booking?->length ?? 0) * $conversionFactor;
                                            $widthM = ($booking?->width ?? 0) * $conversionFactor;
                                            $heightM = ($booking?->height ?? 0) * $conversionFactor;
                                        @endphp
                                        <tr>
                                            <td>{{ $receipt->cargo_receipt_id }}</td>
                                            <td>{{ $receipt->booking_ref_no }}</td>
                                            <td>{{ $receipt->cargoItem->cargo_item_description ?? 'N/A' }}</td>
                                            <td>{{ $receipt->cargo_item_qty ?? 1 }}</td>
                                            <td>
                                                @if ($booking)
                                                    {{ number_format($lengthM, 2) }} × {{ number_format($widthM, 2) }} ×
                                                    {{ number_format($heightM, 2) }}<br>
                                                    <small style="color: #666;">({{ $booking->length }} ×
                                                        {{ $booking->width }} × {{ $booking->height }}
                                                        {{ $unitName }})</small>
                                                @else
                                                    No dimensions
                                                @endif
                                            </td>
                                            <td>{{ $booking->weight ?? 'N/A' }}</td>
                                            <td style="text-align: center;">
                                                <button class="btn btn-sm btn-primary isolate-btn"
                                                    data-receipt-id="{{ $receipt->cargo_receipt_id }}"
                                                    onclick="cargoVisualizer.isolateItem('{{ $receipt->cargo_receipt_id }}')">
                                                    <i class="fas fa-search"></i> Isolate
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- 3D CARGO VISUALIZATION CONTAINER (AUTO-DISPLAY) --}}
                    <div class="card mt-4">
                        <div class="card-header text-white" style="background-color: #485b8c;">
                            <h5>3D Cargo Visualization</h5>
                        </div>
                        <div class="card-body" style="padding: 15px;">
                            <div id="cargo-visualizer-container"
                                style="width: 100%; height: auto; min-height: 650px; max-height: 85vh; border: 1px solid #ccc; background: #f0f0f0;">
                                <!-- 3D visualization renders here -->
                            </div>
                        </div>
                    </div>
                @endif
            @endif

            @if (session('placement_results'))
                <div class="card mt-4">
                    <div class="card-header text-white">
                        <h5>Placement Results</h5>
                    </div>
                    <div class="card-body">
                        @foreach (session('placement_results') as $index => $hatchResult)
                            <div class="mb-4">
                                <h6>{{ $hatchResult['hatch']->hatch_label }}</h6>

                                @if (isset($hatchResult['error']))
                                    <div class="alert alert-danger">{{ $hatchResult['error'] }}</div>
                                @else
                                    @php
                                        $result = $hatchResult['result'];
                                        $packedItems = $result['response']['packed_items'] ?? [];
                                        $unpackedItems = $result['response']['unpacked_items'] ?? [];
                                    @endphp

                                    <p><strong>Packed Items:</strong> {{ count($packedItems) }}</p>
                                    <p><strong>Unpacked Items:</strong> {{ count($unpackedItems) }}</p>

                                    @if (!empty($packedItems))
                                        <table class="table table-sm table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Item ID</th>
                                                    <th>Position (x, y, z)</th>
                                                    <th>Dimensions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($packedItems as $item)
                                                    <tr>
                                                        <td>{{ $item['id'] ?? 'N/A' }}</td>
                                                        <td>{{ $item['x'] ?? 0 }}, {{ $item['y'] ?? 0 }},
                                                            {{ $item['z'] ?? 0 }}</td>
                                                        <td>{{ $item['w'] ?? 0 }} × {{ $item['h'] ?? 0 }} ×
                                                            {{ $item['d'] ?? 0 }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    @endif
                                @endif
                            </div>
                        @endforeach

                        @if (session('remaining_items') && count(session('remaining_items')) > 0)
                            <div class="alert alert-warning">
                                <strong>Warning:</strong> {{ count(session('remaining_items')) }} items could not be placed
                                in any hatch.
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
    <script src="{{ asset('js/cargo-visualizer.js') }}"></script>
    <script>
        /**
         * Wait for CargoVisualizer to be available
         */
        function waitForCargoVisualizer(callback, attempts = 0) {
            if (typeof window.CargoVisualizer !== 'undefined') {
                callback();
            } else if (attempts < 50) {
                setTimeout(() => waitForCargoVisualizer(callback, attempts + 1), 100);
            } else {
                console.error('CargoVisualizer failed to load');
            }
        }

        /**
         * Initialize and render cargo visualization
         */
        async function initializeVisualization(voyageId) {
            const container = document.getElementById('cargo-visualizer-container');

            try {
                // Fetch packing data from API
                const response = await fetch(`{{ route('staff.cargo.packing-data') }}?voyage_id=${voyageId}`, {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                if (!response.ok) {
                    throw new Error('Failed to fetch packing data');
                }

                const data = await response.json();

                // Clear container
                container.innerHTML = '';

                // Initialize Three.js scene with fallback rendering
                window.cargoVisualizer = new window.CargoVisualizer('cargo-visualizer-container');

                // Render using 2-zone packing algorithm
                if (data.cargo && Array.isArray(data.cargo)) {
                    window.cargoVisualizer.packAndVisualize(data.hatches, data.cargo);
                }

            } catch (error) {
                console.error('Error initializing visualization:', error);
                container.innerHTML =
                    '<div class="alert alert-danger" style="margin: 0; padding: 20px;">Error loading 3D visualization: ' +
                    error.message + '</div>';
            }
        }


        /**
         * Auto-initialize visualization if voyage is selected
         */
        document.addEventListener('DOMContentLoaded', function() {
            const voyageSelect = document.getElementById('voyage_id');

            // If a voyage is already selected, initialize visualization
            if (voyageSelect && voyageSelect.value) {
                // Wait for CargoVisualizer to load, then initialize
                waitForCargoVisualizer(() => {
                    console.log('Initializing visualization for voyage:', voyageSelect.value);
                    initializeVisualization(voyageSelect.value);
                });
            }

            // Listen for voyage selection changes
            if (voyageSelect) {
                voyageSelect.addEventListener('change', function() {
                    if (this.value) {
                        console.log('Voyage selected, reloading page...');
                        // Form will submit on change, reloading the page with new voyage_id
                    }
                });
            }
        });
    </script>
@endsection
