<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('components.hero', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="schedules-page">
        <div class="container">
            <h2 class="schedules-page-title">Sailing Schedules & Rates</h2>

            <?php $__empty_1 = true; $__currentLoopData = $routeCategories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php
                    $routePorts = $category->routePorts;
                    // Separate into two directions (left = first, right = second)
                    $leftRoute = $routePorts->first();
                    $rightRoute = $routePorts->count() > 1 ? $routePorts->last() : null;
                ?>

                <div class="route-card">
                    <div class="route-card-header">
                        <span><i class="fas fa-ship"></i> <?php echo e($category->route_category_name); ?></span>
                        <span class="route-card-header-right"><i class="fas fa-calendar-alt"></i> Weekly Sailing
                            Schedules</span>
                    </div>

                    <div class="row">
                        
                        <div class="<?php echo e($rightRoute ? 'col-md-6' : 'col-md-12'); ?>">
                            <?php if($leftRoute): ?>
                                <?php
                                    $leftVoyages = $schedulesByRoutePort[$leftRoute->route_port_id] ?? collect();
                                ?>
                                <div class="direction-block">
                                    <div class="direction-label">
                                        <?php echo e($leftRoute->route_origin); ?> <i class="fas fa-long-arrow-alt-right"></i>
                                        <?php echo e($leftRoute->route_destination); ?>

                                    </div>

                                    <?php if($leftVoyages->isNotEmpty()): ?>
                                        <?php
                                            $dayOrder = [
                                                'Sunday',
                                                'Monday',
                                                'Tuesday',
                                                'Wednesday',
                                                'Thursday',
                                                'Friday',
                                                'Saturday',
                                            ];
                                            $weeklySchedule = $leftVoyages
                                                ->map(function ($v) {
                                                    $dep = \Carbon\Carbon::parse($v->voyage_estimated_TD);
                                                    $arr = \Carbon\Carbon::parse($v->voyage_estimated_TA);
                                                    // Handle overnight: if arrival is before departure, it's the next day
        $diff = $dep->copy()->setDateFrom($dep);
        $arrTime = $arr->copy()->setDateFrom($dep);
        if ($arrTime->lte($diff)) {
            $arrTime->addDay();
        }
        $totalMinutes = $diff->diffInMinutes($arrTime);
        $hours = intdiv($totalMinutes, 60);
        $mins = $totalMinutes % 60;
        $eta = $hours . 'h' . ($mins > 0 ? ' ' . $mins . 'm' : '');

        return [
            'vessel' => $v->vessel->vessel_name ?? 'TBA',
            'day' => \Carbon\Carbon::parse(
                $v->voyage_departure_date,
            )->format('l'),
            'departure' => $dep->format('g:i A'),
            'arrival' => $arr->format('g:i A'),
            'eta' => $eta,
            'dep_raw' => $v->voyage_estimated_TD,
            'arr_raw' => $v->voyage_estimated_TA,
        ];
    })
    ->unique(function ($item) {
        return $item['vessel'] . $item['day'] . $item['departure'];
    })
    ->sort(function ($a, $b) use ($dayOrder) {
        $dayDiff =
            array_search($a['day'], $dayOrder) -
            array_search($b['day'], $dayOrder);
        if ($dayDiff !== 0) {
            return $dayDiff;
        }
        $depDiff = strcmp($a['dep_raw'], $b['dep_raw']);
        if ($depDiff !== 0) {
            return $depDiff;
        }
        return strcmp($a['arr_raw'], $b['arr_raw']);
                                                })
                                                ->values();
                                        ?>
                                        <div class="schedule-table-wrapper">
                                            <table class="table schedule-table">
                                                <thead>
                                                    <tr>
                                                        <th>Day</th>
                                                        <th>Departure</th>
                                                        <th>Arrival</th>
                                                        <th>ETA</th>
                                                        <th>Vessel</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                        $grouped = $weeklySchedule
                                                            ->groupBy('day')
                                                            ->sortBy(function ($items, $day) use ($dayOrder) {
                                                                return array_search($day, $dayOrder);
                                                            });
                                                    ?>
                                                    <?php $__currentLoopData = $grouped; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $day => $rows): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <?php $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idx => $sched): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                            <tr>
                                                                <?php if($idx === 0): ?>
                                                                    <td rowspan="<?php echo e($rows->count()); ?>" class="align-middle">
                                                                        <?php echo e($day); ?></td>
                                                                <?php endif; ?>
                                                                <td><?php echo e($sched['departure']); ?></td>
                                                                <td><?php echo e($sched['arrival']); ?></td>
                                                                <td><?php echo e($sched['eta']); ?></td>
                                                                <td><?php echo e($sched['vessel']); ?></td>
                                                            </tr>
                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php else: ?>
                                        <p class="no-schedule">No schedule data available</p>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        
                        <?php if($rightRoute): ?>
                            <?php
                                $rightVoyages = $schedulesByRoutePort[$rightRoute->route_port_id] ?? collect();
                            ?>
                            <div class="col-md-6">
                                <div class="direction-block">
                                    <div class="direction-label">
                                        <?php echo e($rightRoute->route_origin); ?> <i class="fas fa-long-arrow-alt-right"></i>
                                        <?php echo e($rightRoute->route_destination); ?>

                                    </div>

                                    <?php if($rightVoyages->isNotEmpty()): ?>
                                        <?php
                                            $dayOrder = [
                                                'Sunday',
                                                'Monday',
                                                'Tuesday',
                                                'Wednesday',
                                                'Thursday',
                                                'Friday',
                                                'Saturday',
                                            ];
                                            $weeklyScheduleRight = $rightVoyages
                                                ->map(function ($v) {
                                                    $dep = \Carbon\Carbon::parse($v->voyage_estimated_TD);
                                                    $arr = \Carbon\Carbon::parse($v->voyage_estimated_TA);
                                                    $diff = $dep->copy()->setDateFrom($dep);
                                                    $arrTime = $arr->copy()->setDateFrom($dep);
                                                    if ($arrTime->lte($diff)) {
                                                        $arrTime->addDay();
                                                    }
                                                    $totalMinutes = $diff->diffInMinutes($arrTime);
                                                    $hours = intdiv($totalMinutes, 60);
                                                    $mins = $totalMinutes % 60;
                                                    $eta = $hours . 'h' . ($mins > 0 ? ' ' . $mins . 'm' : '');

                                                    return [
                                                        'vessel' => $v->vessel->vessel_name ?? 'TBA',
                                                        'day' => \Carbon\Carbon::parse(
                                                            $v->voyage_departure_date,
                                                        )->format('l'),
                                                        'departure' => $dep->format('g:i A'),
                                                        'arrival' => $arr->format('g:i A'),
                                                        'eta' => $eta,
                                                        'dep_raw' => $v->voyage_estimated_TD,
                                                        'arr_raw' => $v->voyage_estimated_TA,
                                                    ];
                                                })
                                                ->unique(function ($item) {
                                                    return $item['vessel'] . $item['day'] . $item['departure'];
                                                })
                                                ->sort(function ($a, $b) use ($dayOrder) {
                                                    $dayDiff =
                                                        array_search($a['day'], $dayOrder) -
                                                        array_search($b['day'], $dayOrder);
                                                    if ($dayDiff !== 0) {
                                                        return $dayDiff;
                                                    }
                                                    $depDiff = strcmp($a['dep_raw'], $b['dep_raw']);
                                                    if ($depDiff !== 0) {
                                                        return $depDiff;
                                                    }
                                                    return strcmp($a['arr_raw'], $b['arr_raw']);
                                                })
                                                ->values();
                                        ?>
                                        <div class="schedule-table-wrapper">
                                            <table class="table schedule-table">
                                                <thead>
                                                    <tr>
                                                        <th>Day</th>
                                                        <th>Departure</th>
                                                        <th>Arrival</th>
                                                        <th>ETA</th>
                                                        <th>Vessel</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                        $groupedRight = $weeklyScheduleRight
                                                            ->groupBy('day')
                                                            ->sortBy(function ($items, $day) use ($dayOrder) {
                                                                return array_search($day, $dayOrder);
                                                            });
                                                    ?>
                                                    <?php $__currentLoopData = $groupedRight; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $day => $rows): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <?php $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idx => $sched): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                            <tr>
                                                                <?php if($idx === 0): ?>
                                                                    <td rowspan="<?php echo e($rows->count()); ?>"
                                                                        class="align-middle"><?php echo e($day); ?></td>
                                                                <?php endif; ?>
                                                                <td><?php echo e($sched['departure']); ?></td>
                                                                <td><?php echo e($sched['arrival']); ?></td>
                                                                <td><?php echo e($sched['eta']); ?></td>
                                                                <td><?php echo e($sched['vessel']); ?></td>
                                                            </tr>
                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php else: ?>
                                        <p class="no-schedule">No schedule data available</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="no-routes-card">
                    <i class="fas fa-info-circle"></i>
                    <p>No schedules available at this time. Please check back later.</p>
                </div>
            <?php endif; ?>

            
            <div class="route-card">
                <div class="route-card-header">
                    <span><i class="fas fa-tags"></i> Passage Rates</span>
                </div>

                <?php $__currentLoopData = $routeCategories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $vessels = $vesselsByCategory[$category->route_category_id] ?? collect();
                    ?>

                    <?php if($vessels->isNotEmpty()): ?>
                        <?php
                            $catName = $category->route_category_name;
                            $isBoholCebuCategory = stripos($catName, 'talibon') !== false;
                        ?>
                        <div class="rates-route-group">
                            <div class="rates-route-name"><?php echo e($category->route_category_name); ?></div>

                            <?php $__currentLoopData = $vessels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vessel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php if($vessel->accommodations->isNotEmpty()): ?>
                                    <div class="rates-vessel-block">
                                        <div class="rates-vessel-name"><?php echo e($vessel->vessel_name); ?></div>
                                        <div class="rates-table-wrapper">
                                            <table class="table rates-table">
                                                <thead>
                                                    <tr>
                                                        <th>Accommodation</th>
                                                        <th>Regular</th>
                                                        <th>Senior / PWD</th>
                                                        <th>Student / Uniformed</th>
                                                        <th>Child (3-11 yrs)</th>
                                                        <th>Infant (&lt;3 yrs)</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php $__currentLoopData = $vessel->accommodations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $accommodation): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <?php
                                                            $base = $accommodation->accommodation_regular_price;
                                                        ?>
                                                        <tr>
                                                            <td><?php echo e($accommodation->accommodation_name); ?></td>
                                                            <td>&#8369;<?php echo e(number_format($base, 2)); ?></td>
                                                            <td>&#8369;<?php echo e(number_format($base * 0.8, 2)); ?></td>
                                                            <td>&#8369;<?php echo e(number_format($base * 0.8, 2)); ?></td>
                                                            <td>&#8369;<?php echo e(number_format($base * 0.5, 2)); ?></td>
                                                            <td>
                                                                <?php if($isBoholCebuCategory): ?>
                                                                    <span class="text-success fw-bold">FREE</span>
                                                                <?php else: ?>
                                                                    &#8369;<?php echo e(number_format($base * 0.75, 2)); ?>

                                                                <?php endif; ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                <div class="rates-block">
                    <p class="rates-note">
                        * Senior Citizen / PWD: 20% discount &bull;
                        Student / Uniformed Personnel (PNP, AFP, BFP, BJMP, Seafarers): 20% discount &bull;
                        Child (3-11 years old): Half fare &bull;
                        Infant (Below 3 years old): 25% fare (Free on Bohol-Cebu routes)
                    </p>
                </div>
            </div>
        </div>
    </div>

    <?php echo $__env->make('components.footer', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Shem\Desktop\Capstone\resources\views/passenger/schedules.blade.php ENDPATH**/ ?>