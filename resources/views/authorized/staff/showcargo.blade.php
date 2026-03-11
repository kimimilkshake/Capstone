@extends('layouts.app')
@section('page-title', 'CARGO BOOKING DETAILS')

@section('content')
    @include('components.authHeader')
    @include('components.staff_nav')

    <div class="staff-body">
        <div class="svl-title">
            <h3>REVIEW CARGO BOOKINGS</h3>
        </div>

        <div class="row">
            {{-- LEFT COLUMN: BOOKING INFO --}}
            <div class="col-lg-6">
                <div class="card shadow-sm p-4 mb-4">
                    <h5 class="mb-2">Booking Information</h5>
                    @php
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
                                $filename = basename($cargo->cargo_picture);
                                $imgPath = file_exists(storage_path('app/public/cargo_pictures/' . $filename))
                                    ? asset('storage/cargo_pictures/' . $filename)
                                    : asset('images/passenger.svg');
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
                            <th>Description</th>
                            <th>Classification</th>
                            <th>Qty</th>
                            <th>Length @if($unitLabel !== 'Mixed Units')({{ $unitLabel }})@endif</th>
                            <th>Width @if($unitLabel !== 'Mixed Units')({{ $unitLabel }})@endif</th>
                            <th>Height @if($unitLabel !== 'Mixed Units')({{ $unitLabel }})@endif</th>
                            <th>CBM</th>
                            <th>Freight</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>

                    <tbody>
                        @php $total = 0; @endphp

                        @foreach ($cargoBookings as $c)
                            @php
                                $freight = $c->cargoItem->cargo_item_freight;
                                $cbm = (float) ($c->cbm ?? ($c->length * $c->width * $c->height) / 1000000);
                                $subtotal = $freight * $cbm * $c->quantity;
                                $total += $subtotal;
                                $unit = $c->measurementUnit->measurement_unit_abbreviation ?? 'cm';
                                // Only show unit in value if mixed units
                                $showUnitSuffix = ($units->count() > 1);
                            @endphp

                            <tr>
                                <td>{{ $c->cargoItem->cargo_item_description }}</td>
                                <td>{{ $c->cargoClassification->cargo_classification_name ?? 'N/A' }}</td>
                                <td class="text-center">{{ $c->quantity }}</td>
                                <td class="text-end">{{ number_format($c->length, 2) }}@if($showUnitSuffix) {{ $unit }}@endif</td>
                                <td class="text-end">{{ number_format($c->width, 2) }}@if($showUnitSuffix) {{ $unit }}@endif</td>
                                <td class="text-end">{{ number_format($c->height, 2) }}@if($showUnitSuffix) {{ $unit }}@endif</td>
                                <td class="text-end">{{ number_format($cbm, 4) }}</td>
                                <td class="text-end">₱{{ number_format($freight, 2) }}</td>
                                <td class="text-end">₱{{ number_format($subtotal, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>

                    <tfoot>
                        <tr>
                            <th colspan="8" class="text-end">TOTAL:</th>
                            <th class="text-end">
                                @if ($payment && $payment->total_amount)
                                    ₱{{ number_format($payment->total_amount, 2) }}
                                @else
                                    ₱{{ number_format($total, 2) }}
                                @endif
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

                <button class="btn btn-danger btn-lg px-4" data-bs-toggle="modal" data-bs-target="#rejectModal"
                    style="width: 200px;">Reject</button>
            </div>
        @endif

        <div class="text-center mt-4">
            <a href="{{ route('cargo.bookings.pending') }}" class="btn btn-outline-primary btn-lg px-4">
                Back to Pending Bookings
            </a>

            <a href="{{ route('cargo.bookings.bol', $booking->booking_ref_no) }}" target="_blank"
                class="btn btn-secondary btn-lg px-4 ms-3">
                Bill of Lading
            </a>
        </div>
    </div>

    @if ($booking->booking_status === 'Pending')
        <div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form action="{{ route('cargo.bookings.reject', $booking->booking_ref_no) }}" method="POST">
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

    <script>
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
                // If no cargo items, just submit
                document.getElementById('acceptForm').submit();
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
                        proceedWithAcceptance();
                    } else {
                        // Show warning modal
                        showPlacementWarning(data);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    btn.innerHTML = originalText;
                    btn.disabled = false;

                    // If error, still allow to proceed (fail-open policy)
                    proceedWithAcceptance();
                });
        }

        /**
         * Show placement validation warning modal
         */
        function showPlacementWarning(data) {
            const messageDiv = document.getElementById('placementMessage');
            const itemsList = document.getElementById('unpackedItemsList');

            // Build message
            let messageHtml = `
                    <div class="alert alert-warning mb-3">
                        <h6 class="alert-heading">⚠️ Warning!</h6>
                        <p>${data.message}</p>
                    </div>
                `;

            if (data.unpackedItems && data.unpackedItems.length > 0) {
                messageHtml += `
                        <div class="alert alert-danger">
                            <h6>Items that cannot fit:</h6>
                            <ul class="mb-0">
                                ${data.unpackedItems.map(item => `
                                                <li>${item.name || item.id} - Cannot fit in any hatch</li>
                                            `).join('')}
                            </ul>
                        </div>
                    `;
            }

            messageDiv.innerHTML = messageHtml;
            itemsList.innerHTML = `
                    <div class="alert alert-info">
                        <strong>Note:</strong> You can still accept this booking, but these items will need manual placement or the booking may need to be split across voyages.
                    </div>
                `;

            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('placementValidationModal'));
            modal.show();
        }

        /**
         * Proceed with acceptance after validation
         */
        function proceedWithAcceptance() {
            const form = document.getElementById('acceptForm');
            if (form) {
                form.submit();
            }
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
            height: 500px;
        }

        .carousel-image-container {
            position: relative;
            width: 100%;
            height: 100%;
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
                height: 400px;
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
                height: 320px;
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
                height: 250px;
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

<<<<<<< HEAD
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
