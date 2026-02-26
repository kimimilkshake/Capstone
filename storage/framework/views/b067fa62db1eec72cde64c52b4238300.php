<?php $__env->startSection('page-title', 'EDIT CARGO BOOKING'); ?>
<?php $__env->startSection('content'); ?>
<?php echo $__env->make('components.authHeader', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php echo $__env->make('components.staff_nav', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<div class="staff-body">
    <div class="svl-title">
        <h3>EDIT CARGO ITEMS</h3>
    </div>

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

    
    
    
    <form action="<?php echo e(route('cargo.bookings.update', $booking->booking_ref_no)); ?>" method="POST">
        <?php echo csrf_field(); ?>
        <?php echo method_field('PUT'); ?>

        <?php $__currentLoopData = $booking->cargoBookings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $cargo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="card shadow-sm p-4 mb-4">
                <h5>Cargo Item #<?php echo e($index + 1); ?></h5>

                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label>Classification</label>
                            <select name="classification[]" required>
                                <option value="">Select Classification</option>
                                <?php $__currentLoopData = $cargoClassifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $classification): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($classification->cargo_classification_name); ?>"
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
                            <label>Length (cm)</label>
                            <input type="number" step="0.01" name="length[]" value="<?php echo e(old('length.'.$index, $cargo->length)); ?>" required <?php if($cargo->cargoItem && $cargo->cargoItem->cargo_item_measure_required == 'Yes'): ?> readonly <?php endif; ?>>
                        </div>
                    </div>

                    <div class="form-col">
                        <div class="form-group">
                            <label>Width (cm)</label>
                            <input type="number" step="0.01" name="width[]" value="<?php echo e(old('width.'.$index, $cargo->width)); ?>" required <?php if($cargo->cargoItem && $cargo->cargoItem->cargo_item_measure_required == 'Yes'): ?> readonly <?php endif; ?>>
                        </div>
                    </div>

                    <div class="form-col">
                        <div class="form-group">
                            <label>Height (cm)</label>
                            <input type="number" step="0.01" name="height[]" value="<?php echo e(old('height.'.$index, $cargo->height)); ?>" required <?php if($cargo->cargoItem && $cargo->cargoItem->cargo_item_measure_required == 'Yes'): ?> readonly <?php endif; ?>>
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
                            <input type="number" step="0.0001" name="cbm[]" value="<?php echo e(old('cbm.'.$index, $cargo->cbm ?? 0)); ?>" placeholder="0.0000">
                        </div>
                    </div>

                    <div class="form-col">
                        <div class="form-group">
                            <label>Rate (per unit)</label>
                            <input type="number" step="0.01" name="rate[]" value="<?php echo e(old('rate.'.$index, $cargo->rate ?? 0)); ?>" placeholder="0.00" class="cargo-rate" data-index="<?php echo e($index); ?>">
                        </div>
                    </div>

                    <div class="form-col">
                        <div class="form-group">
                            <label>Value per Item</label>
                            <input type="number" step="0.01" name="value_per_item[]" value="<?php echo e(old('value_per_item.'.$index, $cargo->value_per_item ?? 0)); ?>" placeholder="0.00" class="cargo-value-per-item" data-index="<?php echo e($index); ?>" readonly style="background-color: #f5f5f5;">
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
            <button type="submit" class="btn btn-primary">Update Cargo Items</button>
            <a href="<?php echo e(route('cargo.bookings.show', $booking->booking_ref_no)); ?>" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<script>
    function calculateValues() {
        let totalValue = 0;
        const valueInputs = document.querySelectorAll('.cargo-value-per-item');
        document.querySelectorAll('.cargo-rate').forEach((rateInput, index) => {
            const quantityInput = document.querySelector(`input[name="quantity[${index}]"]`);
            const freightInput = document.querySelector(`input[name="rate[${index}]"]`);
            const arrastreInput = document.querySelector(`input[name="arrastre[${index}]"]`);
            const valueInput = document.querySelector(`.cargo-value-per-item[data-index="${index}"]`);
            let freight = 0, arrastre = 0;
            if (freightInput) freight = parseFloat(freightInput.value) || 0;
            if (arrastreInput) arrastre = parseFloat(arrastreInput.value) || 0;
            if (quantityInput && valueInput) {
                const quantity = parseFloat(quantityInput.value) || 0;
                const value = (freight + arrastre) * quantity;
                valueInput.value = value.toFixed(2);
                totalValue += value;
            }
        });
        const totalDisplay = document.getElementById('totalValueDisplay');
        const totalInput = document.getElementById('totalValueInput');
        if (totalDisplay) totalDisplay.textContent = '₱' + totalValue.toFixed(2);
        if (totalInput) totalInput.value = totalValue.toFixed(2);
    }
    document.addEventListener('DOMContentLoaded', function() {
        calculateValues();
        document.querySelectorAll('.cargo-rate').forEach(input => {
            input.addEventListener('change', calculateValues);
            input.addEventListener('input', calculateValues);
        });
        document.querySelectorAll('input[name^="quantity["]').forEach(input => {
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
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Shem\Desktop\Capstone\resources\views/authorized/staff/editcargo.blade.php ENDPATH**/ ?>