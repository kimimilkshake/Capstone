@extends('layouts.app')
@section('content')
@include('components.hero')

<div class="container my-5">
    <div class="card shadow-sm mx-auto" style="max-width:1100px; background-color:#f0f0f0;">
        <div class="card-header bg-dark text-white text-center mb-1">
            <h5 class="mb-0">CARGO BOOKING FORM</h5>
        </div>

        <div class="card-body">

            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <form action="{{ route('cargobooking.confirm') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row">

                    <!-- LEFT SIDE -->
                    <div class="col-md-6 mb-3">

                        <h6 class="fw-bold">Sender Information</h6>

                        <div class="mb-2 d-flex gap-2">
                            <input type="text" name="sender_firstname" class="form-control" placeholder="First Name" required>
                            <input type="text" name="sender_lastname" class="form-control" placeholder="Last Name" required>
                        </div>

                        <div class="mb-2">
                            <input type="text" name="sender_contact" class="form-control" placeholder="Contact Number" required>
                        </div>

                        <div class="mb-2">
                            <input type="email" name="sender_email" class="form-control" placeholder="Email Address">
                        </div>

                        <h6 class="fw-bold mt-4">Consignee Information</h6>

                        <div class="mb-2 d-flex gap-2">
                            <input type="text" name="consignee_firstname" class="form-control" placeholder="First Name" required>
                            <input type="text" name="consignee_lastname" class="form-control" placeholder="Last Name" required>
                        </div>

                        <div class="mb-2">
                            <input type="text" name="consignee_contact" class="form-control" placeholder="Contact Number" required>
                        </div>

                        <h6 class="fw-bold mt-4">Voyage Information</h6>
                        <div class="bg-white p-3 rounded shadow-sm mb-3 small">
                            <p class="mb-1"><strong>Vessel Name:</strong> {{ $vesselName }}</p>
                            <p class="mb-1"><strong>Route:</strong> {{ $routeFrom }} → {{ $routeTo }}</p>
                            <p class="mb-1"><strong>Departure Date:</strong> {{ $departureDate }}</p>
                            <p class="mb-1"><strong>Departure Time:</strong> {{ $departureTime }}</p>
                            <p class="mb-0"><strong>Port of Origin:</strong> {{ $portOfOrigin }}</p>
                        </div>

                        <input type="hidden" name="voyage_id" value="{{ request()->voyage_id }}">
                    </div>

                    <!-- RIGHT SIDE (CARGO ITEMS) -->
                    <div class="col-md-6 mb-3">
                        <h6 class="fw-bold">Cargo Information</h6>

                        <div id="cargo-items-container">

                            <div class="cargo-item border rounded p-3 mb-3">

                                <!-- DROPDOWN -->
                                <div class="mb-3">
                                    <label class="fw-bold mb-1">Select Cargo Type</label>
                                    <select name="cargo_item_id[]" class="form-control" required>
                                        <option value="">-- Select Cargo Item --</option>
                                        @foreach($cargoItems as $cargo)
                                            <option value="{{ $cargo->cargo_item_id }}">
                                                {{ $cargo->cargo_item_classification }} — {{ $cargo->cargo_item_description }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mb-2 d-flex gap-2">
                                    <input type="number" name="cargo_quantity[]" class="form-control" placeholder="Quantity" min="1" required>
                                    <input type="number" name="cargo_weight[]" class="form-control" placeholder="Weight (kg)" step="0.01" required>
                                </div>

                                <div class="mb-2 d-flex gap-2">
                                    <input type="number" name="cargo_length[]" class="form-control" placeholder="Length (cm)" step="0.01">
                                    <input type="number" name="cargo_width[]" class="form-control" placeholder="Width (cm)" step="0.01">
                                </div>

                                <div class="mb-2">
                                    <input type="number" name="cargo_height[]" class="form-control" placeholder="Height (cm)" step="0.01">
                                </div>

                                <label class="btn btn-outline-secondary w-100 d-flex align-items-center justify-content-center">
                                    <i class="bi bi-image me-2"></i> Add Photo
                                    <input type="file" name="cargo_picture[]" class="d-none" accept="image/*">
                                </label>

                            </div>

                        </div>

                        <div class="d-flex gap-2 mt-2">
                            <button type="button" id="addCargoItem" class="btn btn-secondary">Add Another Cargo</button>
                            <button type="button" id="removeCargoItem" class="btn btn-danger">Remove Last Cargo</button>
                        </div>

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

            </form>
        </div>
    </div>
</div>

<script>
const container = document.getElementById('cargo-items-container');

document.getElementById('addCargoItem').addEventListener('click', function() {
    const first = container.querySelector('.cargo-item');
    const clone = first.cloneNode(true);

    clone.querySelectorAll('input').forEach(input => input.value = '');
    clone.querySelector('select').selectedIndex = 0;

    container.appendChild(clone);
});

document.getElementById('removeCargoItem').addEventListener('click', function() {
    const items = container.querySelectorAll('.cargo-item');
    if (items.length > 1) items[items.length - 1].remove();
});
</script>

@endsection
