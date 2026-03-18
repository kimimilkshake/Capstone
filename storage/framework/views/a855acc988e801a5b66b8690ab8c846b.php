<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('components.hero', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <script>
        let isFormSubmitting = false;

        // Replace the passenger form in browser history with booking type page
        // This way, back button skips the form and goes directly to booking type
        if (window.history && window.history.replaceState) {
            // Replace the previous history entry (passenger form) with booking type
            const bookingTypeUrl = '<?php echo e(route('bookingtype')); ?>';
            window.history.replaceState(null, '', window.location.href);

            // Push current state again so back button will trigger popstate
            window.history.pushState({
                page: 'confirmbooking'
            }, '', window.location.href);
        }

        // Handle back button: cancel booking and redirect to booking type
        window.addEventListener('popstate', function(event) {
            if (!isFormSubmitting) {
                const bookingRef = '<?php echo e($booking->booking_ref_no); ?>';
                const bookingStatus = '<?php echo e(strtolower($booking->booking_status)); ?>';

                // Cancel the booking
                if (bookingStatus === 'pending') {
                    const formData = new FormData();
                    formData.append('_token', '<?php echo e(csrf_token()); ?>');
                    navigator.sendBeacon('<?php echo e(route('booking.cancel', $booking->booking_ref_no)); ?>', formData);
                }

                // Redirect to booking type
                window.location.href = '<?php echo e(route('bookingtype')); ?>';
            }
        });

        // Cancel booking when user leaves the page (close tab, etc.)
        window.addEventListener('beforeunload', function(e) {
            if (isFormSubmitting) {
                return; // Allow legitimate form submission
            }

            const bookingRef = '<?php echo e($booking->booking_ref_no); ?>';
            const bookingStatus = '<?php echo e(strtolower($booking->booking_status)); ?>';

            // Only cancel if booking is still pending
            if (bookingStatus === 'pending') {
                // Use sendBeacon for reliable background request
                const formData = new FormData();
                formData.append('_token', '<?php echo e(csrf_token()); ?>');

                navigator.sendBeacon(
                    '<?php echo e(route('booking.cancel', $booking->booking_ref_no)); ?>',
                    formData
                );
            }
        });

        // Prevent page from being cached and force reload on navigation
        window.onpageshow = function(event) {
            if (event.persisted || performance.navigation.type === 2) {
                // Page was loaded from cache (back/forward button)
                window.location.reload();
            }
        };

        // Also check on page load if booking is still valid
        window.addEventListener('DOMContentLoaded', function() {
            const bookingStatus = '<?php echo e(strtolower($booking->booking_status)); ?>';
            const paymentStatus = '<?php echo e($payment ? strtolower($payment->payment_status) : ''); ?>';

            // If booking is confirmed and payment is completed, reload to show updated state
            if (bookingStatus === 'confirmed' && paymentStatus === 'completed') {
                // Booking was completed - redirect to homepage
                window.location.href = '<?php echo e(route('homepage')); ?>';
            } else if (bookingStatus !== 'pending') {
                // For other non-pending statuses, redirect immediately
                window.location.href = '<?php echo e(route('bookingtype')); ?>';
            }
        });
    </script>

    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0">Confirm Booking</h5>
                    </div>
                    <div class="card-body">
                        <?php if(
                            $payment &&
                                strtolower($payment->payment_status) === 'completed' &&
                                strtolower($booking->booking_status) === 'confirmed'): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <strong>✓ Payment Successful!</strong> Your booking has been confirmed. Check your email for
                                your ticket details.
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <h6>Booking Reference: #<?php echo e($booking->booking_ref_no); ?></h6>
                        <p>Status: <strong><?php echo e($booking->booking_status); ?></strong></p>

                        <?php if($payment): ?>
                            <p>Total: <strong>PHP <?php echo e(number_format($payment->total_amount, 2)); ?></strong></p>
                            <p>Payment Status: <strong><?php echo e($payment->payment_status); ?></strong></p>
                        <?php endif; ?>

                        <hr>

                        <h6>Passengers</h6>
                        <ul class="list-group mb-3">
                            <?php $__currentLoopData = $passengers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li class="list-group-item">
                                    <strong><?php echo e($item['passenger']->passenger_firstname); ?>

                                        <?php echo e($item['passenger']->passenger_lastname); ?></strong>
                                    <div>Type: <?php echo e($item['passenger']->passenger_type); ?></div>
                                    <div>Cot: <?php echo e($item['ticket']->pt_cot_no); ?></div>
                                    <div>Price: PHP <?php echo e(number_format($item['ticket']->pt_ticket_price, 2)); ?>

                                        <?php if($item['ticket']->promo): ?>
                                            <span class="badge bg-success">Promo:
                                                -<?php echo e($item['ticket']->promo->promo_discount_rate); ?>%</span>
                                        <?php endif; ?>
                                    </div>
                                </li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>

                        <div class="mb-3">
                            <p>Your hold will expire in: <span id="countdown">--:--</span></p>
                        </div>

                        <div class="d-flex justify-content-between">
                            <?php
                                $canCancel = strtolower($booking->booking_status) === 'pending';
                                if ($payment && strtolower($payment->payment_status) === 'completed') {
                                    $canCancel = false; // Cannot cancel if payment is completed
                                }
                            ?>

                            <?php if($canCancel): ?>
                                <button type="button" id="cancelBtn" class="btn btn-outline-secondary">Cancel</button>
                            <?php else: ?>
                                <button type="button" class="btn btn-outline-secondary" disabled>
                                    <?php if(strtolower($booking->booking_status) === 'canceled'): ?>
                                        Already Canceled
                                    <?php elseif(strtolower($booking->booking_status) === 'confirmed'): ?>
                                        Cannot Cancel
                                    <?php elseif(isset($payment) && strtolower($payment->payment_status) === 'completed'): ?>
                                        Payment Completed
                                    <?php else: ?>
                                        Cannot Cancel
                                    <?php endif; ?>
                                </button>
                            <?php endif; ?>

                            <?php
                                $canPay = false;
                                if (
                                    isset($payment) &&
                                    strtolower($payment->payment_status) === 'pending' &&
                                    strtolower($booking->booking_status) === 'pending'
                                ) {
                                    $canPay = true;
                                }
                            ?>

                            <?php if($canPay): ?>
                                <button id="payBtn" class="btn btn-primary">Pay with GCash (PayMongo)</button>
                            <?php else: ?>
                                <button class="btn btn-secondary" disabled>
                                    <?php if(strtolower($booking->booking_status) === 'canceled'): ?>
                                        Booking canceled
                                    <?php elseif(isset($payment) && strtolower($payment->payment_status) !== 'pending'): ?>
                                        Payment: <?php echo e($payment->payment_status ?? 'N/A'); ?>

                                    <?php else: ?>
                                        Payment unavailable
                                    <?php endif; ?>
                                </button>
                            <?php endif; ?>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // compute countdown from the earliest ticket valid-until-ts
        (function() {
            // Use server-provided epoch ms when available for robust parsing
            const validUntilMs = <?php echo json_encode($validUntilMs ?? null, 15, 512) ?>;
            let validUntil = null;
            if (validUntilMs) {
                validUntil = new Date(validUntilMs);
            }

            // If no validUntil found, and booking is canceled or payment canceled, show Expired
            const bookingStatus = '<?php echo e(strtolower($booking->booking_status)); ?>';
            const paymentStatus = '<?php echo e(isset($payment) ? strtolower($payment->payment_status) : ''); ?>';
            if (!validUntil) {
                if (bookingStatus === 'canceled' || paymentStatus === 'canceled') {
                    document.getElementById('countdown').innerText = 'Expired';
                }
                return;
            }

            let hasExpired = false;

            function update() {
                const now = new Date();
                const diff = validUntil - now;
                if (diff <= 0) {
                    if (!hasExpired) {
                        hasExpired = true;
                        document.getElementById('countdown').innerText = 'Expired';
                        const payBtn = document.getElementById('payBtn');
                        if (payBtn) payBtn.classList.add('disabled');
                        // Redirect to booking page after 2 seconds
                        setTimeout(() => {
                            window.location.href = '/passenger/bookingtype';
                        }, 2000);
                    }
                    return;
                }
                const mins = Math.floor(diff / 60000);
                const secs = Math.floor((diff % 60000) / 1000);
                document.getElementById('countdown').innerText = `${mins}:${secs.toString().padStart(2,'0')}`;
            }
            update();
            setInterval(update, 1000);
        })();

        const payBtnEl = document.getElementById('payBtn');
        if (payBtnEl) {
            payBtnEl.addEventListener('click', async function(e) {
                e.preventDefault();
                const bookingRef = '<?php echo e($booking->booking_ref_no); ?>';
                if (!bookingRef) {
                    alert('Missing booking reference.');
                    return;
                }

                // Set flag to prevent beforeunload cancellation
                isFormSubmitting = true;

                // Disable button to prevent double clicks
                payBtnEl.disabled = true;
                payBtnEl.innerText = 'Initializing...';

                try {
                    const res = await fetch('<?php echo e(url('/paymongo/create-source')); ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
                        },
                        body: JSON.stringify({
                            booking_ref_no: bookingRef
                        })
                    });

                    // If the server returned a non-JSON (error page), attempt to surface that
                    if (!res.ok) {
                        let text = await res.text();
                        console.error('create-source response not ok', res.status, text);
                        alert('Payment initialization failed (server error). Check logs.');
                        return;
                    }

                    let data = null;
                    try {
                        data = await res.json();
                    } catch (jsonErr) {
                        const txt = await res.text();
                        console.error('Failed to parse JSON from create-source', txt, jsonErr);
                        alert('Payment initialization failed (invalid response).');
                        return;
                    }

                    if (!data || !data.success) {
                        console.error('create-source failed', data);
                        alert((data && data.message) ? data.message : 'Failed to initialize payment.');
                        return;
                    }

                    // Redirect user to PayMongo checkout
                    const checkoutUrl = data.checkout_url || data.checkout || data.redirect_url;
                    if (checkoutUrl) {
                        window.location.href = checkoutUrl;
                    } else {
                        console.error('No checkout_url in create-source response', data);
                        alert('Checkout URL not returned by payment provider.');
                    }
                } catch (err) {
                    console.error('Error calling create-source', err);
                    alert('Error initializing payment. See console and server logs.');
                } finally {
                    // Restore button state if still on this page
                    if (document.contains(payBtnEl)) {
                        payBtnEl.disabled = false;
                        payBtnEl.innerText = 'Pay with GCash (PayMongo)';
                    }
                }
            });
        }

        // Handle cancel button click
        const cancelBtnEl = document.getElementById('cancelBtn');
        if (cancelBtnEl && !cancelBtnEl.disabled) {
            cancelBtnEl.addEventListener('click', async function(e) {
                e.preventDefault();

                // Confirm cancellation
                if (!confirm('Are you sure you want to cancel this booking?')) {
                    return;
                }

                // Set flag to prevent beforeunload cancellation
                isFormSubmitting = true;

                const bookingRef = '<?php echo e($booking->booking_ref_no); ?>';
                if (!bookingRef) {
                    alert('Missing booking reference.');
                    return;
                }

                // Disable button to prevent double clicks
                cancelBtnEl.disabled = true;
                cancelBtnEl.innerText = 'Canceling...';

                try {
                    const res = await fetch(`<?php echo e(url('/booking/cancel')); ?>/${bookingRef}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
                        }
                    });

                    if (!res.ok) {
                        throw new Error(`HTTP error! status: ${res.status}`);
                    }

                    const data = await res.json();

                    if (data.success) {
                        alert('Booking canceled successfully.');
                        // Redirect to booking type page
                        window.location.href = data.redirect_url || '<?php echo e(route('bookingtype')); ?>';
                    } else {
                        alert(data.message || 'Failed to cancel booking.');
                        // Restore button state
                        cancelBtnEl.disabled = false;
                        cancelBtnEl.innerText = 'Cancel';
                    }
                } catch (err) {
                    console.error('Error canceling booking', err);
                    alert('Error canceling booking. Please try again.');
                    // Restore button state
                    cancelBtnEl.disabled = false;
                    cancelBtnEl.innerText = 'Cancel';
                }
            });
        }
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Sophia\Documents\Capstone\resources\views/passenger/confirmbooking.blade.php ENDPATH**/ ?>