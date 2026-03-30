<?php $__env->startSection('page-title', 'EDIT CARGO BOOKING DETAILS'); ?>
<?php $__env->startSection('content'); ?>
<?php echo $__env->make('components.authHeader', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php echo $__env->make('components.staff_nav', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php echo $__env->make('components.toast', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<div class="staff-body">

    <?php if(session('success')): ?>
        <div class="alert alert-success"><?php echo e(session('success')); ?></div>
    <?php endif; ?>

    
    
    
    <div class="card shadow-sm p-4 mb-4">
        <h5>Booking Information</h5>
        <div class="row">
            <div class="col-md-6">
                <p><strong>Booking Ref #:</strong> <?php echo e($booking->booking_ref_no); ?></p>
                <p><strong>Status:</strong> <?php echo e($booking->booking_status); ?></p>
                <p><strong>Created:</strong> <?php echo e($booking->created_at->format('M d, Y')); ?></p>
            </div>
            <div class="col-md-6">
                <?php if($booking->voyage): ?>
                    <p><strong>Voyage Code:</strong> <?php echo e($booking->voyage->voyage_code); ?></p>
                    <p><strong>Departure:</strong> <?php echo e($booking->voyage->voyage_departure_date); ?></p>
                    <p><strong>Arrival:</strong> <?php echo e($booking->voyage->voyage_arrival_date); ?></p>
                <?php else: ?>
                    <p><strong>Voyage:</strong> N/A</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    
    
    
    <div class="card shadow-sm p-4 mb-4">
        <h5>Sender & Consignee Information</h5>
        <div class="row">
            <div class="col-md-6">
                <h6 class="fw-bold">Sender Information</h6>
                <p><strong>Name:</strong> <?php echo e($booking->sender->sender_name); ?></p>
                <p><strong>Contact:</strong> <?php echo e($booking->sender->sender_contactno); ?></p>
                <p><strong>Email:</strong> <?php echo e($booking->sender->sender_email ?? '-'); ?></p>
            </div>
            <div class="col-md-6">
                <h6 class="fw-bold">Consignee Information</h6>
                <p><strong>Name:</strong> <?php echo e($booking->consignee->consignee_name); ?></p>
                <p><strong>Contact:</strong> <?php echo e($booking->consignee->consignee_contactno); ?></p>
            </div>
        </div>
    </div>

    
    
    
    <form action="<?php echo e(route('cargo.bookings.update', $booking->booking_ref_no)); ?>" method="POST" id="editCargoForm">
        <?php echo csrf_field(); ?>
        <?php echo method_field('PUT'); ?>

        <?php $__currentLoopData = $booking->cargoBookings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $cargo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="card shadow-sm p-4 mb-4">
                <h5>Cargo Item #<?php echo e($index + 1); ?></h5>
                <input type="hidden" name="cargo_booking_id[]" value="<?php echo e($cargo->cargo_booking_id); ?>">

                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label>Classification</label>
                            <select name="classification[]" required>
                                <option value="">Select Classification</option>
                                <?php $__currentLoopData = $cargoClassifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $classification): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($classification->cargo_classification_id); ?>"
                                        <?php echo e($cargo->cargo_classification_id == $classification->cargo_classification_id ? 'selected' : ''); ?>>
                                        <?php echo e($classification->cargo_classification_name); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-col">
                        <div class="form-group">
                            <label>Description</label>
                            <select name="description[]" required>
                                <option value="">Select Description</option>
                                <?php $__currentLoopData = $cargoItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($item->cargo_item_id); ?>"
                                        data-freight="<?php echo e($item->cargo_item_freight); ?>"
                                        data-with-measurement="<?php echo e(strtolower($item->cargo_item_measure_required ?? 'no')); ?>"
                                        data-base-cbm="<?php echo e($item->cargo_item_base_cbm ?? ''); ?>"
                                        data-min-length="<?php echo e($item->cargo_item_min_length ?? ''); ?>"
                                        data-max-length="<?php echo e($item->cargo_item_max_length ?? ''); ?>"
                                        data-min-width="<?php echo e($item->cargo_item_min_width ?? ''); ?>"
                                        data-max-width="<?php echo e($item->cargo_item_max_width ?? ''); ?>"
                                        data-min-height="<?php echo e($item->cargo_item_min_height ?? ''); ?>"
                                        data-max-height="<?php echo e($item->cargo_item_max_height ?? ''); ?>"
                                        <?php echo e($cargo->cargo_item_id == $item->cargo_item_id ? 'selected' : ''); ?>>
                                        <?php echo e($item->cargo_item_description); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label>Quantity</label>
                            <input type="number" name="quantity[]" value="<?php echo e(old('quantity.'.$index, $cargo->quantity)); ?>" min="1" required>
                        </div>
                    </div>

                    <div class="form-col">
                        <div class="form-group">
                            <label>Length</label>
                            <input type="number" step="0.01" name="length[]" value="<?php echo e(old('length.'.$index, $cargo->length)); ?>" required>
                        </div>
                    </div>

                    <div class="form-col">
                        <div class="form-group">
                            <label>Width</label>
                            <input type="number" step="0.01" name="width[]" value="<?php echo e(old('width.'.$index, $cargo->width)); ?>" required>
                        </div>
                    </div>

                    <div class="form-col">
                        <div class="form-group">
                            <label>Height</label>
                            <input type="number" step="0.01" name="height[]" value="<?php echo e(old('height.'.$index, $cargo->height)); ?>" required>
                        </div>
                    </div>

                    <div class="form-col">
                        <div class="form-group">
                            <label>Unit</label>
                            <select name="measurement_unit[]" required>
                                <?php
                                    $selectedUnitId = old('measurement_unit.'.$index, $cargo->measurement_unit_id);
                                ?>
                                <?php $__currentLoopData = $measurementUnits; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $measurementUnit): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($measurementUnit->measurement_unit_id); ?>"
                                        data-unit-abbrev="<?php echo e(strtolower($measurementUnit->measurement_unit_abbreviation ?? 'cm')); ?>"
                                        <?php echo e((string) $selectedUnitId === (string) $measurementUnit->measurement_unit_id ? 'selected' : ''); ?>>
                                        <?php echo e($measurementUnit->measurement_unit_abbreviation); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-col">
                        <div class="form-group">
                            <label>Weight (kg)</label>
                            <input type="number" step="0.01" name="weight[]" value="<?php echo e(old('weight.'.$index, $cargo->weight)); ?>" required>
                        </div>
                    </div>

                    <div class="form-col">
                        <div class="form-group">
                            <label>CBM</label>
                            <input type="number" step="0.0001" name="cbm[]" value="<?php echo e(old('cbm.'.$index, $cargo->cbm ?? 0)); ?>" class="cargo-cbm" readonly>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

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
            <button type="submit" class="btn btn-primary" id="updateCargoItemsBtn" disabled>Update Cargo Items</button>
            <a href="<?php echo e(route('cargo.bookings.show', $booking->booking_ref_no)); ?>" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<script>
// Validate that CBM meets the minimum base_cbm requirement when no predefined range exists
function validateBaseCbm() {
    const descriptionSelects = document.querySelectorAll('select[name="description[]"]');
    const cbmInputs = document.querySelectorAll('input[name="cbm[]"]');
    
    for (let i = 0; i < descriptionSelects.length; i++) {
        const select = descriptionSelects[i];
        const option = select.options[select.selectedIndex];
        
        if (!option || !option.value) continue;
        
        const measureRequired = option.dataset.withMeasurement ? option.dataset.withMeasurement.toLowerCase() : 'no';
        const baseCbm = parseFloat(option.dataset.baseCbm) || 0;
        
        // Check if there is no predefined measurement range
        const minLength = option.dataset.minLength;
        const maxLength = option.dataset.maxLength;
        const minWidth = option.dataset.minWidth;
        const maxWidth = option.dataset.maxWidth;
        const minHeight = option.dataset.minHeight;
        const maxHeight = option.dataset.maxHeight;
        
        const hasPredefinedRange = measureRequired === 'yes' ||
            (minLength && minLength !== '' && minLength !== null) ||
            (maxLength && maxLength !== '' && maxLength !== null) ||
            (minWidth && minWidth !== '' && minWidth !== null) ||
            (maxWidth && maxWidth !== '' && maxWidth !== null) ||
            (minHeight && minHeight !== '' && minHeight !== null) ||
            (maxHeight && maxHeight !== '' && maxHeight !== null);
        
        // If no predefined range and base_cbm is defined, validate CBM
        if (!hasPredefinedRange && baseCbm > 0) {
            const currentCbm = parseFloat(cbmInputs[i]?.value) || 0;
            const cargoDescription = option.textContent.trim();
            
            if (currentCbm < baseCbm) {
                showToast(`"${cargoDescription}" - CBM (${currentCbm.toFixed(4)}) is below minimum required (${baseCbm.toFixed(4)}).`, 'danger');
                cbmInputs[i]?.focus();
                return false;
            }
        }
    }
    
    return true;
}

function getEditableFormState() {
    const fields = document.querySelectorAll(
        'select[name="classification[]"], ' +
        'select[name="description[]"], ' +
        'input[name="quantity[]"], ' +
        'input[name="length[]"], ' +
        'input[name="width[]"], ' +
        'input[name="height[]"], ' +
        'select[name="measurement_unit[]"], ' +
        'input[name="weight[]"]'
    );

    return Array.from(fields).map(field => String(field.value ?? '').trim());
}

function syncUpdateButtonState(initialState) {
    const updateButton = document.getElementById('updateCargoItemsBtn');
    if (!updateButton) return;

    const currentState = getEditableFormState();

    let hasChanges = false;

    if (currentState.length !== initialState.length) {
        hasChanges = true;
    } else {
        for (let i = 0; i < currentState.length; i++) {
            if (currentState[i] !== initialState[i]) {
                hasChanges = true;
                break;
            }
        }
    }

    updateButton.disabled = !hasChanges;
}

function calculateCBM(index) {
    const lengthInputs = document.querySelectorAll('input[name="length[]"]');
    const widthInputs = document.querySelectorAll('input[name="width[]"]');
    const heightInputs = document.querySelectorAll('input[name="height[]"]');
    const unitSelects = document.querySelectorAll('select[name="measurement_unit[]"]');
    const cbmInputs = document.querySelectorAll('input[name="cbm[]"]');

    let length = parseFloat(lengthInputs[index]?.value) || 0;
    let width = parseFloat(widthInputs[index]?.value) || 0;
    let height = parseFloat(heightInputs[index]?.value) || 0;

    const unitOption = unitSelects[index]?.options[unitSelects[index].selectedIndex];
    const unit = unitOption ? (unitOption.dataset.unitAbbrev || 'cm').toLowerCase() : 'cm';

    if (unit === 'in') {
        length *= 2.54; width *= 2.54; height *= 2.54;
    } else if (unit === 'mm') {
        length *= 0.1; width *= 0.1; height *= 0.1;
    } else if (unit === 'm') {
        length *= 100; width *= 100; height *= 100;
    } else if (unit === 'ft') {
        length *= 30.48; width *= 30.48; height *= 30.48;
    }

    const cbm = (length * width * height) / 1000000;

    if (cbmInputs[index]) cbmInputs[index].value = cbm.toFixed(4);

    return cbm;
}

function calculateValues() {
    let totalValue = 0;

    const descriptionSelects = document.querySelectorAll('select[name="description[]"]');
    const quantityInputs = document.querySelectorAll('input[name="quantity[]"]');

    descriptionSelects.forEach((select, index) => {
        const option = select.options[select.selectedIndex];
        const freight = option ? (parseFloat(option.dataset.freight) || 0) : 0;
        const measureRequired = option ? (option.dataset.withMeasurement || 'no').toLowerCase() : 'no';
        const quantity = parseFloat(quantityInputs[index]?.value) || 0;

        const cbm = calculateCBM(index);

        let subtotal = 0;

        if (measureRequired === 'yes') {
            subtotal = freight * quantity;
        } else {
            subtotal = cbm * freight * quantity;
        }

        totalValue += subtotal;
    });

    document.getElementById('totalValueDisplay').textContent = '₱' + totalValue.toFixed(2);
    document.getElementById('totalValueInput').value = totalValue.toFixed(2);
}

document.addEventListener('DOMContentLoaded', function() {
    calculateValues();

    const initialState = getEditableFormState();
    syncUpdateButtonState(initialState);

    document.querySelectorAll('input, select').forEach(el => {
        el.addEventListener('input', () => {
            calculateValues();
            syncUpdateButtonState(initialState);
        });
        el.addEventListener('change', () => {
            calculateValues();
            syncUpdateButtonState(initialState);
        });
    });

    // Form submit handler with base_cbm validation
    const editCargoForm = document.getElementById('editCargoForm');
    if (editCargoForm) {
        editCargoForm.addEventListener('submit', function(e) {
            if (!validateBaseCbm()) {
                e.preventDefault();
                return false;
            }
            // Re-enable readonly CBM inputs before submit so they get included in the form data
            document.querySelectorAll('input[name="cbm[]"]').forEach(input => {
                input.readOnly = false;
            });
        });
    }
});
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\kirzt\Documents\GitHub\Capstone\resources\views/authorized/staff/editcargo.blade.php ENDPATH**/ ?>