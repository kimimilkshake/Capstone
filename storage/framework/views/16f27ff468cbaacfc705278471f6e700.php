<?php $__env->startSection('page-title', 'CARGO BOOKING'); ?>
<?php $__env->startSection('content'); ?>
<?php echo $__env->make('components.authHeader', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php echo $__env->make('components.staff_nav', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<div class="staff-body">   
    <div class="svl-title text-center">
        <h3>CARGO BOOKING</h3>
    </div>

    <?php if(session('success')): ?>
        <div class="alert alert-success text-center mx-auto w-75" role="alert"><?php echo e(session('success')); ?></div>
    <?php endif; ?>
    <?php if($errors->any()): ?>
        <div class="alert alert-danger text-center">
            <ul>
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>

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
                            $routeCodeId = $voyage->routePort->route_code_id ?? '';
                        ?>
                        <option value="<?php echo e($voyage->voyage_id); ?>" data-route_port="<?php echo e($voyage->route_port_id ?? $voyage->routePort->route_port_id ?? ''); ?>" data-route_code="<?php echo e($routeCodeId); ?>">
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
                                        <?php $__currentLoopData = $cargoItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($item->cargo_item_id); ?>" 
                                                data-route-code="<?php echo e($item->route_code_id ?? ''); ?>"
                                                data-measure-required="<?php echo e($item->cargo_item_measure_required ?? 'No'); ?>"
                                                data-measurement-unit="<?php echo e($item->measurementUnit->measurement_unit_abbreviation ?? 'cm'); ?>"
                                                data-min-length="<?php echo e($item->cargo_item_min_length ?? ''); ?>"
                                                data-max-length="<?php echo e($item->cargo_item_max_length ?? ''); ?>"
                                                data-min-width="<?php echo e($item->cargo_item_min_width ?? ''); ?>"
                                                data-max-width="<?php echo e($item->cargo_item_max_width ?? ''); ?>"
                                                data-min-height="<?php echo e($item->cargo_item_min_height ?? ''); ?>"
                                                data-max-height="<?php echo e($item->cargo_item_max_height ?? ''); ?>">
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
                                    <label class="form-label">Weight (kg) <span class="text-danger">*</span></label>
                                    <input type="number" name="cargo_weight[]" class="form-control" placeholder="Weight" step="0.01" required>
                                </div>
                            </div>

                            <!-- Cargo Dimensions -->
                            <div class="cargo-dimensions-block">
                            <label class="form-label">Cargo Dimensions <span class="text-danger">*</span></label>
                            <div class="d-flex gap-2 mb-3 align-items-end">
                                <div class="flex-fill">
                                    <label class="form-label small">Length</label>
                                    <input type="number" name="cargo_length[]" class="form-control dimension" placeholder="Length" step="0.01" required>
                                </div>

                                <div class="flex-fill">
                                    <label class="form-label small">Width</label>
                                    <input type="number" name="cargo_width[]" class="form-control dimension" placeholder="Width" step="0.01" required>
                                </div>

                                <div class="flex-fill">
                                    <label class="form-label small">Height</label>
                                    <input type="number" name="cargo_height[]" class="form-control dimension" placeholder="Height" step="0.01" required>
                                </div>

                                <div style="width: 120px;">
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

// Calculate CBM for a cargo item
function calculateCBM(item){
    const unitEl = item.querySelector('.unitSelect');
    const unit = unitEl ? unitEl.value : 'cm';
    let l = parseFloat(item.querySelector('[name="cargo_length[]"]').value) || 0;
    let w = parseFloat(item.querySelector('[name="cargo_width[]"]').value) || 0;
    let h = parseFloat(item.querySelector('[name="cargo_height[]"]').value) || 0;
    
    if (unit === 'in') {
        l = l * 2.54;
        w = w * 2.54;
        h = h * 2.54;
    }
    
    const cbm = ((l*w*h)/1000000);
    item.querySelector('.cbm-output').value = cbm.toFixed(4);
    const hiddenCbm = item.querySelector('.cargo-cbm-input');
    if (hiddenCbm) hiddenCbm.value = cbm.toFixed(4);
}

function applyMeasurementRules(item, selectedOption){
    if (!item || !selectedOption) return;

    const measureRequired = (selectedOption.dataset.measureRequired || 'No').toString();
    const minLength = selectedOption.dataset.minLength || '';
    const minWidth = selectedOption.dataset.minWidth || '';
    const minHeight = selectedOption.dataset.minHeight || '';
    const unitFromItem = selectedOption.dataset.measurementUnit || 'cm';

    const lengthInput = item.querySelector('[name="cargo_length[]"]');
    const widthInput = item.querySelector('[name="cargo_width[]"]');
    const heightInput = item.querySelector('[name="cargo_height[]"]');
    const unitSelect = item.querySelector('.unitSelect');
    const dimensionsBlock = item.querySelector('.cargo-dimensions-block');

    if (unitSelect && (unitFromItem === 'cm' || unitFromItem === 'in')) {
        unitSelect.value = unitFromItem;
    }

    if (measureRequired === 'Yes') {
        if(lengthInput) { lengthInput.value = minLength; lengthInput.readOnly = true; }
        if(widthInput) { widthInput.value = minWidth; widthInput.readOnly = true; }
        if(heightInput) { heightInput.value = minHeight; heightInput.readOnly = true; }
        if(unitSelect) unitSelect.disabled = true;
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

// Handle voyage selection and filter cargo descriptions by route_code
const voyageSelect = document.querySelector('select[name="voyage_id"]');
if(voyageSelect) {
    voyageSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const routeCodeId = selectedOption.dataset.route_code;
        
        // Filter all cargo descriptions by route_code
        Array.from(container.querySelectorAll('.cargo-description')).forEach(descSelect => {
            Array.from(descSelect.options).forEach(opt => {
                if(opt.value === '') return;
                opt.style.display = (opt.dataset.routeCode === routeCodeId) ? 'block' : 'none';
            });
            descSelect.value = '';
        });
    });
}

// Show loading overlay on submit and enforce 25kg max per booking
const staffForm = document.getElementById('staffCargoForm');
if(staffForm){
    staffForm.addEventListener('submit', function(e){
        // Enforce 25kg max
        let totalWeight = 0;
        document.querySelectorAll('input[name="cargo_weight[]"]').forEach(input => {
            totalWeight += parseFloat(input.value) || 0;
        });
        if(totalWeight > 25){
            e.preventDefault();
            alert('Total weight per booking must not exceed 25kg.');
            return false;
        }
        const overlay = document.getElementById('staffOverlay');
        if(overlay){ overlay.style.display = 'flex'; }
    });
}
</script>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\kirzt\Documents\GitHub\Capstone\resources\views/authorized/staff/cargobooking.blade.php ENDPATH**/ ?>