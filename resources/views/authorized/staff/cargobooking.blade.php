@extends('layouts.app')
@section('page-title', 'CARGO BOOKING')
@section('content')
@include('components.authHeader')
@include('components.staff_nav')

<div class="staff-body container my-5" style="max-width: 1100px; margin: auto;">
    {{-- Adjust title spacing --}}
    <div class="svl-title text-center mb-4" style="margin-top: 40px;">
        <h3>CARGO BOOKING</h3>
    </div>

    {{-- Success / Error Messages --}}
    @if(session('success'))
        <div class="alert alert-success text-center">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger text-center">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
{{-- Top: Route / Date / Time Selection --}}
<div class="card mb-4 shadow-sm">
    <div class="card-body">
        <form id="routeSelectionForm">
            <div class="row g-3 align-items-end">

                {{-- Origin --}}
                <div class="col-md-3">
                    <label class="form-label">Origin</label>
                    <select id="routeFrom" class="form-select">
                        <option value="">Select Origin</option>
                        @foreach (collect($voyages)->pluck('routePort.route_origin')->unique() as $origin)
                            <option value="{{ $origin }}">{{ $origin }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Destination --}}
                <div class="col-md-3">
                    <label class="form-label">Destination</label>
                    <select id="routeTo" class="form-select" disabled>
                        <option value="">Select Destination</option>
                    </select>
                </div>

                {{-- Departure Date --}}
                <div class="col-md-3">
                    <label class="form-label">Departure Date</label>
                    <input type="date" id="tripDate" class="form-control" disabled>
                </div>

                {{-- Departure Time --}}
                <div class="col-md-3">
                    <label class="form-label">Departure Time</label>
                    <select id="departureTime" class="form-select" disabled>
                        <option value="">Select Time</option>
                    </select>
                </div>

            </div>
        </form>
    </div>
</div>

    {{-- Booking Form --}}
    <form action="{{ route('staff.cargo_booking.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="row justify-content-center">

            {{-- LEFT: Sender & Consignee --}}
            <div class="col-lg-6 mb-3">
                <div class="card p-3 mb-3 shadow-sm">
                    <h5 class="mb-3">Sender Information</h5>
                    <input type="text" name="sender_firstname" class="form-control mb-2" placeholder="First Name" required>
                    <input type="text" name="sender_lastname" class="form-control mb-2" placeholder="Last Name" required>
                    <input type="text" name="sender_contact" class="form-control mb-2" placeholder="Contact Number" required>
                    <input type="email" name="sender_email" class="form-control mb-2" placeholder="Email Address">

                    <h5 class="mt-4 mb-3">Consignee Information</h5>
                    <input type="text" name="consignee_firstname" class="form-control mb-2" placeholder="First Name" required>
                    <input type="text" name="consignee_lastname" class="form-control mb-2" placeholder="Last Name" required>
                    <input type="text" name="consignee_contact" class="form-control mb-2" placeholder="Contact Number" required>
                </div>
            </div>

            {{-- RIGHT: Cargo Items --}}
            <div class="col-lg-6 mb-3">
                <div class="card p-3 mb-3 shadow-sm">
                    <h5 class="mb-3">Cargo Items</h5>
                    <div id="cargo-items-container">
                        <div class="cargo-item border rounded p-3 mb-3 position-relative">
                            <div class="mb-2">
                                <input type="text" name="cargo_description[]" class="form-control" placeholder="Cargo Description" required>
                            </div>
                            <div class="mb-2 d-flex gap-2">
                                <input type="number" name="cargo_quantity[]" class="form-control" placeholder="Quantity" required min="1">
                                <input type="number" name="cargo_length[]" class="form-control" placeholder="Length (cm)" required>
                            </div>
                            <div class="mb-2 d-flex gap-2">
                                <input type="number" name="cargo_width[]" class="form-control" placeholder="Width (cm)" required>
                                <input type="number" name="cargo_height[]" class="form-control" placeholder="Height (cm)" required>
                            </div>
                            <div class="mb-2">
                                <input type="number" name="cargo_weight[]" class="form-control" placeholder="Weight (kg)" required>
                            </div>
                            <div class="mb-2">
                                <label class="btn btn-outline-secondary w-100 d-flex align-items-center justify-content-center">
                                    <i class="bi bi-image me-2"></i> Add Photo
                                    <input type="file" name="cargo_picture[]" class="d-none" accept="image/*">
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2 mb-3">
                        <button type="button" id="addCargoItem" class="btn btn-secondary">Add Another Cargo</button>
                        <button type="button" id="removeCargoItem" class="btn btn-danger">Remove Last Cargo</button>
                    </div>
                </div>
            </div>

        </div>

        {{-- Form Actions --}}
        <div class="text-center mb-5">
            <button type="submit" class="btn btn-primary btn-lg">PROCEED</button>
            <a href="{{ route('staff.dashboard') }}" class="btn btn-outline-danger btn-lg">CANCEL BOOKING</a>
        </div>
    </form>
</div>

<script>
const voyages = JSON.parse(@json($voyages));

const routeFrom = document.getElementById('routeFrom');
const routeTo = document.getElementById('routeTo');
const tripDate = document.getElementById('tripDate');
const departureTime = document.getElementById('departureTime');

routeFrom.addEventListener('change', function() {
    const origin = this.value;
    const destinations = voyages
        .filter(v => v.routePort.route_origin === origin)
        .map(v => v.routePort.route_destination)
        .filter((v, i, a) => a.indexOf(v) === i);

    routeTo.innerHTML = '<option value="">Select Destination</option>';
    destinations.forEach(dest => routeTo.innerHTML += `<option value="${dest}">${dest}</option>`);
    routeTo.disabled = false;
    tripDate.value = '';
    departureTime.innerHTML = '<option value="">Select Time</option>';
    departureTime.disabled = true;
});

routeTo.addEventListener('change', function() {
    const origin = routeFrom.value;
    const dest = this.value;
    const dates = voyages
        .filter(v => v.routePort.route_origin === origin && v.routePort.route_destination === dest)
        .map(v => v.voyage_departure_date)
        .filter((v, i, a) => a.indexOf(v) === i);

    tripDate.min = dates[0];
    tripDate.disabled = false;
});

tripDate.addEventListener('change', function() {
    const origin = routeFrom.value;
    const dest = routeTo.value;
    const date = this.value;
    const times = voyages
        .filter(v => v.routePort.route_origin === origin && v.routePort.route_destination === dest && v.voyage_departure_date === date)
        .map(v => v.voyage_estimated_TD) // Use actual time slots here if available
        .filter((v, i, a) => a.indexOf(v) === i);

    departureTime.innerHTML = '<option value="">Select Time</option>';
    times.forEach(t => departureTime.innerHTML += `<option value="${t}">${t}</option>`);
    departureTime.disabled = false;
});

// Cargo Item Add/Remove
const container = document.getElementById('cargo-items-container');
document.getElementById('addCargoItem').addEventListener('click', () => {
    const newItem = container.querySelector('.cargo-item').cloneNode(true);
    newItem.querySelectorAll('input').forEach(i => i.value = '');
    container.appendChild(newItem);
});
document.getElementById('removeCargoItem').addEventListener('click', () => {
    const items = container.querySelectorAll('.cargo-item');
    if(items.length > 1) items[items.length-1].remove();
    else alert('At least one cargo item is required.');
});
</script>
@endsection
