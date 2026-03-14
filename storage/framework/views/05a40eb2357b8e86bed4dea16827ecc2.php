<?php $__env->startSection('page-title', 'CARGO AUTO PLACEMENT'); ?>
<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('components.authHeader', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo $__env->make('components.staff_nav', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="staff-body">
        <h3 class="text-center mb-4">CARGO AUTO PLACEMENT</h3>

        <div class="scs-form_container">
            <form method="GET" action="<?php echo e(route('staff.cargo.placement')); ?>" class="mb-4">
                <div class="form-group">
                    <label for="voyage_id">Select Voyage:</label>
                    <select name="voyage_id" id="voyage_id" class="form-control" onchange="this.form.submit()">
                        <option value="">-- Select a Voyage --</option>
                        <?php $__currentLoopData = $voyages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $voyage): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($voyage->voyage_id); ?>"
                                <?php echo e($selectedVoyageId == $voyage->voyage_id ? 'selected' : ''); ?>>
                                <?php echo e($voyage->voyage_code); ?> -
                                <?php echo e($voyage->routePort->route_origin); ?> → <?php echo e($voyage->routePort->route_destination); ?>

                                (<?php echo e(\Carbon\Carbon::parse($voyage->voyage_departure_date)->format('M j, Y')); ?>)
                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
            </form>

            <?php if($errors->any()): ?>
                <div class="alert alert-danger">
                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <p><?php echo e($error); ?></p>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php endif; ?>

            <?php if($selectedVoyageId && $placementData): ?>
                <?php if(isset($placementData['error'])): ?>
                    <div class="alert alert-warning"><?php echo e($placementData['error']); ?></div>
                <?php else: ?>
                    <div class="card mb-4">
                        <div class="card-header text-white" style="background-color: #485b8c;">
                            <h5>Voyage Information</h5>
                        </div>
                        <div class="card-body">
                            <p><strong>Voyage Code:</strong> <?php echo e($placementData['voyage']->voyage_code); ?></p>
                            <p><strong>Vessel:</strong> <?php echo e($placementData['voyage']->vessel->vessel_name); ?></p>
                            <p><strong>Route:</strong> <?php echo e($placementData['voyage']->routePort->route_origin); ?> →
                                <?php echo e($placementData['voyage']->routePort->route_destination); ?></p>
                            <p><strong>Total Hatches:</strong> <?php echo e($placementData['hatches']->count()); ?></p>
                            <p><strong>Total Cargo Items:</strong> <?php echo e($placementData['cargoReceipts']->count()); ?></p>
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
                                        <th
                                            style="background-color: #485b8c; color: white; text-align: center; padding: 12px;">
                                            Current Weight (kg)</th>
                                        <th
                                            style="background-color: #485b8c; color: white; text-align: center; padding: 12px;">
                                            Weight Available (kg)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__currentLoopData = $placementData['hatches']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $hatch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php
                                            $currentWeight = \Illuminate\Support\Facades\DB::table('cargo_receipt')
                                                ->join('cargo_booking', 'cargo_receipt.cargo_booking_id', '=', 'cargo_booking.cargo_booking_id')
                                                ->where('cargo_receipt.hatch_id', $hatch->hatch_id)
                                                ->sum('cargo_booking.weight');
                                        ?>
                                        <tr>
                                            <td style="text-align: center;"><?php echo e($hatch->hatch_label); ?></td>
                                            <td style="text-align: center;"><?php echo e($hatch->hatch_length); ?></td>
                                            <td style="text-align: center;"><?php echo e($hatch->hatch_width); ?></td>
                                            <td style="text-align: center;"><?php echo e($hatch->hatch_height); ?></td>
                                            <td style="text-align: center;"><?php echo e($hatch->hatch_capacity_per_hold); ?> tons (<?php echo e($hatch->hatch_capacity_per_hold * 1000); ?>kg)</td>
                                            <td style="text-align: center;" class="hatch-weight-cell" data-hatch-id="<?php echo e($hatch->hatch_id); ?>"><?php echo e(number_format($currentWeight, 2)); ?></td>
                                            <td style="text-align: center;"><?php echo e(number_format(($hatch->hatch_capacity_per_hold * 1000) - $currentWeight, 2)); ?></td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
                                    <?php $__currentLoopData = $placementData['cargoReceipts']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $receipt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php
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
                                        ?>
                                        <tr data-receipt-id=\"<?php echo e($receipt->cargo_receipt_id); ?>\">
                                            <td><?php echo e($receipt->cargo_receipt_id); ?></td>
                                            <td><?php echo e($receipt->booking_ref_no); ?></td>
                                            <td><?php echo e($receipt->cargoItem->cargo_item_description ?? 'N/A'); ?></td>
                                            <td><?php echo e($receipt->cargo_item_qty ?? 1); ?></td>
                                            <td>
                                                <?php if($booking): ?>
                                                    <?php echo e(number_format($lengthM, 2)); ?> × <?php echo e(number_format($widthM, 2)); ?> ×
                                                    <?php echo e(number_format($heightM, 2)); ?><br>
                                                    <small style="color: #666;">(<?php echo e($booking->length); ?> ×
                                                        <?php echo e($booking->width); ?> × <?php echo e($booking->height); ?>

                                                        <?php echo e($unitName); ?>)</small>
                                                <?php else: ?>
                                                    No dimensions
                                                <?php endif; ?>
                                            </td>
                                            <td class="cargo-weight-cell"><?php echo e($booking->weight ?? 'N/A'); ?></td>
                                            <td style="text-align: center;">
                                                <button class="btn btn-sm btn-primary isolate-btn"
                                                    data-receipt-id="<?php echo e($receipt->cargo_receipt_id); ?>"
                                                    onclick="cargoVisualizer.isolateItem('<?php echo e($receipt->cargo_receipt_id); ?>')">
                                                    <i class="fas fa-search"></i> Isolate
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    
                    <div class="card mt-4">
                        <div class="card-header text-white" style="background-color: #485b8c; display: flex; justify-content: space-between; align-items: center;">
                            <h5 style="margin: 0;">3D Cargo Visualization</h5>
                            <div style="display: flex; gap: 20px; font-size: 13px;">
                                <div style="display: flex; align-items: center; gap: 6px;">
                                    <span style="display: inline-block; width: 14px; height: 14px; background-color: #ff6b6b; border-radius: 2px;"></span>
                                    <span>Heavy (≥300kg)</span>
                                </div>
                                <div style="display: flex; align-items: center; gap: 6px;">
                                    <span style="display: inline-block; width: 14px; height: 14px; background-color: #77a1ff; border-radius: 2px;"></span>
                                    <span>Light (<300kg)</span>
                                </div>
                                <div style="display: flex; align-items: center; gap: 6px;">
                                    <span style="display: inline-block; width: 14px; height: 14px; background-color: #ff9999; border-radius: 2px;"></span>
                                    <span>Unplaced</span>
                                </div>
                            </div>
                        </div>
                        <div class="card-body" style="padding: 15px;">
                            <div id="cargo-visualizer-container"
                                style="width: 100%; height: auto; min-height: 650px; max-height: 85vh; border: 1px solid #ccc; background: #f0f0f0;">
                                <!-- 3D visualization renders here -->
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <?php if(session('placement_results')): ?>
                <div class="card mt-4">
                    <div class="card-header text-white">
                        <h5>Placement Results</h5>
                    </div>
                    <div class="card-body">
                        <?php $__currentLoopData = session('placement_results'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $hatchResult): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="mb-4">
                                <h6><?php echo e($hatchResult['hatch']->hatch_label); ?></h6>

                                <?php if(isset($hatchResult['error'])): ?>
                                    <div class="alert alert-danger"><?php echo e($hatchResult['error']); ?></div>
                                <?php else: ?>
                                    <?php
                                        $result = $hatchResult['result'];
                                        $packedItems = $result['response']['packed_items'] ?? [];
                                        $unpackedItems = $result['response']['unpacked_items'] ?? [];
                                    ?>

                                    <p><strong>Packed Items:</strong> <?php echo e(count($packedItems)); ?></p>
                                    <p><strong>Unpacked Items:</strong> <?php echo e(count($unpackedItems)); ?></p>

                                    <?php if(!empty($packedItems)): ?>
                                        <table class="table table-sm table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Item ID</th>
                                                    <th>Position (x, y, z)</th>
                                                    <th>Dimensions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php $__currentLoopData = $packedItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <tr>
                                                        <td><?php echo e($item['id'] ?? 'N/A'); ?></td>
                                                        <td><?php echo e($item['x'] ?? 0); ?>, <?php echo e($item['y'] ?? 0); ?>,
                                                            <?php echo e($item['z'] ?? 0); ?></td>
                                                        <td><?php echo e($item['w'] ?? 0); ?> × <?php echo e($item['h'] ?? 0); ?> ×
                                                            <?php echo e($item['d'] ?? 0); ?></td>
                                                    </tr>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </tbody>
                                        </table>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                        <?php if(session('remaining_items') && count(session('remaining_items')) > 0): ?>
                            <div class="alert alert-warning">
                                <strong>Warning:</strong> <?php echo e(count(session('remaining_items'))); ?> items could not be placed
                                in any hatch.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
    <script src="<?php echo e(asset('js/cargo-visualizer.js')); ?>"></script>
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
                const response = await fetch(`<?php echo e(route('staff.cargo.packing-data')); ?>?voyage_id=${voyageId}`, {
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

                // Render using 2-zone packing algorithm and save results
                if (data.cargo && Array.isArray(data.cargo)) {
                    const results = window.cargoVisualizer.packAndVisualize(
                        data.hatches, 
                        data.cargo
                    );
                    
                    // Save placement results to database
                    if (results && results.packed && results.packed.length > 0) {
                        const placements = results.packed.map(item => ({
                            receiptId: parseInt(item.id),
                            hatchId: item.binId,
                            weight: item.weight || 0
                        }));
                        
                        // Store cargo data for weight updates
                        window.cargoItemWeights = data.cargo;
                        
                        // Call save API
                        await savePlacementToDB(voyageId, placements);
                    }
                }

            } catch (error) {
                console.error('Error initializing visualization:', error);
                container.innerHTML =
                    '<div class="alert alert-danger" style="margin: 0; padding: 20px;">Error loading 3D visualization: ' +
                    error.message + '</div>';
            }
        }

        /**
         * Save placement results to database
         */
        async function savePlacementToDB(voyageId, placements) {
            try {
                const response = await fetch(`<?php echo e(route('staff.cargo.placement.save')); ?>`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        voyage_id: voyageId,
                        placements: placements
                    })
                });

                const result = await response.json();
                
                if (result.success) {
                    console.log(`✅ Saved ${result.count} placements to database`);
                    // Update weight table without reloading
                    updateWeightTable(placements);
                } else {
                    console.error('Save failed:', result.message);
                }
            } catch (error) {
                console.error('Error saving placement:', error);
            }
        }

        /**
         * Update the Current Weight column in the hatch table
         */
        function updateWeightTable(placements) {
            // Group placements by hatch using weight data from placements
            const weightByHatch = {};
            placements.forEach(p => {
                if (!weightByHatch[p.hatchId]) {
                    weightByHatch[p.hatchId] = 0;
                }
                // Use weight directly from the placement data (passed from packing results)
                const weight = p.weight || 0;
                weightByHatch[p.hatchId] += weight;
            });

            // Update weight cells in hatch table
            document.querySelectorAll('.hatch-weight-cell').forEach(cell => {
                const hatchId = cell.getAttribute('data-hatch-id');
                if (weightByHatch[hatchId] !== undefined) {
                    cell.textContent = weightByHatch[hatchId].toFixed(2);
                }
            });
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
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Sophia\Documents\Capstone\resources\views/authorized/staff/staff_cargoautoplacement.blade.php ENDPATH**/ ?>