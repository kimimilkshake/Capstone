@extends('layouts.app')
@section('page-title', 'CARGO BOOKING')

@section('content')
    @include('components.hero')

    <div class="container-fluid my-5 px-3 px-xl-4">
        <div class="card shadow-sm mx-auto passenger-cargo-card" style="max-width:1520px; background-color:#f0f0f0;">
            <div class="card-header bg-dark text-white text-center mb-1">
                <h5 class="mb-0">CARGO BOOKING FORM</h5>
            </div>

            <div class="card-body">
                @if (session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                <form id="passengerCargoForm" action="{{ route('cargobooking.confirm') }}" method="POST" enctype="multipart/form-data" novalidate>
                    @csrf

                    <div class="row">

                        <!-- LEFT SIDE -->
                        <div class="col-md-6 mb-3">
                            <h6 class="fw-bold mt-4">Voyage Information</h6>
                            <div class="bg-white p-3 rounded shadow-sm mb-3 small">
                                <p class="mb-1"><strong>Vessel Name:</strong> {{ $vesselName }}</p>
                                <p class="mb-1"><strong>Route:</strong> {{ $routeFrom }} → {{ $routeTo }}</p>
                                <p class="mb-1"><strong>Departure Date:</strong> {{ $departureDate }}</p>
                                <p class="mb-1"><strong>Departure Time:</strong> {{ $departureTime }}</p>
                                <p class="mb-0"><strong>Port of Origin:</strong> {{ $portOfOrigin }}</p>
                            </div>

                            <input type="hidden" name="voyage_id" value="{{ request()->voyage_id }}">
                            <h6 class="fw-bold">Sender Information</h6>

                            <div class="mb-2">
                                <label class="form-label">First Name <span class="text-danger">*</span></label>
                                <div class="input-group has-validation">
                                    <input type="text" name="sender_firstname" class="form-control required-field" required>
                                    <span class="input-group-text error-icon" style="display:none; background-color: #f8d7da;">
                                        <i class="bi bi-exclamation-circle-fill text-danger"></i>
                                    </span>
                                </div>
                                <small class="error-message text-danger d-block mt-1" style="display:none;"></small>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Last Name <span class="text-danger">*</span></label>
                                <div class="input-group has-validation">
                                    <input type="text" name="sender_lastname" class="form-control required-field" required>
                                    <span class="input-group-text error-icon" style="display:none; background-color: #f8d7da;">
                                        <i class="bi bi-exclamation-circle-fill text-danger"></i>
                                    </span>
                                </div>
                                <small class="error-message text-danger d-block mt-1" style="display:none;"></small>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Contact Number <span class="text-danger">*</span></label>
                                <div class="input-group has-validation">
                                    <input type="text" name="sender_contact" class="form-control required-field" required>
                                    <span class="input-group-text error-icon" style="display:none; background-color: #f8d7da;">
                                        <i class="bi bi-exclamation-circle-fill text-danger"></i>
                                    </span>
                                </div>
                                <small class="error-message text-danger d-block mt-1" style="display:none;"></small>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Email Address <span class="text-danger">*</span></label>
                                <div class="input-group has-validation">
                                    <input type="email" name="sender_email" class="form-control required-field" required>
                                    <span class="input-group-text error-icon" style="display:none; background-color: #f8d7da;">
                                        <i class="bi bi-exclamation-circle-fill text-danger"></i>
                                    </span>
                                </div>
                                <small class="error-message text-danger d-block mt-1" style="display:none;"></small>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">TIN Number (Optional)</label>
                                <input type="text" name="sender_tin" class="form-control">
                            </div>

                            <h6 class="fw-bold mt-4">Consignee Information</h6>
                            <div class="mb-2">
                                <label class="form-label">First Name <span class="text-danger">*</span></label>
                                <div class="input-group has-validation">
                                    <input type="text" name="consignee_firstname" class="form-control required-field" required>
                                    <span class="input-group-text error-icon" style="display:none; background-color: #f8d7da;">
                                        <i class="bi bi-exclamation-circle-fill text-danger"></i>
                                    </span>
                                </div>
                                <small class="error-message text-danger d-block mt-1" style="display:none;"></small>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Last Name <span class="text-danger">*</span></label>
                                <div class="input-group has-validation">
                                    <input type="text" name="consignee_lastname" class="form-control required-field" required>
                                    <span class="input-group-text error-icon" style="display:none; background-color: #f8d7da;">
                                        <i class="bi bi-exclamation-circle-fill text-danger"></i>
                                    </span>
                                </div>
                                <small class="error-message text-danger d-block mt-1" style="display:none;"></small>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Contact Number <span class="text-danger">*</span></label>
                                <div class="input-group has-validation">
                                    <input type="text" name="consignee_contact" class="form-control required-field" required>
                                    <span class="input-group-text error-icon" style="display:none; background-color: #f8d7da;">
                                        <i class="bi bi-exclamation-circle-fill text-danger"></i>
                                    </span>
                                </div>
                                <small class="error-message text-danger d-block mt-1" style="display:none;"></small>
                            </div>


                        </div>

                        <!-- RIGHT SIDE (CARGO ITEMS) -->
                        <div class="col-md-6 mb-3">
                            <h6 class="fw-bold">Cargo Information</h6>

                                <div class="mb-3 d-flex align-items-center gap-3">
                                    <label class="form-label mb-0">No. of Cargo <span class="text-danger">*</span></label>
                                    <input type="number" name="no_of_cargo" id="no_of_cargo" class="form-control form-control-sm" min="1" max="5" value="1" style="max-width:120px;">
                                    <small class="text-muted mb-0">Maximum 5 items</small>
                                </div>

                                <div id="cargo-items-container">

                                <div class="cargo-item border rounded p-3 mb-3">

                                    <!-- Classification + Description in one row -->
                                    <div class="mb-3 row gx-2">
                                        <div class="col-6">
                                            <label class="form-label">Classification <span
                                                    class="text-danger">*</span></label>
                                            <div class="input-group has-validation">
                                                <select name="cargo_classification[]" class="form-control cargo-classification required-field">
                                                    <option value="">-- Select Classification --</option>
                                                    @foreach($cargoClassifications as $classification)
                                                        <option value="{{ $classification->cargo_classification_id }}">
                                                            {{ $classification->cargo_classification_name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <span class="input-group-text error-icon" style="display:none; background-color: #f8d7da;">
                                                    <i class="bi bi-exclamation-circle-fill text-danger"></i>
                                                </span>
                                            </div>
                                            <small class="error-message text-danger d-block mt-1" style="display:none;"></small>
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label">Description <span class="text-danger">*</span></label>
                                            <div class="input-group has-validation">
                                                <select name="cargo_item_id[]" class="form-control cargo-description required-field">
                                                    <option value="">-- Select Description --</option>
                                                    @foreach($cargoItems->sortBy('cargo_item_description') as $cargo)
                                                            @if($cargo->route_category_id == ($voyage->routePort->route_category_id ?? null))
                                                            <option value="{{ $cargo->cargo_item_id }}"
                                                                data-route-category="{{ $cargo->route_category_id ?? '' }}"
                                                                data-classification="{{ $cargo->cargo_item_classification ?? '' }}"
                                                                data-measure-required="{{ $cargo->cargo_item_measure_required ?? 'No' }}"
                                                                data-measurement-unit="{{ $cargo->measurementUnit->measurement_unit_abbreviation ?? 'cm' }}"
                                                                data-min-length="{{ $cargo->cargo_item_min_length ?? '' }}"
                                                                data-max-length="{{ $cargo->cargo_item_max_length ?? '' }}"
                                                                data-min-width="{{ $cargo->cargo_item_min_width ?? '' }}"
                                                                data-max-width="{{ $cargo->cargo_item_max_width ?? '' }}"
                                                                data-min-height="{{ $cargo->cargo_item_min_height ?? '' }}"
                                                                data-max-height="{{ $cargo->cargo_item_max_height ?? '' }}">
                                                                {{ $cargo->cargo_item_description }}
                                                            </option>
                                                        @endif
                                                    @endforeach
                                                </select>
                                                <span class="input-group-text error-icon" style="display:none; background-color: #f8d7da;">
                                                    <i class="bi bi-exclamation-circle-fill text-danger"></i>
                                                </span>
                                            </div>
                                            <small class="error-message text-danger d-block mt-1" style="display:none;"></small>
                                        </div>
                                    </div>

                                    <!-- Quantity & Weight (Always Visible) -->
                                    <div class="mb-2 d-flex gap-2">
                                        <div class="flex-fill">
                                            <label class="form-label">Quantity <span class="text-danger">*</span></label>
                                            <div class="input-group has-validation">
                                                <input type="number" name="cargo_quantity[]" class="form-control required-field"
                                                    placeholder="Quantity" min="1" required>
                                                <span class="input-group-text error-icon" style="display:none; background-color: #f8d7da;">
                                                    <i class="bi bi-exclamation-circle-fill text-danger"></i>
                                                </span>
                                            </div>
                                            <small class="error-message text-danger d-block mt-1" style="display:none;"></small>
                                        </div>
                                        <div class="flex-fill">
                                            <label class="form-label">Total Weight (kg) <span class="text-danger">*</span></label>
                                            <div class="input-group has-validation">
                                                <input type="number" name="cargo_weight[]" class="form-control required-field"
                                                    placeholder="Weight" step="0.01" min="0" required>
                                                <span class="input-group-text error-icon" style="display:none; background-color: #f8d7da;">
                                                    <i class="bi bi-exclamation-circle-fill text-danger"></i>
                                                </span>
                                            </div>
                                            <small class="error-message text-danger d-block mt-1" style="display:none;"></small>
                                        </div>
                                    </div>

                                    <!-- Cargo Dimensions (Always Visible) -->
                                    <div class="cargo-dimensions-block">
                                        <label class="form-label">Cargo Dimensions <span class="text-danger">*</span></label>
                                        <div class="d-flex gap-2 align-items-end mb-3">
                                        <div class="flex-fill">
                                            <label class="form-label small">Length <span
                                                    class="text-danger">*</span></label>
                                            <div class="input-group has-validation">
                                                <input type="number" name="cargo_length[]" class="form-control dimension"
                                                    placeholder="Length" step="0.01" min="0" required>
                                                <span class="input-group-text error-icon" style="display:none; background-color: #f8d7da;">
                                                    <i class="bi bi-exclamation-circle-fill text-danger"></i>
                                                </span>
                                            </div>
                                            <!-- Removed measurement range display -->
                                            <small class="error-message text-danger d-block mt-1" style="display:none;"></small>
                                        </div>
                                        <div class="flex-fill">
                                            <label class="form-label small">Width <span
                                                    class="text-danger">*</span></label>
                                            <div class="input-group has-validation">
                                                <input type="number" name="cargo_width[]" class="form-control dimension"
                                                    placeholder="Width" step="0.01" min="0" required>
                                                <span class="input-group-text error-icon" style="display:none; background-color: #f8d7da;">
                                                    <i class="bi bi-exclamation-circle-fill text-danger"></i>
                                                </span>
                                            </div>

                                            <small class="error-message text-danger d-block mt-1" style="display:none;"></small>
                                        </div>
                                        <div class="flex-fill">
                                            <label class="form-label small">Height <span
                                                    class="text-danger">*</span></label>
                                            <div class="input-group has-validation">
                                                <input type="number" name="cargo_height[]" class="form-control dimension"
                                                    placeholder="Height" step="0.01" min="0" required>
                                                <span class="input-group-text error-icon" style="display:none; background-color: #f8d7da;">
                                                    <i class="bi bi-exclamation-circle-fill text-danger"></i>
                                                </span>
                                            </div>

                                            <small class="error-message text-danger d-block mt-1" style="display:none;"></small>
                                        </div>
                                        <div class="flex-fill">
                                            <label class="form-label small">Unit</label>
                                            <select name="measurement_unit[]" class="form-select unitSelect">
                                                @foreach($measurementUnits as $measurementUnit)
                                                    <option value="{{ $measurementUnit->measurement_unit_abbreviation ?: 'cm' }}">
                                                        {{ $measurementUnit->measurement_unit_abbreviation ?: 'cm' }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="flex-fill">
                                            <label class="form-label small">CBM</label>
                                            <input type="text" class="form-control cbm-output" readonly placeholder="0.0000">
                                        </div>
                                    </div>
                                    </div>
                                    <input type="hidden" name="cargo_cbm[]" class="cargo-cbm-input" value="0.0000">

                                    <!-- Photo Upload -->
                                    <div class="mb-2">
                                        <label class="form-label">Photo <span class="text-danger">*</span></label>
                                        <div class="photo-input-wrapper">
                                            <label class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-cloud-arrow-up"></i> Choose Photo
                                                <input type="file" name="cargo_picture[]" class="form-control cargo-photo required-field" style="display:none;" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                                            </label>
                                                                            <div class="mb-2">
                                    <small class="text-muted">Accepted file types are JPG, JPEG, PNG, and WEBP. Maximum file size is 5 MB per photo.</small>
                                </div>
                                            <span class="input-group-text error-icon" style="display:none; background-color: #f8d7da; margin-top: 0.5rem; border-radius: 0.25rem; padding: 0.375rem 0.75rem;">
                                                <i class="bi bi-exclamation-circle-fill text-danger"></i>
                                            </span>
                                            <small class="error-message photo-error-message text-danger ms-2" style="display:none;"></small>
                                        </div>
                                        <small class="text-success photo-confirmation" style="display:none;">Photo selected!</small>
                                    </div>

                                </div> <!-- end cargo-item -->

                            </div>

                    <div class="d-flex justify-content-end mt-4 gap-3">
                        <a href="{{ route('bookingtype') }}" class="btn btn-outline-danger fw-bold py-3"
                            style="width:180px;">
                            CANCEL BOOKING
                        </a>
                        <button type="submit" class="btn btn-primary fw-bold py-3" style="width:180px;">
                            PROCEED
                        </button>
                    </div>

                </form>
                <!-- Loading overlay -->
                <div id="passengerOverlay" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.45); z-index:1055; align-items:center; justify-content:center;">
                    <div class="text-center text-white">
                        <div class="spinner-border text-light" role="status" style="width:3rem; height:3rem;"></div>
                        <div class="mt-3">Loading... please wait</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const container = document.getElementById('cargo-items-container');
        const noInput = document.getElementById('no_of_cargo');
        const MAX_ITEMS = 5;
        const MAX_PHOTO_SIZE_MB = 5;
        const MAX_PHOTO_SIZE_BYTES = MAX_PHOTO_SIZE_MB * 1024 * 1024;
        const ALLOWED_PHOTO_TYPES = ['image/jpeg', 'image/png', 'image/webp'];

        function normalizeUnitValue(unitValue) {
            const normalized = String(unitValue || '').trim().toLowerCase();

            if (['in', 'inch', 'inches'].includes(normalized)) return 'in';
            if (['cm', 'centimeter', 'centimeters'].includes(normalized)) return 'cm';
            if (['mm', 'millimeter', 'millimeters'].includes(normalized)) return 'mm';
            if (['m', 'meter', 'meters'].includes(normalized)) return 'm';
            if (['ft', 'foot', 'feet'].includes(normalized)) return 'ft';

            return normalized || 'cm';
        }

        function toCentimeters(value, unitValue) {
            const unit = normalizeUnitValue(unitValue);
            const numericValue = parseFloat(value) || 0;

            if (unit === 'in') return numericValue * 2.54;
            if (unit === 'mm') return numericValue * 0.1;
            if (unit === 'm') return numericValue * 100;
            if (unit === 'ft') return numericValue * 30.48;

            return numericValue;
        }


        function validatePhotoFileInput(photoInput) {
            if (!photoInput || !photoInput.files || photoInput.files.length === 0) {
                return { valid: true };
            }

            const file = photoInput.files[0];
            const isAllowedType = ALLOWED_PHOTO_TYPES.includes(String(file.type || '').toLowerCase());
            if (!isAllowedType) {
                return {
                    valid: false,
                    message: 'Invalid photo type. Allowed: JPG, JPEG, PNG, WEBP.'
                };
            }

            if (file.size > MAX_PHOTO_SIZE_BYTES) {
                return {
                    valid: false,
                    message: `Photo exceeds ${MAX_PHOTO_SIZE_MB} MB limit.`
                };
            }

            return { valid: true };
        }

        function clearAllErrors() {
            document.querySelectorAll('.required-field').forEach(field => {
                if (field.type === 'file') {
                    const cargoItem = field.closest('.cargo-item');
                    const wrapper = field.closest('.photo-input-wrapper');
                    if (cargoItem && wrapper) {
                        const errorIcon = wrapper.querySelector('.error-icon');
                        const errorMessage = cargoItem.querySelector('.photo-error-message');
                        if (errorIcon) errorIcon.style.display = 'none';
                        if (errorMessage) {
                            errorMessage.style.display = 'none';
                            errorMessage.textContent = '';
                        }
                    }
                } else {
                    const inputGroup = field.parentElement;
                    const errorIcon = inputGroup ? inputGroup.querySelector('.error-icon') : null;
                    const errorMessage = inputGroup ? inputGroup.nextElementSibling : null;

                    if (errorIcon) errorIcon.style.display = 'none';
                    if (errorMessage && errorMessage.classList.contains('error-message')) {
                        errorMessage.style.display = 'none';
                        errorMessage.textContent = '';
                    }
                    field.classList.remove('is-invalid');
                }
            });
        }

        function showFieldError(field, message) {
            if (!field) return;
            if (field.type === 'file') {
                const cargoItem = field.closest('.cargo-item');
                const wrapper = field.closest('.photo-input-wrapper');
                if (cargoItem && wrapper) {
                    const errorIcon = wrapper.querySelector('.error-icon');
                    const errorMessage = cargoItem.querySelector('.photo-error-message');
                    const confirmation = cargoItem.querySelector('.photo-confirmation');

                    if (errorIcon) errorIcon.style.display = 'flex';
                    if (confirmation) {
                        confirmation.style.display = 'none';
                        confirmation.textContent = '';
                    }
                    if (errorMessage) {
                        errorMessage.textContent = message;
                        errorMessage.style.display = 'block';
                    }
                }
            } else {
                const inputGroup = field.parentElement;
                const errorIcon = inputGroup ? inputGroup.querySelector('.error-icon') : null;
                const errorMessage = inputGroup ? inputGroup.nextElementSibling : null;

                if (errorIcon) errorIcon.style.display = 'flex';
                if (errorMessage && errorMessage.classList.contains('error-message')) {
                    errorMessage.textContent = message;
                    errorMessage.style.display = 'block';
                }
                field.classList.add('is-invalid');
            }
        }

        function calculateCBM(item) {
            const unitEl = item.querySelector('.unitSelect');
            const unit = unitEl ? unitEl.value : 'cm';
            const l = toCentimeters(item.querySelector('[name="cargo_length[]"]').value, unit);
            const w = toCentimeters(item.querySelector('[name="cargo_width[]"]').value, unit);
            const h = toCentimeters(item.querySelector('[name="cargo_height[]"]').value, unit);

            const cbm = (l * w * h) / 1000000;
            item.querySelector('.cbm-output').value = cbm.toFixed(4);
            const hiddenCbmInput = item.querySelector('.cargo-cbm-input');
            if (hiddenCbmInput) {
                hiddenCbmInput.value = cbm.toFixed(4);
            }
            return cbm;
        }

        function applyMeasurementRules(item, selectedOption) {
            if (!item || !selectedOption) return;

            const measureRequired = (selectedOption.dataset.measureRequired || 'No').toString().trim().toLowerCase();
            const minLength = parseFloat(selectedOption.dataset.minLength || '0');
            const maxLength = parseFloat(selectedOption.dataset.maxLength || selectedOption.dataset.minLength || '0');
            const minWidth = parseFloat(selectedOption.dataset.minWidth || '0');
            const maxWidth = parseFloat(selectedOption.dataset.maxWidth || selectedOption.dataset.minWidth || '0');
            const minHeight = parseFloat(selectedOption.dataset.minHeight || '0');
            const maxHeight = parseFloat(selectedOption.dataset.maxHeight || selectedOption.dataset.minHeight || '0');

            const maxLengthValue = maxLength.toFixed(2);
            const maxWidthValue = maxWidth.toFixed(2);
            const maxHeightValue = maxHeight.toFixed(2);
            const unitFromItem = selectedOption.dataset.measurementUnit || 'cm';

            const lengthInput = item.querySelector('[name="cargo_length[]"]');
            const widthInput = item.querySelector('[name="cargo_width[]"]');
            const heightInput = item.querySelector('[name="cargo_height[]"]');
            const unitSelect = item.querySelector('.unitSelect');
            const dimensionsBlock = item.querySelector('.cargo-dimensions-block');

            if (unitSelect) {
                const normalizedItemUnit = normalizeUnitValue(unitFromItem);
                const matchingOption = Array.from(unitSelect.options).find(option =>
                    normalizeUnitValue(option.value) === normalizedItemUnit
                );

                if (matchingOption) {
                    unitSelect.value = matchingOption.value;
                }
            }

            if (measureRequired === 'yes') {
                if (lengthInput) {
                    lengthInput.value = maxLengthValue;
                    lengthInput.readOnly = true;
                }
                if (widthInput) {
                    widthInput.value = maxWidthValue;
                    widthInput.readOnly = true;
                }
                if (heightInput) {
                    heightInput.value = maxHeightValue;
                    heightInput.readOnly = true;
                }
                // Keep the select enabled so its value is submitted with the form.
                if (unitSelect) {
                    unitSelect.disabled = false;
                }
                if (dimensionsBlock) {
                    dimensionsBlock.style.display = 'none';
                }
            } else {
                if (lengthInput) {
                    lengthInput.value = '';
                    lengthInput.readOnly = false;
                }
                if (widthInput) {
                    widthInput.value = '';
                    widthInput.readOnly = false;
                }
                if (heightInput) {
                    heightInput.value = '';
                    heightInput.readOnly = false;
                }
                if (unitSelect) {
                    unitSelect.disabled = false;
                }
                if (dimensionsBlock) {
                    dimensionsBlock.style.display = '';
                }
            }

            calculateCBM(item);
        }

        function updateCargoItemComputedValues(item) {
            const descriptionSelect = item.querySelector('.cargo-description');
            if (!descriptionSelect) return;
            const selectedOption = descriptionSelect.options[descriptionSelect.selectedIndex];
            applyMeasurementRules(item, selectedOption);
        }

        function syncCargoItems() {
            if (!noInput) return;
            let desired = parseInt(noInput.value) || 1;
            if (desired < 1) { desired = 1; noInput.value = 1; }
            if (desired > MAX_ITEMS) { desired = MAX_ITEMS; noInput.value = MAX_ITEMS; }
            const items = Array.from(container.querySelectorAll('.cargo-item'));
            const current = items.length;
            const original = items[0];

            if (desired > current) {
                for (let i = current; i < desired; i++) {
                    const newItem = original.cloneNode(true);
                    newItem.querySelectorAll('input').forEach(el => {
                        if (el.type === 'file') {
                            el.value = '';
                        } else {
                            el.value = '';
                        }
                    });
                    newItem.querySelectorAll('select').forEach(el => {
                        el.selectedIndex = 0;
                        el.querySelectorAll('option').forEach(o => { o.style.display = ''; });
                    });

                    const photoInput = newItem.querySelector('input[type="file"][name="cargo_picture[]"]');
                    if (photoInput) {
                        const freshPhotoInput = photoInput.cloneNode();
                        photoInput.replaceWith(freshPhotoInput);
                    }

                    const photoConfirmation = newItem.querySelector('.photo-confirmation');
                    if (photoConfirmation) {
                        photoConfirmation.style.display = 'none';
                        photoConfirmation.textContent = 'Photo selected!';
                    }

                    const photoError = newItem.querySelector('.photo-error-message');
                    if (photoError) {
                        photoError.style.display = 'none';
                        photoError.textContent = '';
                    }

                    const photoErrorIcon = newItem.querySelector('.photo-input-wrapper .error-icon');
                    if (photoErrorIcon) {
                        photoErrorIcon.style.display = 'none';
                    }

                    newItem.querySelectorAll('.error-message').forEach(msg => {
                        msg.style.display = 'none';
                        msg.textContent = '';
                    });
                    newItem.querySelectorAll('.error-icon').forEach(icon => {
                        icon.style.display = 'none';
                    });
                    newItem.querySelectorAll('.is-invalid').forEach(field => {
                        field.classList.remove('is-invalid');
                    });

                    newItem.querySelector('.cbm-output').value = '0.0000';
                    const hiddenCbmInput = newItem.querySelector('.cargo-cbm-input');
                    if (hiddenCbmInput) hiddenCbmInput.value = '0.0000';
                    const ms = newItem.querySelector('.measurements-section');
                    if (ms) ms.style.display = 'none';
                    container.appendChild(newItem);
                    updateCargoItemComputedValues(newItem);
                }
            } else if (desired < current) {
                for (let i = current; i > desired; i--) {
                    const last = container.querySelector('.cargo-item:last-child');
                    if (last) last.remove();
                }
            }
        }

        function validateForm() {
            clearAllErrors();
            let isValid = true;
            const form = document.getElementById('passengerCargoForm');

            const senderFirstname = form.querySelector('input[name="sender_firstname"]');
            const senderLastname = form.querySelector('input[name="sender_lastname"]');
            const senderContact = form.querySelector('input[name="sender_contact"]');
            const senderEmail = form.querySelector('input[name="sender_email"]');

            if (!senderFirstname.value.trim()) { showFieldError(senderFirstname, 'First Name is required'); isValid = false; }
            if (!senderLastname.value.trim()) { showFieldError(senderLastname, 'Last Name is required'); isValid = false; }
            if (!senderContact.value.trim()) { showFieldError(senderContact, 'Contact Number is required'); isValid = false; }
            if (!senderEmail.value.trim()) { showFieldError(senderEmail, 'Email Address is required'); isValid = false; }

            const consigneeFirstname = form.querySelector('input[name="consignee_firstname"]');
            const consigneeLastname = form.querySelector('input[name="consignee_lastname"]');
            const consigneeContact = form.querySelector('input[name="consignee_contact"]');

            if (!consigneeFirstname.value.trim()) { showFieldError(consigneeFirstname, 'First Name is required'); isValid = false; }
            if (!consigneeLastname.value.trim()) { showFieldError(consigneeLastname, 'Last Name is required'); isValid = false; }
            if (!consigneeContact.value.trim()) { showFieldError(consigneeContact, 'Contact Number is required'); isValid = false; }

            const cargoItems = document.querySelectorAll('.cargo-item');
            cargoItems.forEach((item) => {
                const classification = item.querySelector('[name="cargo_classification[]"]');
                const description = item.querySelector('[name="cargo_item_id[]"]');
                const photo = item.querySelector('[name="cargo_picture[]"]');
                const quantity = item.querySelector('[name="cargo_quantity[]"]');
                const weight = item.querySelector('[name="cargo_weight[]"]');
                const length = item.querySelector('[name="cargo_length[]"]');
                const width = item.querySelector('[name="cargo_width[]"]');
                const height = item.querySelector('[name="cargo_height[]"]');

                if (!classification.value.trim()) { showFieldError(classification, 'Classification is required'); isValid = false; }
                if (!description.value.trim()) { showFieldError(description, 'Description is required'); isValid = false; }
                if (photo && photo.files.length === 0) { showFieldError(photo, 'Photo is required'); isValid = false; }
                if (quantity && !quantity.value.trim()) { showFieldError(quantity, 'Quantity is required'); isValid = false; }
                if (weight && !weight.value.trim()) { showFieldError(weight, 'Weight is required'); isValid = false; }

                if (quantity && quantity.value !== '' && Number(quantity.value) <= 0) {
                    showFieldError(quantity, 'Quantity must be greater than zero');
                    isValid = false;
                }
                if (weight && weight.value !== '' && Number(weight.value) < 0) {
                    showFieldError(weight, 'Weight cannot be negative');
                    isValid = false;
                }
                if (length && length.value !== '' && Number(length.value) < 0) {
                    showFieldError(length, 'Length cannot be negative');
                    isValid = false;
                }
                if (width && width.value !== '' && Number(width.value) < 0) {
                    showFieldError(width, 'Width cannot be negative');
                    isValid = false;
                }
                if (height && height.value !== '' && Number(height.value) < 0) {
                    showFieldError(height, 'Height cannot be negative');
                    isValid = false;
                }

                const photoValidation = validatePhotoFileInput(photo);
                if (!photoValidation.valid) {
                    showFieldError(photo, photoValidation.message);
                    isValid = false;
                }
            });

            const allNumberInputs = form.querySelectorAll('input[type="number"]');
            allNumberInputs.forEach(input => {
                if (input.value !== '' && Number(input.value) < 0) {
                    showFieldError(input, 'Negative values are not allowed');
                    isValid = false;
                }
            });

            if (!isValid) { window.scrollTo(0, 0); }
            return isValid;
        }


container.addEventListener('change', e => {
    if(!e.target.classList.contains('cargo-description')) return;
    const item = e.target.closest('.cargo-item');
    const selectedOption = e.target.options[e.target.selectedIndex];
    applyMeasurementRules(item, selectedOption);
});





        document.addEventListener('input', function(e){
            if (e.target && e.target.classList && e.target.classList.contains('dimension')){
                const item = e.target.closest('.cargo-item');
                calculateCBM(item);
            }
        });

        // Recalculate CBM when per-item unit changes
        container.addEventListener('change', function(e){
            if (e.target && e.target.classList && e.target.classList.contains('unitSelect')){
                const item = e.target.closest('.cargo-item');
                calculateCBM(item);
            }
        });

        if (noInput) {
            noInput.addEventListener('change', syncCargoItems);
            window.addEventListener('DOMContentLoaded', function () {
                syncCargoItems();
                container.querySelectorAll('.cargo-item').forEach(updateCargoItemComputedValues);
            });
        }


        container.addEventListener('change', function(e) {
            if (!e.target.classList.contains('cargo-photo')) return;
            const cargoItem = e.target.closest('.cargo-item');
            const confirmation = cargoItem.querySelector('.photo-confirmation');
            const errorMessage = cargoItem.querySelector('.photo-error-message');
            const errorIcon = cargoItem.querySelector('.photo-input-wrapper .error-icon');
            if (e.target.files.length > 0) {
                const photoValidation = validatePhotoFileInput(e.target);
                if (!photoValidation.valid) {
                    confirmation.style.display = 'none';
                    confirmation.textContent = '';
                    if (errorMessage) {
                        errorMessage.style.display = 'block';
                        errorMessage.textContent = photoValidation.message;
                    }
                    if (errorIcon) {
                        errorIcon.style.display = 'flex';
                    }
                    e.target.value = '';
                    return;
                }

                confirmation.style.display = 'inline';
                confirmation.textContent = `Photo selected: ${e.target.files[0].name}`;
                if (errorMessage) {
                    errorMessage.style.display = 'none';
                    errorMessage.textContent = '';
                }
                if (errorIcon) {
                    errorIcon.style.display = 'none';
                }
            } else {
                confirmation.style.display = 'none';
                confirmation.textContent = '';
            }
        });

        const passengerForm = document.getElementById('passengerCargoForm');
        if(passengerForm){
            passengerForm.addEventListener('submit', function(e){
                if (!validateForm()) { e.preventDefault(); return false; }
                const overlay = document.getElementById('passengerOverlay');
                if(overlay){ overlay.style.display = 'flex'; }
            });
        }
    </script>

@endsection

<style>
    /* Error state styling */
    .form-control.is-invalid,
    .form-select.is-invalid {
        border-color: #dc3545;
        background-image: none;
        padding-right: calc(1.5em + 0.75rem);
    }

    .form-control.is-invalid:focus,
    .form-select.is-invalid:focus {
        border-color: #dc3545;
        box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
    }

    .input-group .error-icon {
        border-color: #dc3545;
    }

    .error-message {
        font-size: 0.875rem;
        margin-top: 0.25rem;
    }

    /* Passenger cargo card custom roundness */
    .passenger-cargo-card {
        border-radius: 14px;
        overflow: hidden;
        max-width: 1520px;
        border: 1px solid #d7dee9;
    }

    .passenger-cargo-card .card-header {
        border-top-left-radius: 14px;
        border-top-right-radius: 14px;
        margin-bottom: 0 !important;
    }

    .passenger-cargo-card .card-body {
        border-bottom-left-radius: 14px;
        border-bottom-right-radius: 14px;
    }

    .passenger-cargo-card .bg-white {
        border-radius: var(--bs-border-radius);
    }

    /* Slightly larger inner padding for the passenger card to breathe with wider layout */
    .passenger-cargo-card .card-body {
        padding: 1.75rem;
    }

    .photo-input-wrapper .btn {
        border-radius: var(--bs-border-radius-sm);
    }

    .photo-input-wrapper {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    /* Rounded inputs/selects inside passenger card to match system forms */
    .passenger-cargo-card .form-control,
    .passenger-cargo-card .form-select,
    .passenger-cargo-card .input-group-text,
    .passenger-cargo-card .btn {
        border-radius: var(--bs-border-radius);
    }

    .passenger-cargo-card .input-group .form-control {
        border-top-left-radius: var(--bs-border-radius);
        border-bottom-left-radius: var(--bs-border-radius);
    }

    .passenger-cargo-card .input-group .input-group-text {
        border-top-right-radius: var(--bs-border-radius);
        border-bottom-right-radius: var(--bs-border-radius);
    }

    /* When error icon is hidden, restore right-side radius that Bootstrap removes for non-last-child inputs */
    .passenger-cargo-card .input-group:has(.error-icon[style*="display:none"]) > .form-control,
    .passenger-cargo-card .input-group:has(.error-icon[style*="display:none"]) > .form-select {
        border-top-right-radius: var(--bs-border-radius) !important;
        border-bottom-right-radius: var(--bs-border-radius) !important;
    }

    .cargo-dimensions-block .d-flex.align-items-end {
        display: grid !important;
        grid-template-columns: minmax(130px, 1fr) minmax(130px, 1fr) minmax(130px, 1fr) 96px 140px;
        gap: 0.5rem;
        align-items: end;
    }

    .cargo-dimensions-block .d-flex.align-items-end > .flex-fill {
        min-width: 0;
    }

    .cargo-dimensions-block .unitSelect {
        width: 100%;
        min-width: 0;
        padding-right: 2rem;
        text-align: center;
        font-weight: 600;
    }

    @media (max-width: 992px) {
        .cargo-dimensions-block .d-flex.align-items-end {
            grid-template-columns: 1fr 1fr;
        }
    }
</style>

