<?php $__env->startSection('page-title', 'CREATE VESSEL'); ?>
<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('components.authHeader', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo $__env->make('components.admin_nav', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="admin-body">

        <div class="acs-form_container">

            <form action="<?php echo e(route('admin.store_vessel')); ?>" method="POST" enctype="multipart/form-data"
                id="createVesselForm">
                <?php echo csrf_field(); ?>

                <!-- ROW 1: CODE + NAME + CAPACITY -->
                <div class="form-row">

                    <div class="form-col">
                        <div class="form-group">
                            <label for="vessel_code">Vessel Code <span class="text-danger">*</span></label>
                            <input type="text" id="vessel_code" name="vessel_code" placeholder="Enter Vessel Code"
                                required>
                        </div>
                    </div>

                    <div class="form-col">
                        <div class="form-group">
                            <label for="vessel_name">Vessel Name <span class="text-danger">*</span></label>
                            <input type="text" id="vessel_name" name="vessel_name" placeholder="Enter Vessel Name"
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

                                <div class="hatch-row">

                                    <div class="form-group hatch-input">
                                        <label>Hatch Label <span class="text-danger">*</span></label>
                                        <input type="text" name="hatches[0][label]" placeholder="Hatch Label" min="1" required>
                                    </div>

                                    <div class="form-group hatch-input">
                                        <label>Length (m) <span class="text-danger">*</span></label>
                                        <input type="number" name="hatches[0][length]" placeholder="Length (m)"
                                            step="0.01" inputmode="decimal" min="1" required>
                                    </div>

                                    <div class="form-group hatch-input">
                                        <label>Width (m) <span class="text-danger">*</span></label>
                                        <input type="number" name="hatches[0][width]" placeholder="Width (m)"
                                            step="0.01" inputmode="decimal" min="1" required>
                                    </div>

                                    <div class="form-group hatch-input">
                                        <label>Height (m) <span class="text-danger">*</span></label>
                                        <input type="number" name="hatches[0][height]" placeholder="Height (m)"
                                            step="0.01" inputmode="decimal" min="1" required>
                                    </div>

                                    <div class="form-group hatch-input">
                                        <label>Weight Capacity (%)</label>
                                        <input type="number" name="hatches[0][weight_capacity]"
                                            placeholder="Weight Capacity (%)" step="0.01" inputmode="decimal">
                                    </div>

                                    <div class="form-group hatch-input">
                                        <label>Area Capacity (m³) <span class="text-danger">*</span></label>
                                        <input type="number" name="hatches[0][area_capacity]"
                                            placeholder="Area Capacity (  )" step="0.01" inputmode="decimal" min="1" required>
                                    </div>

                                    <div class="form-group hatch-input">
                                        <label>Hold Capacity (Tons) <span class="text-danger">*</span></label>
                                        <input type="number" name="hatches[0][capacity_per_hold]"
                                            placeholder="Capacity Per Hold (Tons)" step="0.01" inputmode="decimal" min="1"
                                            required>
                                    </div>

                                    <button type="button" class="hatch-btn add-hatch">+</button>
                                </div>

                            </div>

                        </div>
                    </div>
                </div>

                <!-- ROW 3: ACCOMMODATIONS -->
                <div class="form-row">
                    <div class="form-col">
                        <label class="ha-label">Accommodations</label>

                        <div id="accommodation-container">

                            <div class="accommodation-row">

                                <div class="form-group acc-input">
                                    <label>Accommodation Label <span class="text-danger">*</span></label>
                                    <input type="text" name="accommodations[0][name]" placeholder="Accommodation Name"
                                        required>
                                </div>

                                <div class="form-group acc-input">
                                    <label>Regular Price <span class="text-danger">*</span></label>
                                    <input type="number" name="accommodations[0][price]" placeholder="Regular Price"
                                        step="0.01" inputmode="decimal" min="1" required>
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

                        </div>

                    </div>
                </div>

                <!-- ACTION BUTTONS -->
                <div class="form-actions" style="display: flex; gap: 1rem; justify-content: center; margin-top: 1.5rem;">
                    <button type="submit" class="acs-add-btn">
                        <i class="fa-solid fa-plus me-2"></i>ADD
                    </button>
                    <a href="<?php echo e(route('admin.vessel_list')); ?>" class="acs-add-btn acs-cancel-btn">
                        <i class="fa-solid fa-xmark me-2"></i>CANCEL
                    </a>
                </div>

            </form>

        </div>
    </div>

    <script src="<?php echo e(asset('js/vessel.js')); ?>"></script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Shem\Desktop\Capstone\resources\views/authorized/admin/create_vessel.blade.php ENDPATH**/ ?>