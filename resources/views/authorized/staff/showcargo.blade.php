@extends('layouts.app')
@section('page-title', 'CARGO BOOKING DETAILS')

@section('content')
    @include('components.authHeader')
    @include('components.staff_nav')

    <div class="staff-body">

        <div class="row">
            {{-- LEFT COLUMN: BOOKING INFO --}}
            <div class="col-lg-6">
                <div class="card shadow-sm p-4 mb-4">
                    <h5 class="mb-2">Booking Information</h5>

                    @php
                        // ✅ UNIT CONVERSION TO METERS
                        function toMeters($value, $unit)
                        {
                            $unit = strtolower($unit);

                            return match ($unit) {
                                'm' => $value,
                                'cm' => $value / 100,
                                'in' => $value * 0.0254,
                                'ft' => $value * 0.3048,
                                default => $value,
                            };
                        }

                        // ✅ CBM CALCULATION (NOW UNIT-CONSISTENT)
                        function computeCBM($cargo)
                        {
                            $unit = $cargo->measurementUnit->measurement_unit_abbreviation ?? 'cm';

                            $length = toMeters((float) $cargo->length, $unit);
                            $width = toMeters((float) $cargo->width, $unit);
                            $height = toMeters((float) $cargo->height, $unit);

                            return $length * $width * $height;
                        }

                        // ✅ CENTRALIZED SUBTOTAL CALCULATION
                        function computeSubtotal($cargo)
                        {
                            $freight = $cargo->cargoItem->cargo_item_freight ?? 0;
                            $qty = (float) ($cargo->quantity ?? 0);

                            $measureRequired = strtolower($cargo->cargoItem->cargo_item_measure_required ?? 'no');

                            if ($measureRequired === 'yes') {
                                return $freight * $qty;
                            }

                            $cbm = computeCBM($cargo);

                            return $freight * $cbm * $qty;
                        }

                        $processedBy = optional(optional($booking->cargoBookings->first())->approvedByStaff)
                            ->staff_name;
                        $processedLabel = 'N/A';

                        if ($processedBy) {
                            if ($booking->booking_status === 'Confirmed') {
                                $processedLabel = 'Approved by ' . $processedBy;
                            } elseif ($booking->booking_status === 'Canceled') {
                                $processedLabel = 'Canceled by ' . $processedBy;
                            } else {
                                $processedLabel = $processedBy;
                            }
                        }
                    @endphp

                    <p><strong>Booking Ref #:</strong> {{ $booking->booking_code }}</p>
                    <p><strong>Status:</strong> {{ $booking->booking_status }}</p>
                    <p><strong>Created:</strong> {{ $booking->created_at->format('M d, Y') }}</p>
                    <p><strong>Approved By:</strong> {{ $processedLabel }}</p>

                    @if ($booking->voyage)
                        <p><strong>Voyage Code:</strong> {{ $booking->voyage->voyage_code }}</p>
                        <p><strong>Departure:</strong> {{ $booking->voyage->voyage_departure_date }}</p>
                        <p><strong>Arrival:</strong> {{ $booking->voyage->voyage_arrival_date }}</p>
                    @else
                        <p><strong>Voyage:</strong> N/A</p>
                    @endif

                    @if ($payment)
                        <p><strong>Mode of Payment:</strong> {{ $payment->mode_of_payment }}</p>
                        <p><strong>Payment Status:</strong> {{ $payment->payment_status }}</p>

                        {{-- ✅ UPDATED CALCULATION --}}
                        @php
                            $calculatedTotal = 0;

                            foreach ($booking->cargoBookings as $c) {
                                $calculatedTotal += computeSubtotal($c);
                            }

                            $stamp = 20.0;
                            $calculatedTotal += $stamp;
                        @endphp

                        <p><strong>Amount Paid:</strong> ₱{{ number_format($payment->total_amount, 2) }}</p>
                    @endif
                </div>
            </div>

            {{-- RIGHT COLUMN: SENDER & CONSIGNEE --}}
            <div class="col-lg-6">
                <div class="card shadow-sm p-4 mb-4">

                    <h6 class="fw-bold">Sender Information</h6>
                    <p><strong>Name:</strong> {{ $booking->sender->sender_name }}</p>
                    <p><strong>Contact:</strong> {{ $booking->sender->sender_contactno }}</p>
                    <p><strong>Email:</strong> {{ $booking->sender->sender_email }}</p>

                    <h6 class="fw-bold mt-3">Consignee Information</h6>
                    <p><strong>Name:</strong> {{ $booking->consignee->consignee_name }}</p>
                    <p><strong>Contact:</strong> {{ $booking->consignee->consignee_contactno }}</p>
                </div>
            </div>
        </div>

        {{-- =============== --}}
        {{-- CARGO PHOTOS --}}
        {{-- =============== --}}
        @php
            $cargoBookings = $booking->cargoBookings;
            $cargoWithPhotos = $cargoBookings->filter(fn($c) => $c->cargo_picture)->values();
            $hasPhotos = $cargoWithPhotos->count() > 0;
        @endphp

        <div class="card shadow-sm p-4 mb-4">
            <h5 class="fw-bold mb-3">Cargo Photos</h5>

            @if (!$hasPhotos)
                <p class="text-muted text-center fst-italic">
                    No photos were included since the booking was made by the staff
                </p>
            @else
                <div id="cargoCarousel" class="carousel slide carousel-fade" data-bs-ride="carousel">
                    <div class="carousel-indicators">
                        @foreach ($cargoWithPhotos as $index => $cargo)
                            <button type="button" data-bs-target="#cargoCarousel" data-bs-slide-to="{{ $index }}"
                                class="{{ $index === 0 ? 'active' : '' }}"
                                aria-label="Slide {{ $index + 1 }}"></button>
                        @endforeach
                    </div>

                    <div class="carousel-inner">
                        @foreach ($cargoWithPhotos as $index => $cargo)
                            @php
                                $imgPath = asset('files/' . $cargo->cargo_picture);
                                $cargoDescription = $cargo->cargoItem->cargo_item_description ?? 'Unknown Cargo';
                                $cargoClassification = $cargo->cargoClassification->cargo_classification_name ?? '';
                            @endphp

                            <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                                <div class="carousel-image-container">
                                    <img src="{{ $imgPath }}" class="d-block w-100 carousel-img"
                                        alt="{{ $cargoDescription }}" style="cursor: default;"
                                        onerror="this.onerror=null;this.src='{{ asset('images/passenger.svg') }}';">

                                    <div class="carousel-caption-overlay">
                                        <h5 class="carousel-cargo-title">{{ $cargoDescription }}</h5>
                                        <p class="carousel-cargo-classification">{{ $cargoClassification }}</p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @if ($cargoWithPhotos->count() > 1)
                        <button class="carousel-control-prev" type="button" data-bs-target="#cargoCarousel"
                            data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Previous</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#cargoCarousel"
                            data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Next</span>
                        </button>
                    @endif
                </div>
            @endif
        </div>

        <div class="card shadow-sm p-3 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">Cargo Items</h5>
                @php
                    $units = $cargoBookings->pluck('measurementUnit.measurement_unit_abbreviation')->unique();
                    $unitLabel = $units->count() === 1 ? $units->first() : 'Mixed Units';
                @endphp
                @if ($unitLabel === 'm' || $unitLabel === 'M')
                    <span class="badge bg-info">Measurements in Meters</span>
                @elseif ($unitLabel === 'cm')
                    <span class="badge bg-secondary">Measurements in Centimeters</span>
                @elseif ($unitLabel === 'in' || $unitLabel === 'inch')
                    <span class="badge bg-secondary">Measurements in Inches</span>
                @elseif ($unitLabel !== 'Mixed Units')
                    <span class="badge bg-secondary">Measurements: {{ $unitLabel }}</span>
                @endif
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-striped mt-3 align-middle cargo-items-table">
                    <thead class="table-dark">
                        <tr>
                            <th>Qty</th>
                            <th>Classification</th>
                            <th>Description</th>
                            <th>Length @if ($unitLabel !== 'Mixed Units')
                                    ({{ $unitLabel }})
                                @endif
                            </th>
                            <th>Width @if ($unitLabel !== 'Mixed Units')
                                    ({{ $unitLabel }})
                                @endif
                            </th>
                            <th>Height @if ($unitLabel !== 'Mixed Units')
                                    ({{ $unitLabel }})
                                @endif
                            </th>
                            <th>CBM</th>
                            <th>Total Weight (kg)</th>
                            <th>Freight</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>

                    <tbody>
                        @php $total = 0; @endphp

                        @foreach ($booking->cargoBookings as $c)
                            @php
                                // ✅ FIXED: Use consistent CBM calculation
                                $cbm = computeCBM($c);

                                $freight = $c->cargoItem->cargo_item_freight ?? 0;

                                $subtotal = computeSubtotal($c);
                                $total += $subtotal;
                            @endphp

                            <tr>
                                <td class="text-center">{{ $c->quantity }}</td>
                                <td>{{ $c->cargoClassification->cargo_classification_name ?? 'N/A' }}</td>
                                <td>{{ $c->cargoItem->cargo_item_description }}</td>
                                <td class="text-end">{{ number_format($c->length, 2) }}</td>
                                <td class="text-end">{{ number_format($c->width, 2) }}</td>
                                <td class="text-end">{{ number_format($c->height, 2) }}</td>
                                <td class="text-end">{{ number_format($cbm, 4) }}</td>
                                <td class="text-end">{{ number_format($c->weight, 2) }}</td>
                                <td class="text-end">₱{{ number_format($freight, 2) }}</td>
                                <td class="text-end">₱{{ number_format($subtotal, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>

                    <tfoot>
                        <tr>
                            <th colspan="9" class="text-end">TOTAL:</th>
                            <th class="text-end">
                                @php
                                    // ✅ FINAL TOTAL RECALCULATION (NO TRUSTING OLD VALUES)
                                    $total = 0;

                                    foreach ($booking->cargoBookings as $c) {
                                        $total += computeSubtotal($c);
                                    }

                                    $stamp = 20.0;
                                    $totalTransaction = $total + $stamp;
                                @endphp

                                ₱{{ number_format($totalTransaction, 2) }}
                            </th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        @if ($booking->booking_status === 'Pending')
            <div class="d-flex justify-content-center gap-3 mt-4">
                <form action="{{ route('cargo.bookings.approve', $booking->booking_ref_no) }}" method="POST"
                    class="w-100" style="max-width: 200px;" id="acceptForm">
                    @csrf
                    <button type="button" class="btn btn-success btn-lg px-4 w-100" id="acceptBtn"
                        onclick="validateAndAccept(event)">Accept</button>
                </form>

                <button type="button" class="btn btn-danger btn-lg px-4" id="rejectBtn" onclick="showRejectModal(event)"
                    style="width: 200px;">Reject</button>
            </div>
        @endif

        @if ($booking->booking_status === 'Confirmed' && ($payment->payment_status ?? null) === 'Pending')
            <div class="text-center mt-3">
                <button type="button" class="btn btn-success btn-lg px-4" onclick="showPaymentModal()">
                    Pay Now
                </button>
            </div>
        @endif

        <div class="text-center mt-4">
            <a href="{{ route('cargo.bookings.pending') }}" class="btn btn-outline-primary btn-lg px-4">
                Back to Pending Bookings
            </a>

            <a href="{{ route('cargo.bookings.bol', $booking->booking_ref_no) }}" target="_blank"
                class="btn btn-secondary btn-lg px-4 ms-3">
                Freight Receipt
            </a>
        </div>
    </div>

    @if ($booking->booking_status === 'Pending')
        <div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form action="{{ route('cargo.bookings.reject', $booking->booking_ref_no) }}" method="POST"
                        onsubmit="showLoadingModal(event)">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title">Reason for Rejection</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Please provide the reason for rejecting this booking</label>
                                <textarea name="reason" class="form-control" rows="4" required></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-danger">Submit Rejection</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Placement Validation Modal --}}
    <div class="modal fade" id="placementValidationModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title" id="placementModalTitle">⚠️ Cargo Placement Validation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="placementMessage"></div>
                    <div id="unpackedItemsList" class="mt-3"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" id="confirmAcceptBtn"
                        onclick="proceedWithAcceptance()">Proceed with Acceptance</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Loading Modal --}}
    <div class="modal fade" id="loadingModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static"
        data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0">
                <div class="modal-body text-center p-5">
                    <div class="spinner-border text-primary mb-3" role="status" style="width: 60px; height: 60px;">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <h5 class="mt-3">Processing Payment...</h5>
                    <p class="text-muted mt-2">Please wait while we confirm your payment</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Payment Confirmation Modal --}}
    <div class="modal fade" id="paymentConfirmModal" tabindex="-1" aria-labelledby="paymentConfirmLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="paymentConfirmLabel">Confirm Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Would you like to pay with <strong id="confirmMethod">CASH</strong>?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
                    <button type="button" class="btn btn-success" id="confirmPaymentBtn">Yes</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        /**
         * Show reject modal
         */
        function showRejectModal(event) {
            event.preventDefault();
            const modal = new bootstrap.Modal(document.getElementById('rejectModal'));
            modal.show();
        }

        /**
         * Show loading modal when form is submitted
         */
        function showLoadingModal(event) {
            event.preventDefault();
            const modal = new bootstrap.Modal(document.getElementById('loadingModal'));
            modal.show();

            // Submit the form after showing the modal
            setTimeout(() => {
                event.target.submit();
            }, 500);
        }

        /**
         * Validate cargo placement before accepting booking
         */
        function validateAndAccept(event) {
            event.preventDefault();

            const voyageId = {{ $booking->voyage_id }};
            const cargoBookingIds = [
                @foreach ($booking->cargoBookings as $cargo)
                    {{ $cargo->cargo_booking_id }},
                @endforeach
            ];

            if (cargoBookingIds.length === 0) {
                // If no cargo items, just show loading and submit
                showLoadingAndSubmit();
                return;
            }

            // Show loading indicator
            const btn = event.target;
            const originalText = btn.innerHTML;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Validating placement...';
            btn.disabled = true;

            // Call API to validate placement
            fetch('{{ route('cargo.placement.validate') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ||
                            document.querySelector('input[name="_token"]')?.value
                    },
                    body: JSON.stringify({
                        voyage_id: voyageId,
                        cargo_booking_ids: cargoBookingIds
                    })
                })
                .then(response => response.json())
                .then(data => {
                    btn.innerHTML = originalText;
                    btn.disabled = false;

                    if (data.success || data.skipValidation) {
                        // Cargo can fit, proceed with acceptance
                        showLoadingAndSubmit();
                    } else {
                        // Show warning modal
                        showPlacementWarning(data);
                    }
                })
                .catch(error => {
                    console.error('Validation error:', error);
                    btn.innerHTML = originalText;
                    btn.disabled = false;

                    // Fail-closed: if validation API errors, block acceptance
                    showToast(
                        '<strong>\u26a0 Validation Error</strong><br>Could not reach the placement validation service. Please try again before accepting.',
                        'danger',
                        true
                    );
                });
        }

        /**
         * Show loading modal and submit form
         */
        function showLoadingAndSubmit() {
            const modal = new bootstrap.Modal(document.getElementById('loadingModal'));
            modal.show();

            setTimeout(() => {
                document.getElementById('acceptForm').submit();
            }, 500);
        }

        /**
         * Show placement validation warning as a persistent danger toast
         */
        function showPlacementWarning(data) {
            let msg = `<strong>\u26a0 Cannot Validate Placement</strong><br>${data.message}`;

            // List unpacked items if provided
            if (data.unpackedItems && data.unpackedItems.length > 0) {
                const names = data.unpackedItems
                    .map(i => `<li>${i.item_name ?? i.description ?? 'Item'} &mdash; ${i.weight ?? 0}kg</li>`)
                    .join('');
                msg += `<ul style="margin:6px 0 0 0;padding-left:18px;font-size:0.9em;">${names}</ul>`;
            }

            // Hatch capacity summary if provided
            if (data.hatchCapacities) {
                const totalAvailable = Object.values(data.hatchCapacities)
                    .reduce((sum, h) => sum + h.availableWeight, 0);
                const totalCapacity = Object.values(data.hatchCapacities)
                    .reduce((sum, h) => sum + h.maxWeight, 0);
                msg +=
                    `<div style="margin:6px 0 0 0;font-size:0.85em;color:#ffe;">Total vessel capacity: ${totalCapacity.toFixed(0)}kg — Available: ${totalAvailable.toFixed(0)}kg</div>`;
            }

            showToast(msg, 'danger', true); // persist=true so it stays until dismissed
        }

        /**
         * Proceed with acceptance after validation
         */
        function proceedWithAcceptance() {
            const form = document.getElementById('acceptForm');
            if (form) {
                const modal = bootstrap.Modal.getInstance(document.getElementById('placementValidationModal'));
                if (modal) {
                    modal.hide();
                }

                showLoadingAndSubmit();
            }
        }
    </script>

    <!-- Payment Modal -->
    <div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="paymentModalLabel">Process Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="transaction-summary mb-4">
                        <h6>Transaction Summary</h6>
                        <div class="row">
                            <div class="col-6"><strong>Booking Reference:</strong></div>
                            <div class="col-6">{{ $booking->booking_code }}</div>
                        </div>
                        <div class="row">
                            <div class="col-6"><strong>Sender:</strong></div>
                            <div class="col-6">{{ $booking->sender->sender_name ?? 'N/A' }}</div>
                        </div>
                        <div class="row">
                            <div class="col-6"><strong>Consignee:</strong></div>
                            <div class="col-6">{{ $booking->consignee->consignee_name ?? 'N/A' }}</div>
                        </div>
                        <div class="row">
                            <div class="col-6"><strong>Total Amount:</strong></div>
                            <div class="col-6">₱{{ number_format($payment->total_amount ?? 0, 2) }}</div>
                        </div>
                    </div>

                    <div class="payment-options">
                        <h6>Select Payment Method</h6>
                        <div class="d-flex gap-3">
                            <button type="button" class="btn btn-outline-primary" onclick="processPayment('cash')">Pay
                                with Cash</button>
                            <button type="button" class="btn btn-outline-success" onclick="processPayment('gcash')">Pay
                                with GCash</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showPaymentModal() {
            const modal = new bootstrap.Modal(document.getElementById('paymentModal'));
            modal.show();
        }

        function processPayment(method) {
            // Show confirmation modal
            document.getElementById('confirmMethod').textContent = method.toUpperCase();
            const modal = new bootstrap.Modal(document.getElementById('paymentConfirmModal'));
            modal.show();

            // Handle confirmation
            const confirmBtn = document.getElementById('confirmPaymentBtn');
            const handleConfirm = function() {
                modal.hide();
                showLoadingAndExecute(method);
                confirmBtn.removeEventListener('click', handleConfirm);
            };
            confirmBtn.addEventListener('click', handleConfirm);
        }

        function showLoadingAndExecute(method) {
            const loadingModal = new bootstrap.Modal(document.getElementById('loadingModal'));
            loadingModal.show();

            // Execute payment after showing loading modal
            setTimeout(() => {
                executePayment(method);
            }, 500);
        }

        function executePayment(method) {
            const bookingRef = '{{ $booking->booking_ref_no }}';

            fetch(`/authorized/staff/cargo-bookings/${bookingRef}/process-payment`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ||
                            document.querySelector('input[name="_token"]')?.value
                    },
                    body: JSON.stringify({
                        payment_method: method
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast(`Payment processed successfully via ${method.toUpperCase()}!`, 'success');
                        window.location.reload();
                    } else {
                        showToast('Error: ' + data.message, 'danger');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('An error occurred while processing payment.', 'danger');
                });
        }
    </script>
@endsection

@section('styles')
    <style>
        /* Bootstrap Carousel Customization */
        #cargoCarousel {
            background: #f8f9fa;
            border-radius: 12px;
            overflow: hidden;
            padding: 20px;
        }

        .carousel-inner {
            border-radius: 8px;
            overflow: hidden;
            background: white;
        }

        .carousel-item {
            aspect-ratio: 1 / 1;
        }

        .carousel-image-container {
            position: relative;
            width: 100%;
            aspect-ratio: 1 / 1;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #e9ecef;
            overflow: hidden;
        }

        .carousel-img {
            object-fit: contain;
            padding: 30px;
            transition: transform 0.3s ease;
            cursor: pointer;
        }

        .carousel-item:hover .carousel-img {
            transform: scale(1.05);
        }

        /* Zoom Overlay */
        .carousel-zoom-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .carousel-item:hover .carousel-zoom-overlay {
            opacity: 1;
        }

        .zoom-content {
            text-align: center;
            color: white;
        }

        .zoom-content i {
            font-size: 3.5rem;
            display: block;
            margin-bottom: 12px;
            text-shadow: 0 2px 6px rgba(0, 0, 0, 0.4);
        }

        .zoom-content p {
            font-size: 1.1rem;
            font-weight: 600;
            margin: 0;
            text-shadow: 0 1px 3px rgba(0, 0, 0, 0.4);
        }

        /* Caption Overlay at Bottom */
        .carousel-caption-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(to top, rgba(0, 0, 0, 0.85), rgba(0, 0, 0, 0.6) 70%, rgba(0, 0, 0, 0));
            color: white;
            padding: 30px 20px 20px;
            text-align: center;
        }

        .carousel-cargo-title {
            font-size: 1.6rem;
            font-weight: 700;
            margin: 0 0 10px 0;
            line-height: 1.3;
        }

        .carousel-cargo-classification {
            font-size: 1rem;
            color: #e0e0e0;
            margin: 0;
            font-weight: 500;
        }

        /* Indicators */
        .carousel-indicators {
            bottom: -50px;
            padding: 20px 0 0;
        }

        .carousel-indicators button {
            width: 14px;
            height: 14px;
            border-radius: 50%;
            background-color: #dee2e6;
            border: 2px solid #dee2e6;
            transition: all 0.3s ease;
        }

        .carousel-indicators button:hover {
            background-color: #0d6efd;
            border-color: #0d6efd;
        }

        .carousel-indicators button.active {
            background-color: #0d6efd;
            border-color: #0d6efd;
            width: 16px;
            height: 16px;
        }

        /* Navigation Controls */
        .carousel-control-prev,
        .carousel-control-next {
            width: 50px;
            height: 50px;
            background: rgba(13, 110, 253, 0.9);
            border-radius: 50%;
            top: 50%;
            transform: translateY(-50%);
            transition: all 0.3s ease;
            opacity: 1;
        }

        .carousel-control-prev:hover,
        .carousel-control-next:hover {
            background: rgba(13, 110, 253, 1);
            box-shadow: 0 4px 12px rgba(13, 110, 253, 0.4);
        }

        .carousel-control-prev-icon,
        .carousel-control-next-icon {
            filter: brightness(1.3);
            font-size: 1.5rem;
        }

        .carousel-control-prev {
            left: 20px;
        }

        .carousel-control-next {
            right: 20px;
        }

        /* Fade Animation */
        .carousel-fade .carousel-item {
            opacity: 0;
            transition-property: opacity;
            transition-duration: 0.6s;
        }

        .carousel-fade .carousel-item.active {
            opacity: 1;
        }

        /* Responsive Design */
        @media (max-width: 992px) {
            #cargoCarousel {
                padding: 15px;
            }

            .carousel-item {
                aspect-ratio: 1 / 1;
            }

            .carousel-img {
                padding: 25px;
            }

            .carousel-cargo-title {
                font-size: 1.4rem;
            }

            .carousel-cargo-classification {
                font-size: 0.95rem;
            }

            .carousel-control-prev {
                left: 10px;
            }

            .carousel-control-next {
                right: 10px;
            }
        }

        @media (max-width: 768px) {
            #cargoCarousel {
                padding: 12px;
            }

            .carousel-item {
                aspect-ratio: 1 / 1;
            }

            .carousel-img {
                padding: 20px;
            }

            .zoom-content i {
                font-size: 2.5rem;
                margin-bottom: 8px;
            }

            .zoom-content p {
                font-size: 0.95rem;
            }

            .carousel-cargo-title {
                font-size: 1.2rem;
                padding: 0 10px;
            }

            .carousel-cargo-classification {
                font-size: 0.85rem;
            }

            .carousel-control-prev,
            .carousel-control-next {
                width: 44px;
                height: 44px;
            }

            .carousel-control-prev-icon,
            .carousel-control-next-icon {
                font-size: 1.25rem;
            }
        }

        @media (max-width: 576px) {
            #cargoCarousel {
                padding: 10px;
            }

            .carousel-item {
                aspect-ratio: 1 / 1;
            }

            .carousel-img {
                padding: 15px;
            }

            .carousel-caption-overlay {
                padding: 20px 15px 15px;
            }

            .zoom-content i {
                font-size: 2rem;
                margin-bottom: 6px;
            }

            .zoom-content p {
                font-size: 0.85rem;
            }

            .carousel-cargo-title {
                font-size: 1rem;
                padding: 0 8px;
            }

            .carousel-cargo-classification {
                font-size: 0.75rem;
            }

            .carousel-control-prev,
            .carousel-control-next {
                width: 40px;
                height: 40px;
            }

            .carousel-control-prev {
                left: 5px;
            }

            .carousel-control-next {
                right: 5px;
            }

            .carousel-indicators {
                bottom: -40px;
            }

            .carousel-indicators button {
                width: 12px;
                height: 12px;
            }

            .carousel-indicators button.active {
                width: 14px;
                height: 14px;
            }
        }

        .cargo-items-table th,
        .cargo-items-table td {
            vertical-align: middle;
        }
    </style>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.addEventListener('click', function(event) {
                const trigger = event.target.closest('[data-bs-target="#photoModal"]');
                if (!trigger) return;

                const image = trigger.getAttribute('data-image');
                const description = trigger.getAttribute('data-description') || 'Cargo Item';
                const classification = trigger.getAttribute('data-classification') || '--';

                const modalImage = document.getElementById('modalCargoPhoto');
                const modalCaption = document.getElementById('modalPhotoCaption');
                const modalClassification = document.getElementById('modalPhotoClassification');

                if (modalImage) modalImage.src = image || '';
                if (modalCaption) modalCaption.textContent = description;
                if (modalClassification) modalClassification.textContent = 'Classification: ' +
                    classification;
            });

            const photoModal = document.getElementById('photoModal');
            if (photoModal) {
                photoModal.addEventListener('hidden.bs.modal', function() {
                    const modalImage = document.getElementById('modalCargoPhoto');
                    if (modalImage) {
                        modalImage.removeAttribute('src');
                    }
                });
            }
        });
    </script>
@endsection
