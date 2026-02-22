<?php $__env->startSection('page-title', 'DASHBOARD'); ?>
<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('components.authHeader', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo $__env->make('components.admin_nav', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?> 
    <div class="admin-body">
        <div class="dashbord-titles">
            <h3 id="dashboard-datetoday"><?php echo e(\Carbon\Carbon::now()->format('F d, Y, l')); ?></h3>
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
                        <th>ETD</th>
                        <th>Arrival Date</th>
                        <th>ETA</th>
                        <th>Vessel</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $voyages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $voyage): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td><?php echo e($voyage->voyage_code); ?></td>
                        <td>
                            <?php echo e($voyage->routePort->route_origin); ?> → 
                            <?php echo e($voyage->routePort->route_destination); ?>

                        </td>
                        <td><?php echo e(\Carbon\Carbon::parse($voyage->voyage_departure_date)->format('M j, Y, D')); ?></td>
                        <td><?php echo e(\Carbon\Carbon::parse($voyage->voyage_estimated_TD)->format('g:iA')); ?></td>
                        <td><?php echo e(\Carbon\Carbon::parse($voyage->voyage_arrival_date)->format('M j, Y, D')); ?></td>
                        <td><?php echo e(\Carbon\Carbon::parse($voyage->voyage_estimated_TA)->format('g:iA')); ?></td>
                        <td><?php echo e($voyage->vessel->vessel_name); ?></td>
                        <td><?php echo e($voyage->voyage_status); ?></td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="8" class="text-center">No voyages for today.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    
    
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\clint\Desktop\Capstone\resources\views/authorized/admin/dashboard.blade.php ENDPATH**/ ?>