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
                            <input type="number" step="0.0001" name="cbm[]" value="<?php echo e(old('cbm.'.$index, $cargo->cbm ?? 0)); ?>" placeholder="0.0000" class="cargo-cbm" data-index="<?php echo e($index); ?>" readonly>
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
    function calculateCBM(index) {
        const lengthInputs = document.querySelectorAll('input[name="length[]"]');
        const widthInputs = document.querySelectorAll('input[name="width[]"]');
        const heightInputs = document.querySelectorAll('input[name="height[]"]');
        const unitSelects = document.querySelectorAll('select[name="measurement_unit[]"]');
        const cbmInputs = document.querySelectorAll('input[name="cbm[]"]');

        let length = lengthInputs[index] ? (parseFloat(lengthInputs[index].value) || 0) : 0;
        let width = widthInputs[index] ? (parseFloat(widthInputs[index].value) || 0) : 0;
        let height = heightInputs[index] ? (parseFloat(heightInputs[index].value) || 0) : 0;
        const selectedUnitOption = unitSelects[index]
            ? unitSelects[index].options[unitSelects[index].selectedIndex]
            : null;
        const unit = selectedUnitOption
            ? (selectedUnitOption.dataset.unitAbbrev || 'cm').toLowerCase()
            : 'cm';

        if (unit === 'in') {
            length *= 2.54;
            width *= 2.54;
            height *= 2.54;
        } else if (unit === 'mm') {
            length *= 0.1;
            width *= 0.1;
            height *= 0.1;
        } else if (unit === 'm') {
            length *= 100;
            width *= 100;
            height *= 100;
        }

        const cbm = (length * width * height) / 1000000;
        if (cbmInputs[index]) {
            cbmInputs[index].value = cbm.toFixed(4);
        }

        return cbm;
    }

    function calculateValues() {
        let totalValue = 0;
        const descriptionSelects = document.querySelectorAll('select[name="description[]"]');
        const quantityInputs = document.querySelectorAll('input[name="quantity[]"]');

        descriptionSelects.forEach((descriptionSelect, index) => {
            const selectedOption = descriptionSelect.options[descriptionSelect.selectedIndex];
            const quantity = quantityInputs[index] ? (parseFloat(quantityInputs[index].value) || 0) : 0;
            const cbm = calculateCBM(index);
            const freight = selectedOption ? (parseFloat(selectedOption.dataset.freight) || 0) : 0;

            totalValue += freight * cbm * quantity;
        });

        const totalDisplay = document.getElementById('totalValueDisplay');
        const totalInput = document.getElementById('totalValueInput');
        if (totalDisplay) totalDisplay.textContent = '₱' + totalValue.toFixed(2);
        if (totalInput) totalInput.value = totalValue.toFixed(2);
    }

    document.addEventListener('DOMContentLoaded', function() {
        calculateValues();
        document.querySelectorAll('select[name="description[]"]').forEach(input => {
            input.addEventListener('change', calculateValues);
        });
        document.querySelectorAll('select[name="measurement_unit[]"]').forEach(input => {
            input.addEventListener('change', calculateValues);
        });
        document.querySelectorAll('input[name="length[]"], input[name="width[]"], input[name="height[]"]').forEach(input => {
            input.addEventListener('change', calculateValues);
            input.addEventListener('input', calculateValues);
        });
        document.querySelectorAll('input[name="cbm[]"]').forEach(input => {
            input.addEventListener('change', calculateValues);
            input.addEventListener('input', calculateValues);
        });
        document.querySelectorAll('input[name="quantity[]"]').forEach(input => {
            input.addEventListener('change', calculateValues);
            input.addEventListener('input', calculateValues);
        });
    });

</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Shem\Desktop\Capstone\resources\views/authorized/staff/editcargo.blade.php ENDPATH**/ ?>