<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('components.hero', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="trip-booking-container container">
        <div class="row align-items-stretch">

            <!-- Left Side: Available Voyages -->
            <div class="col-lg-6">
                <div class="table-container">
                    <h4 class="text-center text-primary">Available Voyages (Next 7 Days)</h4>
                    <p class="text-center text-muted small fst-italic mb-2">Click a voyage to auto-fill the form</p>

                    <div class="table-responsive">
                        <table class="table table-hover voyage-table">
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
                                    <tr class="voyage-row" style="cursor: pointer;"
                                        data-voyage-id="<?php echo e(data_get($voyage, 'voyage_id')); ?>"
                                        data-route-from="<?php echo e(data_get($voyage, 'route_from')); ?>"
                                        data-route-to="<?php echo e(data_get($voyage, 'route_to')); ?>"
                                        data-departure-date="<?php echo e(data_get($voyage, 'departure_date')); ?>"
                                        data-departure-time="<?php echo e(data_get($voyage, 'departure_time')); ?>">
                                        <?php
                                            $depDate = data_get($voyage, 'departure_date');
                                            $depTime = data_get($voyage, 'departure_time');
                                        ?>
                                        <td><?php echo e($depDate ? \Carbon\Carbon::parse($depDate)->format('M d') : '-'); ?></td>
                                        <td><?php echo e(data_get($voyage, 'route_from', '-')); ?> -
                                            <?php echo e(data_get($voyage, 'route_to', '-')); ?></td>
                                        <td><?php echo e($depTime ? \Carbon\Carbon::parse($depTime)->format('h:i A') : '-'); ?></td>
                                        <td><?php echo e(data_get($voyage, 'vessel_name', '-')); ?></td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="4" class="text-center">
                                            <div class="text-muted">
                                                <i class="bi bi-calendar-x fs-1 d-block"></i>
                                                No voyages available in the next 7 days
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

                    <div class="d-flex justify-content-between align-items-center mb-3 booking-header">
                        <h4 class="text-primary mb-0">Select Route & Schedule</h4>
                        <a href="#" id="requestTicketLink"
                            class="text-decoration-none text-primary fw-semibold d-none d-lg-block"
                            style="font-size: 0.9rem;">
                            <i class="fas fa-ticket-alt me-1"></i>Request Ticket Copy
                        </a>
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

                    <!-- Mobile-only: link to open ticket request popup -->
                    <a href="#" id="requestTicketLinkMobile"
                        class="text-decoration-none text-primary fst-italic text-center mt-2 d-block d-lg-none"
                        style="font-size: 0.9rem;">
                        <i class="fas fa-ticket-alt me-1"></i>Request Ticket Copy
                    </a>

                </div>
            </div>

        </div>

    </div>

    <!-- Modal Overlay for Request Ticket Copy -->
    <div id="ticketRequestModal" class="ticket-modal" style="display: none;">
        <div class="ticket-modal-overlay"></div>
        <div class="ticket-modal-content">
            <div class="ticket-modal-header">
                <h5><i class="fas fa-ticket-alt me-2"></i>Request Ticket Copy</h5>
                <button type="button" class="ticket-modal-close" id="closeTicketModal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="ticket-modal-body">
                <p class="text-muted text-center mb-4">Lost your ticket? Enter your email and departure date to receive a
                    copy.</p>

                <form id="requestTicketForm">
                    <?php echo csrf_field(); ?>
                    <div class="mb-3">
                        <label for="ticketEmail" class="form-label">Email Address <span
                                class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="ticketEmail" name="email" required
                            placeholder="your.email@example.com">
                    </div>
                    <div class="mb-3">
                        <label for="departureDate" class="form-label">Departure Date <span
                                class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="departureDate" name="departure_date" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label for="requestRouteFrom" class="form-label">From <span
                                    class="text-danger">*</span></label>
                            <select id="requestRouteFrom" name="route_from" class="form-select" required>
                                <option value="">Select Origin</option>
                                <?php $__currentLoopData = collect($voyages)->pluck('route_from')->unique(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $origin): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($origin); ?>"><?php echo e($origin); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="col-6">
                            <label for="requestRouteTo" class="form-label">To <span class="text-danger">*</span></label>
                            <select id="requestRouteTo" name="route_to" class="form-select" required disabled>
                                <option value="">Select Destination</option>
                            </select>
                        </div>
                    </div>
                    <div class="text-center">
                        <button type="submit" class="btn btn-primary w-100" id="requestTicketBtn">
                            <i class="fas fa-paper-plane me-2"></i>Request Ticket Copy
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script type="application/json" id="voyages-data"><?php echo json_encode($voyages); ?></script>
    <script src="<?php echo e(asset('js/bookingtype.js')); ?>"></script>
    <script>
        // Modal functionality
        const modal = document.getElementById('ticketRequestModal');
        const openLink = document.getElementById('requestTicketLink');
        const closeBtn = document.getElementById('closeTicketModal');
        const overlay = modal.querySelector('.ticket-modal-overlay');

        // Open modal trigger (desktop link)
        if (openLink) {
            openLink.addEventListener('click', function(e) {
                e.preventDefault();
                modal.style.display = 'block';
                // Force reflow for smooth animation
                modal.offsetHeight;
                modal.classList.add('active');
            });
        }

        // Open modal trigger (mobile link below PROCEED button)
        const mobileTicketLink = document.getElementById('requestTicketLinkMobile');
        if (mobileTicketLink) {
            mobileTicketLink.addEventListener('click', function(e) {
                e.preventDefault();
                modal.style.display = 'block';
                modal.offsetHeight;
                modal.classList.add('active');
            });
        }

        // Close modal function
        function closeModal() {
            modal.classList.remove('active');
            setTimeout(() => {
                modal.style.display = 'none';
            }, 200);
        }

        // Close button click
        closeBtn.addEventListener('click', closeModal);

        // Click overlay to close
        overlay.addEventListener('click', closeModal);

        // Close on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && modal.classList.contains('active')) {
                closeModal();
            }
        });

        // Handle route filtering for request ticket form
        const voyagesData = JSON.parse(document.getElementById('voyages-data').textContent);
        const requestRouteFromSelect = document.getElementById('requestRouteFrom');
        const requestRouteToSelect = document.getElementById('requestRouteTo');

        // Update destinations based on selected origin for request form
        function updateRequestDestinations(fromSelect, toSelect) {
            const origin = fromSelect.value;
            toSelect.innerHTML = '<option value="">Select Destination</option>';

            if (!origin) {
                toSelect.disabled = true;
                return;
            }

            const destinations = voyagesData
                .filter((v) => v.route_from === origin)
                .map((v) => v.route_to)
                .filter((v, i, a) => a.indexOf(v) === i);

            destinations.forEach((dest) => {
                const option = document.createElement('option');
                option.value = dest;
                option.textContent = dest;
                toSelect.appendChild(option);
            });

            toSelect.disabled = false;
        }

        // Add listeners for route filtering
        if (requestRouteFromSelect) {
            requestRouteFromSelect.addEventListener('change', function() {
                updateRequestDestinations(requestRouteFromSelect, requestRouteToSelect);
            });
        }

        // Form submission
        document.getElementById('requestTicketForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const btn = document.getElementById('requestTicketBtn');
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Searching...';

            const formData = new FormData(this);

            try {
                const response = await fetch('<?php echo e(route('ticket.request-copy')); ?>', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
                        'Accept': 'application/json',
                    },
                    body: formData
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    // Direct send - system automatically sends to most recent passenger
                    closeModal();
                    showToast('Ticket sent to your email!', 'success');
                    this.reset();
                } else {
                    showToast(data.message || 'No ticket found.', 'danger');
                }
            } catch (error) {
                console.error('Error:', error);
                showToast('An error occurred. Please try again.', 'danger');
            } finally {
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        });
    </script>

    <?php echo $__env->make('components.footer', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Shem\Desktop\Capstone\resources\views/passenger/bookingtype.blade.php ENDPATH**/ ?>