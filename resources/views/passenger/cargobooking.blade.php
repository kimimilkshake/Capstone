@extends('layouts.app')
@section('content')
@include('components.hero')

<div class="container my-5">
    <div class="card shadow-sm mx-auto" style="max-width:1100px; background-color:#f0f0f0;">
        <div class="card-header bg-dark text-white text-center mb-1">
            <h5 class="mb-0">CARGO BOOKING FORM</h5>
        </div>

        <div class="card-body">
            <form action="{{ route('cargobooking.store') }}" method="POST" id="cargoBookingForm" enctype="multipart/form-data">
                @csrf
                <div class="row">

                    <!-- LEFT: Sender & Consignee Info -->
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

                        <h6 class="fw-bold mt-4">Consignee Information</h6>
                        <div class="mb-2 d-flex gap-2">
                            <input type="text" name="consignee_firstname" class="form-control flex-grow-1" placeholder="First Name" required>
                            <input type="text" name="consignee_suffix" class="form-control" style="width:80px;" placeholder="Suffix">
                        </div>
                        <div class="mb-2 d-flex gap-2">
                            <input type="text" name="consignee_lastname" class="form-control flex-grow-1" placeholder="Last Name" required>
                            <input type="text" name="consignee_mi" class="form-control" style="width:80px;" placeholder="MI">
                        </div>
                        <div class="mb-2">
                            <input type="text" name="consignee_contact" class="form-control" placeholder="Contact Number" required>
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
<!-- RIGHT: Cargo Items -->
<div class="col-md-6 mb-3">
    <h6 class="fw-bold">Cargo Information</h6>
    <div id="cargo-items-container">
        <div class="cargo-item border rounded p-3 mb-3">
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

<div class="d-flex justify-content-end mt-4 gap-3">
    <a href="{{ route('bookingtype') }}" class="btn btn-outline-danger fw-bold py-3" style="width:180px;">
        CANCEL BOOKING
    </a>
    <button type="submit" class="btn btn-primary fw-bold py-3" style="width:180px;">
        PROCEED
    </button>
</div>


<script>
const container = document.getElementById('cargo-items-container');

document.getElementById('addCargoItem').addEventListener('click', function() {
    const newItem = container.querySelector('.cargo-item').cloneNode(true);
    newItem.querySelectorAll('input').forEach(input => input.value = '');
    container.appendChild(newItem);
});

document.getElementById('removeCargoItem').addEventListener('click', function() {
    const items = container.querySelectorAll('.cargo-item');
    if(items.length > 1) {
        items[items.length - 1].remove();
    } else {
        alert('At least one cargo item is required.');
    }
});
</script>
