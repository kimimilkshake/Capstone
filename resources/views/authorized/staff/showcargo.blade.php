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
                <h5 class="mb-3">Booking Information</h5>
                <p><strong>Booking Ref #:</strong> {{ $booking->booking_code }}</p>
                <p><strong>Status:</strong> {{ $booking->booking_status }}</p>
                <p><strong>Created:</strong> {{ $booking->created_at->format('M d, Y') }}</p>

                @if($booking->voyage)
                    <p><strong>Voyage Code:</strong> {{ $booking->voyage->voyage_code }}</p>
                    <p><strong>Departure:</strong> {{ $booking->voyage->voyage_departure_date }}</p>
                    <p><strong>Arrival:</strong> {{ $booking->voyage->voyage_arrival_date }}</p>
                @else
                    <p><strong>Voyage:</strong> N/A</p>
                @endif

                @if($payment)
                    <p><strong>Mode of Payment:</strong> {{ $payment->mode_of_payment }}</p>
                    <p><strong>Payment Status:</strong> {{ $payment->payment_status }}</p>
                    <p><strong>Amount Paid:</strong> ₱{{ number_format($payment->total_amount,2) }}</p>
                @endif
            </div>
        </div>

        {{-- RIGHT COLUMN: SENDER & CONSIGNEE --}}
        <div class="col-lg-6">
            <div class="card shadow-sm p-4 mb-4">
                <h5 class="mb-3">Sender & Consignee Information</h5>

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

        {{-- If NO PHOTOS --}}
        @if(!$hasPhotos)
            <p class="text-muted text-center fst-italic">
                No Photos Attached, Booking was made in the Office
            </p>
        @endif

        <div class="d-flex justify-content-center gap-3">

            @for ($i = 0; $i < 3; $i++)
                @php
                    $has = isset($cargoWithPhotos[$i]);
                    if($has) {
                        $cargo = $cargoWithPhotos[$i];
                        $filename = basename($cargo->cargo_picture);
                        $imgPath = file_exists(storage_path('app/public/cargo_pictures/' . $filename))
                                    ? asset('storage/cargo_pictures/' . $filename)
                                    : asset('images/no-image.png');
                    }
                @endphp

                <div class="cargo-photo-card">
                    @if($has)
                        <img src="{{ $imgPath }}"
                             class="cargo-photo-thumbnail"
                             data-bs-toggle="modal"
                             data-bs-target="#photoModal"
                             onclick="openPhoto('{{ $imgPath }}')">
                    @else
                        <div class="empty-photo">
                            <i class="bi bi-image"></i>
                        </div>
                    @endif
                </div>

            @endfor

        </div>
    </div>

    {{-- Modal --}}
    <div class="modal fade" id="photoModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content bg-dark">
                <div class="modal-body text-center p-3">
                    <img id="modalPhoto" class="img-fluid" style="max-height:650px; object-fit:contain;">
                </div>
            </div>
        </div>
    </div>

    <script>
        function openPhoto(src) {
            document.getElementById('modalPhoto').src = src;
        }
    </script>

    {{-- =============== --}}
    {{-- CARGO ITEMS --}}
    {{-- =============== --}}
    <div class="card shadow-sm p-3 mb-4">
        <h5>Cargo Items</h5>

        <table class="table table-bordered table-striped mt-3">
            <thead class="table-dark">
                <tr>
                    <th>Description</th>
                    <th>Qty</th>
                    <th>Dimensions</th>
                    <th>CBM</th>
                    <th>Freight</th>
                    <th>Arrastre</th>
                    <th>Subtotal</th>
                </tr>
            </thead>

            <tbody>
                @php $total = 0; @endphp

                @foreach ($cargoBookings as $c)
                    @php
                        $freight = $c->cargoItem->cargo_item_freight;
                        $arrastre = $c->cargoItem->cargo_item_arrastre;
                        $cbm = ($c->length * $c->width * $c->height) / 1000000;
                        $subtotal = ($freight + $arrastre) * $cbm * $c->quantity;
                        $total += $subtotal;
                    @endphp

                    <tr>
                        <td>{{ $c->cargoItem->cargo_item_description }}
                            <br><small class="text-muted">({{ $c->cargoItem->cargo_item_classification }})</small>
                        </td>
                        <td>{{ $c->quantity }}</td>
                        <td>{{ $c->length }} × {{ $c->width }} × {{ $c->height }}</td>
                        <td>{{ number_format($cbm, 4) }}</td>
                        <td>₱{{ number_format($freight, 2) }}</td>
                        <td>₱{{ number_format($arrastre, 2) }}</td>
                        <td>₱{{ number_format($subtotal, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>

            <tfoot>
                <tr>
                    <th colspan="6" class="text-end">TOTAL:</th>
                    <th>
                        @if($payment && $payment->total_amount)
                            ₱{{ number_format($payment->total_amount, 2) }}
                        @else
                            ₱{{ number_format($total, 2) }}
                        @endif
                    </th>
                </tr>
            </tfoot>
        </table>
    </div>

    {{-- ACTION BUTTONS --}}
    @if($booking->booking_status === 'Pending')
        <div class="d-flex justify-content-center gap-3 mt-4">
            <form action="{{ route('cargo.bookings.approve', $booking->booking_ref_no) }}" method="POST">
                @csrf
                <button class="btn btn-success btn-lg px-4">Accept</button>
            </form>

            <!-- Open modal to collect rejection reason -->
            <button class="btn btn-danger btn-lg px-4" data-bs-toggle="modal" data-bs-target="#rejectModal">Reject</button>
        </div>

        <!-- Reject Modal -->
        <div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form action="{{ route('cargo.bookings.reject', $booking->booking_ref_no) }}" method="POST">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title">Reason for Rejection</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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

    <div class="text-center mt-4">
        <a href="{{ route('cargo.bookings.pending') }}" class="btn btn-outline-primary btn-lg px-4">
            Back to Pending Bookings
        </a>
    </div>

</div>
@endsection

@section('styles')
<style>
    .cargo-photo-card {
        background: #fbf8ed;
        width: 90px;
        height: 90px;
        border-radius: 8px;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .cargo-photo-thumbnail {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform .25s ease;
    }

    .cargo-photo-thumbnail:hover {
        transform: scale(1.15);
    }

    .empty-photo {
        width: 100%;
        height: 100%;
        background: #f3efdf;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #b7b7b7;
        font-size: 1.8rem;
    }
</style>
@endsection
