@extends('layouts.app')
@section('content')
    @include('components.hero')

    <script>
        let isFormSubmitting = false;

        // Replace the passenger form in browser history with booking type page
        // This way, back button skips the form and goes directly to booking type
        if (window.history && window.history.replaceState) {
            // Replace the previous history entry (passenger form) with booking type
            const bookingTypeUrl = '{{ route('bookingtype') }}';
            window.history.replaceState(null, '', window.location.href);

            // Push current state again so back button will trigger popstate
            window.history.pushState({
                page: 'confirmbooking'
            }, '', window.location.href);
        }

        // Handle back button: cancel booking and redirect to booking type
        window.addEventListener('popstate', function(event) {
            if (!isFormSubmitting) {
                const bookingRef = '{{ $booking->booking_ref_no }}';
                const bookingStatus = '{{ strtolower($booking->booking_status) }}';

                // Cancel the booking
                if (bookingStatus === 'pending') {
                    const formData = new FormData();
                    formData.append('_token', '{{ csrf_token() }}');
                    navigator.sendBeacon('{{ route('booking.cancel', $booking->booking_ref_no) }}', formData);
                }

                // Redirect to booking type
                window.location.href = '{{ route('bookingtype') }}';
            }
        });

        // Cancel booking when user leaves the page (close tab, etc.)
        window.addEventListener('beforeunload', function(e) {
            if (isFormSubmitting) {
                return; // Allow legitimate form submission
            }

            const bookingRef = '{{ $booking->booking_ref_no }}';
            const bookingStatus = '{{ strtolower($booking->booking_status) }}';

            // Only cancel if booking is still pending
            if (bookingStatus === 'pending') {
                // Use sendBeacon for reliable background request
                const formData = new FormData();
                formData.append('_token', '{{ csrf_token() }}');

                navigator.sendBeacon(
                    '{{ route('booking.cancel', $booking->booking_ref_no) }}',
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
            const bookingStatus = '{{ strtolower($booking->booking_status) }}';
            const paymentStatus = '{{ $payment ? strtolower($payment->payment_status) : '' }}';

            // If booking is confirmed and payment is completed, reload to show updated state
            if (bookingStatus === 'confirmed' && paymentStatus === 'completed') {
                // Booking was completed - redirect to homepage
                window.location.href = '{{ route('homepage') }}';
            } else if (bookingStatus !== 'pending') {
                // For other non-pending statuses, redirect immediately
                window.location.href = '{{ route('bookingtype') }}';
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

                        <h6>Booking Reference: #{{ $booking->booking_ref_no }}</h6>
                        <p>Status: <strong>{{ $booking->booking_status }}</strong></p>

                        <hr>

                        <h6>Passengers</h6>
                        @php $grandTotal = 0; @endphp
                        <div class="mb-3">
                            @foreach ($passengers as $item)
                                @php
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
                                @endphp
                                <div class="border rounded p-3 mb-2">
                                    {{-- Passenger name - large, on its own line --}}
                                    <div class="fw-bold fs-5 mb-2">
                                        {{ $item['passenger']->passenger_firstname }}
                                        @if ($item['passenger']->passenger_midinitial)
                                            {{ $item['passenger']->passenger_midinitial }}.
                                        @endif
                                        {{ $item['passenger']->passenger_lastname }}
                                        @if ($item['passenger']->passenger_suffix)
                                            {{ $item['passenger']->passenger_suffix }}
                                        @endif
                                    </div>
                                    {{-- Info row: spread edge to edge, each item left-aligned --}}
                                    <div class="d-flex justify-content-between small mb-0">
                                        <div style="white-space: nowrap;"><span class="text-muted">Type: </span><strong
                                                class="text-dark">{{ $item['passenger']->passenger_type }}</strong></div>
                                        <div style="white-space: nowrap;"><span class="text-muted">Cot: </span><strong
                                                class="text-dark">{{ $item['ticket']->pt_cot_no }}</strong></div>
                                        <div style="white-space: nowrap;">
                                            @if ($item['accommodation_name'])
                                                <span class="text-muted">Accommodation: </span><strong
                                                    class="text-dark">{{ $item['accommodation_name'] }}</strong>
                                            @endif
                                        </div>
                                        <div style="white-space: nowrap;">
                                            @if ($routeRate > 0 && $basePrice !== null)
                                                <span class="text-muted">Accommodation Price: </span><strong
                                                    class="text-dark">PHP {{ number_format($basePrice, 2) }}</strong>
                                            @else
                                                <span class="text-muted">Price: </span><strong class="text-dark">PHP
                                                    {{ number_format($ticketPrice, 2) }}</strong>
                                            @endif
                                        </div>
                                    </div>
                                    {{-- DISCOUNTS + ROUTE RATE + TOTAL --}}
                                    @if ($showBreakdown)
                                        <div class="border-top mt-2 pt-2">
                                            @if ($routeRate > 0 && $basePrice !== null)
                                                <div class="d-flex align-items-center text-muted small">
                                                    <span style="white-space:nowrap;">Route Rate</span>
                                                    <span style="flex:1;border-bottom:2px dotted #aaa;margin:0 8px;"></span>
                                                    <span style="white-space:nowrap;">+{{ $rateDisplay }}%</span>
                                                </div>
                                            @endif
                                            @if ($typeDiscountPct > 0)
                                                <div class="d-flex align-items-center text-muted small">
                                                    <span style="white-space:nowrap;">Passenger Type Discount</span>
                                                    <span style="flex:1;border-bottom:2px dotted #aaa;margin:0 8px;"></span>
                                                    <span style="white-space:nowrap;">-{{ $typeDiscountPct }}%</span>
                                                </div>
                                            @endif
                                            @if ($item['ticket']->promo)
                                                <div class="d-flex align-items-center text-muted small">
                                                    <span style="white-space:nowrap;">Promo</span>
                                                    <span style="flex:1;border-bottom:2px dotted #aaa;margin:0 8px;"></span>
                                                    <span
                                                        style="white-space:nowrap;">-{{ $item['ticket']->promo->promo_discount_rate }}%</span>
                                                </div>
                                            @endif
                                            <div class="d-flex align-items-center mt-1">
                                                <span class="fw-semibold" style="white-space:nowrap;">Total</span>
                                                <span style="flex:1;border-bottom:2px dotted #888;margin:0 8px;"></span>
                                                <span class="fw-semibold" style="white-space:nowrap;">PHP
                                                    {{ number_format($ticketPrice, 2) }}</span>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endforeach

                            {{-- GRAND TOTAL (only shown when multiple passengers) --}}
                            @if (count($passengers) > 1)
                                <div class="border rounded p-3 bg-dark text-white d-flex justify-content-between">
                                    <span class="fw-bold">Grand Total</span>
                                    <span class="fw-bold">PHP {{ number_format($grandTotal, 2) }}</span>
                                </div>
                            @endif
                        </div>

                        <div class="mb-3">
                            <p>Your hold will expire in: <span id="countdown">--:--</span></p>
                        </div>

                        <div class="d-flex justify-content-between">
                            @php
                                $canCancel = strtolower($booking->booking_status) === 'pending';
                                if ($payment && strtolower($payment->payment_status) === 'completed') {
                                    $canCancel = false; // Cannot cancel if payment is completed
                                }
                            @endphp

                            @if ($canCancel)
                                <button type="button" id="cancelBtn" class="btn btn-outline-danger">Cancel</button>
                            @else
                                <button type="button" class="btn btn-outline-secondary" disabled>
                                    @if (strtolower($booking->booking_status) === 'canceled')
                                        Already Canceled
                                    @elseif (strtolower($booking->booking_status) === 'confirmed')
                                        Cannot Cancel
                                    @elseif (isset($payment) && strtolower($payment->payment_status) === 'completed')
                                        Payment Completed
                                    @else
                                        Cannot Cancel
                                    @endif
                                </button>
                            @endif

                            @php
                                $canPay = false;
                                if (
                                    isset($payment) &&
                                    strtolower($payment->payment_status) === 'pending' &&
                                    strtolower($booking->booking_status) === 'pending'
                                ) {
                                    $canPay = true;
                                }
                            @endphp

                            @if ($canPay)
                                <button id="payBtn" class="btn btn-primary">Pay with GCash (PayMongo)</button>
                            @else
                                <button class="btn btn-secondary" disabled>
                                    @if (strtolower($booking->booking_status) === 'canceled')
                                        Booking canceled
                                    @elseif (isset($payment) && strtolower($payment->payment_status) !== 'pending')
                                        Payment: {{ $payment->payment_status ?? 'N/A' }}
                                    @else
                                        Payment unavailable
                                    @endif
                                </button>
                            @endif
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
            const validUntilMs = @json($validUntilMs ?? null);
            let validUntil = null;
            if (validUntilMs) {
                validUntil = new Date(validUntilMs);
            }

            // If no validUntil found, and booking is canceled or payment canceled, show Expired
            const bookingStatus = '{{ strtolower($booking->booking_status) }}';
            const paymentStatus = '{{ isset($payment) ? strtolower($payment->payment_status) : '' }}';
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
                const bookingRef = '{{ $booking->booking_ref_no }}';
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
                    const res = await fetch('{{ url('/paymongo/create-source') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
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

                        const bookingRef = '{{ $booking->booking_ref_no }}';
                        if (!bookingRef) {
                            showToast('Missing booking reference.', 'danger');
                            return;
                        }

                        // Disable button to prevent double clicks
                        cancelBtnEl.disabled = true;
                        cancelBtnEl.innerText = 'Canceling...';

                        try {
                            const res = await fetch(`{{ url('/booking/cancel') }}/${bookingRef}`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
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
                                        '{{ route('bookingtype') }}';
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

    @include('components.footer')
@endsection
