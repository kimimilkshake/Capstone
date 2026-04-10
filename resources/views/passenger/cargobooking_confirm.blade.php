@extends('layouts.app')
@section('content')
    @include('components.hero')

    <div class="container my-5">
        <div class="card shadow-sm mx-auto" style="max-width:1100px; background-color:#f0f0f0;">
            <div class="card-header bg-dark text-white text-center mb-1">
                <h5 class="mb-0">CARGO BOOKING CONFIRMATION</h5>
            </div>

            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <h6>Booking Reference: <strong>{{ $booking->booking_code }}</strong></h6>
                    </div>
                </div>

                @if($booking->voyage)
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Voyage Code:</strong> {{ $booking->voyage->voyage_code }}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Status:</strong> {{ $booking->booking_status }}</p>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Departure:</strong> {{ \Carbon\Carbon::parse($booking->voyage->voyage_departure_date)->format('M d, Y') }}</p>
                        @php
                            $originPort = $booking->voyage->routePort?->portOrigin;
                            $originDisplay = $originPort ? ($originPort->terminal_name ?? '') . ' ' . ($originPort->port_name ?? '') . ', ' . ($originPort->city ?? '') : 'N/A';
                        @endphp
                    </div>
                    <div class="col-md-6">
                        <p><strong>Port of Origin:</strong> {{ trim($originDisplay) }}</p>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Arrival:</strong> {{ \Carbon\Carbon::parse($booking->voyage->voyage_arrival_date)->format('M d, Y') }}</p>
                        @php
                            $destPort = $booking->voyage->routePort?->portDestination;
                            $destDisplay = $destPort ? ($destPort->terminal_name ?? '') . ' ' . ($destPort->port_name ?? '') . ', ' . ($destPort->city ?? '') : 'N/A';
                        @endphp
                    </div>
                    <div class="col-md-6">
                        <p><strong>Port of Destination:</strong> {{ trim($destDisplay) }}</p>
                    </div>
                </div>
                @else
                <div class="row">
                    <div class="col-md-12">
                        <p>Voyage information not available.</p>
                    </div>
                </div>
                @endif

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
                            <th style="text-align: right;">Length</th>
                            <th style="text-align: right;">Width</th>
                            <th style="text-align: right;">Height</th>
                            <th style="text-align: right;">Freight Rate</th>
                            <th style="text-align: right;">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                        $totalExpense = 0; @endphp

                        @foreach ($cargoItems as $item)
                            @php

                                $withMeasurement = strtolower(trim($item->with_measurement ?? 'yes'));
                                $freight = (float) ($item->freight ?? 0);
                                $quantity = (int) ($item->quantity ?? 0);
                                $cbm = (float) ($item->cbm ?? 0);

                                if ($withMeasurement === 'no') {
                                    $subtotal = $freight * $cbm * $quantity;
                                    $rateDisplay = '₱' . number_format($freight, 2) . ' / CBM';
                                } else {
                                    $subtotal = $freight * $quantity;
                                    $rateDisplay = '₱' . number_format($freight, 2) . ' / qty';
                                }

                                $totalExpense += $subtotal;
                            @endphp
                            <tr>
                                <td>{{ $item->quantity }}</td>
                                <td>{{ $item->cargo_classification_name ?? 'N/A' }}</td>
                                <td>{{ $item->cargo_item_description }}</td>
                                <td style="text-align: right;">{{ number_format((float) $item->length, 2) }} {{ $item->display_measurement_unit }}</td>
                                <td style="text-align: right;">{{ number_format((float) $item->width, 2) }} {{ $item->display_measurement_unit }}</td>
                                <td style="text-align: right;">{{ number_format((float) $item->height, 2) }} {{ $item->display_measurement_unit }}</td>
                                <td style="text-align: right;">₱{{ number_format($item->freight, 2) }}</td>
                                <td style="text-align: right;">₱{{ number_format($subtotal, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="7" class="text-end">Total Expense:</th>
                            <th style="text-align: right;">₱{{ number_format($totalExpense, 2) }}</th>
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
            </div>
        </div>
    </div>

    @include('components.footer')

    <script>
        // Lock back button: push a duplicate history entry so pressing back fires
        // popstate here instead of actually navigating back to the form.
        (function() {
            history.pushState(null, '', window.location.href);

            window.addEventListener('popstate', function() {
                window.location.replace('{{ route('bookingtype') }}');
            });

            window.addEventListener('pageshow', function(e) {
                if (e.persisted) {
                    window.location.replace('{{ route('bookingtype') }}');
                }
            });
        })();
    </script>
@endsection
