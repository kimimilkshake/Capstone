<?php $__env->startSection('page-title', 'CARGO'); ?>
<?php $__env->startSection('content'); ?>
  <?php echo $__env->make('components.authHeader', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
  <?php echo $__env->make('components.staff_nav', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
  <div class="staff-body">
    <div class="svl-title">
      <h3>CREATE CARGO ITEM</h3>
    </div>

    <div class="aci-form_container">

      
      <?php if($errors->any()): ?>
        <div class="alert alert-danger">
          <strong>All fields are required.</strong><br>
          <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php echo e($error); ?><br>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
      <?php endif; ?>

      
      <?php if(session('success')): ?>
        <div class="alert alert-success">
          <?php echo e(session('success')); ?>

        </div>
      <?php endif; ?>

      <form action="<?php echo e(route('staff.store_cargo_item')); ?>" method="POST" class="create-cargoitem-form">
        <?php echo csrf_field(); ?>

        <div class="form-row">
          <div class="form-col">
            <div class="form-group">
              <label>Classification <span class="text-danger">*</span></label>
              <input type="text" name="cargo_item_classification" required>
            </div>
          </div>
          
          <div class="form-col">
            <div class="form-group">
              <label>Description <span class="text-danger">*</span></label>
              <input type="text" name="cargo_item_description" required>
            </div>
          </div>
        </div>

        <div class="form-row">
          <div class="form-col">
            <div class="form-group">
              <label>Freight <span class="text-danger">*</span></label>
              <input type="number" name="cargo_item_freight" step="0.01" required>
            </div>
          </div>

          <div class="form-col">
            <div class="form-group">
              <label>Arrastre <span class="text-danger">*</span></label>
              <input type="number" name="cargo_item_arrastre" step="0.01" required>
            </div>
          </div>

          <div class="form-col">
            <div class="form-group">
              <label>Route Destination <span class="text-danger">*</span></label>
              <select name="route_port_id" required>
                <option value="">Select Destination</option>
                <?php $__currentLoopData = $routes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $route): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                  <option value="<?php echo e($route->route_port_id); ?>">
                    <?php echo e($route->route_destination); ?>

                  </option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
              </select>
            </div>
          </div>
        </div>

        

        <div class="form-actions">
          <button type="submit" class="acs-add-btn">
            <i class="fa-solid fa-plus me-2"></i> Add Cargo Item
          </button>
        </div>
      </form>
    </div>
  </div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Sophia\Documents\Capstone\resources\views/authorized/staff/screate_cargo_item.blade.php ENDPATH**/ ?>