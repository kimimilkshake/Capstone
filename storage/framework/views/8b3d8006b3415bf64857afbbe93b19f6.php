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

            <form action="<?php echo e(route('cargobooking.confirm')); ?>" method="POST" enctype="multipart/form-data">
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
                            <input type="text" name="sender_firstname" class="form-control" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Last Name <span class="text-danger">*</span></label>
                            <input type="text" name="sender_lastname" class="form-control" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Contact Number <span class="text-danger">*</span></label>
                            <input type="text" name="sender_contact" class="form-control" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Email Address<span class="text-danger">*</span></label>
                            <input type="email" name="sender_email" class="form-control">
                        </div>
                        <div class="mb-2">
                            <label class="form-label">TIN Number (Optional)</label>
                            <input type="text" name="sender_tin" class="form-control">
                        </div>

                        <h6 class="fw-bold mt-4">Consignee Information</h6>
                        <div class="mb-2">
                            <label class="form-label">First Name <span class="text-danger">*</span></label>
                            <input type="text" name="consignee_firstname" class="form-control" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Last Name <span class="text-danger">*</span></label>
                            <input type="text" name="consignee_lastname" class="form-control" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Contact Number <span class="text-danger">*</span></label>
                            <input type="text" name="consignee_contact" class="form-control" required>
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
                                        <label class="form-label">Classification <span class="text-danger">*</span></label>
                                        <select name="cargo_classification[]" class="form-control cargo-classification">
                                            <option value="">-- Select Classification --</option>
                                            <?php
                                                $classifications = $cargoItems
                                                    ->where('route_port_id', $voyage->routePort->route_port_id ?? null)
                                                    ->pluck('cargo_item_classification')
                                                    ->unique();
                                            ?>
                                            <?php $__currentLoopData = $classifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $class): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($class); ?>"><?php echo e($class); ?></option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </select>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label">Description <span class="text-danger">*</span></label>
                                        <select name="cargo_item_id[]" class="form-control cargo-description" required>
                                            <option value="">-- Select Description --</option>
                                            <?php $__currentLoopData = $cargoItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cargo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <?php if($cargo->route_port_id == ($voyage->routePort->route_port_id ?? null)): ?>
                                                <option value="<?php echo e($cargo->cargo_item_id); ?>" data-classification="<?php echo e($cargo->cargo_item_classification); ?>">
                                                    <?php echo e($cargo->cargo_item_description); ?>

                                                </option>
                                                <?php endif; ?>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </select>
                                    </div>
                                </div>

                                <!-- Quantity & Weight -->
                                <div class="mb-2 d-flex gap-2">
                                    <div class="flex-fill">
                                        <label class="form-label">Quantity <span class="text-danger">*</span></label>
                                        <input type="number" name="cargo_quantity[]" class="form-control" placeholder="Quantity" min="1" required>
                                    </div>
                                    <div class="flex-fill">
                                        <label class="form-label">Weight (kg) <span class="text-danger">*</span></label>
                                        <input type="number" name="cargo_weight[]" class="form-control" placeholder="Weight" step="0.01" required>
                                    </div>
                                </div>

                                <!-- Dimensions + CBM -->
                                <div class="mb-2">
                                    <label class="form-label">Cargo Dimensions (cm) </label>
                                    <div class="d-flex gap-2">
                                        <div class="flex-fill">
                                            <label class="form-label small">Length <span class="text-danger">*</span></label>
                                            <input type="number" name="cargo_length[]" class="form-control dimension" step="0.01">
                                        </div>
                                        <div class="flex-fill">
                                            <label class="form-label small">Width <span class="text-danger">*</span></label>
                                            <input type="number" name="cargo_width[]" class="form-control dimension" step="0.01">
                                        </div>
                                        <div class="flex-fill">
                                            <label class="form-label small">Height <span class="text-danger">*</span></label>
                                            <input type="number" name="cargo_height[]" class="form-control dimension" step="0.01">
                                        </div>
                                        <div class="flex-fill">
                                            <label class="form-label small">CBM</label>
                                            <input type="text" class="form-control cbm-output" readonly placeholder="0.0000">
                                        </div>
                                    </div>
                                </div>

                                <!-- Photo Upload with confirmation -->
                                <div class="mb-2">
                                    <label class="btn btn-outline-secondary w-100 d-flex align-items-center justify-content-center mt-2">
                                        <i class="bi bi-image me-2"></i> Add Photo <span class="text-danger">*</span>
                                        <input type="file" name="cargo_picture[]" class="d-none cargo-photo" accept="image/*">
                                    </label>
                                    <small class="text-success photo-confirmation" style="display:none;">Photo selected!</small>
                                </div>

                            </div> <!-- end cargo-item -->

                        </div>

                        <div class="d-flex gap-2 mt-2">
                            <button type="button" id="addCargoItem" class="btn btn-secondary">Add Another Cargo</button>
                            <button type="button" id="removeCargoItem" class="btn btn-danger">Remove Last Cargo</button>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-4 gap-3">
                    <a href="<?php echo e(route('bookingtype')); ?>" class="btn btn-outline-danger fw-bold py-3" style="width:180px;">
                        CANCEL BOOKING
                    </a>
                    <button type="submit" class="btn btn-primary fw-bold py-3" style="width:180px;">
                        PROCEED
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

<script>
const container = document.getElementById('cargo-items-container');

// CBM calculation
container.addEventListener('input', function(e){
    if(e.target.classList.contains('dimension')){
        const item = e.target.closest('.cargo-item');
        const length = parseFloat(item.querySelector('[name="cargo_length[]"]').value) || 0;
        const width = parseFloat(item.querySelector('[name="cargo_width[]"]').value) || 0;
        const height = parseFloat(item.querySelector('[name="cargo_height[]"]').value) || 0;
        item.querySelector('.cbm-output').value = ((length * width * height)/1000000).toFixed(4);
    }
});

// Add/remove cargo items
document.getElementById('addCargoItem').addEventListener('click', function(){
    const first = container.querySelector('.cargo-item');
    const clone = first.cloneNode(true);
    clone.querySelectorAll('input').forEach(i=>i.value='');
    clone.querySelectorAll('select').forEach(s=>s.selectedIndex=0);
    container.appendChild(clone);
});
document.getElementById('removeCargoItem').addEventListener('click', function(){
    const items = container.querySelectorAll('.cargo-item');
    if(items.length > 1) items[items.length-1].remove();
});

// Classification filters Description
container.addEventListener('change', function(e){
    if(!e.target.classList.contains('cargo-classification')) return;
    const item = e.target.closest('.cargo-item');
    const classification = e.target.value;
    const descriptionSelect = item.querySelector('.cargo-description');
    Array.from(descriptionSelect.options).forEach(opt=>{
        if(opt.value==='') return;
        opt.style.display = (opt.dataset.classification===classification)?'block':'none';
    });
    descriptionSelect.value='';
});

// Photo confirmation
container.addEventListener('change', function(e){
    if(!e.target.classList.contains('cargo-photo')) return;

    const confirmation = e.target.closest('.cargo-item').querySelector('.photo-confirmation');

    if(e.target.files.length > 0){
        confirmation.style.display = 'inline';
        confirmation.textContent = `Photo selected: ${e.target.files[0].name}`;
    } else {
        confirmation.style.display = 'none';
        confirmation.textContent = '';
    }
});
</script>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Sophia\Documents\Capstone\resources\views/passenger/cargobooking.blade.php ENDPATH**/ ?>