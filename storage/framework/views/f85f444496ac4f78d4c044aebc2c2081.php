<?php $__env->startSection('page-title', 'CARGO BOOKING'); ?>

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('components.hero', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="container my-5">
        <div class="card shadow-sm mx-auto" style="max-width:1100px; background-color:#f0f0f0;">
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
                                                    <?php
                                                        $classifications = $cargoItems
                                                            ->where(
                                                                'route_port_id',
                                                                $voyage->routePort->route_port_id ?? null,
                                                            )
                                                            ->pluck('cargo_item_classification')
                                                            ->unique();
                                                    ?>
                                                    <?php $__currentLoopData = $classifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $class): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <option value="<?php echo e($class); ?>"><?php echo e($class); ?></option>
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
                                                        <?php if($cargo->route_port_id == ($voyage->routePort->route_port_id ?? null)): ?>
                                                            <option value="<?php echo e($cargo->cargo_item_id); ?>"
                                                                data-classification="<?php echo e($cargo->cargo_item_classification); ?>">
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
                                        <label class="form-label">Cargo Dimensions (cm) </label>
                                        <div class="d-flex gap-2">
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

                            <div class="d-flex gap-2 mt-2">
                                <button type="button" id="addCargoItem" class="btn btn-secondary">Add Another
                                    Cargo</button>
                                <button type="button" id="removeCargoItem" class="btn btn-danger">Remove Last
                                    Cargo</button>
                            </div>
                        </div>
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

        // Clear all error states
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
                    const errorIcon = inputGroup.querySelector('.error-icon');
                    const errorMessage = inputGroup.nextElementSibling;

                    if (errorIcon) errorIcon.style.display = 'none';
                    if (errorMessage && errorMessage.classList.contains('error-message')) {
                        errorMessage.style.display = 'none';
                        errorMessage.textContent = '';
                    }
                    field.classList.remove('is-invalid');
                }
            });
        }

        // Show error on specific field
        function showFieldError(field, message) {
            // Handle file inputs differently
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
                const errorIcon = inputGroup.querySelector('.error-icon');
                const errorMessage = inputGroup.nextElementSibling;

                if (errorIcon) errorIcon.style.display = 'flex';
                if (errorMessage && errorMessage.classList.contains('error-message')) {
                    errorMessage.textContent = message;
                    errorMessage.style.display = 'block';
                }
            }
        }

        // Form validation
        function validateForm() {
            clearAllErrors();
            let isValid = true;

            const form = document.getElementById('passengerCargoForm');

            // Sender fields
            const senderFirstname = form.querySelector('input[name="sender_firstname"]');
            const senderLastname = form.querySelector('input[name="sender_lastname"]');
            const senderContact = form.querySelector('input[name="sender_contact"]');
            const senderEmail = form.querySelector('input[name="sender_email"]');

            if (!senderFirstname.value.trim()) {
                showFieldError(senderFirstname, 'First Name is required');
                isValid = false;
            }
            if (!senderLastname.value.trim()) {
                showFieldError(senderLastname, 'Last Name is required');
                isValid = false;
            }
            if (!senderContact.value.trim()) {
                showFieldError(senderContact, 'Contact Number is required');
                isValid = false;
            }
            if (!senderEmail.value.trim()) {
                showFieldError(senderEmail, 'Email Address is required');
                isValid = false;
            }

            // Consignee fields
            const consigneeFirstname = form.querySelector('input[name="consignee_firstname"]');
            const consigneeLastname = form.querySelector('input[name="consignee_lastname"]');
            const consigneeContact = form.querySelector('input[name="consignee_contact"]');

            if (!consigneeFirstname.value.trim()) {
                showFieldError(consigneeFirstname, 'First Name is required');
                isValid = false;
            }
            if (!consigneeLastname.value.trim()) {
                showFieldError(consigneeLastname, 'Last Name is required');
                isValid = false;
            }
            if (!consigneeContact.value.trim()) {
                showFieldError(consigneeContact, 'Contact Number is required');
                isValid = false;
            }

            // Cargo items
            const cargoItems = document.querySelectorAll('.cargo-item');
            cargoItems.forEach((item, index) => {
                const classification = item.querySelector('[name="cargo_classification[]"]');
                const description = item.querySelector('[name="cargo_item_id[]"]');
                const quantity = item.querySelector('[name="cargo_quantity[]"]');
                const weight = item.querySelector('[name="cargo_weight[]"]');
                const length = item.querySelector('[name="cargo_length[]"]');
                const width = item.querySelector('[name="cargo_width[]"]');
                const height = item.querySelector('[name="cargo_height[]"]');
                const photo = item.querySelector('[name="cargo_picture[]"]');

                if (!classification.value.trim()) {
                    showFieldError(classification, 'Classification is required');
                    isValid = false;
                }
                if (!description.value.trim()) {
                    showFieldError(description, 'Description is required');
                    isValid = false;
                }
                if (!quantity.value.trim()) {
                    showFieldError(quantity, 'Quantity is required');
                    isValid = false;
                }
                if (!weight.value.trim()) {
                    showFieldError(weight, 'Weight is required');
                    isValid = false;
                }
                if (!length.value.trim()) {
                    showFieldError(length, 'Length is required');
                    isValid = false;
                }
                if (!width.value.trim()) {
                    showFieldError(width, 'Width is required');
                    isValid = false;
                }
                if (!height.value.trim()) {
                    showFieldError(height, 'Height is required');
                    isValid = false;
                }
                if (photo.files.length === 0) {
                    showFieldError(photo, 'Photo is required');
                    isValid = false;
                }
            });

            if (!isValid) {
                window.scrollTo(0, 0);
            }

            return isValid;
        }

        // Add blur validation to all required fields
        document.querySelectorAll('.required-field').forEach(field => {
            field.addEventListener('blur', function() {
                // Special handling for file inputs
                if (this.type === 'file') {
                    const wrapper = this.closest('.photo-input-wrapper');
                    if (wrapper) {
                        const errorIcon = wrapper.querySelector('.error-icon');
                        const errorMessage = wrapper.nextElementSibling;

                        if (this.files.length === 0) {
                            if (errorIcon) errorIcon.style.display = 'flex';
                            if (errorMessage && errorMessage.classList.contains('error-message')) {
                                errorMessage.textContent = 'Photo is required';
                                errorMessage.style.display = 'block';
                            }
                        } else {
                            if (errorIcon) errorIcon.style.display = 'none';
                            if (errorMessage && errorMessage.classList.contains('error-message')) {
                                errorMessage.style.display = 'none';
                            }
                        }
                    }
                } else if (this.value.trim() === '') {
                    const inputGroup = this.parentElement;
                    const errorIcon = inputGroup.querySelector('.error-icon');
                    const errorMessage = inputGroup.nextElementSibling;

                    this.classList.add('is-invalid');
                    if (errorIcon) errorIcon.style.display = 'flex';

                    // Create appropriate error message
                    let message = '';
                    const fieldName = this.name;
                    if (fieldName.includes('firstname')) message = 'First Name is required';
                    else if (fieldName.includes('lastname')) message = 'Last Name is required';
                    else if (fieldName.includes('contact')) message = 'Contact Number is required';
                    else if (fieldName.includes('email')) message = 'Email Address is required';
                    else if (fieldName.includes('classification')) message = 'Classification is required';
                    else if (fieldName.includes('item_id')) message = 'Description is required';
                    else if (fieldName.includes('quantity')) message = 'Quantity is required';
                    else if (fieldName.includes('weight')) message = 'Weight is required';
                    else if (fieldName.includes('length')) message = 'Length is required';
                    else if (fieldName.includes('width')) message = 'Width is required';
                    else if (fieldName.includes('height')) message = 'Height is required';
                    else message = 'This field is required';

                    if (errorMessage && errorMessage.classList.contains('error-message')) {
                        errorMessage.textContent = message;
                        errorMessage.style.display = 'block';
                    }
                } else {
                    // Clear error if field has value
                    const inputGroup = this.parentElement;
                    const errorIcon = inputGroup.querySelector('.error-icon');
                    const errorMessage = inputGroup.nextElementSibling;

                    this.classList.remove('is-invalid');
                    if (errorIcon) errorIcon.style.display = 'none';
                    if (errorMessage && errorMessage.classList.contains('error-message')) {
                        errorMessage.style.display = 'none';
                    }
                }
            });
        });

        // CBM calculation
        container.addEventListener('input', function(e) {
            if (e.target.classList.contains('dimension')) {
                const item = e.target.closest('.cargo-item');
                const length = parseFloat(item.querySelector('[name="cargo_length[]"]').value) || 0;
                const width = parseFloat(item.querySelector('[name="cargo_width[]"]').value) || 0;
                const height = parseFloat(item.querySelector('[name="cargo_height[]"]').value) || 0;
                item.querySelector('.cbm-output').value = ((length * width * height) / 1000000).toFixed(4);
            }
        });

        // Add/remove cargo items
        document.getElementById('addCargoItem').addEventListener('click', function() {
            const first = container.querySelector('.cargo-item');
            const clone = first.cloneNode(true);
            clone.querySelectorAll('input').forEach(i => i.value = '');
            clone.querySelectorAll('select').forEach(s => s.selectedIndex = 0);
            container.appendChild(clone);
        });
        document.getElementById('removeCargoItem').addEventListener('click', function() {
            const items = container.querySelectorAll('.cargo-item');
            if (items.length > 1) items[items.length - 1].remove();
        });

        // Classification filters Description
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

        // Photo confirmation
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

        // Show loading overlay on submit
        const passengerForm = document.getElementById('passengerCargoForm');
        if(passengerForm){
            passengerForm.addEventListener('submit', function(e){
                if (!validateForm()) {
                    e.preventDefault();
                    return false;
                }
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
</style>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\kirzt\Documents\GitHub\Capstone\resources\views/passenger/cargobooking.blade.php ENDPATH**/ ?>