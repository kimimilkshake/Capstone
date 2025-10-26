@extends('layouts.app')
@section('content')
    @include('components.hero')

    <div class="container my-5">
        <div class="row justify-content-center align-items-start">

            <!-- LEFT SIDE (Cot Plan Image Placeholder) -->
            <div class="col-md-6 mb-4 text-center">
                <h4 class="mb-3">Cot Plan Layout</h4>
                <img src="{{ asset('images/sample-cot-plan.jpg') }}" alt="Cot Plan" class="img-fluid rounded shadow-sm"
                    style="max-height: 500px; object-fit: contain;">
                <p class="text-muted mt-2">Cot plan image placeholder</p>
            </div>

            <!-- RIGHT SIDE (Passenger Form) -->
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-header bg-dark text-white text-center">
                        <h5 class="mb-0">PASSENGER FORM</h5>
                    </div>
                    <div class="card-body bg-light">

                        <form id="bookingForm" data-submit-url="{{ route('booking.submit') }}"
                            data-csrf="{{ csrf_token() }}">
                            <!-- Number of Passengers -->
                            <div class="mb-4">
                                <label for="numPassengers" class="form-label fw-bold">Number of Passengers</label>
                                <select id="numPassengers" class="form-select">
                                    <option value="1">1</option>
                                    <option value="2">2</option>
                                    <option value="3">3</option>
                                    <option value="4">4</option>
                                    <option value="5">5</option>
                                </select>
                            </div>

                            <!-- Dynamic Passenger Sections -->
                            <div id="passengerSections"></div>

                            <hr>

                            <!-- Voyage Information -->
                            <h6 class="fw-bold mb-3">Voyage Information</h6>
                            <div class="bg-white p-3 rounded shadow-sm small">
                                <p class="mb-1"><strong>Vessel Name:</strong> {{ $vesselName }}</p>
                                <p class="mb-1"><strong>Route:</strong> {{ $routeFrom }} - {{ $routeTo }}</p>
                                <p class="mb-1"><strong>Departure Date:</strong> {{ $departureDate }}</p>
                                <p class="mb-1"><strong>Departure Time:</strong>
                                    {{ \Carbon\Carbon::parse($departureTime)->format('g:i A') }}</p>
                                <p class="mb-0"><strong>Port of Origin:</strong> {{ $portOfOrigin }}</p>
                            </div>

                            <div class="d-flex justify-content-between mt-4">
                                <a href="{{ route('passenger') }}"
                                    class="btn btn-outline-danger fw-bold w-50 py-3 me-2">CANCEL</a>
                                <button type="submit" class="btn btn-primary fw-bold w-50 py-3">BOOK NOW</button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Fullscreen Loader -->
    <!-- OCR Loader -->
    <div id="ocrLoader"
        style="display:none; position:fixed; top:0; left:0; width:100%; height:100%;
               background:rgba(0,0,0,0.7); z-index:1050; justify-content:center; align-items:center; flex-direction:column;">
        <div class="spinner-border text-light" style="width:3rem; height:3rem;" role="status"></div>
        <p class="text-white mt-3 fw-bold">Scanning ID... Please wait</p>
    </div>

    <!-- JS -->
    <script src="{{ asset('js/bookingform.js') }}"></script>
@endsection
