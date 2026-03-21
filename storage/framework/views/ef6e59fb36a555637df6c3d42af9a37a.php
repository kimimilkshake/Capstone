<?php $__env->startSection('page-title', 'EDIT VESSEL'); ?>
<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('components.authHeader', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo $__env->make('components.admin_nav', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="admin-body">

        <div class="acs-form_container">

            <form action="<?php echo e(route('admin.vessel_update', $vessel->vessel_id)); ?>" method="POST" enctype="multipart/form-data"
                id="editVesselForm">
                <?php echo csrf_field(); ?>

                <!-- ROW 1: CODE + NAME + CAPACITY -->
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label for="vessel_code">Vessel Code <span class="text-danger">*</span></label>
                            <input type="text" id="vessel_code" name="vessel_code" value="<?php echo e($vessel->vessel_code); ?>"
                                required>
                        </div>
                    </div>

                    <div class="form-col">
                        <div class="form-group">
                            <label for="vessel_name">Vessel Name <span class="text-danger">*</span></label>
                            <input type="text" id="vessel_name" name="vessel_name" value="<?php echo e($vessel->vessel_name); ?>"
                                required>
                        </div>
                    </div>
                </div>

                <!-- ROW 2: HATCHES -->
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label class="ha-label">Hatches</label>
                            <div id="hatch-container">
                                <?php $__currentLoopData = $vessel->hatches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $hatch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="hatch-row">
                                        <div class="form-group hatch-input">
                                            <label>Hatch Label <span class="text-danger">*</span></label>
                                            <input type="text" name="hatches[<?php echo e($index); ?>][label]"
                                                value="<?php echo e($hatch->hatch_label); ?>" placeholder="Hatch Label" required>
                                        </div>
                                        <div class="form-group hatch-input">
                                            <label>Length (m) <span class="text-danger">*</span></label>
                                            <input type="number" name="hatches[<?php echo e($index); ?>][length]"
                                                value="<?php echo e($hatch->hatch_length); ?>" step="0.01" inputmode="decimal"
                                                required>
                                        </div>
                                        <div class="form-group hatch-input">
                                            <label>Width (m) <span class="text-danger">*</span></label>
                                            <input type="number" name="hatches[<?php echo e($index); ?>][width]"
                                                value="<?php echo e($hatch->hatch_width); ?>" step="0.01" inputmode="decimal"
                                                required>
                                        </div>
                                        <div class="form-group hatch-input">
                                            <label>Height (m) <span class="text-danger">*</span></label>
                                            <input type="number" name="hatches[<?php echo e($index); ?>][height]"
                                                value="<?php echo e($hatch->hatch_height); ?>" step="0.01" inputmode="decimal"
                                                required>
                                        </div>
                                        <div class="form-group hatch-input">
                                            <label>Weight Capacity (%)</label>
                                            <input type="number" name="hatches[<?php echo e($index); ?>][weight_capacity]"
                                                value="<?php echo e($hatch->hatch_weight_capacity); ?>" step="0.01"
                                                inputmode="decimal">
                                        </div>
                                        <div class="form-group hatch-input">
                                            <label>Area Capacity (m³) <span class="text-danger">*</span></label>
                                            <input type="number" name="hatches[<?php echo e($index); ?>][area_capacity]"
                                                value="<?php echo e($hatch->hatch_area_capacity); ?>" step="0.01"
                                                inputmode="decimal" required>
                                        </div>
                                        <div class="form-group hatch-input">
                                            <label>Capacity per Hold (Tons) <span class="text-danger">*</span></label>
                                            <input type="number" name="hatches[<?php echo e($index); ?>][capacity_per_hold]"
                                                value="<?php echo e($hatch->hatch_capacity_per_hold); ?>" step="0.01"
                                                inputmode="decimal" required>
                                        </div>
                                        <button type="button" class="hatch-btn add-hatch">+</button>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                                <?php if($vessel->hatches->isEmpty()): ?>
                                    <div class="hatch-row">
                                        <div class="form-group hatch-input">
                                            <label>Hatch Label <span class="text-danger">*</span></label>
                                            <input type="text" name="hatches[0][label]" placeholder="Hatch Label"
                                                required>
                                        </div>
                                        <div class="form-group hatch-input">
                                            <label>Length (m) <span class="text-danger">*</span></label>
                                            <input type="number" name="hatches[0][length]" placeholder="Length (m)"
                                                step="0.01" inputmode="decimal" required>
                                        </div>
                                        <div class="form-group hatch-input">
                                            <label>Width (m) <span class="text-danger">*</span></label>
                                            <input type="number" name="hatches[0][width]" placeholder="Width (m)"
                                                step="0.01" inputmode="decimal" required>
                                        </div>
                                        <div class="form-group hatch-input">
                                            <label>Height (m) <span class="text-danger">*</span></label>
                                            <input type="number" name="hatches[0][height]" placeholder="Height (m)"
                                                step="0.01" inputmode="decimal" required>
                                        </div>
                                        <div class="form-group hatch-input">
                                            <label>Weight Capacity (%)</label>
                                            <input type="number" name="hatches[0][weight_capacity]"
                                                placeholder="Weight Capacity (%)" step="0.01" inputmode="decimal">
                                        </div>
                                        <div class="form-group hatch-input">
                                            <label>Area Capacity (m³) <span class="text-danger">*</span></label>
                                            <input type="number" name="hatches[0][area_capacity]"
                                                placeholder="Area Capacity (m³)" step="0.01" inputmode="decimal"
                                                required>
                                        </div>
                                        <div class="form-group hatch-input">
                                            <label>Capacity per Hold (Tons) <span class="text-danger">*</span></label>
                                            <input type="number" name="hatches[0][capacity_per_hold]"
                                                placeholder="Capacity Per Hold (Tons)" step="0.01" inputmode="decimal"
                                                required>
                                        </div>
                                        <button type="button" class="hatch-btn add-hatch">+</button>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ROW 3: ACCOMMODATIONS -->
                <div class="form-row">
                    <div class="form-col">
                        <label class="ha-label">Accommodations</label>
                        <div id="accommodation-container">
                            <?php $__currentLoopData = $vessel->accommodations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $acc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="accommodation-row">
                                    <div class="form-group acc-input">
                                        <label>Name <span class="text-danger">*</span></label>
                                        <input type="text" name="accommodations[<?php echo e($index); ?>][name]"
                                            value="<?php echo e($acc->accommodation_name); ?>" placeholder="Accommodation Name"
                                            required>
                                    </div>
                                    <div class="form-group acc-input">
                                        <label>Regular Price <span class="text-danger">*</span></label>
                                        <input type="number" name="accommodations[<?php echo e($index); ?>][price]"
                                            value="<?php echo e($acc->accommodation_regular_price); ?>" step="0.01"
                                            inputmode="decimal" required>
                                    </div>
                                    <div class="form-group acc-input">
                                        <label>Cot Range <span class="text-danger">*</span></label>
                                        <input type="text" name="accommodations[<?php echo e($index); ?>][cot_range]"
                                            value="<?php echo e($acc->accommodation_cot_range); ?>"
                                            placeholder="Cot Range (e.g., 1-5, 7-10)" required>
                                    </div>
                                    <div class="form-group acc-input">
                                        <label>Cot Plan Image <em style="color: #888; font-size: 0.8em;">jpg, jpeg, png
                                                only</em></label>
                                        <?php if($acc->accommodation_cot_plan_url): ?>
                                            <img src="<?php echo e(asset('storage/' . $acc->accommodation_cot_plan_url)); ?>"
                                                alt="Cot Plan"
                                                style="max-height: 80px; object-fit: contain; border: 1px solid #ccc; border-radius: 4px; margin-bottom: 4px; display: block;">
                                        <?php endif; ?>
                                        <input type="hidden"
                                            name="accommodations[<?php echo e($index); ?>][existing_cot_plan]"
                                            value="<?php echo e($acc->accommodation_cot_plan_url); ?>">
                                        <input type="file" name="accommodations[<?php echo e($index); ?>][cot_plan]"
                                            accept="image/jpeg,image/jpg,image/png">
                                    </div>
                                    <button type="button" class="accommodation-btn add-accommodation">+</button>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                            <?php if($vessel->accommodations->isEmpty()): ?>
                                <div class="accommodation-row">
                                    <div class="form-group acc-input">
                                        <label>Name <span class="text-danger">*</span></label>
                                        <input type="text" name="accommodations[0][name]"
                                            placeholder="Accommodation Name" required>
                                    </div>
                                    <div class="form-group acc-input">
                                        <label>Regular Price <span class="text-danger">*</span></label>
                                        <input type="number" name="accommodations[0][price]" placeholder="Regular Price"
                                            step="0.01" inputmode="decimal" required>
                                    </div>
                                    <div class="form-group acc-input">
                                        <label>Cot Range <span class="text-danger">*</span></label>
                                        <input type="text" name="accommodations[0][cot_range]"
                                            placeholder="Cot Range (e.g., 1-5, 7-10)" required>
                                    </div>
                                    <div class="form-group acc-input">
                                        <label>Cot Plan Image <em style="color: #888; font-size: 0.8em;">jpg, jpeg, png
                                                only</em></label>
                                        <input type="file" name="accommodations[0][cot_plan]"
                                            accept="image/jpeg,image/jpg,image/png">
                                    </div>
                                    <button type="button" class="accommodation-btn add-accommodation">+</button>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- ROW 4: STATUS -->
                <div class="form-row" style="display: flex; gap: 2rem; align-items: flex-start; width: 100%;">
                    <div class="form-col" style="flex: 1;">
                        <div class="form-group" style="width: 100%;">
                            <label for="vessel_status" style="margin-bottom: 8px;">Status <span
                                    class="text-danger">*</span></label>
                            <select name="vessel_status" id="vessel_status" required
                                style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 8px;">
                                <option value="Active" <?php echo e($vessel->vessel_status === 'Active' ? 'selected' : ''); ?>>Active
                                </option>
                                <option value="Inactive" <?php echo e($vessel->vessel_status === 'Inactive' ? 'selected' : ''); ?>>
                                    Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- ACTION BUTTONS -->
                <div class="form-actions" style="display: flex; gap: 1rem; justify-content: center; margin-top: 1.5rem;">
                    <button type="submit" class="acs-add-btn" id="saveEditBtn">
                        <i class="fa-solid fa-save me-2"></i>Update Vessel
                    </button>
                    <a href="<?php echo e(route('admin.vessel_list')); ?>" class="acs-add-btn acs-cancel-btn">
                        <i class="fa-solid fa-xmark me-2"></i>Cancel
                    </a>
                </div>

            </form>
        </div>
    </div>

    <script src="<?php echo e(asset('js/vessel.js')); ?>"></script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Sophia\Documents\Capstone\resources\views/authorized/admin/vessel_edit.blade.php ENDPATH**/ ?>