<?php $__env->startSection('page-title', 'EDIT CARGO ITEM'); ?>
<?php $__env->startSection('content'); ?>
  <?php echo $__env->make('components.authHeader', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
  <?php echo $__env->make('components.staff_nav', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
  <div class="staff-body">
    <div class="svl-title">
      <h3>EDIT CARGO ITEM</h3>
    </div>

    <div class="aci-form_container">
      <?php if(session('success')): ?>
          <div class="alert alert-success text-center mx-auto w-75" role="alert"><?php echo e(session('success')); ?></div>
      <?php endif; ?>
      <?php if($errors->any()): ?>
          <div class="alert alert-danger text-center">
              <ul>
                  <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                      <li><?php echo e($error); ?></li>
                  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
              </ul>
          </div>
      <?php endif; ?>
      <form action="<?php echo e(route('staff.cargo_item_update', $cargo_item->cargo_item_id)); ?>" method="POST">
        <?php echo csrf_field(); ?>
        <?php echo method_field('PUT'); ?>
        <div class="form-row"> <!-- First Row -->
          <div class="form-col">
            <div class="form-group">
              <label>Route Code <span class="text-danger">*</span></label>
              <select name="route_code_id" required>
                <?php $__currentLoopData = $route_codes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $routeCode): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                  <option value="<?php echo e($routeCode->route_code_id); ?>" <?php echo e($cargo_item->route_code_id == $routeCode->route_code_id ? 'selected' : ''); ?>>
                    <?php echo e($routeCode->route_code_name); ?>

                  </option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
              </select>
            </div>
          </div>
          
          <div class="form-col">
            <div class="form-group">
              <label>Category <span class="text-danger">*</span></label>
              <select name="cargo_category_id" required>
                <?php $__currentLoopData = $cargo_categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                  <option value="<?php echo e($category->cargo_category_id); ?>" <?php echo e($cargo_item->cargo_category_id == $category->cargo_category_id ? 'selected' : ''); ?>>
                    <?php echo e($category->cargo_category_name); ?>

                  </option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
              </select>
            </div>
          </div>

          <div class="form-col">
            <div class="form-group">
              <label>Description <span class="text-danger">*</span></label>
              <input type="text" name="cargo_item_description" value="<?php echo e(old('cargo_item_description', $cargo_item->cargo_item_description)); ?>"  required>
            </div>
          </div>

          <div class="form-col">
            <div class="form-group">
              <label>Freight <span class="text-danger">*</span></label>
              <input type="number" name="cargo_item_freight" value="<?php echo e(old('cargo_item_freight', $cargo_item->cargo_item_freight)); ?>" step="0.01" min="0"  required>
            </div>
          </div>

          
        </div>

        <div class="form-row"> <!-- Second Row -->
          
          <div class="form-col">
            <div class="form-group inline-radio">
              <label class="inline-label">
                With Measurement Range? <span class="text-danger">*</span>
              </label>

              <div class="radio-group">
                <label class="radio-option">
                  <input type="radio" name="cargo_item_measure_required" value="Yes"
                    <?php echo e($cargo_item->cargo_item_measure_required == 'Yes' ? 'checked' : ''); ?> required>
                  <span>Yes</span>
                </label>

                <label class="radio-option">
                  <input type="radio" name="cargo_item_measure_required" value="No"
                    <?php echo e($cargo_item->cargo_item_measure_required == 'No' ? 'checked' : ''); ?>>
                  <span>No</span>
                </label>
              </div>
            </div>
          </div>
        </div>

        
        <div id="measurement-section" style="<?php echo e($cargo_item->cargo_item_measure_required == 'Yes' ? 'display:block;' : 'display:none;'); ?>">
          <div class="form-row">
            <div class="form-col">
              <div class="form-group">
                <label>Min Length</label>
                <input type="number" step="0.01" name="cargo_item_min_length" 
                       value="<?php echo e(old('cargo_item_min_length', $cargo_item->cargo_item_min_length)); ?>">
              </div>
            </div>

            <div class="form-col">
              <div class="form-group">
                <label>Min Width</label>
                <input type="number" step="0.01" name="cargo_item_min_width" 
                       value="<?php echo e(old('cargo_item_min_width', $cargo_item->cargo_item_min_width)); ?>">
              </div>
            </div>

            <div class="form-col">
              <div class="form-group">
                <label>Min Height</label>
                <input type="number" step="0.01" name="cargo_item_min_height" 
                       value="<?php echo e(old('cargo_item_min_height', $cargo_item->cargo_item_min_height)); ?>">
              </div>
            </div>

            <div class="form-col">
              <div class="form-group">
                <label>Max Length</label>
                <input type="number" step="0.01" name="cargo_item_max_length" 
                       value="<?php echo e(old('cargo_item_max_length', $cargo_item->cargo_item_max_length)); ?>">
              </div>
            </div>

            <div class="form-col">
              <div class="form-group">
                <label>Max Width</label>
                <input type="number" step="0.01" name="cargo_item_max_width" 
                       value="<?php echo e(old('cargo_item_max_width', $cargo_item->cargo_item_max_width)); ?>">
              </div>
            </div>

            <div class="form-col">
              <div class="form-group">
                <label>Max Height</label>
                <input type="number" step="0.01" name="cargo_item_max_height" 
                       value="<?php echo e(old('cargo_item_max_height', $cargo_item->cargo_item_max_height)); ?>">
              </div>
            </div>

            <div class="form-col">
              <div class="form-group">
                <label>Unit</label>
                <select name="measurement_unit_id">
                  <option value="">Select Unit</option>
                  <?php $__currentLoopData = $measurement_units; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $unit): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($unit->measurement_unit_id); ?>"
                      <?php echo e($cargo_item->measurement_unit_id == $unit->measurement_unit_id ? 'selected' : ''); ?>>
                      <?php echo e($unit->measurement_unit_name); ?>

                    </option>
                  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
              </div>
            </div>

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

<script>
document.addEventListener('DOMContentLoaded', function () {
  const radios = document.querySelectorAll('input[name="cargo_item_measure_required"]');
  const section = document.getElementById('measurement-section');

  radios.forEach(radio => {
    radio.addEventListener('change', function () {
      if (this.value === 'Yes') {
        section.style.display = 'block';
        section.querySelectorAll('input, select').forEach(el => el.required = true);
      } else {
        section.style.display = 'none';
        section.querySelectorAll('input, select').forEach(el => {
          el.required = false;
          el.value = '';
        });
      }
    });
  });
});
</script>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Sophia\Documents\Capstone\resources\views/authorized/staff/scargo_item_edit.blade.php ENDPATH**/ ?>