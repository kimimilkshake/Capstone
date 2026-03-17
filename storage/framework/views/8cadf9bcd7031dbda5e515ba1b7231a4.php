<?php $__env->startSection('page-title', 'STAFF'); ?>
<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('components.authHeader', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?> 
    <?php echo $__env->make('components.admin_nav', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?> 

<div class="admin-body">
  <div class="acs-title">
    <h3>CREATE STAFF</h3>
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

    <form action="<?php echo e(route('admin.storeStaff')); ?>" method="POST" class="create-staff-form">
      <?php echo csrf_field(); ?>
      <div class="form-row">
        <div class="form-col">
          <div class="form-group">
            <label for="staff_name">Full Name <span class="text-danger">*</span></label>
            <input type="text" id="staff_name" name="staff_name" required>
          </div>

          <div class="form-group">
            <label for="staff_gender">Gender <span class="text-danger">*</span></label>
            <select id="staff_gender" name="staff_gender" required>
              <option value="">Select</option>
              <option value="M">Male</option>
              <option value="F">Female</option>
            </select>
          </div>

          <div class="form-group">
            <label for="staff_dob">Date of Birth <span class="text-danger">*</span></label>
            <input type="date" id="staff_dob" name="staff_dob" required>
          </div>
        </div>

        <!-- Column 2 -->
        <div class="form-col">
          <div class="form-group">
            <label for="staff_email">Email <span class="text-danger">*</span></label>
            <input type="email" id="staff_email" name="staff_email" required>
          </div>

          <div class="form-group">
            <label for="staff_user">Username <span class="text-danger">*</span></label>
            <input type="text" id="staff_user" name="staff_user" required>
          </div>

          <div class="form-group">
            <label for="staff_password">Password <span class="text-danger">*</span></label>
            <input type="password" id="staff_password" name="staff_password" required>
          </div>
        </div>
      </div>

      <div class="form-actions">
        <button type="submit" class="acs-add-btn"><i class="fa-solid fa-plus me-2"></i>Add Staff</button>
      </div>
    </form>
  </div>
  
</div>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Sophia\Documents\Capstone\resources\views/authorized/admin/create_staff.blade.php ENDPATH**/ ?>