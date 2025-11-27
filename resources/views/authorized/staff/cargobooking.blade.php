@extends('layouts.app')
@section('page-title', 'CARGO BOOKING')

@section('content')
@include('components.authHeader')
@include('components.staff_nav')

<div class="staff-body container my-5" style="max-width: 1100px; margin: auto;">

    <div class="svl-title text-center mb-4" style="margin-top: 40px;">
        <h3>CARGO BOOKING</h3>
    </div>

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

    <form action="{{ route('cargo.bookings.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <!-- Voyage Selection -->
        <div class="mb-4">
            <label class="form-label fw-bold">Select Voyage <span class="text-danger">*</span></label>
            <select name="voyage_id" class="form-select" required>
                <option value="">-- Choose Voyage --</option>
                @foreach($voyages as $voyage)
                    <option value="{{ $voyage->voyage_id }}">
                        {{ $voyage->voyage_code }} | {{ $voyage->routePort->route_origin ?? 'N/A' }} → {{ $voyage->routePort->route_destination ?? 'N/A' }} | Departure: {{ $voyage->voyage_departure_date }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="row justify-content-center">

            <!-- LEFT: Sender & Consignee -->
            <div class="col-lg-6 mb-3">
                <div class="card p-3 mb-3 shadow-sm">
                    <h5 class="mb-3 fw-bold">Sender Information</h5>

                    <label class="form-label">First Name <span class="text-danger">*</span></label>
                    <input type="text" name="sender_firstname" class="form-control mb-2" required>

                    <label class="form-label">Last Name <span class="text-danger">*</span></label>
                    <input type="text" name="sender_lastname" class="form-control mb-2" required>

                    <label class="form-label">Contact Number <span class="text-danger">*</span></label>
                    <input type="text" name="sender_contact" class="form-control mb-2" required>

                    <label class="form-label">Email Address <span class="text-danger">*</span></label>
                    <input type="email" name="sender_email" class="form-control mb-2">

                    <label class="form-label">TIN Number (Optional)</label>
                    <input type="text" name="sender_tin" class="form-control mb-2">

                    <h5 class="mt-4 mb-3 fw-bold">Consignee Information</h5>

                    <label class="form-label">First Name <span class="text-danger">*</span></label>
                    <input type="text" name="consignee_firstname" class="form-control mb-2" required>

                    <label class="form-label">Last Name <span class="text-danger">*</span></label>
                    <input type="text" name="consignee_lastname" class="form-control mb-2" required>

                    <label class="form-label">Contact Number <span class="text-danger">*</span></label>
                    <input type="text" name="consignee_contact" class="form-control mb-2" required>
                </div>
            </div>

 <!-- ... Keep everything above unchanged ... -->

<!-- RIGHT: Cargo Items -->
<div class="col-lg-6 mb-3">
    <div class="card p-3 mb-3 shadow-sm">
        <h5 class="mb-3 fw-bold">Cargo Items</h5>

        <div id="cargo-items-container">
            <div class="cargo-item border rounded p-3 mb-3 position-relative">

                <!-- Classification -->
                <label class="form-label">Classification <span class="text-danger">*</span></label>
                <select name="cargo_classification[]" class="form-select cargo-classification mb-2">
                    <option value="">-- Select Classification --</option>
                    @foreach($cargoItems->unique('cargo_item_classification') as $item)
                        <option value="{{ $item->cargo_item_classification }}" data-route_port="{{ $item->route_port_id }}">
                            {{ $item->cargo_item_classification }}
                        </option>
                    @endforeach
                </select>

                <!-- Description -->
                <label class="form-label">Cargo Description <span class="text-danger">*</span></label>
                <select name="cargo_item_id[]" class="form-select cargo-description mb-2" required>
                    <option value="">-- Select Description --</option>
                    @foreach($cargoItems as $item)
                        <option value="{{ $item->cargo_item_id }}" data-classification="{{ $item->cargo_item_classification }}" data-route_port="{{ $item->route_port_id }}">
                            {{ $item->cargo_item_description }}
                        </option>
                    @endforeach
                </select>

                <!-- Quantity & Weight in one row -->
                <div class="d-flex gap-2 mb-2">
                    <div class="flex-fill">
                        <label class="form-label">Quantity <span class="text-danger">*</span></label>
                        <input type="number" name="cargo_quantity[]" class="form-control" required min="1">
                    </div>
                    <div class="flex-fill">
                        <label class="form-label">Weight (kg) <span class="text-danger">*</span></label>
                        <input type="number" name="cargo_weight[]" class="form-control" required>
                    </div>
                </div>

                <!-- Dimensions + CBM -->
                <label class="form-label">Cargo Dimensions (cm) & CBM <span class="text-danger">*</span></label>
                <div class="d-flex gap-2 mb-2 align-items-end">
                    <div class="flex-fill">
                        <label class="form-label">Length</label>
                        <input type="number" name="cargo_length[]" class="form-control dimension" required>
                    </div>
                    <div class="flex-fill">
                        <label class="form-label">Width</label>
                        <input type="number" name="cargo_width[]" class="form-control dimension" required>
                    </div>
                    <div class="flex-fill">
                        <label class="form-label">Height</label>
                        <input type="number" name="cargo_height[]" class="form-control dimension" required>
                    </div>
                    <div class="flex-fill">
                        <label class="form-label">CBM</label>
                        <input type="text" class="form-control cbm-output" readonly placeholder="0.0000">
                    </div>
                </div>

                <!-- Upload Photo with confirmation -->
                <label class="form-label">Upload Photo</label>
                <label class="btn btn-outline-secondary w-100 d-flex align-items-center justify-content-center mb-2">
                    <i class="bi bi-image me-2"></i> Add Photo
                    <input type="file" name="cargo_picture[]" class="d-none cargo-photo" accept="image/*">
                </label>
                <small class="text-success photo-confirmation" style="display:none;">Photo selected!</small>

            </div>
        </div>

        <div class="d-flex gap-2 mb-3">
            <button type="button" id="addCargoItem" class="btn btn-secondary">Add Another Cargo</button>
            <button type="button" id="removeCargoItem" class="btn btn-danger">Remove Last Cargo</button>
        </div>
    </div>
</div>

<div class="text-center mb-5">
    <button type="submit" class="btn btn-primary btn-lg">PROCEED</button>
    <a href="{{ route('staff.dashboard') }}" class="btn btn-outline-danger btn-lg">CANCEL BOOKING</a>
</div>


<!-- JS -->
<script>
const container = document.getElementById('cargo-items-container');
const voyageSelect = document.querySelector('select[name="voyage_id"]');

// Calculate CBM for a cargo item
function calculateCBM(item){
    const l = parseFloat(item.querySelector('[name="cargo_length[]"]').value) || 0;
    const w = parseFloat(item.querySelector('[name="cargo_width[]"]').value) || 0;
    const h = parseFloat(item.querySelector('[name="cargo_height[]"]').value) || 0;
    item.querySelector('.cbm-output').value = ((l*w*h)/1000000).toFixed(4);
}

// CBM listener
container.addEventListener('input', e => {
    if(e.target.classList.contains('dimension')){
        const item = e.target.closest('.cargo-item');
        calculateCBM(item);
    }
});

// Add/Remove cargo items
document.getElementById('addCargoItem').addEventListener('click', () => {
    const newItem = container.querySelector('.cargo-item').cloneNode(true);
    newItem.querySelectorAll('input, select').forEach(i => i.value = '');
    newItem.querySelector('.photo-confirmation').style.display = 'none';
    container.appendChild(newItem);
});
document.getElementById('removeCargoItem').addEventListener('click', () => {
    const items = container.querySelectorAll('.cargo-item');
    if(items.length>1) items[items.length-1].remove();
});

// Classification filter for descriptions
container.addEventListener('change', e => {
    if(!e.target.classList.contains('cargo-classification')) return;
    const item = e.target.closest('.cargo-item');
    const classification = e.target.value;
    const voyageId = voyageSelect.value;
    const descSelect = item.querySelector('.cargo-description');
    Array.from(descSelect.options).forEach(opt=>{
        if(opt.value==='') return;
        const matchesClass = opt.dataset.classification===classification;
        const matchesVoyage = opt.dataset.route_port==voyageId;
        opt.style.display = (matchesClass && matchesVoyage)?'block':'none';
    });
    descSelect.value='';
});

// Voyage change updates classifications
voyageSelect.addEventListener('change', () => {
    const vid = voyageSelect.value;
    container.querySelectorAll('.cargo-item').forEach(item=>{
        const classSelect = item.querySelector('.cargo-classification');
        const descSelect = item.querySelector('.cargo-description');
        descSelect.value='';
        const validClasses = Array.from(container.querySelectorAll('.cargo-description option'))
            .filter(opt => opt.dataset.route_port==vid && opt.value!=='')
            .map(opt => opt.dataset.classification)
            .filter((v,i,a)=>a.indexOf(v)===i);
        classSelect.innerHTML = '<option value="">-- Select Classification --</option>';
        validClasses.forEach(cls=>{
            const opt = document.createElement('option');
            opt.value = cls;
            opt.text = cls;
            classSelect.appendChild(opt);
        });
    });
});

// Photo confirmation
container.addEventListener('change', e => {
    if(!e.target.classList.contains('cargo-photo')) return;
    const confirmation = e.target.closest('.cargo-item').querySelector('.photo-confirmation');
    if(e.target.files.length>0){
        confirmation.style.display='inline';
        confirmation.textContent = `Photo selected: ${e.target.files[0].name}`;
    } else {
        confirmation.style.display='none';
        confirmation.textContent='';
    }
});
</script>

@endsection
