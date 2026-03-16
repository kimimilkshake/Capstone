<?php $__env->startSection('page-title', 'CARGO AUTO PLACEMENT'); ?>
<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('components.authHeader', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo $__env->make('components.staff_nav', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="staff-body">
        <h3 class="text-center mb-4">SELECT VOYAGE</h3>

        <div class="scs-form_container">
            <div class="mb-4">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                    <h5 class="mb-0"><i class="fas fa-ship"></i> Select a Voyage to View Placement</h5>
                    <div style="display: flex; align-items: center; gap: 1rem;">
                        <?php if($viewPast): ?>
                            <p style="color: #999; font-size: 0.95rem; margin: 0; font-style: italic;">Past 7 days</p>
                            <a href="<?php echo e(route('staff.cargo.placement')); ?>" class="btn btn-primary btn-sm">
                                <i class="fas fa-arrow-right"></i> View Upcoming
                            </a>
                        <?php else: ?>
                            <a href="<?php echo e(route('staff.cargo.placement', ['view' => 'past'])); ?>"
                                class="btn btn-secondary btn-sm">
                                <i class="fas fa-history"></i> View Past
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    <?php $__currentLoopData = $voyages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $voyage): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            // Calculate cargo count first
                            $cargoCount = $voyage->cargoReceipts()->count();

                            // Get hatches and calculate available weight
                            $hatches = $voyage->vessel->hatches ?? collect();
                            $hatchInfo = [];
                            foreach ($hatches->take(2) as $hatch) {
                                $currentWeight =
                                    \Illuminate\Support\Facades\DB::table('cargo_receipt')
                                        ->join(
                                            'cargo_booking',
                                            'cargo_receipt.cargo_booking_id',
                                            '=',
                                            'cargo_booking.cargo_booking_id',
                                        )
                                        ->where('cargo_receipt.hatch_id', $hatch->hatch_id)
                                        ->where('cargo_receipt.voyage_id', $voyage->voyage_id)
                                        ->sum('cargo_booking.weight') ?? 0;

                                $maxCapacity = ((float) $hatch->hatch_capacity_per_hold) * 1000;
                                $availableWeight = $maxCapacity - $currentWeight;
                                $hatchInfo[] = [
                                    'label' => $hatch->hatch_label,
                                    'available' => number_format($availableWeight, 2),
                                ];
                            }
                        ?>

                        <?php if($cargoCount === 0): ?>
                            <div class="card"
                                style="opacity: 0.6; cursor: not-allowed; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-left: auto; width: 100%; max-width: 100%;">
                            <?php else: ?>
                                <a href="<?php echo e(route('staff.cargo.placement', ['voyage_id' => $voyage->voyage_id])); ?>"
                                    class="card text-decoration-none"
                                    style="transition: all 0.3s ease; cursor: pointer; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-left: auto; width: 100%; max-width: 100%;"
                                    onmouseover="this.style.boxShadow='0 6px 12px rgba(0,0,0,0.15); this.style.transform='translateY(-2px)'"
                                    onmouseout="this.style.boxShadow='0 2px 4px rgba(0,0,0,0.1); this.style.transform='translateY(0)'">
                        <?php endif; ?>
                        <div class="card-body" style="display: flex; justify-content: space-between; align-items: center;">
                            <!-- Left Side: Voyage Info -->
                            <div style="flex: 1;">
                                <h6 class="card-title mb-2" style="color: #485b8c; font-weight: bold;">
                                    <?php echo e($voyage->voyage_code); ?></h6>
                                <p class="card-text mb-2" style="font-size: 0.9rem;">
                                    <i class="fas fa-route"></i>
                                    <strong><?php echo e($voyage->routePort->route_origin); ?> →
                                        <?php echo e($voyage->routePort->route_destination); ?></strong>
                                </p>
                                <p class="card-text mb-2" style="font-size: 0.85rem; color: #666;">
                                    <i class="fas fa-calendar"></i>
                                    <?php echo e(\Carbon\Carbon::parse($voyage->voyage_departure_date)->format('M j, Y')); ?>

                                </p>
                                <p class="card-text mb-0" style="font-size: 0.85rem; color: #666;">
                                    <i class="fas fa-ship"></i>
                                    Vessel: <?php echo e($voyage->vessel->vessel_name ?? 'N/A'); ?>

                                </p>
                            </div>

                            <!-- Center: Departure Info -->
                            <div
                                style="flex: 0 0 auto; display: flex; flex-direction: column; align-items: center; gap: 0.5rem; margin: 0 2rem; padding: 1rem; background-color: #f8f9fa; border-radius: 8px; border-left: 4px solid #485b8c;">
                                <p
                                    style="font-size: 0.75rem; color: #999; text-transform: uppercase; letter-spacing: 0.5px; margin: 0; font-weight: 600;">
                                    Departure</p>
                                <p style="font-size: 1.1rem; color: #485b8c; font-weight: bold; margin: 0;">
                                    <?php echo e(\Carbon\Carbon::parse($voyage->voyage_departure_date)->format('M d, Y')); ?>

                                </p>
                                <p style="font-size: 0.95rem; color: #666; margin: 0; font-weight: 600;">
                                    <i class="fas fa-clock" style="color: #485b8c; margin-right: 0.3rem;"></i>
                                    <?php echo e(\Carbon\Carbon::parse($voyage->voyage_estimated_TD)->format('g:i A')); ?>

                                </p>
                            </div>

                            <!-- Right Side: Hatch Columns or Empty Message -->
                            <div style="flex: 0 0 auto; display: flex; gap: 2rem; margin-left: 2rem; align-items: center;">
                                <?php if($cargoCount === 0): ?>
                                    <div style="text-align: center; width: 250px;">
                                        <p style="font-size: 2.5rem; color: #dc3545; font-weight: bold; margin: 0;">
                                            EMPTY
                                        </p>
                                    </div>
                                <?php else: ?>
                                    <?php $__currentLoopData = $hatchInfo; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $hatch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <div style="text-align: center;">
                                            <p class="mb-1"
                                                style="font-size: 1.5rem; color: #485b8c; font-weight: bold; margin-bottom: 0.5rem;">
                                                Hatch <?php echo e($hatch['label']); ?>

                                            </p>
                                            <p style="font-size: 0.85rem; color: #666; margin: 0.3rem 0;">
                                                Available Weight
                                            </p>
                                            <p style="font-size: 1rem; color: #485b8c; font-weight: bold; margin: 0;">
                                                <?php echo e($hatch['available']); ?> kg
                                            </p>
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php if($cargoCount === 0): ?>
                </div>
            <?php else: ?>
                </a>
                <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                <?php if($voyages->isEmpty()): ?>
                    <div class="text-center" style="background-color: transparent; border: none; width: 100%; margin: 0;">
                        <p style="color: #999; font-size: 0.95rem; letter-spacing: 0.5px;">
                            NO VOYAGES AVAILABLE AT THIS TIME.
                        </p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Sophia\Documents\Capstone\resources\views/authorized/staff/staff_cargo_placement_select.blade.php ENDPATH**/ ?>