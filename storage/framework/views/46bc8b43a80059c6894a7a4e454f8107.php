<?php $__env->startSection('page-title', 'GENERATE REPORTS'); ?>

<?php $__env->startSection('content'); ?>
<?php echo $__env->make('components.authHeader', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php echo $__env->make('components.admin_nav', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<div class="admin-body">

    <!-- FILTERS -->
    <div class="reports-filter-row">
        <form method="GET" class="reports-filter-form">

            <!-- DATE FROM --> 
            <div class="reports-filter-group"> 
                <label class="reports-filter-label">From</label> 
                <input type="date" name="start_date" value="<?php echo e(request('start_date')); ?>" class="reports-filter-input"> 
            </div> 
            <!-- DATE TO --> 
            <div class="reports-filter-group"> 
                <label class="reports-filter-label">To</label> 
                <input type="date" name="end_date" value="<?php echo e(request('end_date')); ?>" class="reports-filter-input"> 
            </div>

            <!-- ROUTE --> 
            <div class="reports-filter-group"> 
                <label class="reports-filter-label">Route</label> 
                <select name="route" class="reports-filter-select"> 
                    <option value="" <?php echo e($selectedRoute === null || $selectedRoute === '' ? 'selected' : ''); ?>> All Routes </option> 
                    <?php $__currentLoopData = $routes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $route): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?> 
                    <option value="<?php echo e($route->route_port_id); ?>" <?php echo e($selectedRoute == $route->route_port_id ? 'selected' : ''); ?>> <?php echo e($route->route_origin); ?> - <?php echo e($route->route_destination); ?> </option> 
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?> 
                    </select> 
                </div> 
                <!-- BOOKING TYPE --> 
                <div class="reports-filter-group"> 
                    <label class="reports-filter-label">Booking Type</label> 
                    <select name="booking_type" class="reports-filter-select"> 
                        <option value="" <?php echo e($selectedBookingType === null || $selectedBookingType === '' ? 'selected' : ''); ?>>All Booking Types</option> 
                        <option value="cargo" <?php echo e($selectedBookingType == 'cargo' ? 'selected' : ''); ?>>Cargo</option> 
                        <option value="passenger" <?php echo e($selectedBookingType == 'passenger' ? 'selected' : ''); ?>>Passenger</option> 
                    </select> 
                </div>

            <!-- GENERATE BUTTON --> 
            <div class="reports-filter-actions"> 
                <button type="submit" class="reports-btn-generate"> 
                    <i class="fa-solid fa-file-lines"></i> Generate 
                </button> 
            </div> 
            
            <!-- PRINT BUTTON --> 
            <div class="reports-filter-actions"> 
                <button type="button" class="reports-btn-print"> 
                    <i class="fa-solid fa-print"></i> 
                </button> 
            </div>
        </form>
    </div>

    <!-- TITLE -->
    <?php
        $bookingLabel = $isPassenger ? 'Passenger Booking Type' :
                        ($isCargo ? 'Cargo Booking Type' : 'All Booking Type');

        if ($selectedRoute) {
            $routeObj = $routes->firstWhere('route_port_id', $selectedRoute);
            $routeLabel = $routeObj
                ? $routeObj->route_origin . ' - ' . $routeObj->route_destination
                : 'Unknown Route';
        } else {
            $routeLabel = 'All Routes';
        }

        $fromDate = $startDate ? \Carbon\Carbon::parse($startDate)->format('m/d/Y') : 'Start Date';
        $toDate = $endDate ? \Carbon\Carbon::parse($endDate)->format('m/d/Y') : 'End Date';
    ?>

    <h3 class="report-title">
        <?php echo e($bookingLabel); ?> Data for <?php echo e($routeLabel); ?> from <?php echo e($fromDate); ?> to <?php echo e($toDate); ?>

    </h3>

    <!-- FIRST ROW -->
    <div class="reports-row">

        <?php if(!$isPassenger && !$isCargo): ?>
            <div class="stat-box">
                <span class="anumberStat">XXX</span>
                Passenger Bookings
            </div>
            <div class="stat-box">
                <span class="anumberStat">XXX</span>
                Cargo Bookings
            </div>
            <div class="stat-box">
                <span class="anumberStat">₱ XX,XXX.XX</span>
                Passenger Revenue
            </div>
            <div class="stat-box">
                <span class="anumberStat">₱ XX,XXX.XX</span>
                Cargo Revenue
            </div>
            <div class="stat-box">
                <span class="anumberStat">₱ XXX,XXX.XX</span>
                Total Revenue
            </div>

        <?php elseif($isPassenger): ?>
            <div class="stat-box">
                <span class="anumberStat">XXX</span>
                Passenger Bookings
            </div>
            <div class="stat-box">
                <span class="anumberStat">₱ XX,XXX.XX</span>
                Passenger Revenue
            </div>

        <?php elseif($isCargo): ?>
            <div class="stat-box">
                <span class="anumberStat">XXX</span>
                Cargo Bookings
            </div>
            <div class="stat-box">
                <span class="anumberStat">₱ XX,XXX.XX</span>
                Cargo Revenue
            </div>
        <?php endif; ?>

    </div>

    <!-- SECOND ROW -->
    <div class="reports-row">

        <!-- PAYMENT -->
        <div class="report-box">
            Payment Methods (Pie Chart)
        </div>

        <!-- CHART -->
        <div class="report-box">
            <div class="chart-header">
                <select>
                    <option>Bookings</option>
                    <option>Revenue</option>
                </select>
            </div>

            <?php if($isAllRoutes): ?>
                <p>Bar Chart (per route)</p>
            <?php else: ?>
                <p>Line Chart (past N days)</p>
            <?php endif; ?>
        </div>

        <!-- TOP CARGO -->
        <?php if(!$isPassenger): ?>
            <div class="report-box">
                Top 10 Cargo Items
            </div>
        <?php endif; ?>

    </div>
    
    <!-- VOYAGE TABLE -->
    <div class="astat-voyage mt-4">
        <h4>Voyages</h4>
        <table class="reports-table">
            <thead>
                <tr>
                    <th>Voyage Code</th>
                    <th>Route</th>
                    <th>Vessel</th>
                    <th>Departure Date</th>
                    <th>ETD</th>
                    <th>Arrival Date</th>
                    <th>ETA</th>
                    <?php if(!$isCargo): ?> <th>PAX/CAP</th> <?php endif; ?>
                    <?php if(!$isPassenger): ?> <th>SKS</th><th>VAR</th><th>MC</th> <?php endif; ?>
                    <?php if($isPassenger): ?> <th>Passenger Revenue</th> <?php endif; ?>
                    <?php if($isCargo): ?> <th>Cargo Revenue</th> <?php endif; ?>
                    <?php if(!$isPassenger && !$isCargo): ?> 
                        <th>Passenger Revenue</th>
                        <th>Cargo Revenue</th>
                    <?php endif; ?>
                    <th>Total Revenue</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $voyages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $voyage): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td><?php echo e($voyage->voyage_code); ?></td>
                        <td><?php echo e($voyage->routePort->route_origin); ?> → <?php echo e($voyage->routePort->route_destination); ?></td>
                        <td><?php echo e($voyage->vessel->vessel_code); ?></td>
                        <td><?php echo e($voyage->voyage_actual_departure_date ? \Carbon\Carbon::parse($voyage->voyage_actual_departure_date)->format('M j, Y, D') : '-'); ?></td>
                        <td><?php echo e($voyage->voyage_actual_TD ? \Carbon\Carbon::parse($voyage->voyage_actual_TD)->format('g:iA') : '-'); ?></td>
                        <td><?php echo e($voyage->voyage_actual_arrival_date ? \Carbon\Carbon::parse($voyage->voyage_actual_arrival_date)->format('M j, Y, D') : '-'); ?></td>
                        <td><?php echo e($voyage->voyage_actual_TA ? \Carbon\Carbon::parse($voyage->voyage_actual_TA)->format('g:iA') : '-'); ?></td>

                        <?php if(!$isCargo): ?>
                            <td><?php echo e($voyage->passenger_tickets_count); ?>/<?php echo e($voyage->vessel->vessel_total_passenger_capacity); ?></td> <!`-- PAX/CAP -->
                        <?php endif; ?>

                        <?php if(!$isPassenger): ?>
                            <td>-</td>  <!-- SKS -->
                            <td>-</td>  <!-- VAR -->
                            <td>-</td>  <!-- MC -->
                        <?php endif; ?>

                        <?php if($isPassenger): ?>
                            <td>-</td> <!-- Passenger Revenue -->
                        <?php endif; ?>

                        <?php if($isCargo): ?>
                            <td>-</td> <!-- Cargo Revenue -->
                        <?php endif; ?>

                        <?php if(!$isPassenger && !$isCargo): ?>
                            <td>-</td> <!-- Passenger Revenue -->
                            <td>-</td> <!-- Cargo Revenue -->
                        <?php endif; ?>

                        <td>-</td>
                        <td><?php echo e($voyage->voyage_status); ?></td> <!- Status -->
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="14" class="text-center">No voyages for selected filters.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        <div class="mt-3">
            <?php echo e($voyages->links('pagination::bootstrap-5')); ?>

        </div>
    </div>

</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\clint\Desktop\Capstone\resources\views/authorized/admin/generate_reports.blade.php ENDPATH**/ ?>