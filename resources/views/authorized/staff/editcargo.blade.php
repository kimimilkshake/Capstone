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
                                @foreach($cargoClassifications as $classification)
                                    <option value="{{ $classification->cargo_classification_id }}"
                                        {{ $cargo->cargo_classification_id == $classification->cargo_classification_id ? 'selected' : '' }}>
                                        {{ $classification->cargo_classification_name }}
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
                                @foreach($cargoItems as $item)
                                    <option value="{{ $item->cargo_item_id }}"
                                        data-freight="{{ $item->cargo_item_freight }}"
                                        data-arrastre="{{ $item->cargo_item_arrastre }}"
                                        {{ $cargo->cargo_item_id == $item->cargo_item_id ? 'selected' : '' }}>
                                        {{ $item->cargo_item_description }}
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
                            <label>Length</label>
                            <input type="number" step="0.01" name="length[]" value="{{ old('length.'.$index, $cargo->length) }}" required>
                        </div>
                    </div>

                    <div class="form-col">
                        <div class="form-group">
                            <label>Width</label>
                            <input type="number" step="0.01" name="width[]" value="{{ old('width.'.$index, $cargo->width) }}" required>
                        </div>
                    </div>

                    <div class="form-col">
                        <div class="form-group">
                            <label>Height</label>
                            <input type="number" step="0.01" name="height[]" value="{{ old('height.'.$index, $cargo->height) }}" required>
                        </div>
                    </div>

                    <div class="form-col">
                        <div class="form-group">
                            <label>Unit</label>
                            <select name="measurement_unit[]" required>
                                @php
                                    $selectedUnit = old('measurement_unit.'.$index, $cargo->measurementUnit->measurement_unit_abbreviation ?? 'cm');
                                @endphp
                                @foreach($measurementUnits as $measurementUnit)
                                    <option value="{{ $measurementUnit->measurement_unit_abbreviation }}" {{ $selectedUnit === $measurementUnit->measurement_unit_abbreviation ? 'selected' : '' }}>
                                        {{ $measurementUnit->measurement_unit_abbreviation }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-col">
                        <div class="form-group">
                            <label>Weight (kg)</label>
                            <input type="number" step="0.01" name="weight[]" value="{{ old('weight.'.$index, $cargo->weight) }}" required>
                        </div>
                    </div>

                    <div class="form-col">
                        <div class="form-group">
                            <label>CBM</label>
                            <input type="number" step="0.0001" name="cbm[]" value="{{ old('cbm.'.$index, $cargo->cbm ?? 0) }}" placeholder="0.0000" class="cargo-cbm" data-index="{{ $index }}" readonly>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach

        <div class="card shadow-sm p-4 mb-4" style="background-color: #f9f9f9;">
            <div class="row">
                <div class="col-md-6">
                    <h5>Total Value Summary</h5>
                </div>
                <div class="col-md-6 text-end">
                    <h5>Total Value: <strong id="totalValueDisplay">₱0.00</strong></h5>
                    <input type="hidden" name="total_value" id="totalValueInput" value="0.00">
                </div>
            </div>
        </div>

        <div class="form-actions mb-4">
            <button type="submit" class="btn btn-primary">Update Cargo Items</button>
            <a href="{{ route('cargo.bookings.show', $booking->booking_ref_no) }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<script>
    function calculateCBM(index) {
        const lengthInputs = document.querySelectorAll('input[name="length[]"]');
        const widthInputs = document.querySelectorAll('input[name="width[]"]');
        const heightInputs = document.querySelectorAll('input[name="height[]"]');
        const unitSelects = document.querySelectorAll('select[name="measurement_unit[]"]');
        const cbmInputs = document.querySelectorAll('input[name="cbm[]"]');

        let length = lengthInputs[index] ? (parseFloat(lengthInputs[index].value) || 0) : 0;
        let width = widthInputs[index] ? (parseFloat(widthInputs[index].value) || 0) : 0;
        let height = heightInputs[index] ? (parseFloat(heightInputs[index].value) || 0) : 0;
        const unit = unitSelects[index] ? unitSelects[index].value : 'cm';

        if (unit === 'in') {
            length *= 2.54;
            width *= 2.54;
            height *= 2.54;
        }

        const cbm = (length * width * height) / 1000000;
        if (cbmInputs[index]) {
            cbmInputs[index].value = cbm.toFixed(4);
        }

        return cbm;
    }

    function calculateValues() {
        let totalValue = 0;
        const descriptionSelects = document.querySelectorAll('select[name="description[]"]');
        const quantityInputs = document.querySelectorAll('input[name="quantity[]"]');

        descriptionSelects.forEach((descriptionSelect, index) => {
            const selectedOption = descriptionSelect.options[descriptionSelect.selectedIndex];
            const quantity = quantityInputs[index] ? (parseFloat(quantityInputs[index].value) || 0) : 0;
            const cbm = calculateCBM(index);
            const freight = selectedOption ? (parseFloat(selectedOption.dataset.freight) || 0) : 0;
            const arrastre = selectedOption ? (parseFloat(selectedOption.dataset.arrastre) || 0) : 0;

            totalValue += (freight + arrastre) * cbm * quantity;
        });

        const totalDisplay = document.getElementById('totalValueDisplay');
        const totalInput = document.getElementById('totalValueInput');
        if (totalDisplay) totalDisplay.textContent = '₱' + totalValue.toFixed(2);
        if (totalInput) totalInput.value = totalValue.toFixed(2);
    }

    document.addEventListener('DOMContentLoaded', function() {
        calculateValues();
        document.querySelectorAll('select[name="description[]"]').forEach(input => {
            input.addEventListener('change', calculateValues);
        });
        document.querySelectorAll('select[name="measurement_unit[]"]').forEach(input => {
            input.addEventListener('change', calculateValues);
        });
        document.querySelectorAll('input[name="length[]"], input[name="width[]"], input[name="height[]"]').forEach(input => {
            input.addEventListener('change', calculateValues);
            input.addEventListener('input', calculateValues);
        });
        document.querySelectorAll('input[name="cbm[]"]').forEach(input => {
            input.addEventListener('change', calculateValues);
            input.addEventListener('input', calculateValues);
        });
        document.querySelectorAll('input[name="quantity[]"]').forEach(input => {
            input.addEventListener('change', calculateValues);
            input.addEventListener('input', calculateValues);
        });
    });

    // Enforce 25kg max per booking
    document.querySelector('form').addEventListener('submit', function(e){
        let totalWeight = 0;
        document.querySelectorAll('input[name="weight[]"]').forEach(input => {
            totalWeight += parseFloat(input.value) || 0;
        });
        if(totalWeight > 25){
            e.preventDefault();
            alert('Total weight per booking must not exceed 25kg.');
            return false;
        }
    });
</script>
@endsection
