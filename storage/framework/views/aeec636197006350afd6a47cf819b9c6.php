<?php $__env->startSection('page-title', 'EDIT STAFF MEMBER'); ?>
<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('components.authHeader', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo $__env->make('components.admin_nav', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <div class="admin-body">

        <div class="acs-form_container">

            <form action="<?php echo e(route('admin.staff_update', $staff->staff_id)); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>

                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label for="staff_name">Name <span class="text-danger">*</span></label>
                            <input type="text" name="staff_name" id="staff_name" value="<?php echo e(old('staff_name', $staff->staff_name)); ?>" maxlength="50" required>
                            <?php $__errorArgs = ['staff_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <span class="error"><?php echo e($message); ?></span>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div class="form-group">
                            <label for="staff_user">Username <span class="text-danger">*</span></label>
                            <input type="text" name="staff_user" id="staff_user" value="<?php echo e(old('staff_user', $staff->staff_user)); ?>" maxlength="10" required>
                            <?php $__errorArgs = ['staff_user'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <span class="error"><?php echo e($message); ?></span>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div class="form-group">
                            <label for="staff_dob">Date of Birth <span class="text-danger">*</span></label>
                            <input type="date" name="staff_dob" id="staff_dob" value="<?php echo e(old('staff_dob', $staff->staff_dob)); ?>" required>
                            <?php $__errorArgs = ['staff_dob'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <span class="error"><?php echo e($message); ?></span>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label for="staff_gender">Gender <span class="text-danger">*</span></label>
                            <select name="staff_gender" id="staff_gender" required>
                                <option value="M" <?php echo e((old('staff_gender', $staff->staff_gender) == 'M') ? 'selected' : ''); ?>>Male</option>
                                <option value="F" <?php echo e((old('staff_gender', $staff->staff_gender) == 'F') ? 'selected' : ''); ?>>Female</option>
                            </select>
                            <?php $__errorArgs = ['staff_gender'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <span class="error"><?php echo e($message); ?></span>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div class="form-group">
                            <label for="staff_email">Email Address <span class="text-danger">*</span></label>
                            <input type="email" name="staff_email" id="staff_email" value="<?php echo e(old('staff_email', $staff->staff_email)); ?>" required>
                            <?php $__errorArgs = ['staff_email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <span class="error"><?php echo e($message); ?></span>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div class="form-group">
                            <label for="staff_status">Status <span class="text-danger">*</span></label>
                            <select name="staff_status" id="staff_status" required>
                                <option value="Active" <?php echo e((old('staff_status', $staff->staff_status) == 'Active') ? 'selected' : ''); ?>>Active</option>
                                <option value="Inactive" <?php echo e((old('staff_status', $staff->staff_status) == 'Inactive') ? 'selected' : ''); ?>>Inactive</option>
                            </select>
                            <?php $__errorArgs = ['staff_status'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <span class="error"><?php echo e($message); ?></span>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                    </div>
                </div>

                <br>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary update-btn" id="saveEditBtn">Update Staff</button>
                    <a href="<?php echo e(route('admin.staff_list')); ?>" class="btn btn-secondary cancel-btn">Cancel</a>
                </div>
            </form>

        </div>

    </div>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Sophia\Documents\Capstone\resources\views/authorized/admin/staff_edit.blade.php ENDPATH**/ ?>