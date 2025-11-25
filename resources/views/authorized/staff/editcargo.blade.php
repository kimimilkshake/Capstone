@extends('layouts.app')
@section('page-title', 'EDIT CARGO BOOKING')

@section('content')
@include('components.authHeader')
@include('components.staff_nav')

<div class="staff-body container my-5" style="max-width:1100px; margin:auto;">
    <div class="svl-title text-center mb-4" style="margin-top:40px;">
        <h3>EDIT CARGO BOOKING #{{ $booking->booking_ref_no }}</h3>
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

    <form action="{{ route('cargo.bookings.update', $booking->booking_ref_no) }}" method="POST" enctype="multipart/form-data">
        @csrf

        {{-- Sender & Consignee --}}
        <div class="card p-3 mb-4 shadow-sm">
            <h5 class="mb-3">Sender Information</h5>
            <p>{{ $booking->sender->sender_name ?? '-' }} | {{ $booking->sender->sender_contactno ?? '-' }} | {{ $booking->sender->sender_email ?? '-' }}</p>

            <h5 class="mt-4 mb-3">Consignee Information</h5>
            <p>{{ $booking->consignee->consignee_name ?? '-' }} | {{ $booking->consignee->consignee_contactno ?? '-' }}</p>
        </div>

        {{-- Cargo Items --}}
        <div class="card p-3 mb-4 shadow-sm">
            <h5 class="mb-3">Cargo Items</h5>
            <div id="cargo-items-container">
                @foreach($booking->cargoBookings as $index => $cargo)
                <div class="cargo-item border rounded p-3 mb-3 position-relative">
                    {{-- Cargo Description --}}
                    <div class="mb-2">
                        <label for="cargo_item_{{ $index }}">Cargo Description</label>
                        <select name="cargo_item_id[]" id="cargo_item_{{ $index }}" class="form-select" required>
                            @foreach($cargoItems as $item)
                                <option value="{{ $item->cargo_item_id }}"
                                    {{ $cargo->cargo_item_id == $item->cargo_item_id ? 'selected' : '' }}>
                                    {{ $item->cargo_item_description }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Dimensions & Quantity --}}
                    <div class="mb-2 d-flex gap-2">
                        <input type="number" name="quantity[]" class="form-control" value="{{ $cargo->quantity }}" placeholder="Quantity" required min="1">
                        <input type="number" name="length[]" class="form-control" value="{{ $cargo->length }}" placeholder="Length (cm)" required>
                    </div>
                    <div class="mb-2 d-flex gap-2">
                        <input type="number" name="width[]" class="form-control" value="{{ $cargo->width }}" placeholder="Width (cm)" required>
                        <input type="number" name="height[]" class="form-control" value="{{ $cargo->height }}" placeholder="Height (cm)" required>
                    </div>
                    <div class="mb-2">
                        <input type="number" name="weight[]" class="form-control" value="{{ $cargo->weight }}" placeholder="Weight (kg)" required>
                    </div>

                    {{-- Cargo Image --}}
                    <div class="mb-2">
                        @if($cargo->cargo_picture)
                            <img src="{{ asset('storage/cargo_pictures/'.$cargo->cargo_picture) }}" alt="Cargo Image" style="max-width:150px;">
                        @endif
                        <label class="btn btn-outline-secondary w-100 d-flex align-items-center justify-content-center mt-2">
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

        {{-- Form Actions --}}
        <div class="text-center mb-5">
            <button type="submit" class="btn btn-primary btn-lg">UPDATE CARGO BOOKING</button>
            <a href="{{ route('cargo.bookings.pending') }}" class="btn btn-outline-danger btn-lg">CANCEL</a>
        </div>
    </form>
</div>

<script>
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
