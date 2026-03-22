@extends('layouts.app')
@section('content')
    @include('components.hero')

    <div class="container my-5">
        <div class="row justify-content-center align-items-start">

            <!-- PASSENGER FORM (appears first on mobile, right side on desktop) -->
            <div class="col-md-6 order-1 order-md-2">
                <div class="card shadow-sm">
                    <div class="card-header bg-dark text-white text-center">
                        <h5 class="mb-0">PASSENGER FORM</h5>
                    </div>
                    <div class="card-body bg-light">

                        <form id="bookingForm" data-submit-url="{{ route('booking.submit') }}" data-csrf="{{ csrf_token() }}">
                            <!-- Hidden voyage fields used by JS to submit booking -->
                            <input type="hidden" id="routeFrom" name="route_from" value="{{ $routeFrom }}">
                            <input type="hidden" id="routeTo" name="route_to" value="{{ $routeTo }}">
                            <input type="hidden" id="departureDate" name="departure_date" value="{{ $departureDate }}">
                            <input type="hidden" id="departureTime" name="departure_time" value="{{ $departureTime }}">
                            <input type="hidden" id="voyageId" name="voyage_id" value="{{ $voyage->voyage_id }}">
                            <!-- Number of Passengers -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <label for="numPassengers" class="form-label fw-bold">Number of Passengers</label>
                                    <select id="numPassengers" class="form-select"
                                        style="font-size: 1.1rem; padding: 0.75rem;">
                                        @for ($i = 1; $i <= 50; $i++)
                                            <option value="{{ $i }}">{{ $i }}</option>
                                        @endfor
                                    </select>
                                </div>
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
                                <a href="{{ route('bookingtype') }}"
                                    class="btn btn-outline-danger fw-bold w-50 py-3 me-2">CANCEL</a>
                                <button type="submit" class="btn btn-primary fw-bold w-50 py-3">BOOK NOW</button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>

            <!-- COT PLAN IMAGE (appears second on mobile, left side on desktop) -->
            <div class="col-md-6 mb-4 mt-5 mt-md-0 text-center order-2 order-md-1">
                <h4 class="mb-3">Cot Plan Layout</h4>
                <img id="cotPlanImage" src="" alt="Cot Plan" class="img-fluid rounded shadow-sm"
                    style="max-height: 500px; object-fit: contain; display: none;">
                <p id="cotPlanPlaceholder"
                    style="color: #888; font-style: italic; min-height: 200px; display: flex; align-items: center; justify-content: center; font-size: 1rem;">
                    No accommodation selected
                </p>
            </div>
        </div>
    </div>

    <!-- Fullscreen Loader -->
    <!-- OCR Loader -->
    <div id="ocrLoader"
        style="display:none; position:fixed; top:0; left:0; width:100%; height:100%;
               background:rgba(0,0,0,0.7); z-index:1050; justify-content:center; align-items:center; flex-direction:column;">
        <div class="spinner-border text-light" style="width:3rem; height:3rem;" role="status"></div>
        <p class="text-white mt-3 fw-bold">Loading... Please wait</p>
    </div>

    <!-- JS Data -->
    <script type="application/json" id="accommodations-data">{!! json_encode($accommodations) !!}</script>
    <script src="{{ asset('js/passengerform.js') }}"></script>
@endsection
