<?php $__env->startSection('page-title', 'CARGO BOOKING'); ?>
<?php $__env->startSection('content'); ?>
<?php echo $__env->make('components.authHeader', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php echo $__env->make('components.staff_nav', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php echo $__env->make('components.toast', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<div class="staff-body">   

    <form id="staffCargoForm" action="<?php echo e(route('cargo.bookings.store')); ?>" method="POST" enctype="multipart/form-data">
        <?php echo csrf_field(); ?>

        <!-- Loading overlay -->
        <div id="staffOverlay" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.45); z-index:1055; align-items:center; justify-content:center;">
            <div class="text-center text-white">
                <div class="spinner-border text-light" role="status" style="width:3rem; height:3rem;"></div>
                <div class="mt-3">Loading... please wait</div>
            </div>
        </div>

        <!-- Voyage Selection + No. of Cargo -->
        <div class="mb-4 d-flex gap-3 align-items-end">
            <div style="flex:1">
                <label class="form-label fw-bold">Select Voyage <span class="text-danger">*</span></label>
                <select name="voyage_id" class="form-select" required>
                    <option value="">-- Choose Voyage --</option>
                    <?php $__currentLoopData = $voyages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $voyage): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $depDate = \Carbon\Carbon::parse($voyage->voyage_departure_date)->format('M j, Y');
                            $depTime = $voyage->voyage_estimated_TD ? \Carbon\Carbon::parse($voyage->voyage_estimated_TD)->format('g:i A') : '';
                            $routeCategoryId = $voyage->routePort->route_category_id ?? '';
                        ?>
                        <option value="<?php echo e($voyage->voyage_id); ?>" data-route_port="<?php echo e($voyage->route_port_id ?? $voyage->routePort->route_port_id ?? ''); ?>" data-route_category="<?php echo e($routeCategoryId); ?>" data-departure-date="<?php echo e($voyage->voyage_departure_date ?? ''); ?>" data-departure-time="<?php echo e($voyage->voyage_estimated_TD ?? ''); ?>">
                            <?php echo e($voyage->voyage_code); ?> - <?php echo e($voyage->routePort->route_origin ?? 'N/A'); ?> → <?php echo e($voyage->routePort->route_destination ?? 'N/A'); ?> - Departure: <?php echo e($depDate); ?> <?php echo e($depTime ? '(' . $depTime . ')' : ''); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>

            <div style="width:150px;">
                <label class="form-label fw-bold">No. of Cargo <span class="text-danger">*</span></label>
                <input type="number" name="no_of_cargo" id="no_of_cargo" class="form-control" min="1" value="1">
            </div>
        </div>

        <div class="row">

            
            <div class="col-lg-6 mb-3">
                <div class="card p-3 shadow-sm">
                    <h5 class="fw-bold mb-3">Sender Information</h5>

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

                    <h5 class="fw-bold mt-4 mb-3">Consignee Information</h5>

                    <label class="form-label">First Name <span class="text-danger">*</span></label>
                    <input type="text" name="consignee_firstname" class="form-control mb-2" required>

                    <label class="form-label">Last Name <span class="text-danger">*</span></label>
                    <input type="text" name="consignee_lastname" class="form-control mb-2" required>

                    <label class="form-label">Contact Number <span class="text-danger">*</span></label>
                    <input type="text" name="consignee_contact" class="form-control mb-2" required>
                </div>
            </div>

            
            <div class="col-lg-6 mb-3">
                <div class="card p-3 shadow-sm">
                    <h5 class="fw-bold mb-3">Cargo Items</h5>
                    
        <div class="mb-3">
            <small class="text-muted">Note: You can only add numerous QTY to one cargo item if similar in dimensions.</small>
        </div>

                    <div id="cargo-items-container">

                        <div class="cargo-item border rounded p-3 mb-3">

                            <!-- Classification + Description in row -->
                            <div class="row gx-2 mb-3">
                                <div class="col-6">
                                    <label class="form-label">Cargo Classification <span class="text-danger">*</span></label>
                                    <select name="cargo_classification[]" class="form-select cargo-classification">
                                        <option value="">-- Select Classification --</option>
                                        <?php $__currentLoopData = $cargoClassifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $classification): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($classification->cargo_classification_id); ?>">
                                                <?php echo e($classification->cargo_classification_name); ?>

                                            </option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </div>
                                <div class="col-6">
                                    <label class="form-label">Cargo Description <span class="text-danger">*</span></label>
                                    <select name="cargo_item_id[]" class="form-select cargo-description" required>
                                        <option value="">-- Select Description --</option>
                                        <?php $__currentLoopData = $cargoItems->sortBy('cargo_item_description'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($item->cargo_item_id); ?>" 
                                                data-route-category="<?php echo e($item->route_category_id ?? ''); ?>"
                                                data-measure-required="<?php echo e($item->cargo_item_measure_required ?? 'No'); ?>"
                                                data-measurement-unit="<?php echo e($item->measurementUnit->measurement_unit_abbreviation ?? 'cm'); ?>"
                                                data-min-length="<?php echo e($item->cargo_item_min_length ?? ''); ?>"
                                                data-max-length="<?php echo e($item->cargo_item_max_length ?? ''); ?>"
                                                data-min-width="<?php echo e($item->cargo_item_min_width ?? ''); ?>"
                                                data-max-width="<?php echo e($item->cargo_item_max_width ?? ''); ?>"
                                                data-min-height="<?php echo e($item->cargo_item_min_height ?? ''); ?>"
                                                data-max-height="<?php echo e($item->cargo_item_max_height ?? ''); ?>"
                                                data-base-cbm="<?php echo e($item->cargo_item_base_cbm ?? ''); ?>">
                                                <?php echo e($item->cargo_item_description); ?>

                                            </option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </div>
                            </div>

                            <!-- Quantity & Weight -->
                            <div class="row gx-2 mb-3">
                                <div class="col-6">
                                    <label class="form-label">Quantity <span class="text-danger">*</span></label>
                                    <input type="number" name="cargo_quantity[]" class="form-control" placeholder="Qty" min="1" required>
                                </div>
                                <div class="col-6">
                                    <label class="form-label">Total Weight (kg) <span class="text-danger">*</span></label>
                                    <input type="number" name="cargo_weight[]" class="form-control" placeholder="Weight" step="0.01" min="0" required>
                                </div>
                            </div>

                            <!-- Cargo Dimensions -->
                            <div class="cargo-dimensions-block">
                            <label class="form-label">Cargo Dimensions <span class="text-danger">*</span></label>
                            <div class="d-flex gap-2 mb-3 align-items-end">
                                <div class="flex-fill">
                                    <label class="form-label small">Length</label>
                                    <input type="number" name="cargo_length[]" class="form-control dimension" placeholder="Length" step="0.01" min="0" required>
                                </div>

                                <div class="flex-fill">
                                    <label class="form-label small">Width</label>
                                    <input type="number" name="cargo_width[]" class="form-control dimension" placeholder="Width" step="0.01" min="0" required>
                                </div>

                                <div class="flex-fill">
                                    <label class="form-label small">Height</label>
                                    <input type="number" name="cargo_height[]" class="form-control dimension" placeholder="Height" step="0.01" min="0" required>
                                </div>

                                <div style="width: 120px;">
                                    <label class="form-label small">Unit</label>
                                    <select name="measurement_unit[]" class="form-select unitSelect">
                                        <?php $__currentLoopData = $measurementUnits; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $measurementUnit): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($measurementUnit->measurement_unit_abbreviation); ?>">
                                                <?php echo e($measurementUnit->measurement_unit_abbreviation); ?>

                                            </option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select> 
                                </div>

                                <div class="flex-fill">
                                    <label class="form-label small">CBM</label>
                                    <input type="text" class="form-control cbm-output" readonly placeholder="0.0000">
                                </div>
                            </div>
                            </div>
                            <input type="hidden" name="cargo_cbm[]" class="cargo-cbm-input" value="0.0000">

                        </div>
                    </div>

                </div>
            </div>

        </div> 

        <div class="text-center mt-4 mb-5">
            <button type="submit" class="btn btn-primary btn-lg">PROCEED</button>
            <a href="<?php echo e(route('staff.dashboard')); ?>" class="btn btn-outline-danger btn-lg">CANCEL</a>
        </div>

    </form>
</div>


<!-- JS -->
<script>
const container = document.getElementById('cargo-items-container');

function normalizeUnitValue(unitValue){
    const normalized = String(unitValue || '').trim().toLowerCase();

    if (['in', 'inch', 'inches'].includes(normalized)) return 'in';
    if (['cm', 'centimeter', 'centimeters'].includes(normalized)) return 'cm';
    if (['mm', 'millimeter', 'millimeters'].includes(normalized)) return 'mm';
    if (['m', 'meter', 'meters'].includes(normalized)) return 'm';
    if (['ft', 'foot', 'feet'].includes(normalized)) return 'ft';

    return normalized || 'cm';
}

function toCentimeters(value, unitValue){
    const unit = normalizeUnitValue(unitValue);
    const numericValue = parseFloat(value) || 0;

    if (unit === 'in') return numericValue * 2.54;
    if (unit === 'mm') return numericValue * 0.1;
    if (unit === 'm') return numericValue * 100;
    if (unit === 'ft') return numericValue * 30.48;

    return numericValue;
}

// Calculate CBM for a cargo item
function calculateCBM(item){
    const unitEl = item.querySelector('.unitSelect');
    const unit = unitEl ? unitEl.value : 'cm';
    const l = toCentimeters(item.querySelector('[name="cargo_length[]"]').value, unit);
    const w = toCentimeters(item.querySelector('[name="cargo_width[]"]').value, unit);
    const h = toCentimeters(item.querySelector('[name="cargo_height[]"]').value, unit);
    
    const cbm = ((l*w*h)/1000000);
    item.querySelector('.cbm-output').value = cbm.toFixed(4);
    const hiddenCbm = item.querySelector('.cargo-cbm-input');
    if (hiddenCbm) hiddenCbm.value = cbm.toFixed(4);
}

function applyMeasurementRules(item, selectedOption){
    if (!item || !selectedOption) return;

    const measureRequired = (selectedOption.dataset.measureRequired || 'No').toString().trim().toLowerCase();
    const minLength = parseFloat(selectedOption.dataset.minLength || '0');
    const maxLength = parseFloat(selectedOption.dataset.maxLength || selectedOption.dataset.minLength || '0');
    const minWidth = parseFloat(selectedOption.dataset.minWidth || '0');
    const maxWidth = parseFloat(selectedOption.dataset.maxWidth || selectedOption.dataset.minWidth || '0');
    const minHeight = parseFloat(selectedOption.dataset.minHeight || '0');
    const maxHeight = parseFloat(selectedOption.dataset.maxHeight || selectedOption.dataset.minHeight || '0');
    const baseCbm = parseFloat(selectedOption.dataset.baseCbm) || 0;

    // Check if this cargo item has NO predefined measurement range
    const hasPredefinedRange = measureRequired === 'yes' || 
        (selectedOption.dataset.minLength !== '' && selectedOption.dataset.maxLength !== '') ||
        (selectedOption.dataset.minWidth !== '' && selectedOption.dataset.maxWidth !== '') ||
        (selectedOption.dataset.minHeight !== '' && selectedOption.dataset.maxHeight !== '');

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
        if(lengthInput) { lengthInput.value = maxLengthValue; lengthInput.readOnly = true; }
        if(widthInput) { widthInput.value = maxWidthValue; widthInput.readOnly = true; }
        if(heightInput) { heightInput.value = maxHeightValue; heightInput.readOnly = true; }
        // Keep enabled so selected unit is included in form POST.
        if(unitSelect) unitSelect.disabled = false;
        if(dimensionsBlock) dimensionsBlock.style.display = 'none';
    } else {
        if(lengthInput) { lengthInput.value = ''; lengthInput.readOnly = false; }
        if(widthInput) { widthInput.value = ''; widthInput.readOnly = false; }
        if(heightInput) { heightInput.value = ''; heightInput.readOnly = false; }
        if(unitSelect) unitSelect.disabled = false;
        if(dimensionsBlock) dimensionsBlock.style.display = '';
    }

    calculateCBM(item);
}

function updateCargoItemComputedValues(item){
    const descriptionSelect = item.querySelector('.cargo-description');
    if (!descriptionSelect) return;
    const selectedOption = descriptionSelect.options[descriptionSelect.selectedIndex];
    applyMeasurementRules(item, selectedOption);
}

function parseDepartureDateTime(dateValue, timeValue){
    const dateText = String(dateValue || '').trim();
    const timeText = String(timeValue || '').trim();
    if (!dateText) return null;

    let parsed = null;
    if (timeText) {
        parsed = new Date(`${dateText}T${timeText}`);
        if (Number.isNaN(parsed.getTime())) {
            parsed = new Date(`${dateText} ${timeText}`);
        }
    }

    if (!parsed || Number.isNaN(parsed.getTime())) {
        parsed = new Date(dateText);
    }

    return Number.isNaN(parsed.getTime()) ? null : parsed;
}

function validateBookingCutoffByDeparture(departureDateTime){
    if (!departureDateTime) {
        return { valid: false, message: 'Invalid voyage departure date/time.' };
    }

    const now = new Date();
    const depHour = departureDateTime.getHours();
    const depDateStart = new Date(
        departureDateTime.getFullYear(),
        departureDateTime.getMonth(),
        departureDateTime.getDate(),
        0, 0, 0, 0
    );

    if (depHour <= 18) {
        if (now >= depDateStart) {
            return {
                valid: false,
                message: 'For departures from 12:00 AM to 6:00 PM, booking must be completed the day before.'
            };
        }
        return { valid: true };
    }

    const depDateEnd = new Date(
        departureDateTime.getFullYear(),
        departureDateTime.getMonth(),
        departureDateTime.getDate(),
        23, 59, 59, 999
    );

    if (now > depDateEnd) {
        return { valid: false, message: 'This voyage booking window has already closed.' };
    }

    const isSameDay = now.getFullYear() === departureDateTime.getFullYear()
        && now.getMonth() === departureDateTime.getMonth()
        && now.getDate() === departureDateTime.getDate();

    if (isSameDay) {
        const cutoff = new Date(depDateStart);
        cutoff.setHours(17, 0, 0, 0);
        if (now >= cutoff) {
            return {
                valid: false,
                message: 'For departures from 7:00 PM to 11:59 PM, same-day booking cutoff is 5:00 PM.'
            };
        }
    }

    return { valid: true };
}

function refreshVoyageAvailabilityByCutoff(){
    if(!voyageSelect) return;

    const options = Array.from(voyageSelect.options);
    let hasAvailableVoyage = false;

    options.forEach((opt, index) => {
        // Keep placeholder option always visible and enabled.
        if (index === 0 || !opt.value) {
            opt.disabled = false;
            opt.hidden = false;
            return;
        }

        const departureDateTime = parseDepartureDateTime(opt.dataset.departureDate, opt.dataset.departureTime);
        const cutoffValidation = validateBookingCutoffByDeparture(departureDateTime);

        opt.disabled = !cutoffValidation.valid;
        opt.hidden = !cutoffValidation.valid;

        if (cutoffValidation.valid) {
            hasAvailableVoyage = true;
        }
    });

    const selectedOption = voyageSelect.options[voyageSelect.selectedIndex];
    if (selectedOption && selectedOption.disabled) {
        voyageSelect.value = '';
    }

    if (!hasAvailableVoyage) {
        voyageSelect.value = '';
    }
}

function validateStaffNumericInputs(form){
    const quantityInputs = form.querySelectorAll('input[name="cargo_quantity[]"]');
    for (const quantityInput of quantityInputs) {
        const value = parseFloat(quantityInput.value);
        if (quantityInput.value !== '' && !Number.isNaN(value) && value <= 0) {
            quantityInput.focus();
            alert('Quantity must be greater than zero.');
            return false;
        }
    }

    const numberInputs = form.querySelectorAll('input[type="number"]');
    for (const input of numberInputs) {
        const value = parseFloat(input.value);
        if (input.value !== '' && !Number.isNaN(value) && value < 0) {
            input.focus();
            alert('Negative values are not allowed in numeric fields.');
            return false;
        }
    }

    return true;
}

// Validate base_cbm requirement for cargo items without predefined measurement range
function validateBaseCbmRequirement(form){
    const cargoItems = form.querySelectorAll('.cargo-item');
    
    for (const item of cargoItems) {
        const descriptionSelect = item.querySelector('.cargo-description');
        if (!descriptionSelect || !descriptionSelect.value) continue;
        
        const selectedOption = descriptionSelect.options[descriptionSelect.selectedIndex];
        if (!selectedOption) continue;
        
        const measureRequired = (selectedOption.dataset.measureRequired || 'No').toString().trim().toLowerCase();
        const baseCbm = parseFloat(selectedOption.dataset.baseCbm) || 0;
        
        // Check if this cargo item has NO predefined measurement range
        const hasPredefinedRange = measureRequired === 'yes' || 
            (selectedOption.dataset.minLength !== '' && selectedOption.dataset.maxLength !== '') ||
            (selectedOption.dataset.minWidth !== '' && selectedOption.dataset.maxWidth !== '') ||
            (selectedOption.dataset.minHeight !== '' && selectedOption.dataset.maxHeight !== '');
        
        // Skip validation if item has predefined measurement range
        if (hasPredefinedRange) continue;
        
        // Skip if no base_cbm requirement
        if (baseCbm <= 0) continue;
        
        // Get the calculated CBM for this item
        const calculatedCbm = parseFloat(item.querySelector('.cargo-cbm-input')?.value || '0') || 0;
        
        // Check if calculated CBM is less than base_cbm
        if (calculatedCbm < baseCbm) {
            const itemDescription = selectedOption.textContent.trim();
            showToast(`"${itemDescription}" requires minimum ${baseCbm.toFixed(4)} CBM. Current: ${calculatedCbm.toFixed(4)} CBM. Please increase dimensions or quantity.`, 'danger');
            
            // Focus on dimension input if available
            const lengthInput = item.querySelector('[name="cargo_length[]"]');
            if (lengthInput && !lengthInput.readOnly) {
                lengthInput.focus();
            }
            return false;
        }
    }
    
    return true;
}

// CBM listener
container.addEventListener('input', e => {
    if(e.target.classList.contains('dimension')){
        const item = e.target.closest('.cargo-item');
        calculateCBM(item);
    }
});

// Recalculate CBM when unit changes
container.addEventListener('change', e => {
    if(e.target.classList.contains('unitSelect')){
        const item = e.target.closest('.cargo-item');
        calculateCBM(item);
    }
});

// Change number of cargo items based on No. of Cargo input
const noInput = document.getElementById('no_of_cargo');
const MAX_ITEMS = 5;
if(noInput){
    function syncCargoItems(){
        let desired = parseInt(noInput.value) || 1;
        if(desired < 1) { desired = 1; noInput.value = 1; }
        if(desired > MAX_ITEMS) { desired = MAX_ITEMS; noInput.value = MAX_ITEMS; }
        const items = Array.from(container.querySelectorAll('.cargo-item'));
        const current = items.length;
        const original = items[0];

        if(desired > current){
            for(let i = current; i < desired; i++){
                const newItem = original.cloneNode(true);
                newItem.querySelectorAll('input').forEach(el => {
                    if (el.type === 'hidden') {
                        el.value = '0.0000';
                    } else {
                        el.value = '';
                    }
                });
                newItem.querySelectorAll('select').forEach(el => {
                    el.selectedIndex = 0;
                    el.disabled = false;
                });
                newItem.querySelector('.cbm-output').value = '0.0000';
                container.appendChild(newItem);
                updateCargoItemComputedValues(newItem);
            }
        } else if(desired < current){
            for(let i = current; i > desired; i--){
                const last = container.querySelector('.cargo-item:last-child');
                if(last) last.remove();
            }
        }
    }

    noInput.addEventListener('change', syncCargoItems);
    window.addEventListener('DOMContentLoaded', function () {
        syncCargoItems();
        container.querySelectorAll('.cargo-item').forEach(updateCargoItemComputedValues);
    });
}

// Display min/max ranges when cargo description changes and apply measurement-required behavior

// Passenger-style: When cargo description is selected, set dimension fields based on measurement requirement
container.addEventListener('change', e => {
    if(!e.target.classList.contains('cargo-description')) return;
    const item = e.target.closest('.cargo-item');
    const selectedOption = e.target.options[e.target.selectedIndex];
    applyMeasurementRules(item, selectedOption);
});

// Handle voyage selection and filter cargo descriptions by route_category
const voyageSelect = document.querySelector('select[name="voyage_id"]');
if(voyageSelect) {
    refreshVoyageAvailabilityByCutoff();

    voyageSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const routeCategoryId = selectedOption.dataset.route_category;
        
        // Filter all cargo descriptions by route_category
        Array.from(container.querySelectorAll('.cargo-description')).forEach(descSelect => {
            Array.from(descSelect.options).forEach(opt => {
                if(opt.value === '') return;
                opt.style.display = (opt.dataset.routeCategory === routeCategoryId) ? 'block' : 'none';
            });
            descSelect.value = '';
        });
    });
}

// Show loading overlay on submit and enforce 25kg max per booking
const staffForm = document.getElementById('staffCargoForm');
if(staffForm){
    staffForm.addEventListener('submit', function(e){
        const selectedVoyageOption = voyageSelect ? voyageSelect.options[voyageSelect.selectedIndex] : null;
        const departureDateTime = selectedVoyageOption
            ? parseDepartureDateTime(selectedVoyageOption.dataset.departureDate, selectedVoyageOption.dataset.departureTime)
            : null;

        const cutoffValidation = validateBookingCutoffByDeparture(departureDateTime);
        if (!cutoffValidation.valid) {
            e.preventDefault();
            alert(cutoffValidation.message);
            return false;
        }

        if (!validateStaffNumericInputs(staffForm)) {
            e.preventDefault();
            return false;
        }

        // Validate base_cbm requirement for cargo items without predefined measurement range
        if (!validateBaseCbmRequirement(staffForm)) {
            e.preventDefault();
            return false;
        }

        const overlay = document.getElementById('staffOverlay');
        if(overlay){ overlay.style.display = 'flex'; }
    });
}
</script>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\kirzt\Documents\GitHub\Capstone\resources\views/authorized/staff/cargobooking.blade.php ENDPATH**/ ?>