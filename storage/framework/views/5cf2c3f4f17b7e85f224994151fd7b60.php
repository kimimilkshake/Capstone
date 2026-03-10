<?php $__env->startSection('page-title', 'PROMO'); ?>
<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('components.authHeader', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo $__env->make('components.admin_nav', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="admin-body">
      <div class="apl-title">
        <h3>EDIT PROMO</h3>
      </div>

      <div class="acs-form_container">
        <?php if($errors->any()): ?>
          <div class="alert alert-danger" style="color: red; text-align: center;">
            <strong>All fields are required.</strong><br>
            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <?php echo e($error); ?><br>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </div>
        <?php endif; ?>

        <?php if(session('success')): ?>
          <div class="alert alert-success" style="color: green; text-align: center; margin-bottom: 1rem;">
            <?php echo e(session('success')); ?>

          </div>
        <?php endif; ?>

        <form action="<?php echo e(route('admin.promo_update', $promo->promo_id)); ?>" method="POST" class="create-promo-form">
          <?php echo csrf_field(); ?>
          <?php echo method_field('PUT'); ?>

          <div class="form-row">
            <!-- Column 1 -->
            <div class="form-col">
              <div class="form-group">
                <label for="promo_name">Promo Name <span class="text-danger">*</span></label>
                <input type="text" id="promo_name" name="promo_name" value="<?php echo e($promo->promo_name); ?>" required>
              </div>

              <div class="form-group">
                <label for="promo_code">Promo Code <span class="text-danger">*</span></label>
                <input type="text" id="promo_code" name="promo_code" value="<?php echo e($promo->promo_code); ?>" required>
              </div>

              <div class="form-group">
                <label for="promo_discount_rate">Discount Rate</label>
                <input type="number" id="promo_discount_rate" name="promo_discount_rate" value="<?php echo e($promo->promo_discount_rate); ?>" required>
              </div>
            </div>

            <!-- Column 2 -->
            <div class="form-col">
              <div class="form-group">
                <label for="promo_start_date">Date Start <span class="text-danger">*</span></label>
                <input type="date" id="promo_start_date" name="promo_start_date" value="<?php echo e($promo->promo_start_date); ?>" required>
              </div>

              <div class="form-group">
                <label for="promo_end_date">Date End <span class="text-danger">*</span></label>
                <input type="date" id="promo_end_date" name="promo_end_date" value="<?php echo e($promo->promo_end_date); ?>" required>
              </div>

                <div class="form-group" style="flex: 1;">
                  <label for="promo_status">Status <span class="text-danger">*</span></label>
                  <select id="promo_status" name="promo_status" required>
                    <option value="Active" <?php echo e($promo->promo_status == 'Active' ? 'selected' : ''); ?>>Active</option>
                    <option value="Inactive" <?php echo e($promo->promo_status == 'Inactive' ? 'selected' : ''); ?>>Inactive</option>
                  </select>
              </div>
            </div>
          </div>

          <!-- Description & Status side by side -->
          <div class="form-row" style="display: flex; gap: 1rem;">
            <div class="form-group" style="flex: 1;">
              <label for="promo_description">Promo Description <span class="text-danger">*</span></label>
              <textarea id="promo_description" name="promo_description" required><?php echo e($promo->promo_description); ?></textarea>
            </div>

            
          </div>

          <div class="form-actions" style="display: flex; gap: 1rem; justify-content: center; margin-top: 1.5rem;">
            <button type="submit" class="acs-add-btn">
              <i class="fa-solid fa-floppy-disk me-2"></i>Save Changes
            </button>
            <a href="<?php echo e(route('admin.promo_list')); ?>" class="acs-add-btn acs-cancel-btn">
              <i class="fa-solid fa-xmark me-2"></i>Cancel
            </a>
          </div>
        </form>
      </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Sophia\Documents\Capstone\resources\views/authorized/admin/promo_edit.blade.php ENDPATH**/ ?>