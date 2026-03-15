<?php $__env->startSection('page-title', 'RESERVATION'); ?>
<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('components.authHeader', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo $__env->make('components.staff_nav', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="staff-body">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card shadow-lg">
                        <div class="card-header bg-primary text-white text-center">
                            <h4 class="mb-0"><i class="fas fa-clock"></i> Reservation Summary</h4>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info mx-auto" style="text-align: center;">
                                <div><i class="fas fa-info-circle"></i> <strong>Note:</strong> This reservation will expire
                                    if
                                    not completed within the time limit.</div>
                            </div>

                            <!-- Timer Display -->
                            <div class="text-center mb-4 p-4 bg-light rounded">
                                <p class="mb-2"><strong>Reservation Time Remaining:</strong></p>
                                <h1 class="text-danger mb-0" id="reservationTimer">05:00</h1>
                                <small class="text-muted">Complete the booking before time expires</small>
                            </div>

                            <!-- Booking Reference -->
                            <div class="text-center mb-4">
                                <p class="mb-1"><strong>Booking Reference:</strong></p>
                                <h3 class="text-primary"><?php echo e($bookingRefNo); ?></h3>
                            </div>

                            <!-- Payment Summary -->
                            <div class="card mb-4">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0"><i class="fas fa-receipt"></i> Payment Summary</h5>
                                </div>
                                <div class="card-body">
                                    <?php $__currentLoopData = $passengers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $passenger): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Passenger <?php echo e($index + 1); ?> (<?php echo e($passenger['type']); ?>):</span>
                                            <span>₱<?php echo e(number_format($passenger['price'], 2)); ?></span>
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    <hr>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0">Total Amount to Pay:</h5>
                                        <h3 class="mb-0 text-primary">₱<?php echo e(number_format($totalAmount, 2)); ?></h3>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="d-grid gap-3">
                                <form action="<?php echo e(route('staff.passenger_booking.complete_cash', $bookingRefNo)); ?>"
                                    method="POST" id="cashForm">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" class="btn btn-success btn-lg w-100">
                                        <i class="fas fa-money-bill-wave"></i> Continue with Cash
                                    </button>
                                </form>

                                <form action="<?php echo e(route('staff.passenger_booking.complete_gcash', $bookingRefNo)); ?>"
                                    method="POST" id="gcashForm">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" class="btn btn-info btn-lg w-100">
                                        <i class="fas fa-mobile-alt"></i> Continue with GCash
                                    </button>
                                </form>

                                <form action="<?php echo e(route('staff.passenger_booking.cancel', $bookingRefNo)); ?>" method="POST"
                                    id="cancelForm">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" class="btn btn-danger btn-lg w-100">
                                        <i class="fas fa-times"></i> Cancel Reservation
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let reservationTimeLeft = 300; // 5 minutes in seconds
        let timerInterval = null;
        let isFormSubmitting = false; // Flag to prevent beforeunload when submitting forms

        // Replace the passenger form in browser history with the staff booking create page
        // This way, back button skips the form and goes directly to booking create page
        if (window.history && window.history.replaceState) {
            window.history.replaceState(null, '', window.location.href);
            window.history.pushState({
                page: 'reservation'
            }, '', window.location.href);
        }

        // Handle back button: cancel booking and redirect to staff booking create page
        window.addEventListener('popstate', function(event) {
            if (!isFormSubmitting && reservationTimeLeft > 0) {
                // Cancel the booking
                const formData = new FormData();
                formData.append('_token', '<?php echo e(csrf_token()); ?>');
                navigator.sendBeacon('<?php echo e(route('staff.passenger_booking.cancel', $bookingRefNo)); ?>', formData);

                // Stop the timer
                clearInterval(timerInterval);

                // Redirect to staff booking create page
                window.location.href = '<?php echo e(route('staff.passenger_booking.create')); ?>';
            }
        });

        // Prevent page from being cached
        window.onpageshow = function(event) {
            if (event.persisted || performance.navigation.type === 2) {
                // Page was loaded from cache (back/forward button)
                window.location.reload();
            }
        };

        // Check on page load if booking is still valid
        window.addEventListener('DOMContentLoaded', function() {
            const bookingStatus = '<?php echo e(strtolower($booking->booking_status ?? 'pending')); ?>';
            if (bookingStatus !== 'pending') {
                // Booking is no longer pending, redirect away
                alert('This reservation is no longer active.');
                window.location.href = '<?php echo e(route('staff.passenger_booking.create')); ?>';
            }
        });

        function startTimer() {
            updateTimerDisplay();

            timerInterval = setInterval(() => {
                reservationTimeLeft--;
                updateTimerDisplay();

                if (reservationTimeLeft <= 0) {
                    clearInterval(timerInterval);
                    // Auto-cancel the booking
                    autoCancelReservation();
                }
            }, 1000);
        }

        function updateTimerDisplay() {
            const minutes = Math.floor(reservationTimeLeft / 60);
            const seconds = reservationTimeLeft % 60;
            document.getElementById('reservationTimer').textContent =
                `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
        }

        async function autoCancelReservation() {
            try {
                await fetch('<?php echo e(route('staff.passenger_booking.cancel', $bookingRefNo)); ?>', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
                        'Content-Type': 'application/json'
                    }
                });
            } catch (error) {
                console.error('Auto-cancel error:', error);
            }

            alert('Reservation time expired. Booking has been cancelled.');
            window.location.href = '<?php echo e(route('staff.passenger_booking.create')); ?>';
        }

        // Start timer when page loads
        startTimer();

        // Auto-cancel booking if user leaves the page (closes tab, navigates away, etc.)
        // BUT NOT when they're submitting a legitimate form
        window.addEventListener('beforeunload', function(e) {
            if (reservationTimeLeft > 0 && !isFormSubmitting) {
                // Send cancel request synchronously
                navigator.sendBeacon(
                    '<?php echo e(route('staff.passenger_booking.cancel', $bookingRefNo)); ?>',
                    new URLSearchParams({
                        '_token': '<?php echo e(csrf_token()); ?>'
                    })
                );
            }
        });

        // Clear timer and set flag when forms are submitted
        document.getElementById('cashForm').addEventListener('submit', function(e) {
            if (confirm('Confirm that cash payment has been received?')) {
                isFormSubmitting = true;
                clearInterval(timerInterval);
                return true;
            } else {
                e.preventDefault();
                return false;
            }
        });
        document.getElementById('gcashForm').addEventListener('submit', function(e) {
            if (confirm('Confirm that customer has shown proof of GCash payment?')) {
                isFormSubmitting = true;
                clearInterval(timerInterval);
                return true;
            } else {
                e.preventDefault();
                return false;
            }
        });
        document.getElementById('cancelForm').addEventListener('submit', function(e) {
            if (confirm('Are you sure you want to cancel this reservation?')) {
                isFormSubmitting = true;
                clearInterval(timerInterval);
                return true;
            } else {
                e.preventDefault();
                return false;
            }
        });
    </script>

    <style>
        .staff-body {
            padding: 2rem;
            background: #f8f9fa;
            min-height: 100vh;
        }

        #reservationTimer {
            font-size: 4rem;
            font-weight: bold;
        }

        .alert {
            text-align: center !important;
        }
    </style>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Shem\Desktop\Capstone\resources\views/authorized/staff/reservation.blade.php ENDPATH**/ ?>