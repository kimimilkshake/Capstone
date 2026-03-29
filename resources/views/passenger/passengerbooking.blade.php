@extends('layouts.app')
@section('content')
    @include('components.hero')

    <div class="container my-5">

        <!-- Voyage Information Card -->
        <div class="card shadow-sm mb-5" style="border-radius: 8px; overflow: hidden;">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">Voyage Information</h5>
            </div>
            <div class="card-body bg-light">
                <div class="row text-center">
                    <div class="col-md-4 mb-3 mb-md-0">
                        <p class="mb-1 text-muted small">Vessel Name</p>
                        <p class="mb-3 fw-bold">{{ $vesselName }}</p>
                        <p class="mb-1 text-muted small">Route</p>
                        <p class="mb-0 fw-bold">{{ $routeFrom }} - {{ $routeTo }}</p>
                    </div>
                    <div class="col-md-4 mb-3 mb-md-0">
                        <p class="mb-1 text-muted small">Departure Date</p>
                        <p class="mb-3 fw-bold">{{ \Carbon\Carbon::parse($departureDate)->format('F d, Y') }}</p>
                        <p class="mb-1 text-muted small">Departure Time</p>
                        <p class="mb-0 fw-bold">{{ \Carbon\Carbon::parse($departureTime)->format('g:i A') }}</p>
                    </div>
                    <div class="col-md-4 mb-3 mb-md-0">
                        <p class="mb-1 text-muted small">Port of Origin</p>
                        <p class="mb-3 fw-bold">{{ $portOfOrigin }}</p>
                        <p class="mb-1 text-muted small">Number of Passengers</p>
                        <div class="d-inline-flex align-items-center mx-auto">
                            <button type="button" id="passengerMinus" class="btn btn-sm fw-bold"
                                style="font-size: 1.2rem; padding: 0; width: 28px; border: none; background: none; box-shadow: none;">−</button>
                            <input type="number" id="numPassengers" class="form-control form-control-sm text-center mx-1"
                                value="1" min="1" max="50" style="width: 55px; height: 32px;">
                            <button type="button" id="passengerPlus" class="btn btn-sm fw-bold"
                                style="font-size: 1.2rem; padding: 0; width: 28px; border: none; background: none; box-shadow: none;">+</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row justify-content-center align-items-start">

            <!-- PASSENGER FORM (appears first on mobile, right side on desktop) -->
            <div class="col-md-6 order-1 order-md-2">
                <div class="card shadow-sm" style="border-radius: 8px; overflow: hidden;">
                    <div class="card-header bg-dark text-white text-center">
                        <h5 class="mb-0">PASSENGER FORM</h5>
                    </div>
                    <div class="card-body bg-light">

                        <form id="bookingForm" data-submit-url="{{ route('booking.submit') }}"
                            data-csrf="{{ csrf_token() }}">
                            <!-- Hidden voyage fields used by JS to submit booking -->
                            <input type="hidden" id="routeFrom" name="route_from" value="{{ $routeFrom }}">
                            <input type="hidden" id="routeTo" name="route_to" value="{{ $routeTo }}">
                            <input type="hidden" id="departureDate" name="departure_date" value="{{ $departureDate }}">
                            <input type="hidden" id="departureTime" name="departure_time" value="{{ $departureTime }}">
                            <input type="hidden" id="voyageId" name="voyage_id" value="{{ $voyage->voyage_id }}">

                            <!-- Dynamic Passenger Sections -->
                            <div id="passengerSections"></div>

                            <hr>

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
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const input = document.getElementById('numPassengers');
            const minBtn = document.getElementById('passengerMinus');
            const maxBtn = document.getElementById('passengerPlus');

            function clamp(val) {
                return Math.max(1, Math.min(50, parseInt(val) || 1));
            }

            function updateMinusState() {
                if (parseInt(input.value) <= 1) {
                    minBtn.style.color = '#ccc';
                    minBtn.style.pointerEvents = 'none';
                } else {
                    minBtn.style.color = '';
                    minBtn.style.pointerEvents = '';
                }
            }

            updateMinusState();

            minBtn.addEventListener('click', function() {
                input.value = clamp(input.value - 1);
                input.dispatchEvent(new Event('change'));
                updateMinusState();
            });

            maxBtn.addEventListener('click', function() {
                input.value = clamp(parseInt(input.value) + 1);
                input.dispatchEvent(new Event('change'));
                updateMinusState();
            });

            input.addEventListener('blur', function() {
                const clamped = clamp(input.value);
                if (parseInt(input.value) !== clamped) {
                    input.value = clamped;
                    input.dispatchEvent(new Event('change'));
                }
                updateMinusState();
            });

            input.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    input.value = clamp(input.value);
                    input.dispatchEvent(new Event('change'));
                    updateMinusState();
                }
            });
        });
    </script>
    <script src="{{ asset('js/passengerform.js') }}"></script>

    @include('components.footer')
@endsection
