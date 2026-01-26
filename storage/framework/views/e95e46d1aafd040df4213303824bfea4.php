<?php $__env->startSection('page-title', 'CARGO'); ?>
<?php $__env->startSection('content'); ?>
  <?php echo $__env->make('components.authHeader', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
  <?php echo $__env->make('components.staff_nav', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
  <div class="staff-body">
    <div class="svl-title">
      <h3>EDIT CARGO ITEM</h3>
    </div>

    <div class="aci-form_container">
      <?php if(session('success')): ?>
        <div class="alert alert-success"><?php echo e(session('success')); ?></div>
      <?php endif; ?>
      <form action="<?php echo e(route('staff.cargo_item_update', $cargo_item->cargo_item_id)); ?>" method="POST">
        <?php echo csrf_field(); ?>
        <?php echo method_field('PUT'); ?>
        <div class="form-row">
          <div class="form-col">
            <div class="form-group">
              <label>Classification <span class="text-danger">*</span></label>
              <input type="text" name="cargo_item_classification" value="<?php echo e(old('cargo_item_classification', $cargo_item->cargo_item_classification)); ?>" required>
            </div>
          </div>
          
          <div class="form-col">
            <div class="form-group">
              <label>Description <span class="text-danger">*</span></label>
              <input type="text" name="cargo_item_description" value="<?php echo e(old('cargo_item_description', $cargo_item->cargo_item_description)); ?>"  required>
            </div>
          </div>
        </div>

        <div class="form-row">
          <div class="form-col">
            <div class="form-group">
              <label>Freight <span class="text-danger">*</span></label>
              <input type="number" name="cargo_item_freight" value="<?php echo e(old('cargo_item_freight', $cargo_item->cargo_item_freight)); ?>"  required>
            </div>
          </div>

          <div class="form-col">
            <div class="form-group">
              <label>Arrastre <span class="text-danger">*</span></label>
              <input type="number" name="cargo_item_arrastre" value="<?php echo e(old('cargo_item_arrastre', $cargo_item->cargo_item_arrastre)); ?>" required>
            </div>
          </div>

          <div class="form-col">
              <div class="form-group">
                  <label>Route Destination <span class="text-danger">*</span></label>
                  <select name="route_port_id" required>
                      <option value="">Select Destination</option>
                      <?php $__currentLoopData = $routes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $route): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                          <option value="<?php echo e($route->route_port_id); ?>"
                              <?php echo e($cargo_item->route_port_id == $route->route_port_id ? 'selected' : ''); ?>>
                              <?php echo e($route->route_destination); ?>

                          </option>
                      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                  </select>
              </div>
          </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Update Cargo Item</button>
            <a href="<?php echo e(route('staff.cargo_item_list')); ?>" class="btn btn-secondary">Cancel</a>
        </div>
      </form>
    </div>
  </div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\kirzt\Documents\GitHub\Capstone\resources\views/authorized/staff/scargo_item_edit.blade.php ENDPATH**/ ?>