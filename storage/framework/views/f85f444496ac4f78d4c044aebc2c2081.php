<?php $__env->startSection('page-title', 'CARGO BOOKING'); ?>

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('components.hero', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="container my-5">
        <div class="card shadow-sm mx-auto passenger-cargo-card" style="max-width:1300px; background-color:#f0f0f0;">
            <div class="card-header bg-dark text-white text-center mb-1">
                <h5 class="mb-0">CARGO BOOKING FORM</h5>
            </div>

            <div class="card-body">
                <?php if(session('error')): ?>
                    <div class="alert alert-danger"><?php echo e(session('error')); ?></div>
                <?php endif; ?>

                <form id="passengerCargoForm" action="<?php echo e(route('cargobooking.confirm')); ?>" method="POST" enctype="multipart/form-data" novalidate>
                    <?php echo csrf_field(); ?>

                    <div class="row">

                        <!-- LEFT SIDE -->
                        <div class="col-md-6 mb-3">
                            <h6 class="fw-bold mt-4">Voyage Information</h6>
                            <div class="bg-white p-3 rounded shadow-sm mb-3 small">
                                <p class="mb-1"><strong>Vessel Name:</strong> <?php echo e($vesselName); ?></p>
                                <p class="mb-1"><strong>Route:</strong> <?php echo e($routeFrom); ?> → <?php echo e($routeTo); ?></p>
                                <p class="mb-1"><strong>Departure Date:</strong> <?php echo e($departureDate); ?></p>
                                <p class="mb-1"><strong>Departure Time:</strong> <?php echo e($departureTime); ?></p>
                                <p class="mb-0"><strong>Port of Origin:</strong> <?php echo e($portOfOrigin); ?></p>
                            </div>

                            <input type="hidden" name="voyage_id" value="<?php echo e(request()->voyage_id); ?>">
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
                                                    <?php $__currentLoopData = $cargoClassifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $classification): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <option value="<?php echo e($classification->cargo_classification_name); ?>">
                                                            <?php echo e($classification->cargo_classification_name); ?>

                                                        </option>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
                                                    <?php $__currentLoopData = $cargoItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cargo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                            <?php if($cargo->route_code_id == ($voyage->routePort->route_code_id ?? null)): ?>
                                                            <option value="<?php echo e($cargo->cargo_item_id); ?>"
                                                                data-route-code="<?php echo e($cargo->route_code_id ?? ''); ?>"
                                                                data-classification="<?php echo e($cargo->cargo_item_classification ?? ''); ?>"
                                                                data-measure-required="<?php echo e($cargo->cargo_item_measure_required ?? 'No'); ?>"
                                                                data-min-length="<?php echo e($cargo->cargo_item_min_length ?? ''); ?>"
                                                                data-max-length="<?php echo e($cargo->cargo_item_max_length ?? ''); ?>"
                                                                data-min-width="<?php echo e($cargo->cargo_item_min_width ?? ''); ?>"
                                                                data-max-width="<?php echo e($cargo->cargo_item_max_width ?? ''); ?>"
                                                                data-min-height="<?php echo e($cargo->cargo_item_min_height ?? ''); ?>"
                                                                data-max-height="<?php echo e($cargo->cargo_item_max_height ?? ''); ?>">
                                                                <?php echo e($cargo->cargo_item_description); ?>

                                                            </option>
                                                        <?php endif; ?>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
                                                <input type="number" name="cargo_quantity[]" class="form-control"
                                                    placeholder="Quantity" min="1" required>
                                                <span class="input-group-text error-icon" style="display:none; background-color: #f8d7da;">
                                                    <i class="bi bi-exclamation-circle-fill text-danger"></i>
                                                </span>
                                            </div>
                                            <small class="error-message text-danger d-block mt-1" style="display:none;"></small>
                                        </div>
                                        <div class="flex-fill">
                                            <label class="form-label">Weight (kg) <span class="text-danger">*</span></label>
                                            <div class="input-group has-validation">
                                                <input type="number" name="cargo_weight[]" class="form-control"
                                                    placeholder="Weight" step="0.01" required>
                                                <span class="input-group-text error-icon" style="display:none; background-color: #f8d7da;">
                                                    <i class="bi bi-exclamation-circle-fill text-danger"></i>
                                                </span>
                                            </div>
                                            <small class="error-message text-danger d-block mt-1" style="display:none;"></small>
                                        </div>
                                    </div>

                                    <!-- Cargo Dimensions (Always Visible) -->
                                    <label class="form-label">Cargo Dimensions <span class="text-danger">*</span></label>
                                    <div class="d-flex gap-2 align-items-end mb-3">
                                        <div class="flex-fill">
                                            <label class="form-label small">Length <span
                                                    class="text-danger">*</span></label>
                                            <div class="input-group has-validation">
                                                <input type="number" name="cargo_length[]" class="form-control dimension"
                                                    placeholder="Length" step="0.01" required>
                                                <span class="input-group-text error-icon" style="display:none; background-color: #f8d7da;">
                                                    <i class="bi bi-exclamation-circle-fill text-danger"></i>
                                                </span>
                                            </div>
                                            <small class="text-muted dimension-range"></small>
                                            <small class="error-message text-danger d-block mt-1" style="display:none;"></small>
                                        </div>
                                        <div class="flex-fill">
                                            <label class="form-label small">Width <span
                                                    class="text-danger">*</span></label>
                                            <div class="input-group has-validation">
                                                <input type="number" name="cargo_width[]" class="form-control dimension"
                                                    placeholder="Width" step="0.01" required>
                                                <span class="input-group-text error-icon" style="display:none; background-color: #f8d7da;">
                                                    <i class="bi bi-exclamation-circle-fill text-danger"></i>
                                                </span>
                                            </div>
                                            <small class="text-muted dimension-range"></small>
                                            <small class="error-message text-danger d-block mt-1" style="display:none;"></small>
                                        </div>
                                        <div class="flex-fill">
                                            <label class="form-label small">Height <span
                                                    class="text-danger">*</span></label>
                                            <div class="input-group has-validation">
                                                <input type="number" name="cargo_height[]" class="form-control dimension"
                                                    placeholder="Height" step="0.01" required>
                                                <span class="input-group-text error-icon" style="display:none; background-color: #f8d7da;">
                                                    <i class="bi bi-exclamation-circle-fill text-danger"></i>
                                                </span>
                                            </div>
                                            <small class="text-muted dimension-range"></small>
                                            <small class="error-message text-danger d-block mt-1" style="display:none;"></small>
                                        </div>
                                        <div class="flex-fill">
                                            <label class="form-label small">Unit</label>
                                            <select name="measurement_unit[]" class="form-select unitSelect">
                                                <option value="cm">cm</option>
                                                <option value="in">in</option>
                                            </select>
                                        </div>
                                        <div class="flex-fill">
                                            <label class="form-label small">CBM</label>
                                            <input type="text" class="form-control cbm-output" readonly placeholder="0.0000">
                                        </div>
                                    </div>

                                    <!-- Photo Upload -->
                                    <div class="mb-2">
                                        <label class="form-label">Photo <span class="text-danger">*</span></label>
                                        <div class="photo-input-wrapper">
                                            <label class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-cloud-arrow-up"></i> Choose Photo
                                                <input type="file" name="cargo_picture[]" class="form-control cargo-photo required-field" style="display:none;" accept="image/*">
                                            </label>
                                            <span class="input-group-text error-icon" style="display:none; background-color: #f8d7da; margin-top: 0.5rem; border-radius: 0.25rem; padding: 0.375rem 0.75rem;">
                                                <i class="bi bi-exclamation-circle-fill text-danger"></i>
                                            </span>
                                        </div>
                                        <small class="text-success photo-confirmation" style="display:none;">Photo selected!</small>
                                        <small class="error-message text-danger d-block mt-1" style="display:none;"></small>
                                    </div>

                                </div> <!-- end cargo-item -->

                            </div>

                    <div class="d-flex justify-content-end mt-4 gap-3">
                        <a href="<?php echo e(route('bookingtype')); ?>" class="btn btn-outline-danger fw-bold py-3"
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

        function clearAllErrors() {
            document.querySelectorAll('.required-field').forEach(field => {
                if (field.type === 'file') {
                    const wrapper = field.closest('.photo-input-wrapper');
                    if (wrapper) {
                        const errorIcon = wrapper.querySelector('.error-icon');
                        const errorMessage = wrapper.nextElementSibling;
                        if (errorIcon) errorIcon.style.display = 'none';
                        if (errorMessage && errorMessage.classList.contains('error-message')) {
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
                const wrapper = field.closest('.photo-input-wrapper');
                if (wrapper) {
                    const errorIcon = wrapper.querySelector('.error-icon');
                    const errorMessage = wrapper.nextElementSibling;

                    if (errorIcon) errorIcon.style.display = 'flex';
                    if (errorMessage && errorMessage.classList.contains('error-message')) {
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
            let l = parseFloat(item.querySelector('[name="cargo_length[]"]').value) || 0;
            let w = parseFloat(item.querySelector('[name="cargo_width[]"]').value) || 0;
            let h = parseFloat(item.querySelector('[name="cargo_height[]"]').value) || 0;

            if (unit === 'in') { l = l * 2.54; w = w * 2.54; h = h * 2.54; }

            const cbm = (l * w * h) / 1000000;
            item.querySelector('.cbm-output').value = cbm.toFixed(4);
            return cbm;
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
                    newItem.querySelectorAll('input').forEach(el => { if(el.type==='file') el.value = null; else el.value = ''; });
                    newItem.querySelectorAll('select').forEach(el => el.selectedIndex = 0);
                    newItem.querySelector('.cbm-output').value = '0.0000';
                    newItem.querySelector('.measurements-section').style.display = 'none';
                    container.appendChild(newItem);
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

                if (!classification.value.trim()) { showFieldError(classification, 'Classification is required'); isValid = false; }
                if (!description.value.trim()) { showFieldError(description, 'Description is required'); isValid = false; }
                if (photo && photo.files.length === 0) { showFieldError(photo, 'Photo is required'); isValid = false; }
            });

            if (!isValid) { window.scrollTo(0, 0); }
            return isValid;
        }

        // Display min/max ranges when cargo description changes and apply measurement-required behavior
        container.addEventListener('change', e => {
            if(!e.target.classList.contains('cargo-description')) return;
            const item = e.target.closest('.cargo-item');
            const cargoItemId = e.target.value;
            
            // Get cargo item data via attributes stored in the option
            const selectedOption = e.target.options[e.target.selectedIndex];
            const minLength = selectedOption.dataset.minLength || '';
            const maxLength = selectedOption.dataset.maxLength || '';
            const minWidth = selectedOption.dataset.minWidth || '';
            const maxWidth = selectedOption.dataset.maxWidth || '';
            const minHeight = selectedOption.dataset.minHeight || '';
            const maxHeight = selectedOption.dataset.maxHeight || '';
            const measureRequired = (selectedOption.dataset.measureRequired || 'No').toString();
            
            // Update dimension range displays
            const dimensionRanges = item.querySelectorAll('.dimension-range');
            if(dimensionRanges[0]) dimensionRanges[0].textContent = minLength && maxLength ? `(${minLength} - ${maxLength})` : '';
            if(dimensionRanges[1]) dimensionRanges[1].textContent = minWidth && maxWidth ? `(${minWidth} - ${maxWidth})` : '';
            if(dimensionRanges[2]) dimensionRanges[2].textContent = minHeight && maxHeight ? `(${minHeight} - ${maxHeight})` : '';
            
            // Store min/max values on input fields for validation
            const lengthInput = item.querySelector('[name="cargo_length[]"]');
            const widthInput = item.querySelector('[name="cargo_width[]"]');
            const heightInput = item.querySelector('[name="cargo_height[]"]');
            if(lengthInput){ lengthInput.dataset.min = minLength || ''; lengthInput.dataset.max = maxLength || ''; }
            if(widthInput){ widthInput.dataset.min = minWidth || ''; widthInput.dataset.max = maxWidth || ''; }
            if(heightInput){ heightInput.dataset.min = minHeight || ''; heightInput.dataset.max = maxHeight || ''; }

            if(measureRequired === 'Yes'){
                // Auto-fill and show L/W/H but make them read-only so the client cannot edit
                if(lengthInput){ lengthInput.value = minLength || ''; lengthInput.readOnly = true; }
                if(widthInput){ widthInput.value = minWidth || ''; widthInput.readOnly = true; }
                if(heightInput){ heightInput.value = minHeight || ''; heightInput.readOnly = true; }

                // hide the textual min/max ranges to avoid confusing the user
                dimensionRanges.forEach(dr => { if(dr) dr.style.display = 'none'; });

                // Recalculate CBM with provided values
                calculateCBM(item);
            } else {
                // Allow manual input
                if(lengthInput){ lengthInput.readOnly = false; }
                if(widthInput){ widthInput.readOnly = false; }
                if(heightInput){ heightInput.readOnly = false; }
                dimensionRanges.forEach((dr) => { if(dr) dr.style.display = ''; });
            }
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
            window.addEventListener('DOMContentLoaded', syncCargoItems);
        }

        container.addEventListener('change', function(e) {
            if (!e.target.classList.contains('cargo-classification')) return;
            const item = e.target.closest('.cargo-item');
            const classification = e.target.value;
            const descriptionSelect = item.querySelector('.cargo-description');
            Array.from(descriptionSelect.options).forEach(opt => {
                if (opt.value === '') return;
                opt.style.display = (opt.dataset.classification === classification) ? 'block' : 'none';
            });
            descriptionSelect.value = '';
        });

        container.addEventListener('change', function(e) {
            if (!e.target.classList.contains('cargo-photo')) return;
            const confirmation = e.target.closest('.cargo-item').querySelector('.photo-confirmation');
            if (e.target.files.length > 0) {
                confirmation.style.display = 'inline';
                confirmation.textContent = `Photo selected: ${e.target.files[0].name}`;
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

<?php $__env->stopSection(); ?>

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
        border-radius: 0;
        overflow: hidden;
        max-width: 1300px;
    }

    .passenger-cargo-card .bg-white {
        border-radius: 0;
    }

    /* Slightly larger inner padding for the passenger card to breathe with wider layout */
    .passenger-cargo-card .card-body {
        padding: 1.5rem;
    }

    .photo-input-wrapper .btn {
        border-radius: 0;
    }

    /* Rounded inputs/selects inside passenger card to match system forms */
    .passenger-cargo-card .form-control,
    .passenger-cargo-card .form-select,
    .passenger-cargo-card .input-group-text,
    .passenger-cargo-card .btn {
        border-radius: 0;
    }

    .passenger-cargo-card .input-group .form-control {
        border-top-left-radius: 0;
        border-bottom-left-radius: 0;
    }

    .passenger-cargo-card .input-group .input-group-text {
        border-top-right-radius: 0;
        border-bottom-right-radius: 0;
    }
</style>


<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('components.hero', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="container my-5">
        <div class="card shadow-sm mx-auto passenger-cargo-card" style="max-width:1300px; background-color:#f0f0f0;">
            <div class="card-header bg-dark text-white text-center mb-1">
                <h5 class="mb-0">CARGO BOOKING FORM</h5>
            </div>

            <div class="card-body">
                <?php if(session('error')): ?>
                    <div class="alert alert-danger"><?php echo e(session('error')); ?></div>
                <?php endif; ?>

                <form id="passengerCargoForm" action="<?php echo e(route('cargobooking.confirm')); ?>" method="POST" enctype="multipart/form-data" novalidate>
                    <?php echo csrf_field(); ?>

                    <!-- top controls removed — No. of Cargo moved into Cargo Information column; unit selector is per-item -->

                    <div class="row">

                        <!-- LEFT SIDE -->
                        <div class="col-md-6 mb-3">
                            <h6 class="fw-bold mt-4">Voyage Information</h6>
                            <div class="bg-white p-3 rounded shadow-sm mb-3 small">
                                <p class="mb-1"><strong>Vessel Name:</strong> <?php echo e($vesselName); ?></p>
                                <p class="mb-1"><strong>Route:</strong> <?php echo e($routeFrom); ?> → <?php echo e($routeTo); ?></p>
                                <p class="mb-1"><strong>Departure Date:</strong> <?php echo e($departureDate); ?></p>
                                <p class="mb-1"><strong>Departure Time:</strong> <?php echo e($departureTime); ?></p>
                                <p class="mb-0"><strong>Port of Origin:</strong> <?php echo e($portOfOrigin); ?></p>
                            </div>

                            <input type="hidden" name="voyage_id" value="<?php echo e(request()->voyage_id); ?>">
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
                                                    <?php $__currentLoopData = $cargoClassifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $classification): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <option value="<?php echo e($classification->cargo_classification_name); ?>">
                                                            <?php echo e($classification->cargo_classification_name); ?>

                                                        </option>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
                                                    <?php $__currentLoopData = $cargoItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cargo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <?php if($cargo->route_code_id == ($voyage->routePort->route_code_id ?? null)): ?>
                                                            <option value="<?php echo e($cargo->cargo_item_id); ?>"
                                                                data-route-code="<?php echo e($cargo->route_code_id ?? ''); ?>">
                                                                <?php echo e($cargo->cargo_item_description); ?>

                                                            </option>
                                                        <?php endif; ?>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </select>
                                                <span class="input-group-text error-icon" style="display:none; background-color: #f8d7da;">
                                                    <i class="bi bi-exclamation-circle-fill text-danger"></i>
                                                </span>
                                            </div>
                                            <small class="error-message text-danger d-block mt-1" style="display:none;"></small>
                                        </div>
                                    </div>

                                    <!-- Quantity & Weight -->
                                    <div class="mb-2 d-flex gap-2">
                                        <div class="flex-fill">
                                            <label class="form-label">Quantity <span class="text-danger">*</span></label>
                                            <div class="input-group has-validation">
                                                <input type="number" name="cargo_quantity[]" class="form-control required-field"
                                                    placeholder="Quantity" min="1">
                                                <span class="input-group-text error-icon" style="display:none; background-color: #f8d7da;">
                                                    <i class="bi bi-exclamation-circle-fill text-danger"></i>
                                                </span>
                                            </div>
                                            <small class="error-message text-danger d-block mt-1" style="display:none;"></small>
                                        </div>
                                        <div class="flex-fill">
                                            <label class="form-label">Weight (kg) <span
                                                    class="text-danger">*</span></label>
                                            <div class="input-group has-validation">
                                                <input type="number" name="cargo_weight[]" class="form-control required-field"
                                                    placeholder="Weight" step="0.01">
                                                <span class="input-group-text error-icon" style="display:none; background-color: #f8d7da;">
                                                    <i class="bi bi-exclamation-circle-fill text-danger"></i>
                                                </span>
                                            </div>
                                            <small class="error-message text-danger d-block mt-1" style="display:none;"></small>
                                        </div>
                                    </div>

                                    <!-- Dimensions + CBM -->
                                    <div class="mb-2">
                                        <label class="form-label">Cargo Dimensions</label>
                                        <div class="d-flex gap-2 align-items-end">
                                            <div class="flex-fill">
                                                <label class="form-label small">Length <span
                                                        class="text-danger">*</span></label>
                                                <div class="input-group has-validation">
                                                    <input type="number" name="cargo_length[]"
                                                        class="form-control dimension required-field" step="0.01">
                                                    <span class="input-group-text error-icon" style="display:none; background-color: #f8d7da;">
                                                        <i class="bi bi-exclamation-circle-fill text-danger"></i>
                                                    </span>
                                                </div>
                                                <small class="error-message text-danger d-block mt-1" style="display:none;"></small>
                                            </div>
                                            <div class="flex-fill">
                                                <label class="form-label small">Width <span
                                                        class="text-danger">*</span></label>
                                                <div class="input-group has-validation">
                                                    <input type="number" name="cargo_width[]" class="form-control dimension required-field"
                                                        step="0.01">
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
                                                    <input type="number" name="cargo_height[]"
                                                        class="form-control dimension required-field" step="0.01">
                                                    <span class="input-group-text error-icon" style="display:none; background-color: #f8d7da;">
                                                        <i class="bi bi-exclamation-circle-fill text-danger"></i>
                                                    </span>
                                                </div>
                                                <small class="error-message text-danger d-block mt-1" style="display:none;"></small>
                                            </div>
                                                                                        <div style="width:140px;">
                                                                                            <label class="form-label small">Unit</label>
                                                                                            <select name="measurement_unit[]" class="form-select unitSelect">
                                                                                                <option value="cm">cm</option>
                                                                                                <option value="in">in</option>
                                                                                            </select>
                                                                                        </div>
                                            <div class="flex-fill">
                                                <label class="form-label small">CBM</label>
                                                <input type="text" class="form-control cbm-output" readonly
                                                    placeholder="0.0000">
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Photo Upload with confirmation -->
                                    <div class="mb-2">
                                        <label class="form-label">Photo <span class="text-danger">*</span></label>
                                        <div class="photo-input-wrapper">
                                            <label
                                                class="btn btn-outline-secondary w-100 d-flex align-items-center justify-content-center mt-2">
                                                <i class="bi bi-image me-2"></i> Add Photo
                                                <input type="file" name="cargo_picture[]" class="d-none cargo-photo required-field"
                                                    accept="image/*">
                                            </label>
                                            <span class="input-group-text error-icon" style="display:none; background-color: #f8d7da; margin-top: 0.5rem; border-radius: 0.25rem; padding: 0.375rem 0.75rem;">
                                                <i class="bi bi-exclamation-circle-fill text-danger"></i>
                                            </span>
                                        </div>
                                        <small class="text-success photo-confirmation" style="display:none;">Photo
                                            selected!</small>
                                        <small class="error-message text-danger d-block mt-1" style="display:none;"></small>
                                    </div>

                                </div> <!-- end cargo-item -->

                            </div>

                    <div class="d-flex justify-content-end mt-4 gap-3">
                        <a href="<?php echo e(route('bookingtype')); ?>" class="btn btn-outline-danger fw-bold py-3"
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

        function clearAllErrors() {
            document.querySelectorAll('.required-field').forEach(field => {
                if (field.type === 'file') {
                    const wrapper = field.closest('.photo-input-wrapper');
                    if (wrapper) {
                        const errorIcon = wrapper.querySelector('.error-icon');
                        const errorMessage = wrapper.nextElementSibling;
                        if (errorIcon) errorIcon.style.display = 'none';
                        if (errorMessage && errorMessage.classList.contains('error-message')) {
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
                const wrapper = field.closest('.photo-input-wrapper');
                if (wrapper) {
                    const errorIcon = wrapper.querySelector('.error-icon');
                    const errorMessage = wrapper.nextElementSibling;

                    if (errorIcon) errorIcon.style.display = 'flex';
                    if (errorMessage && errorMessage.classList.contains('error-message')) {
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
            let l = parseFloat(item.querySelector('[name="cargo_length[]"]').value) || 0;
            let w = parseFloat(item.querySelector('[name="cargo_width[]"]').value) || 0;
            let h = parseFloat(item.querySelector('[name="cargo_height[]"]').value) || 0;

            if (unit === 'in') { l = l * 2.54; w = w * 2.54; h = h * 2.54; }

            const cbm = (l * w * h) / 1000000;
            item.querySelector('.cbm-output').value = cbm.toFixed(4);
            return cbm;
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
                    newItem.querySelectorAll('input').forEach(el => { if(el.type==='file') el.value = null; else el.value = ''; });
                    newItem.querySelectorAll('select').forEach(el => el.selectedIndex = 0);
                    newItem.querySelector('.cbm-output').value = '0.0000';
                    container.appendChild(newItem);
                }
            } else if (desired < current) {
                for (let i = current; i > desired; i--) {
                    const last = container.querySelector('.cargo-item:last-child');
                    if (last) last.remove();
                }
            }
            toggleAddButton();
        }

        function toggleAddButton(){
            const addBtn = document.getElementById('addCargoItem');
            const items = container.querySelectorAll('.cargo-item');
            if(addBtn) addBtn.disabled = items.length >= MAX_ITEMS;
            const removeBtn = document.getElementById('removeCargoItem');
            if(removeBtn) removeBtn.disabled = items.length <= 1;
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
                const quantity = item.querySelector('[name="cargo_quantity[]"]');
                const weight = item.querySelector('[name="cargo_weight[]"]');
                const length = item.querySelector('[name="cargo_length[]"]');
                const width = item.querySelector('[name="cargo_width[]"]');
                const height = item.querySelector('[name="cargo_height[]"]');
                const photo = item.querySelector('[name="cargo_picture[]"]');

                if (!classification.value.trim()) { showFieldError(classification, 'Classification is required'); isValid = false; }
                if (!description.value.trim()) { showFieldError(description, 'Description is required'); isValid = false; }
                if (!quantity.value.trim()) { showFieldError(quantity, 'Quantity is required'); isValid = false; }
                if (!weight.value.trim()) { showFieldError(weight, 'Weight is required'); isValid = false; }
                if (!length || !length.value || !length.value.toString().trim()) { showFieldError(length || description, 'Length is required'); isValid = false; }
                if (!width || !width.value || !width.value.toString().trim()) { showFieldError(width || description, 'Width is required'); isValid = false; }
                if (!height || !height.value || !height.value.toString().trim()) { showFieldError(height || description, 'Height is required'); isValid = false; }
                if (photo && photo.files.length === 0) { showFieldError(photo, 'Photo is required'); isValid = false; }

                const cbm = calculateCBM(item);
                if (cbm < 0.06) { showFieldError(length, 'Minimum CBM per item is 0.06'); isValid = false; }
            });

            if (!isValid) { window.scrollTo(0, 0); }
            return isValid;
        }

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
            window.addEventListener('DOMContentLoaded', syncCargoItems);
        }

        const addBtn = document.getElementById('addCargoItem');
        const removeBtn = document.getElementById('removeCargoItem');
        if (addBtn) {
            addBtn.addEventListener('click', function() {
                const items = container.querySelectorAll('.cargo-item');
                if (items.length >= MAX_ITEMS) return;
                const first = container.querySelector('.cargo-item');
                const clone = first.cloneNode(true);
                clone.querySelectorAll('input').forEach(i => { if(i.type === 'file') i.value = null; else i.value = ''; });
                clone.querySelectorAll('select').forEach(s => s.selectedIndex = 0);
                clone.querySelector('.cbm-output').value = '0.0000';
                container.appendChild(clone);
                if (noInput) noInput.value = container.querySelectorAll('.cargo-item').length;
                toggleAddButton();
            });
        }
        if (removeBtn) {
            removeBtn.addEventListener('click', function() {
                const items = container.querySelectorAll('.cargo-item');
                if (items.length > 1) {
                    items[items.length - 1].remove();
                    if (noInput) noInput.value = container.querySelectorAll('.cargo-item').length;
                }
                toggleAddButton();
            });
        }

        container.addEventListener('change', function(e) {
            if (!e.target.classList.contains('cargo-classification')) return;
            const item = e.target.closest('.cargo-item');
            const classification = e.target.value;
            const descriptionSelect = item.querySelector('.cargo-description');
            Array.from(descriptionSelect.options).forEach(opt => {
                if (opt.value === '') return;
                opt.style.display = (opt.dataset.classification === classification) ? 'block' : 'none';
            });
            descriptionSelect.value = '';
        });

        container.addEventListener('change', function(e) {
            if (!e.target.classList.contains('cargo-photo')) return;
            const confirmation = e.target.closest('.cargo-item').querySelector('.photo-confirmation');
            if (e.target.files.length > 0) {
                confirmation.style.display = 'inline';
                confirmation.textContent = `Photo selected: ${e.target.files[0].name}`;
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

        toggleAddButton();
    </script>

<?php $__env->stopSection(); ?>

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
        border-radius: 0;
        overflow: hidden;
        max-width: 1300px;
    }

    .passenger-cargo-card .bg-white {
        border-radius: 0;
    }

    /* Slightly larger inner padding for the passenger card to breathe with wider layout */
    .passenger-cargo-card .card-body {
        padding: 1.5rem;
    }

    .photo-input-wrapper .btn {
        border-radius: 0;
    }

    /* Rounded inputs/selects inside passenger card to match system forms */
    .passenger-cargo-card .form-control,
    .passenger-cargo-card .form-select,
    .passenger-cargo-card .input-group-text,
    .passenger-cargo-card .btn {
        border-radius: 0;
    }

    .passenger-cargo-card .input-group .form-control {
        border-top-left-radius: 0;
        border-bottom-left-radius: 0;
    }

    .passenger-cargo-card .input-group .input-group-text {
        border-top-right-radius: 0;
        border-bottom-right-radius: 0;
    }
</style>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\kirzt\Documents\GitHub\Capstone\resources\views/passenger/cargobooking.blade.php ENDPATH**/ ?>