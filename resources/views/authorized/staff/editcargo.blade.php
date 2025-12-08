@extends('layouts.app')
@section('page-title', 'EDIT CARGO BOOKING')
@section('content')
@include('components.authHeader')
@include('components.staff_nav')

<div class="staff-body">
    <div class="svl-title">
        <h3>EDIT CARGO ITEMS</h3>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- ========================= --}}
    {{-- BOOKING INFORMATION --}}
    {{-- ========================= --}}
    <div class="card shadow-sm p-4 mb-4">
        <h5>Booking Information</h5>
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

    {{-- ========================= --}}
    {{-- SENDER & CONSIGNEE INFORMATION --}}
    {{-- ========================= --}}
    <div class="card shadow-sm p-4 mb-4">
        <h5>Sender & Consignee Information</h5>
        <div class="row">
            <div class="col-md-6">
                <h6 class="fw-bold">Sender Information</h6>
                <p><strong>Name:</strong> {{ $booking->sender->sender_name }}</p>
                <p><strong>Contact:</strong> {{ $booking->sender->sender_contactno }}</p>
                <p><strong>Email:</strong> {{ $booking->sender->sender_email ?? '-' }}</p>
            </div>
            <div class="col-md-6">
                <h6 class="fw-bold">Consignee Information</h6>
                <p><strong>Name:</strong> {{ $booking->consignee->consignee_name }}</p>
                <p><strong>Contact:</strong> {{ $booking->consignee->consignee_contactno }}</p>
            </div>
        </div>
    </div>

    {{-- ========================= --}}
    {{-- EDIT CARGO ITEMS FORM --}}
    {{-- ========================= --}}
    <form action="{{ route('cargo.bookings.update', $booking->booking_ref_no) }}" method="POST">
        @csrf
        @method('PUT')

        @foreach($booking->cargoBookings as $index => $cargo)
            <div class="card shadow-sm p-4 mb-4">
                <h5>Cargo Item #{{ $index + 1 }}</h5>

                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label>Classification</label>
                            <select name="classification[]" required>
                                <option value="">Select Classification</option>
                                @foreach($classifications as $classification)
                                    <option value="{{ $classification }}"
                                        {{ $cargo->cargoItem->cargo_item_classification == $classification ? 'selected' : '' }}>
                                        {{ $classification }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-col">
                        <div class="form-group">
                            <label>Description</label>
                            <select name="description[]" required>
                                <option value="">Select Description</option>
                                @foreach($descriptions as $description)
                                    <option value="{{ $description }}"
                                        {{ $cargo->cargoItem->cargo_item_description == $description ? 'selected' : '' }}>
                                        {{ $description }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label>Quantity</label>
                            <input type="number" name="quantity[]" value="{{ old('quantity.'.$index, $cargo->quantity) }}" min="1" required>
                        </div>
                    </div>

                    <div class="form-col">
                        <div class="form-group">
                            <label>Length (cm)</label>
                            <input type="number" step="0.01" name="length[]" value="{{ old('length.'.$index, $cargo->length) }}" required>
                        </div>
                    </div>

                    <div class="form-col">
                        <div class="form-group">
                            <label>Width (cm)</label>
                            <input type="number" step="0.01" name="width[]" value="{{ old('width.'.$index, $cargo->width) }}" required>
                        </div>
                    </div>

                    <div class="form-col">
                        <div class="form-group">
                            <label>Height (cm)</label>
                            <input type="number" step="0.01" name="height[]" value="{{ old('height.'.$index, $cargo->height) }}" required>
                        </div>
                    </div>

                    <div class="form-col">
                        <div class="form-group">
                            <label>Weight (kg)</label>
                            <input type="number" step="0.01" name="weight[]" value="{{ old('weight.'.$index, $cargo->weight) }}" required>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach

        <div class="form-actions mb-4">
            <button type="submit" class="btn btn-primary">Update Cargo Items</button>
            <a href="{{ route('cargo.bookings.show', $booking->booking_ref_no) }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
