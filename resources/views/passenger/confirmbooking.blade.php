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
            if (bookingStatus !== 'pending') {
                // Booking is no longer pending, redirect away
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
                        <h6>Booking Reference: {{ $booking->booking_ref_no }}</h6>
                        <p>Status: <strong>{{ $booking->booking_status }}</strong></p>

                        @if ($payment)
                            <p>Total: <strong>PHP {{ number_format($payment->total_amount, 2) }}</strong></p>
                            <p>Payment Status: <strong>{{ $payment->payment_status }}</strong></p>
                        @endif

                        <hr>

                        <h6>Passengers</h6>
                        <ul class="list-group mb-3">
                            @foreach ($passengers as $item)
                                <li class="list-group-item">
                                    <strong>{{ $item['passenger']->passenger_firstname }}
                                        {{ $item['passenger']->passenger_lastname }}</strong>
                                    <div>Cot: {{ $item['ticket']->pt_cot_no }}</div>
                                    <div>Price: PHP {{ number_format($item['ticket']->pt_ticket_price, 2) }}</div>
                                </li>
                            @endforeach
                        </ul>

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
                                <button type="button" id="cancelBtn" class="btn btn-outline-secondary">Cancel</button>
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

            function update() {
                const now = new Date();
                const diff = validUntil - now;
                if (diff <= 0) {
                    document.getElementById('countdown').innerText = 'Expired';
                    const payBtn = document.getElementById('payBtn');
                    if (payBtn) payBtn.classList.add('disabled');
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
                    alert('Missing booking reference.');
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

                const bookingRef = '{{ $booking->booking_ref_no }}';
                if (!bookingRef) {
                    alert('Missing booking reference.');
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
                        alert('Booking canceled successfully.');
                        // Redirect to booking type page
                        window.location.href = data.redirect_url || '{{ route('bookingtype') }}';
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
@endsection
