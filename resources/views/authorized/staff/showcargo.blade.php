@extends('layouts.app')
@section('page-title', 'CARGO BOOKING DETAILS')

@section('content')
@include('components.authHeader')
@include('components.staff_nav')

<div class="staff-body">

    <div class="svl-title">
        <h3>REVIEW CARGO BOOKINGS</h3>
    </div>

    {{-- ========================= --}}
    {{-- BOOKING INFORMATION --}}
    {{-- ========================= --}}
    <div class="card shadow-sm p-4 mb-4">
        <h5 class="mb-3">Booking Information</h5>

        <div class="row">
            <div class="col-md-6">
                <p><strong>Booking Ref #:</strong> {{ $booking->booking_ref_no }}</p>
                <p><strong>Status:</strong> {{ $booking->booking_status }}</p>
                <p><strong>Created:</strong> {{ $booking->created_at->format('M d, Y') }}</p>
            </div>

            <div class="col-md-6">
                @if($booking->voyage)
                    <p><strong>Voyage Code:</strong> {{ $booking->voyage->voyage_code }}</p>
                    <p><strong>Departure:</strong> {{ $booking->voyage->voyage_departure_date }}</p>
                    <p><strong>Arrival:</strong> {{ $booking->voyage->voyage_arrival_date }}</p>
                @else
                    <p><strong>Voyage:</strong> N/A</p>
                @endif
            </div>
        </div>
    </div>

    {{-- ========================= --}}
    {{-- SENDER & CONSIGNEE --}}
    {{-- ========================= --}}
    <div class="card shadow-sm p-4 mb-4">
        <h5 class="mb-3">Sender & Consignee Information</h5>

        <div class="row">
            <div class="col-md-6">
                <h6 class="fw-bold">Sender Information</h6>
                <p><strong>Name:</strong> {{ $booking->sender->sender_name }}</p>
                <p><strong>Contact:</strong> {{ $booking->sender->sender_contactno }}</p>
                <p><strong>Email:</strong> {{ $booking->sender->sender_email }}</p>
            </div>

            <div class="col-md-6">
                <h6 class="fw-bold">Consignee Information</h6>
                <p><strong>Name:</strong> {{ $booking->consignee->consignee_name }}</p>
                <p><strong>Contact:</strong> {{ $booking->consignee->consignee_contactno }}</p>
            </div>
        </div>
    </div>

    {{-- ========================= --}}
    {{-- CARGO PHOTO CAROUSEL --}}
    {{-- ========================= --}}
    @php
        $cargoBookings = $booking->cargoBookings;
    @endphp

    @if ($cargoBookings->count() > 0)
        <div class="card shadow-sm p-4 mb-4">
            <h4 class="fw-bold mb-3">Cargo Photos</h4>

            <div id="cargoCarousel" class="carousel slide" data-bs-ride="carousel">

                {{-- Indicators --}}
                <div class="carousel-indicators">
                    @foreach($cargoBookings as $index => $c)
                        @if($c->cargo_picture)
                            <button type="button"
                                data-bs-target="#cargoCarousel"
                                data-bs-slide-to="{{ $index }}"
                                class="{{ $index === 0 ? 'active' : '' }}">
                            </button>
                        @endif
                    @endforeach
                </div>

                {{-- Slides --}}
                <div class="carousel-inner">
                    @foreach($cargoBookings as $c)
                        @if($c->cargo_picture)
                            <div class="carousel-item {{ $loop->first ? 'active' : '' }}">
                                <img src="{{ asset('storage/cargo_pictures/' . $c->cargo_picture) }}"
                                     class="d-block w-100 cargo-carousel-img">

                                <div class="carousel-caption text-start">
                                    {{ $c->quantity }}
                                    {{ $c->cargoItem->cargo_item_classification }}
                                    of
                                    {{ $c->cargoItem->cargo_item_description }}
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>

                {{-- Controls --}}
                <button class="carousel-control-prev" type="button" data-bs-target="#cargoCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon"></span>
                </button>

                <button class="carousel-control-next" type="button" data-bs-target="#cargoCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon"></span>
                </button>

            </div>
        </div>
    @endif

    {{-- ========================= --}}
    {{-- CARGO ITEMS TABLE --}}
    {{-- ========================= --}}
    <div class="card shadow-sm p-3 mb-4">
        <h5>Cargo Items</h5>

        <table class="table table-bordered table-striped mt-3">
            <thead class="table-dark">
                <tr>
                    <th>Description</th>
                    <th>Qty</th>
                    <th>Dimensions (L×W×H cm)</th>
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
                        <td>
                            {{ $c->cargoItem->cargo_item_description }} <br>
                            <small class="text-muted">({{ $c->cargoItem->cargo_item_classification }})</small>
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
                    <th>₱{{ number_format($total, 2) }}</th>
                </tr>
            </tfoot>
        </table>
    </div>

    {{-- ========================= --}}
    {{-- ACTION BUTTONS --}}
    {{-- ========================= --}}
    @if($booking->booking_status === 'Pending')
        <div class="d-flex justify-content-center gap-3 mt-4">
            <form action="{{ route('cargo.bookings.approve', $booking->booking_ref_no) }}" method="POST">
                @csrf
                <button class="btn btn-success btn-lg px-4">Accept</button>
            </form>

            <form action="{{ route('cargo.bookings.reject', $booking->booking_ref_no) }}" method="POST">
                @csrf
                <button class="btn btn-danger btn-lg px-4">Reject</button>
            </form>
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
    .cargo-carousel-img {
        max-height: 320px;
        object-fit: contain;
        background: #f8f9fa;
        border-radius: 10px;
        padding: 10px;
    }

    .carousel-caption {
        background: rgba(0,0,0,0.6);
        padding: 5px 10px;
        border-radius: 5px;
        bottom: 10px;
    }
</style>
@endsection
