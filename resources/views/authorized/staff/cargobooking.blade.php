@extends('layouts.app')
@section('page-title', 'CARGO BOOKING')

@section('content')
@include('components.authHeader')
@include('components.staff_nav')

<div class="staff-body container my-5" style="max-width: 1100px; margin: auto;">

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

    {{-- Booking Form --}}
    <form action="{{ route('cargo.bookings.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        {{-- Voyage Selection --}}
        <div class="mb-4">
            <label class="form-label">Select Voyage</label>
            <select name="voyage_id" class="form-select" required>
                <option value="">-- Choose Voyage --</option>
                @foreach($voyages as $voyage)
                    <option value="{{ $voyage->voyage_id }}">
                        {{ $voyage->voyage_code }} | {{ $voyage->routePort->route_origin ?? 'N/A' }} → {{ $voyage->routePort->route_destination ?? 'N/A' }} 
                        | Departure: {{ $voyage->voyage_departure_date }}
                    </option>
                @endforeach
            </select>
        </div>

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
                            {{-- Cargo Description Dropdown --}}
                            <div class="mb-2">
                                <label class="form-label">Cargo Description</label>
                                <select name="cargo_item_id[]" class="form-select" required>
                                    <option value="">-- Select Cargo Item --</option>
                                    @foreach($cargoItems as $item)
                                        <option value="{{ $item->cargo_item_id }}">
                                            {{ $item->cargo_item_description }} ({{ $item->cargo_item_classification }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Quantity & Length --}}
                            <div class="mb-2 d-flex gap-2">
                                <input type="number" name="cargo_quantity[]" class="form-control" placeholder="Quantity" required min="1">
                                <input type="number" name="cargo_length[]" class="form-control" placeholder="Length (cm)" required>
                            </div>

                            {{-- Width & Height --}}
                            <div class="mb-2 d-flex gap-2">
                                <input type="number" name="cargo_width[]" class="form-control" placeholder="Width (cm)" required>
                                <input type="number" name="cargo_height[]" class="form-control" placeholder="Height (cm)" required>
                            </div>

                            {{-- Weight --}}
                            <div class="mb-2">
                                <input type="number" name="cargo_weight[]" class="form-control" placeholder="Weight (kg)" required>
                            </div>

                            {{-- Photo --}}
                            <div class="mb-2">
                                <label class="btn btn-outline-secondary w-100 d-flex align-items-center justify-content-center">
                                    <i class="bi bi-image me-2"></i> Add Photo
                                    <input type="file" name="cargo_picture[]" class="d-none" accept="image/*">
                                </label>
                            </div>
                        </div>
                    </div>

                    {{-- Add / Remove Cargo --}}
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
const container = document.getElementById('cargo-items-container');
document.getElementById('addCargoItem').addEventListener('click', () => {
    const newItem = container.querySelector('.cargo-item').cloneNode(true);
    newItem.querySelectorAll('input, select').forEach(i => i.value = '');
    container.appendChild(newItem);
});
document.getElementById('removeCargoItem').addEventListener('click', () => {
    const items = container.querySelectorAll('.cargo-item');
    if(items.length > 1) items[items.length-1].remove();
    else alert('At least one cargo item is required.');
});
</script>
@endsection
