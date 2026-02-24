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

        <div class="form-row"> <!-- First Row -->

          <div class="form-col">
            <div class="form-group">
              <label>Route Code <span class="text-danger">*</span></label>
              <select name="route_code_id" required>
                <option value="">Select Route Code</option>
                <?php $__currentLoopData = $route_codes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $routeCode): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                  <option value="<?php echo e($routeCode->route_code_id); ?>">
                    <?php echo e($routeCode->route_code_name); ?>

                  </option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
              </select>
            </div>
          </div>
          
          <div class="form-col">
            <div class="form-group">
              <label>Description <span class="text-danger">*</span></label>
              <input type="text" name="cargo_item_description" required>
            </div>
          </div>

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
        </div>

        <div class="form-row"> <!-- Second Row -->
          <div class="form-col">
            <div class="form-group inline-radio">
              <label class="inline-label">
                With Measurement Range? <span class="text-danger">*</span>
              </label>

              <div class="radio-group">
                <label class="radio-option">
                  <input type="radio" name="cargo_item_measure_required" value="Yes" required>
                  <span>Yes</span>
                </label>

                <label class="radio-option">
                  <input type="radio" name="cargo_item_measure_required" value="No">
                  <span>No</span>
                </label>
              </div>
            </div>
          </div>
        </div>

        
        <div id="measurement-section" style="display: none;">

          
          <div class="form-row">
            <div class="form-col">
              <div class="form-group">
                <label>Min Length</label>
                <input type="number" step="0.01" name="cargo_item_min_length">
              </div>
            </div>

            <div class="form-col">
              <div class="form-group">
                <label>Min Width</label>
                <input type="number" step="0.01" name="cargo_item_min_width">
              </div>
            </div>

            <div class="form-col">
              <div class="form-group">
                <label>Min Height</label>
                <input type="number" step="0.01" name="cargo_item_min_height">
              </div>
            </div>

            <div class="form-col">
              <div class="form-group">
                <label>Unit</label>
                <select name="measurement_unit_id">
                  <option value="">Select Unit</option>
                  <?php $__currentLoopData = $measurement_units; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $unit): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($unit->measurement_unit_id); ?>">
                      <?php echo e($unit->measurement_unit_name); ?>

                    </option>
                  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
              </div>
            </div>
          </div>

          
          <div class="form-row">
            <div class="form-col">
              <div class="form-group">
                <label>Max Length</label>
                <input type="number" step="0.01" name="cargo_item_max_length">
              </div>
            </div>

            <div class="form-col">
              <div class="form-group">
                <label>Max Width</label>
                <input type="number" step="0.01" name="cargo_item_max_width">
              </div>
            </div>

            <div class="form-col">
              <div class="form-group">
                <label>Max Height</label>
                <input type="number" step="0.01" name="cargo_item_max_height">
              </div>
            </div>

            <div class="form-col">
              <div class="form-group">
                <label>Unit</label>
                <select name="measurement_unit_id_max">
                  <option value="">Select Unit</option>
                  <?php $__currentLoopData = $measurement_units; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $unit): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($unit->measurement_unit_id); ?>">
                      <?php echo e($unit->measurement_unit_name); ?>

                    </option>
                  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
              </div>
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

<script>
document.addEventListener('DOMContentLoaded', function () {
  const radios = document.querySelectorAll('input[name="cargo_item_measure_required"]');
  const section = document.getElementById('measurement-section');

  radios.forEach(radio => {
    radio.addEventListener('change', function () {
      if (this.value === 'Yes') {
        section.style.display = 'block';

        // make inputs required
        section.querySelectorAll('input, select').forEach(el => {
          el.required = true;
        });
      } else {
        section.style.display = 'none';

        // remove required + clear values
        section.querySelectorAll('input, select').forEach(el => {
          el.required = false;
          el.value = '';
        });
      }
    });
  });
});
</script>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Shem\Desktop\Capstone\resources\views/authorized/staff/screate_cargo_item.blade.php ENDPATH**/ ?>