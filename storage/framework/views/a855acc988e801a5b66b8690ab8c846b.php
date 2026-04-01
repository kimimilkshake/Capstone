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

                        <h6>Booking Reference: #<?php echo e($booking->booking_ref_no); ?></h6>
                        <p>Status: <strong><?php echo e($booking->booking_status); ?></strong></p>

                        <hr>

                        <h6>Passengers</h6>
                        <?php $grandTotal = 0; ?>
                        <div class="mb-3">
                            <?php $__currentLoopData = $passengers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $routeRate = $item['route_rate'] ?? 0;
                                    $basePrice = $item['accommodation_base_price'] ?? null;
                                    $rateDisplay = rtrim(rtrim(number_format($routeRate, 2), '0'), '.');
                                    $ticketPrice = (float) $item['ticket']->pt_ticket_price;
                                    $grandTotal += $ticketPrice;

                                    $passengerType = $item['passenger']->passenger_type ?? 'Regular';
                                    $typeDiscountPct = $item['type_discount_rate'] ?? 0;

                                    $showBreakdown =
                                        ($routeRate > 0 && $basePrice !== null) ||
                                        $typeDiscountPct > 0 ||
                                        $item['ticket']->promo;
                                ?>
                                <div class="border rounded p-3 mb-2">
                                    
                                    <div class="fw-bold fs-5 mb-2">
                                        <?php echo e($item['passenger']->passenger_firstname); ?>

                                        <?php if($item['passenger']->passenger_midinitial): ?>
                                            <?php echo e($item['passenger']->passenger_midinitial); ?>.
                                        <?php endif; ?>
                                        <?php echo e($item['passenger']->passenger_lastname); ?>

                                        <?php if($item['passenger']->passenger_suffix): ?>
                                            <?php echo e($item['passenger']->passenger_suffix); ?>

                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between small mb-0">
                                        <div style="white-space: nowrap;"><span class="text-muted">Type: </span><strong
                                                class="text-dark"><?php echo e($item['passenger']->passenger_type); ?></strong></div>
                                        <div style="white-space: nowrap;"><span class="text-muted">Cot: </span><strong
                                                class="text-dark"><?php echo e($item['ticket']->pt_cot_no); ?></strong></div>
                                        <div style="white-space: nowrap;">
                                            <?php if($item['accommodation_name']): ?>
                                                <span class="text-muted">Accommodation: </span><strong
                                                    class="text-dark"><?php echo e($item['accommodation_name']); ?></strong>
                                            <?php endif; ?>
                                        </div>
                                        <div style="white-space: nowrap;">
                                            <?php if($routeRate > 0 && $basePrice !== null): ?>
                                                <span class="text-muted">Accommodation Price: </span><strong
                                                    class="text-dark">PHP <?php echo e(number_format($basePrice, 2)); ?></strong>
                                            <?php else: ?>
                                                <span class="text-muted">Price: </span><strong class="text-dark">PHP
                                                    <?php echo e(number_format($ticketPrice, 2)); ?></strong>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <?php if($showBreakdown): ?>
                                        <div class="border-top mt-2 pt-2">
                                            <?php if($routeRate > 0 && $basePrice !== null): ?>
                                                <div class="d-flex align-items-center text-muted small">
                                                    <span style="white-space:nowrap;">Route Rate</span>
                                                    <span style="flex:1;border-bottom:2px dotted #aaa;margin:0 8px;"></span>
                                                    <span style="white-space:nowrap;">+<?php echo e($rateDisplay); ?>%</span>
                                                </div>
                                            <?php endif; ?>
                                            <?php if($typeDiscountPct > 0): ?>
                                                <div class="d-flex align-items-center text-muted small">
                                                    <span style="white-space:nowrap;">Passenger Type Discount</span>
                                                    <span style="flex:1;border-bottom:2px dotted #aaa;margin:0 8px;"></span>
                                                    <span style="white-space:nowrap;">-<?php echo e($typeDiscountPct); ?>%</span>
                                                </div>
                                            <?php endif; ?>
                                            <?php if($item['ticket']->promo): ?>
                                                <div class="d-flex align-items-center text-muted small">
                                                    <span style="white-space:nowrap;">Promo</span>
                                                    <span style="flex:1;border-bottom:2px dotted #aaa;margin:0 8px;"></span>
                                                    <span
                                                        style="white-space:nowrap;">-<?php echo e($item['ticket']->promo->promo_discount_rate); ?>%</span>
                                                </div>
                                            <?php endif; ?>
                                            <div class="d-flex align-items-center mt-1">
                                                <span class="fw-semibold" style="white-space:nowrap;">Total</span>
                                                <span style="flex:1;border-bottom:2px dotted #888;margin:0 8px;"></span>
                                                <span class="fw-semibold" style="white-space:nowrap;">PHP
                                                    <?php echo e(number_format($ticketPrice, 2)); ?></span>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                            
                            <?php if(count($passengers) > 1): ?>
                                <div class="border rounded p-3 bg-dark text-white d-flex justify-content-between">
                                    <span class="fw-bold">Grand Total</span>
                                    <span class="fw-bold">PHP <?php echo e(number_format($grandTotal, 2)); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>

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
                                <button type="button" id="cancelBtn" class="btn btn-outline-danger">Cancel</button>
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
                    showToast('Missing booking reference.', 'danger');
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
                        showToast('Payment initialization failed (server error). Check logs.', 'danger');
                        return;
                    }

                    let data = null;
                    try {
                        data = await res.json();
                    } catch (jsonErr) {
                        const txt = await res.text();
                        console.error('Failed to parse JSON from create-source', txt, jsonErr);
                        showToast('Payment initialization failed (invalid response).', 'danger');
                        return;
                    }

                    if (!data || !data.success) {
                        console.error('create-source failed', data);
                        showToast((data && data.message) ? data.message : 'Failed to initialize payment.',
                            'danger');
                        return;
                    }

                    // Redirect user to PayMongo checkout
                    const checkoutUrl = data.checkout_url || data.checkout || data.redirect_url;
                    if (checkoutUrl) {
                        window.location.href = checkoutUrl;
                    } else {
                        console.error('No checkout_url in create-source response', data);
                        showToast('Checkout URL not returned by payment provider.', 'danger');
                    }
                } catch (err) {
                    console.error('Error calling create-source', err);
                    showToast('Error initializing payment. See console and server logs.', 'danger');
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
            cancelBtnEl.addEventListener('click', function(e) {
                e.preventDefault();

                // Prevent stacking: disable while confirmation toast is visible
                cancelBtnEl.disabled = true;

                showToast('Are you sure you want to cancel this booking?', 'warning', true);

                const container = document.getElementById('globalToastContainer');
                const toast = container.querySelector('.toast:last-child');
                if (toast) {
                    const body = toast.querySelector('.toast-body');
                    const closeBtn = toast.querySelector('.btn-close');
                    if (closeBtn) closeBtn.remove();

                    body.innerHTML = `
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Are you sure you want to cancel this booking?
                        <div class="mt-2 d-flex gap-2 justify-content-end">
                            <button class="btn btn-sm btn-light" id="confirmCancelBooking">Yes, cancel</button>
                            <button class="btn btn-sm btn-outline-light" id="stayCancelBooking">No, keep it</button>
                        </div>
                    `;

                    document.getElementById('stayCancelBooking').addEventListener('click', function() {
                        bootstrap.Toast.getInstance(toast).hide();
                        cancelBtnEl.disabled = false;
                    });

                    toast.addEventListener('hidden.bs.toast', function() {
                        // Re-enable if user didn't confirm (confirmation sets disabled permanently)
                        if (cancelBtnEl.innerText !== 'Canceling...') {
                            cancelBtnEl.disabled = false;
                        }
                    });

                    document.getElementById('confirmCancelBooking').addEventListener('click', async function() {
                        bootstrap.Toast.getInstance(toast).hide();

                        // Set flag to prevent beforeunload cancellation
                        isFormSubmitting = true;

                        const bookingRef = '<?php echo e($booking->booking_ref_no); ?>';
                        if (!bookingRef) {
                            showToast('Missing booking reference.', 'danger');
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
                                showToast('Booking canceled successfully.', 'success');
                                setTimeout(() => {
                                    window.location.href = data.redirect_url ||
                                        '<?php echo e(route('bookingtype')); ?>';
                                }, 1500);
                            } else {
                                showToast(data.message || 'Failed to cancel booking.', 'danger');
                                cancelBtnEl.disabled = false;
                                cancelBtnEl.innerText = 'Cancel';
                            }
                        } catch (err) {
                            console.error('Error canceling booking', err);
                            showToast('Error canceling booking. Please try again.', 'danger');
                            cancelBtnEl.disabled = false;
                            cancelBtnEl.innerText = 'Cancel';
                        }
                    });
                }
            });
        }
    </script>

    <?php echo $__env->make('components.footer', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Sophia\Documents\Capstone\resources\views/passenger/confirmbooking.blade.php ENDPATH**/ ?>