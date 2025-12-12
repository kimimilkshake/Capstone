<?php $__env->startSection('page-title', 'VESSEL'); ?>
<?php $__env->startSection('content'); ?>
  <?php echo $__env->make('components.authHeader', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
  <?php echo $__env->make('components.admin_nav', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

  <div class="admin-body">
    <div class="avl-title">
      <h3>EDIT VESSEL</h3>
    </div>

    <div class="acs-form_container">
      <?php if(session('success')): ?>
        <div class="alert alert-success" style="color: green; text-align: center; margin-bottom: 1rem;">
          <?php echo e(session('success')); ?>

        </div>
      <?php endif; ?>

      <form action="<?php echo e(route('admin.vessel_update', $vessel->vessel_id)); ?>" method="POST" enctype="multipart/form-data" id="editVesselForm">
        <?php echo csrf_field(); ?>

        <!-- ROW 1: CODE + NAME + CAPACITY -->
        <div class="form-row">

          <div class="form-col">
            <div class="form-group">
              <label for="vessel_code">Vessel Code:</label>
              <input type="text" id="vessel_code" name="vessel_code" value="<?php echo e($vessel->vessel_code); ?>" required>
            </div>
          </div>

          <div class="form-col">
            <div class="form-group">
              <label for="vessel_name">Vessel Name:</label>
              <input type="text" id="vessel_name" name="vessel_name" value="<?php echo e($vessel->vessel_name); ?>" required>
            </div>
          </div>

          <div class="form-col">
            <div class="form-group">
              <label for="vessel_total_passenger_capacity">Total Passenger Capacity:</label>
             <input type="number" value="<?php echo e($vessel->vessel_total_passenger_capacity); ?>" disabled> 
            </div>
          </div>
        </div>

        <!-- ROW 2: HATCHES -->
        <div class="form-row">
          <!-- Hatches -->
          <div class="form-col">
            <label class="ha-label">Hatches</label>
            <div id="hatch-container">
              <?php $__currentLoopData = $vessel->hatches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $hatch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="hatch-row">
                  <input type="text" name="hatches[<?php echo e($index); ?>][label]" value="<?php echo e($hatch->hatch_label); ?>" placeholder="Hatch Label" required>
                  <input type="number" name="hatches[<?php echo e($index); ?>][area_capacity]" value="<?php echo e($hatch->hatch_area_capacity); ?>" placeholder="Area Capacity in Cubic Meters" title="Area Capacity in Cubic Meters" required>
                  <input type="number" name="hatches[<?php echo e($index); ?>][weight_capacity]" value="<?php echo e($hatch->hatch_weight_capacity); ?>" placeholder="Weight Capacity in Tons" title="Weight Capacity in Tons" required>
                  <button type="button" class="hatch-btn">+</button>
                </div>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

              <?php if($vessel->hatches->isEmpty()): ?>
                <div class="hatch-row">
                  <input type="text" name="hatches[0][label]" placeholder="Hatch Label" required>
                  <input type="number" name="hatches[0][area_capacity]" placeholder="Area Capacity in Cubic Meters" required>
                  <input type="number" name="hatches[0][weight_capacity]" placeholder="Weight Capacity in Tons" required>
                  <button type="button" class="hatch-btn">+</button>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <!--ROW 3: ACCOMMODATIONS-->
        <div class="form-row">
          <div class="form-col">
            <label class="ha-label">Accommodations</label>
            <div id="accommodation-container">
              <?php $__currentLoopData = $vessel->accommodations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $acc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="accommodation-row">
                  <input type="text" name="accommodations[<?php echo e($index); ?>][name]" value="<?php echo e($acc->accommodation_name); ?>" placeholder="Accommodation Name" required>
                  <input type="number" name="accommodations[<?php echo e($index); ?>][price]" value="<?php echo e($acc->accommodation_regular_price); ?>" placeholder="Regular Price" required>
                  <input type="number" name="accommodations[<?php echo e($index); ?>][capacity]" value="<?php echo e($acc->accommodation_capacity); ?>" placeholder="Capacity" required>
                  <button type="button" class="accommodation-btn add-accommodation">+</button>
                </div>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
              <?php if($vessel->accommodations->isEmpty()): ?>
                <div class="accommodation-row">
                  <input type="text" name="accommodations[0][name]" placeholder="Accommodation Name" required>
                  <input type="number" name="accommodations[0][price]" placeholder="Regular Price" required>
                  <input type="number" name="accommodations[0][capacity]" placeholder="Accommodation Capacity" required>
                  <button type="button" class="accommodation-btn add-accommodation">+</button>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <!-- ROW 4: COT PLAN + STATUS -->
        <div class="form-row" style="display: flex; gap: 2rem; align-items: flex-start; width: 100%;">
          <!-- Cot Plan -->
          <div class="form-col" style="flex: 1;">
            <div class="form-group vcot-plan" style="display: flex; flex-direction: column; align-items: flex-start; width: 100%;">
              <label for="vessel_cot_plan_url" style="margin-bottom: 8px;">Cot Plan:</label>

              <?php if($vessel->vessel_cot_plan_url): ?>
                <img 
                  src="<?php echo e(asset('storage/'.$vessel->vessel_cot_plan_url)); ?>" 
                  alt="Cot Plan" 
                  style="width: 100%; height: auto; max-height: 250px; object-fit: contain; border: 1px solid #ccc; border-radius: 8px; margin-bottom: 10px; background-color: #f9f9f9;">
              <?php else: ?>
                <p style="color: #888; font-style: italic; margin-bottom: 10px;">No cot plan uploaded yet.</p>
              <?php endif; ?>

              <input 
                type="file" 
                id="vessel_cot_plan_url" 
                name="vessel_cot_plan_url" 
                accept="image/*"
                style="width: 100%; margin-top: 5px;">
            </div>
          </div>

          <!-- Status -->
          <div class="form-col" style="flex: 1;">
            <div class="form-group" style="width: 100%;">
              <label for="vessel_status" style="margin-bottom: 8px;">Status:</label>
              <select 
                name="vessel_status" 
                id="vessel_status" 
                required 
                style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 8px;">
                <option value="Active" <?php echo e($vessel->vessel_status === 'Active' ? 'selected' : ''); ?>>Active</option>
                <option value="Inactive" <?php echo e($vessel->vessel_status === 'Inactive' ? 'selected' : ''); ?>>Inactive</option>
              </select>
            </div>
          </div>

        </div>

        <!-- ACTION BUTTONS -->
        <div class="form-actions" style="display: flex; gap: 1rem; justify-content: center; margin-top: 1.5rem;">
          <button type="submit" class="acs-add-btn">
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

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\clint\Desktop\Capstone\resources\views/authorized/admin/vessel_edit.blade.php ENDPATH**/ ?>