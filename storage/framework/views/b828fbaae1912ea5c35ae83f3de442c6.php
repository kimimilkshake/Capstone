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

    <form action="<?php echo e(route('cargo.bookings.store')); ?>" method="POST" enctype="multipart/form-data">
        <?php echo csrf_field(); ?>

        <!-- Voyage Selection -->
        <div class="mb-4">
            <label class="form-label fw-bold">Select Voyage <span class="text-danger">*</span></label>
            <select name="voyage_id" class="form-select" required>
                <option value="">-- Choose Voyage --</option>
                <?php $__currentLoopData = $voyages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $voyage): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($voyage->voyage_id); ?>">
                        <?php echo e($voyage->voyage_code); ?> | 
                        <?php echo e($voyage->routePort->route_origin ?? 'N/A'); ?> →
                        <?php echo e($voyage->routePort->route_destination ?? 'N/A'); ?> |
                        Departure: <?php echo e($voyage->voyage_departure_date); ?>

                    </option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
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

                    <div id="cargo-items-container">

                        <div class="cargo-item border rounded p-3 mb-3">

                            <div class="row gx-2 mb-2">
                                <div class="col-6">
                                    <label class="form-label">Cargo Classification <span class="text-danger">*</span></label>
                                    <select name="cargo_classification[]" class="form-select cargo-classification">
                                        <option value="">-- Select Classification --</option>
                                        <?php $__currentLoopData = $cargoItems->unique('cargo_item_classification'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($item->cargo_item_classification); ?>" data-route_port="<?php echo e($item->route_port_id); ?>">
                                                <?php echo e($item->cargo_item_classification); ?>

                                            </option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </div>
                                <div class="col-6">
                                    <label class="form-label">Cargo Description <span class="text-danger">*</span></label>
                                    <select name="cargo_item_id[]" class="form-select cargo-description" required>
                                        <option value="">-- Select Description --</option>
                                        <?php $__currentLoopData = $cargoItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($item->cargo_item_id); ?>" data-classification="<?php echo e($item->cargo_item_classification); ?>" data-route_port="<?php echo e($item->route_port_id); ?>">
                                                <?php echo e($item->cargo_item_description); ?>

                                            </option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </div>
                            </div>

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

                            <label class="form-label">Cargo Dimensions (cm) <span class="text-danger">*</span></label>
                            <div class="d-flex gap-2 mb-2 align-items-end">
                                <div class="flex-fill">
                                    <label class="form-label">Length <span class="text-danger">*</span></label>
                                    <input type="number" name="cargo_length[]" class="form-control dimension" required>
                                </div>

                                <div class="flex-fill">
                                    <label class="form-label">Width <span class="text-danger">*</span></label>
                                    <input type="number" name="cargo_width[]" class="form-control dimension" required>
                                </div>

                                <div class="flex-fill">
                                    <label class="form-label">Height <span class="text-danger">*</span></label>
                                    <input type="number" name="cargo_height[]" class="form-control dimension" required>
                                </div>

                                <div class="flex-fill">
                                    <label class="form-label">CBM </label>
                                    <input type="text" class="form-control cbm-output" readonly placeholder="0.0000">
                                </div>
                            </div>


                    <div class="d-flex gap-2 my-3">
                        <button type="button" id="addCargoItem" class="btn btn-secondary">Add Cargo</button>
                        <button type="button" id="removeCargoItem" class="btn btn-danger">Remove</button>
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
    const original = container.querySelector('.cargo-item');
    const newItem = original.cloneNode(true);

    // Clear all input/select values in the cloned item
    newItem.querySelectorAll('input, select').forEach(el => {
        el.value = '';
    });

    // Reset CBM
    newItem.querySelector('.cbm-output').value = '0.0000';

    container.appendChild(newItem);
});

document.getElementById('removeCargoItem').addEventListener('click', () => {
    const items = container.querySelectorAll('.cargo-item');
    if(items.length > 1) {
        items[items.length - 1].remove();
    }
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

// Photo upload removed for staff UI — no JS needed
</script>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Sophia\Documents\Capstone\resources\views/authorized/staff/cargobooking.blade.php ENDPATH**/ ?>