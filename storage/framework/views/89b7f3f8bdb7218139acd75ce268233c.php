<?php $__env->startSection('page-title', 'Cargo Auto Placement'); ?>
<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('components.authHeader', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo $__env->make('components.staff_nav', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<div class="staff-body">
    <h3 class="text-center mb-4">Cargo Auto Placement</h3>

    <div class="scs-form_container">
        <form method="GET" action="<?php echo e(route('staff.cargo.placement')); ?>" class="mb-4">
            <div class="form-group">
                <label for="voyage_id">Select Voyage:</label>
                <select name="voyage_id" id="voyage_id" class="form-control" onchange="this.form.submit()">
                    <option value="">-- Select a Voyage --</option>
                    <?php $__currentLoopData = $voyages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $voyage): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($voyage->voyage_id); ?>" <?php echo e($selectedVoyageId == $voyage->voyage_id ? 'selected' : ''); ?>>
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
                    <div class="card-header text-white">
                        <h5>Voyage Information</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Voyage Code:</strong> <?php echo e($placementData['voyage']->voyage_code); ?></p>
                        <p><strong>Vessel:</strong> <?php echo e($placementData['voyage']->vessel->vessel_name); ?></p>
                        <p><strong>Route:</strong> <?php echo e($placementData['voyage']->routePort->route_origin); ?> → <?php echo e($placementData['voyage']->routePort->route_destination); ?></p>
                        <p><strong>Total Hatches:</strong> <?php echo e($placementData['hatches']->count()); ?></p>
                        <p><strong>Total Cargo Items:</strong> <?php echo e($placementData['cargoReceipts']->count()); ?></p>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header text-white">
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
                                <?php $__currentLoopData = $placementData['hatches']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $hatch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td><?php echo e($hatch->hatch_label); ?></td>
                                        <td><?php echo e($hatch->hatch_length); ?></td>
                                        <td><?php echo e($hatch->hatch_width); ?></td>
                                        <td><?php echo e($hatch->hatch_height); ?></td>
                                        <td><?php echo e($hatch->hatch_weight_capacity); ?></td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header text-white">
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
                                <?php $__currentLoopData = $placementData['cargoReceipts']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $receipt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php
                                        $booking = \App\Models\CargoBooking::where('booking_ref_no', $receipt->booking_ref_no)->first();
                                    ?>
                                    <tr>
                                        <td><?php echo e($receipt->cargo_receipt_id); ?></td>
                                        <td><?php echo e($receipt->booking_ref_no); ?></td>
                                        <td><?php echo e($receipt->cargoItem->cargo_item_description ?? 'N/A'); ?></td>
                                        <td><?php echo e($receipt->cargo_item_qty ?? 1); ?></td>
                                        <td>
                                            <?php if($booking): ?>
                                                <?php echo e($booking->length); ?> × <?php echo e($booking->width); ?> × <?php echo e($booking->height); ?>

                                            <?php else: ?>
                                                No dimensions
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo e($booking->weight ?? 'N/A'); ?></td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <form method="POST" action="<?php echo e(route('staff.cargo.place')); ?>">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="voyage_id" value="<?php echo e($selectedVoyageId); ?>">
                    <button type="submit" class="btn button-textcolor1 btn-lg btn-block">
                        <i class="fas fa-box-open"></i> Calculate Auto Placement
                    </button>
                </form>
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
                                                    <td><?php echo e($item['x'] ?? 0); ?>, <?php echo e($item['y'] ?? 0); ?>, <?php echo e($item['z'] ?? 0); ?></td>
                                                    <td><?php echo e($item['w'] ?? 0); ?> × <?php echo e($item['h'] ?? 0); ?> × <?php echo e($item['d'] ?? 0); ?></td>
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
                            <strong>Warning:</strong> <?php echo e(count(session('remaining_items'))); ?> items could not be placed in any hatch.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\kirzt\Documents\GitHub\Capstone\resources\views/authorized/staff/staff_cargoautoplacement.blade.php ENDPATH**/ ?>