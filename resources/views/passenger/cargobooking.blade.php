@extends('layouts.app')
@section('content')
@include('components.hero')

<div class="container my-5">
    <div class="card shadow-sm mx-auto" style="max-width:1100px; background-color:#f0f0f0;">
        <div class="card-header bg-dark text-white text-center mb-1">
            <h5 class="mb-0">CARGO BOOKING FORM</h5>
        </div>

        <div class="card-body">
            <form action="{{ route('cargobooking.store') }}" method="POST" id="cargoBookingForm">
                @csrf
                <div class="row">

                    <!-- LEFT: Sender Info -->
                    <div class="col-md-6 mb-3">
                        <h6 class="fw-bold">Sender Information</h6>
                        <div class="mb-2 d-flex gap-2">
                            <input type="text" name="sender_firstname" class="form-control flex-grow-1" placeholder="First Name" required>
                            <input type="text" name="sender_suffix" class="form-control" style="width:80px;" placeholder="Suffix">
                        </div>
                        <div class="mb-2 d-flex gap-2">
                            <input type="text" name="sender_lastname" class="form-control flex-grow-1" placeholder="Last Name" required>
                            <input type="text" name="sender_mi" class="form-control" style="width:80px;" placeholder="MI">
                        </div>
                        <div class="mb-2">
                            <input type="text" name="sender_contact" class="form-control" placeholder="Contact Number" required>
                        </div>
                        <div class="mb-2">
                            <input type="email" name="sender_email" class="form-control" placeholder="Email Address">
                        </div>

                        <!-- Voyage Info -->
                        <h6 class="fw-bold mt-4">Voyage Information</h6>
                        <div class="bg-white p-3 rounded shadow-sm mb-3 small">
                            <p class="mb-1"><strong>Vessel Name:</strong> {{ $vesselName }}</p>
                            <p class="mb-1"><strong>Route:</strong> {{ $routeFrom }} - {{ $routeTo }}</p>
                            <p class="mb-1"><strong>Departure Date:</strong> {{ $departureDate }}</p>
                            <p class="mb-1"><strong>Departure Time:</strong> {{ $departureTime }}</p>
                            <p class="mb-0"><strong>Port of Origin:</strong> {{ $portOfOrigin }}</p>
                        </div>
                    </div>

                    <!-- RIGHT: Cargo Info -->
                    <div class="col-md-6 mb-3">
                        <h6 class="fw-bold">Cargo Information</h6>
                        <div class="mb-2">
                            <input type="text" name="consignee" class="form-control" placeholder="Consignee" required>
                        </div>
                        <div class="mb-2">
                            <input type="text" name="receiver_contact" class="form-control" placeholder="Contact Number" required>
                        </div>
                        <div class="mb-2 d-flex gap-2">
                            <select name="cargo_item_id" class="form-control flex-grow-1" required>
                                <option value="">-- Select Cargo Item --</option>
                                @foreach($cargoItems as $item)
                                    <option value="{{ $item->id }}">{{ $item->cargo_item_description }}</option>
                                @endforeach
                            </select>
                            <input type="number" name="cargo_quantity" class="form-control" style="width:100px;" placeholder="Qty" required min="1">
                        </div>

                        <div class="d-flex justify-content-end gap-3 mt-5">
                            <a href="{{ route('bookingtype') }}" class="btn btn-outline-danger fw-bold py-3" style="width:180px;">CANCEL</a>
                            <button type="submit" class="btn btn-primary fw-bold py-3" style="width:180px;">BOOK NOW</button>
                        </div>
                    </div>

                </div>
            </form>
        </div>
    </div>
</div>
@endsection
