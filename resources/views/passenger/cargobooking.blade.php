@extends('layouts.app')
@section('content')
    @include('components.hero') {{-- Head Nav --}}
    <div class="container my-5">
        <div class="card shadow-sm mx-auto" style="max-width:1100px; background-color:#f0f0f0;">
            <div class="card-header bg-dark text-white text-center mb-1">
                <h5 class="mb-0">CARGO FORM</h5>
            </div>
            <div class="card-body">
                <form id="cargoBookingForm">
                    <div class="row">
                        <!-- LEFT SIDE: Sender + Voyage Information -->
                        <div class="col-md-6 mb-3">
                            <!-- Sender Information -->
                            <h6 class="fw-bold">Sender Information</h6>
                            <div class="mb-2 d-flex gap-2">
                                <input type="text" class="form-control flex-grow-1" placeholder="First Name">
                                <input type="text" class="form-control" style="width:80px;" placeholder="Suffix">
                            </div>
                            <div class="mb-2 d-flex gap-2">
                                <input type="text" class="form-control flex-grow-1" placeholder="Last Name">
                                <input type="text" class="form-control" style="width:80px;" placeholder="MI">
                            </div>
                            <div class="mb-2">
                                <input type="text" class="form-control" placeholder="Contact Number">
                            </div>
                            <div class="mb-2">
                                <input type="email" class="form-control" placeholder="Email Address">
                            </div>

                            <!-- Voyage Information -->
                            <h6 class="fw-bold mt-4">Voyage Information</h6>
                            <div class="bg-white p-3 rounded shadow-sm mb-3 small">
                                <p class="mb-1"><strong>Vessel Name:</strong> {{ $vesselName }}</p>
                                <p class="mb-1"><strong>Route:</strong> {{ $routeFrom }} - {{ $routeTo }}</p>
                                <p class="mb-1"><strong>Departure Date:</strong> {{ $departureDate }}</p>
                                <p class="mb-1"><strong>Departure Time:</strong>
                                    {{ \Carbon\Carbon::parse($departureTime)->format('g:i A') }}</p>
                                <p class="mb-0"><strong>Port of Origin:</strong> {{ $portOfOrigin }}</p>
                            </div>
                        </div>

                        <!-- RIGHT SIDE: Cargo Information -->
                        <div class="col-md-6 mb-3">
                            <h6 class="fw-bold">Cargo Information</h6>
                            <div class="mb-2">
                                <input type="text" class="form-control" placeholder="Consignee">
                            </div>
                            <div class="mb-2">
                                <input type="text" class="form-control" placeholder="Contact Number">
                            </div>
                            <div class="mb-2 d-flex gap-2">
                                <input type="text" class="form-control flex-grow-1" placeholder="Description">
                                <input type="number" class="form-control" style="width:100px;" placeholder="Quantity">
                            </div>
                            <div class="d-flex justify-content-end mb-2">
                                <button type="button" class="btn btn-dark btn-sm">+ ADD</button>
                            </div>

                            <div class="mt-5 mb-9 fst-italic text-muted text-center" id="additionalCargo">
                                Additional cargo will be shown here
                            </div>

                            <!-- Buttons moved here -->
                            <div class="d-flex justify-content-end gap-3 mt-5">
                                <a href="{{ route('bookingtype') }}" class="btn btn-outline-danger fw-bold py-3"
                                    style="width:180px;">CANCEL</a>
                                <button type="submit" class="btn btn-primary fw-bold py-3" style="width:180px;">BOOK
                                    NOW</button>
                            </div>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>

    <script src="{{ asset('js/cargoform.js') }}"></script>
@endsection
