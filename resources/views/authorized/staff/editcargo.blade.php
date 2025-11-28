@extends('layouts.app')
@section('page-title', 'EDIT CARGO BOOKING')

@section('content')
@include('components.authHeader')
@include('components.staff_nav')

<div class="staff-body container my-7">

    <div class="svl-title text-center mb-4">
        <h3>EDIT CARGO BOOKING #{{ $booking->booking_ref_no }}</h3>
    </div>

    {{-- Success / Error Messages --}}
    @if(session('success'))
        <div class="alert alert-success text-center">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger text-center">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('cargo.bookings.update', $booking->booking_ref_no) }}" method="POST" enctype="multipart/form-data">
        @csrf

    {{--    BOOKING INFORMATION    --}}
    <div class="card shadow-sm p-4 mb-4">
        <h5 class="mb-3">Booking Information</h5>
        <div class="row">
            <div class="col-md-6">
                <p><strong>Booking Ref #:</strong> {{ $booking->booking_ref_no }}</p>
                <p><strong>Status:</strong> {{ $booking->booking_status }}</p>
                <p><strong>Created:</strong> {{ $booking->created_at->format('M d, Y') }}</p>
            </div>
            <div class="col-md-6">
                @if($booking->voyage)
                    <p><strong>Voyage Code:</strong> {{ $booking->voyage->voyage_code }}</p>
                    <p><strong>Departure:</strong> {{ $booking->voyage->voyage_departure_date }}</p>
                    <p><strong>Arrival:</strong> {{ $booking->voyage->voyage_arrival_date }}</p>
                @else
                    <p><strong>Voyage:</strong> N/A</p>
                @endif
            </div>
        </div>
    </div>

    {{--   SENDER & CONSIGNEE      --}}

    <div class="card shadow-sm p-4 mb-4">
        <h5 class="mb-3">Sender & Consignee Information</h5>
        <div class="row">
            <div class="col-md-6">
                <h6 class="fw-bold">Sender Information</h6>
                <p><strong>Name:</strong> {{ $booking->sender->sender_name }}</p>
                <p><strong>Contact:</strong> {{ $booking->sender->sender_contactno }}</p>
                <p><strong>Email:</strong> {{ $booking->sender->sender_email }}</p>
            </div>
            <div class="col-md-6">
                <h6 class="fw-bold">Consignee Information</h6>
                <p><strong>Name:</strong> {{ $booking->consignee->consignee_name }}</p>
                <p><strong>Contact:</strong> {{ $booking->consignee->consignee_contactno }}</p>
            </div>
        </div>
    </div>


{{-- Cargo Items --}}
<div class="card p-3 mb-4 shadow-sm">
    <h5 class="fw-bold mb-3">Cargo Items</h5>
    <div id="cargo-items-container">
        @foreach($booking->cargoBookings as $index => $cargo)
        <div class="cargo-item border rounded p-3 mb-3">

            {{-- Cargo Classification & Description --}}
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Cargo Classification</label>
                    <select name="cargo_classification[]" class="form-select cargo-classification mb-2">
                        <option value="">-- Select Classification --</option>
                        @foreach($cargoItems->unique('cargo_item_classification') as $item)
                            <option value="{{ $item->cargo_item_classification }}" 
                                    data-route_port="{{ $item->route_port_id }}"
                                    {{ $cargo->cargoItem->cargo_item_classification == $item->cargo_item_classification ? 'selected' : '' }}>
                                {{ $item->cargo_item_classification }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Cargo Description</label>
                    <select name="cargo_item_id[]" class="form-select cargo-description mb-2" required>
                        <option value="">-- Select Description --</option>
                        @foreach($cargoItems as $item)
                            <option value="{{ $item->cargo_item_id }}"
                                    data-classification="{{ $item->cargo_item_classification }}"
                                    data-route_port="{{ $item->route_port_id }}"
                                    {{ $cargo->cargo_item_id == $item->cargo_item_id ? 'selected' : '' }}>
                                {{ $item->cargo_item_description }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Quantity & Weight --}}
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Quantity *</label>
                    <input type="number" name="quantity[]" class="form-control" value="{{ $cargo->quantity }}" required min="1">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Weight (kg) *</label>
                    <input type="number" name="weight[]" class="form-control" value="{{ $cargo->weight }}" required>
                </div>
            </div>

            {{-- Dimensions & CBM --}}
            <div class="row mb-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Length (cm) *</label>
                    <input type="number" name="length[]" class="form-control dimension-input" value="{{ $cargo->length }}" required step="0.01" min="0">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Width (cm) *</label>
                    <input type="number" name="width[]" class="form-control dimension-input" value="{{ $cargo->width }}" required step="0.01" min="0">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Height (cm) *</label>
                    <input type="number" name="height[]" class="form-control dimension-input" value="{{ $cargo->height }}" required step="0.01" min="0">
                </div>
                <div class="col-md-3">
                    <label class="form-label">CBM</label>
                    <input type="text" class="form-control cbm-output" value="{{ number_format(($cargo->length * $cargo->width * $cargo->height)/1000000, 4) }}" readonly>
                </div>
            </div>

            {{-- Cargo Image --}}
            <div class="mb-2">
                @if($cargo->cargo_picture)
                    <img src="{{ asset('storage/cargo_pictures/'.$cargo->cargo_picture) }}" alt="Cargo Image" class="img-fluid mb-2" style="max-width:150px;">
                @endif
                <label class="btn btn-outline-secondary w-100 d-flex align-items-center justify-content-center">
                    <i class="bi bi-image me-2"></i> Change Photo
                    <input type="file" name="cargo_picture[]" class="d-none" accept="image/*">
                </label>
            </div>

        </div>
        @endforeach
    </div>

    {{-- Add/Remove Buttons --}}
    <div class="d-flex gap-2 mb-3">
        <button type="button" id="addCargoItem" class="btn btn-secondary">Add Another Cargo</button>
        <button type="button" id="removeCargoItem" class="btn btn-danger">Remove Last Cargo</button>
    </div>
</div>

{{-- Update / Cancel Buttons After Cargo Items Card --}}
<div class="text-center mb-5">
    <button type="submit" class="btn btn-primary btn-lg">UPDATE</button>
    <a href="{{ route('cargo.bookings.pending') }}" class="btn btn-outline-danger btn-lg">CANCEL</a>
</div>



<script>
const container = document.getElementById('cargo-items-container');
const voyageSelect = document.getElementById('voyage-select'); // make sure your voyage select has this ID

// Add Cargo Item
document.getElementById('addCargoItem').addEventListener('click', () => {
    const newItem = container.querySelector('.cargo-item').cloneNode(true);
    newItem.querySelectorAll('input').forEach(i => {
        if(!i.classList.contains('cbm-output')) i.value = '';
    });
    newItem.querySelectorAll('select').forEach(s => s.selectedIndex = 0);
    container.appendChild(newItem);
    attachCBMListener(newItem);
    attachClassificationFilter(newItem);
});

// Remove Cargo Item
document.getElementById('removeCargoItem').addEventListener('click', () => {
    const items = container.querySelectorAll('.cargo-item');
    if(items.length > 1) items[items.length-1].remove();
    else alert('At least one cargo item is required.');
});

// CBM Calculation
function calculateCBM(input) {
    const parent = input.closest('.cargo-item');
    const length = parseFloat(parent.querySelector('input[name="length[]"]').value) || 0;
    const width = parseFloat(parent.querySelector('input[name="width[]"]').value) || 0;
    const height = parseFloat(parent.querySelector('input[name="height[]"]').value) || 0;
    const cbm = (length * width * height) / 1000000;
    parent.querySelector('.cbm-output').value = cbm.toFixed(4);
}

// Attach listener to dimension inputs
function attachCBMListener(item) {
    item.querySelectorAll('.dimension-input').forEach(input => {
        input.addEventListener('input', () => calculateCBM(input));
    });
}

// Attach CBM listeners to existing items on page load
container.querySelectorAll('.cargo-item').forEach(attachCBMListener);

// Classification filter for descriptions
function attachClassificationFilter(item) {
    const classificationSelect = item.querySelector('.cargo-classification');
    const descSelect = item.querySelector('.cargo-description');

    classificationSelect.addEventListener('change', () => {
        const classification = classificationSelect.value;
        const voyageId = voyageSelect ? voyageSelect.value : null;

        Array.from(descSelect.options).forEach(opt => {
            if(opt.value === '') return;
            const matchesClass = opt.dataset.classification === classification;
            const matchesVoyage = voyageId ? opt.dataset.route_port == voyageId : true;
            opt.style.display = (matchesClass && matchesVoyage) ? 'block' : 'none';
        });
        descSelect.value = '';
    });

    // Trigger change to filter on page load for preselected values
    classificationSelect.dispatchEvent(new Event('change'));
}

// Attach classification filter to existing items on page load
container.querySelectorAll('.cargo-item').forEach(attachClassificationFilter);
</script>
@endsection
