@extends('layouts.app')
@section('page-title', 'CARGO BOOKING DETAILS')

@section('content')
@include('components.authHeader')
@include('components.staff_nav')

<div class="staff-body container my-5" style="max-width:1100px; margin:auto;">
    <div class="svl-title mb-4">
        <h3>Cargo Booking Details</h3>
    </div>

    {{-- Booking Info --}}
    <div class="card mb-4 shadow-sm p-3">
        <h5>Booking Information</h5>
        <p><strong>Booking Ref #:</strong> {{ $booking->booking_ref_no }}</p>
        <p><strong>Status:</strong> {{ $booking->booking_status }}</p>
        <p><strong>Created At:</strong> {{ $booking->created_at->format('M d, Y') }}</p>
        <p><strong>Voyage:</strong>
            @if($booking->voyage)
                {{ $booking->voyage->voyage_code }} |
                Departure: {{ $booking->voyage->voyage_departure_date }} |
                Arrival: {{ $booking->voyage->voyage_arrival_date }}
            @else
                N/A
            @endif
        </p>
    </div>

    {{-- Sender & Consignee --}}
    <div class="card mb-4 shadow-sm p-3">
        <h5>Sender</h5>
        <p>
            {{ $booking->sender ? $booking->sender->sender_name : 'N/A' }} |
            {{ $booking->sender ? $booking->sender->sender_contactno : 'N/A' }} |
            {{ $booking->sender ? $booking->sender->sender_email : 'N/A' }}
        </p>

        <h5>Consignee</h5>
        <p>
            {{ $booking->consignee ? $booking->consignee->consignee_name : 'N/A' }} |
            {{ $booking->consignee ? $booking->consignee->consignee_contactno : 'N/A' }}
        </p>
    </div>

    {{-- Cargo Items --}}
    <div class="card mb-4 shadow-sm p-3">
        <h5>Cargo Items</h5>
        <table class="table table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>Item</th>
                    <th>Quantity</th>
                    <th>Dimensions (L×W×H cm)</th>
                    <th>CBM (m³)</th>
                    <th>Freight Rate</th>
                    <th>Arrastre Rate</th>
                    <th>Subtotal</th>
                    <th>Photo</th>
                </tr>
            </thead>
            <tbody>
                @php $totalExpense = 0; @endphp
                @foreach($booking->cargoBookings as $item)
                    @php
                        $freight = $item->cargoItem->cargo_item_freight ?? 0;
                        $arrastre = $item->cargoItem->cargo_item_arrastre ?? 0;
                        $cbm = ($item->length * $item->width * $item->height) / 1000000;
                        $subtotal = ($freight + $arrastre) * $cbm * $item->quantity;
                        $totalExpense += $subtotal;
                    @endphp
                    <tr>
                        <td>{{ $item->cargoItem->cargo_item_description ?? $item->cargo_item_description ?? 'N/A' }} ({{ $item->cargoItem->cargo_item_classification ?? 'N/A' }})</td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ $item->length }} × {{ $item->width }} × {{ $item->height }}</td>
                        <td>{{ number_format($cbm, 3) }}</td>
                        <td>PHP {{ number_format($freight, 2) }}</td>
                        <td>PHP {{ number_format($arrastre, 2) }}</td>
                        <td>PHP {{ number_format($subtotal, 2) }}</td>
                        <td>
                            @if($item->cargo_picture)
                                <img src="{{ asset('storage/cargo_pictures/' . $item->cargo_picture) }}" width="100" alt="Cargo Image">
                            @else
                                N/A
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="6" class="text-end">Total Expense:</th>
                    <th colspan="2">PHP {{ number_format($totalExpense, 2) }}</th>
                </tr>
            </tfoot>
        </table>
    </div>

    {{-- Actions --}}
    @if(strtolower($booking->booking_status) === 'pending')
    <div class="d-flex justify-content-center gap-2 mt-4">
        <form action="{{ route('cargo.bookings.approve', $booking->booking_ref_no) }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-success btn-lg">Accept</button>
        </form>

        <form action="{{ route('cargo.bookings.reject', $booking->booking_ref_no) }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-danger btn-lg">Reject</button>
        </form>
    </div>
    @endif

    <div class="text-center mt-4">
        <a href="{{ route('cargo.bookings.pending') }}" class="btn btn-outline-primary btn-lg">Back to Pending Bookings</a>
    </div>

</div>
@endsection
