<?php $__env->startSection('page-title', 'DASHBOARD'); ?>
<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('components.authHeader', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo $__env->make('components.staff_nav', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="staff-body">
        <div class="dashbord-titles">
            <h3 id="dashboard-datetoday"><?php echo e(\Carbon\Carbon::now()->format('F d, Y, l')); ?></h3>
        </div>
        <div class="astat-boxes-row">
            <div class="astat-boxes-col">
                <span class="anumberStat"><?php echo e($passengerBookings); ?></span>
                <p>Passenger Bookings</p>
            </div>
            <div class="astat-boxes-col">
                <span class="anumberStat"><?php echo e($cargoBookings); ?></span>
                <p>Cargo Bookings</p>
            </div>
            <div class="astat-boxes-col">
                <span class="anumberStat">₱ XX,XXX.XX</span>
                <p>Passenger Revenue</p>
            </div>
            <div class="astat-boxes-col">
                <span class="anumberStat">₱ XX,XXX.XX</span>
                <p>Cargo Revenue</p>
            </div>
            <div class="astat-boxes-col">
                <span class="anumberStat">₱<?php echo e(number_format($totalSales, 2)); ?></span>
                <p>Total Revenue</p>
            </div>
        </div>
        <div class="astat-boxes-row">
            <div class="astat-boxes-col">
                <ul>
                    <li>the pie chart of the payment methods will go here</li>
                    <li>it will be multicolored, with each color representing a different payment method</li>
                    <li>green for cash and orange for gcash</li>
                    <li>this portion will also show amount received in cash and amount received in gcash and the total amount</li>
                </ul>
            </div>
            <div class="astat-boxes-col">
                <ul>
                    <li>the bar chart showing the total number of bookings per route will go here</li>
                    <li>x-axis will be the routes, per route so one red and blue for CEBBAY, another red and blue for another route, etc.</li>
                    <li>y-axis will be for the total number of bookings</li>
                    <li>it will be multicolored, red for cargo bookings and blue for passenger bookings</li>
                </ul>
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
                            <td><?php echo e($voyage->passenger_tickets_count); ?>/<?php echo e($voyage->vessel->vessel_total_passenger_capacity); ?>

                            </td>
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

    <?php if(session('success')): ?>
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
    <?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\clint\Desktop\Capstone\resources\views/authorized/staff/dashboard.blade.php ENDPATH**/ ?>