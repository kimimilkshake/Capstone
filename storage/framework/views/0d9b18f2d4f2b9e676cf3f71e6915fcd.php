<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('components.hero', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="trip-booking-container container">
        <div class="row align-items-stretch">

            <!-- Left Side: Available Voyages -->
            <div class="col-lg-6">
                <div class="table-container">
                    <h4 class="text-center text-primary">Available Voyages (Next 8 Days)</h4>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead class="table-dark text-center">
                                <tr>
                                    <th>Date</th>
                                    <th>Route</th>
                                    <th>Departure</th>
                                    <th>Vessel</th>
                                </tr>
                            </thead>
                            <tbody class="text-center">
                                
                                <?php $__empty_1 = true; $__currentLoopData = $voyages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $voyage): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td><?php echo e(\Carbon\Carbon::parse($voyage['departure_date'])->format('M d')); ?></td>
                                        <td><?php echo e($voyage['route_from']); ?> - <?php echo e($voyage['route_to']); ?></td>
                                        <td><?php echo e(\Carbon\Carbon::parse($voyage['departure_time'])->format('h:i A')); ?></td>
                                        <td><?php echo e($voyage['vessel_name']); ?></td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="4" class="text-center">
                                            <div class="text-muted">
                                                <i class="bi bi-calendar-x fs-1 d-block"></i>
                                                No voyages available in the next 8 days
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>

            <!-- Right Side: Booking Selection -->
            <div class="col-lg-6">
                <div class="booking-selection-content d-flex flex-column">

                    <div class="text-center">
                        <h4 class="text-primary">Select Route & Schedule</h4>
                    </div>

                    <!-- Booking Type -->
                    <div class="booking-type-section">
                        <h6 class="text-secondary">Booking Type</h6>

                        <div class="booking-type-options">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="bookingType" id="passengerType"
                                    value="passenger" checked>
                                <label class="form-check-label" for="passengerType">Passenger</label>
                            </div>

                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="bookingType" id="cargoType"
                                    value="cargo">
                                <label class="form-check-label" for="cargoType">Cargo</label>
                            </div>
                        </div>
                    </div>

                    <!-- Route Selection -->
                    <div class="route-selection">
                        <div class="route-inputs">
                            <div class="route-input-group">
                                <label class="form-label">From</label>
                                <select id="routeFrom" class="form-select">
                                    <option value="">Select Origin</option>
                                    <?php $__currentLoopData = collect($voyages)->pluck('route_from')->unique(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $origin): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($origin); ?>"><?php echo e($origin); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>

                            <div class="route-arrow">
                                <i class="bi bi-arrow-right fs-4 text-primary"></i>
                            </div>

                            <div class="route-input-group">
                                <label class="form-label">To</label>
                                <select id="routeTo" class="form-select" disabled>
                                    <option value="">Select Destination</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Date and Time Selection -->
                    <div class="date-time-selection">
                        <div class="datetime-inputs">
                            <div class="datetime-input-group">
                                <label class="form-label">Departure Date</label>
                                <div class="calendar-input-wrapper">
                                    <span class="calendar-icon"><i class="bi bi-calendar-event"></i></span>
                                    <input type="date" id="tripDate" class="calendar-input form-control" disabled>
                                </div>
                            </div>

                            <div class="datetime-input-group">
                                <label class="form-label">Departure Time</label>
                                <select id="departureTime" class="form-select" disabled>
                                    <option value="">Select Time</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Spacer to push button to bottom -->
                    <div class="spacer"></div>

                    <!-- Proceed Button -->
                    <button id="proceedBtn" class="proceed-btn btn btn-primary btn-lg" type="button"
                        data-passenger-url="<?php echo e(route('passengerbooking')); ?>" data-cargo-url="<?php echo e(route('cargobooking')); ?>"
                        disabled>
                        <i class="bi bi-arrow-right-circle me-2"></i>PROCEED
                    </button>

                </div>
            </div>

        </div>
    </div>

    <script type="application/json" id="voyages-data"><?php echo json_encode($voyages); ?></script>
    <script src="<?php echo e(asset('js/bookingtype.js')); ?>"></script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\clint\Desktop\Capstone\resources\views/passenger/bookingtype.blade.php ENDPATH**/ ?>