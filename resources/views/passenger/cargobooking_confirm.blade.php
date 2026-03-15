@extends('layouts.app')
@section('content')
    @include('components.hero')

    <div class="container my-5">
        <div class="card shadow-sm mx-auto" style="max-width:1100px; background-color:#f0f0f0;">
            <div class="card-header bg-dark text-white text-center mb-1">
                <h5 class="mb-0">CARGO BOOKING CONFIRMATION</h5>
            </div>

            <div class="card-body">
                <h6>Booking Reference:
                    <strong>{{ 'CBBK-' . str_pad($booking->booking_ref_no, 6, '0', STR_PAD_LEFT) }}</strong></h6>
                <p>Status: <strong>{{ $booking->booking_status }}</strong></p>

                <hr>

                <div class="row">
                    {{-- Sender Information --}}
                    <div class="col-md-6">
                        <h6>Sender Information</h6>
                        <p><strong>Name:</strong> {{ $sender->sender_name }}</p>
                        <p><strong>Contact No:</strong> {{ $sender->sender_contactno }}</p>
                        <p><strong>Email:</strong> {{ $sender->sender_email }}</p>
                    </div>

                    {{-- Consignee Information --}}
                    <div class="col-md-6">
                        <h6>Consignee Information</h6>
                        <p><strong>Name:</strong> {{ $consignee->consignee_name }}</p>
                        <p><strong>Contact No:</strong> {{ $consignee->consignee_contactno }}</p>
                    </div>
                </div>

                <hr>


                <h6>Cargo Items</h6>
                <table class="table table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th>Quantity</th>
                            <th>Classification</th>
                            <th>Description</th>
                            <th>Length</th>
                            <th>Width</th>
                            <th>Height</th>
                            <th>Freight Rate</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $totalExpense = 0; @endphp
                        @foreach ($cargoItems as $item)
                            @php
                                $cbm = (float) ($item->cbm ?? 0);
                                $subtotal = $cbm * $item->freight * $item->quantity;
                                $totalExpense += $subtotal;
                            @endphp
                            <tr>
                                <td>{{ $item->quantity }}</td>
                                <td>{{ $item->cargo_classification_name ?? 'N/A' }}</td>
                                <td>{{ $item->cargo_item_description }}</td>
                                <td>{{ number_format((float) $item->length, 2) }} {{ $item->display_measurement_unit }}
                                </td>
                                <td>{{ number_format((float) $item->width, 2) }} {{ $item->display_measurement_unit }}
                                </td>
                                <td>{{ number_format((float) $item->height, 2) }} {{ $item->display_measurement_unit }}
                                </td>
                                <td>₱{{ number_format($item->freight, 2) }}</td>
                                <td>₱{{ number_format($subtotal, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="7" class="text-end">Total Expense:</th>
                            <th>₱{{ number_format($totalExpense, 2) }}</th>
                        </tr>
                    </tfoot>
                </table>

                <div class="d-flex justify-content-end gap-2 mt-4">
                    @if (strtolower($booking->booking_status) === 'pending')
                        <form action="{{ route('cargobooking.finalize', ['booking_ref_no' => $booking->booking_ref_no]) }}"
                            method="POST">
                            @csrf
                            <button type="submit" class="btn btn-primary">Proceed</button>
                        </form>

                        <form action="{{ route('cargobooking.cancel', ['booking_ref_no' => $booking->booking_ref_no]) }}"
                            method="POST">
                            @csrf
                            <button type="submit" class="btn btn-outline-secondary">Cancel Booking</button>
                        </form>
                    @endif
                </div>
            @endsection
