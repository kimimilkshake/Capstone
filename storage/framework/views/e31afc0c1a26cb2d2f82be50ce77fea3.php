<?php $__env->startSection('page-title', 'EDIT VOYAGE'); ?>
<?php $__env->startSection('content'); ?>
  <?php echo $__env->make('components.authHeader', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
  <?php echo $__env->make('components.admin_nav', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

  <div class="admin-body">

    <div class="acs-form_container">
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

      <form action="<?php echo e(route('admin.voyage_update', $voyage->voyage_id)); ?>" method="POST" enctype="multipart/form-data" id="editVoyageForm">
        <?php echo csrf_field(); ?>
        <?php echo method_field('PUT'); ?>

        <div class="voyage_code">
          <h3>VOYAGE CODE: <?php echo e($voyage->voyage_code); ?></h3>
        </div>

        <!-- ROW 1: ROUTE, PORT, VESSEL -->
        <div class="form-row">
          <div class="form-col">
            <div class="form-group">
              <label for="route_port_id">Route <span class="text-danger">*</span></label>
              <select name="route_port_id" id="route_port_id" required <?php if($isCompleted): ?> disabled <?php endif; ?>>
                <?php $__currentLoopData = $route_port; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                  <option value="<?php echo e($rp->route_port_id); ?>" 
                    <?php echo e($voyage->route_port_id == $rp->route_port_id ? 'selected' : ''); ?>>
                    <?php echo e($rp->route_origin); ?> → <?php echo e($rp->route_destination); ?>

                  </option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
              </select>
            </div>
          </div>

          <div class="form-col">
            <div class="form-group">
              <label for="vessel_id">Vessel <span class="text-danger">*</span></label>
              <select name="vessel_id" id="vessel_id" required <?php if($isCompleted): ?> disabled <?php endif; ?>>
                <?php $__currentLoopData = $vessels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vessel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                  <option value="<?php echo e($vessel->vessel_id); ?>" 
                    <?php echo e($voyage->vessel_id == $vessel->vessel_id ? 'selected' : ''); ?>>
                    <?php echo e($vessel->vessel_name); ?>

                  </option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
              </select>
            </div>
          </div>
        </div>
        

        <!--ROW 2: PORT OF ORIGIN AND PORT OF DESTINATION-->
        <div class="form-row">
            <div class="form-col">
                <div class="form-group">
                    <label for="port_origin_info">Port of Origin</label>
                    <input id="port_origin_info" type="text" placeholder="Port Origin Name, City, Province" disabled>
                </div>
            </div>

            <div class="form-col">
                <div class="form-group">
                    <label for="port_destination_info">Port of Destination</label>
                    <input id="port_destination_info" type="text" placeholder="Port Destination Name, City, Province" disabled>
                </div>
            </div>
        </div>

        <!-- ROW 3: DEPARTURE DATE, ETD, ATD -->
        <div class="form-row">
          <div class="form-col">
            <div class="form-group">
              <label for="voyage_departure_date">Departure Date <span class="text-danger">*</span></label>
              <input 
                type="date" 
                id="voyage_departure_date" 
                name="voyage_departure_date" 
                value="<?php echo e($voyage->voyage_departure_date); ?>"
                <?php if($isCompleted): ?> disabled <?php endif; ?>
                min="<?php echo e(\Carbon\Carbon::today()->format('Y-m-d')); ?>"
                max="<?php echo e(\Carbon\Carbon::today()->addDays(8)->format('Y-m-d')); ?>"
                onchange="setArrivalMin(this.value)"
                required>
            </div>
          </div>

          <div class="form-col">
            <div class="form-group">
              <label for="voyage_estimated_TD">Estimated Time of Departure (ETD) <span class="text-danger">*</span></label>
              <input type="time" id="voyage_estimated_TD" name="voyage_estimated_TD" 
                value="<?php echo e($voyage->voyage_estimated_TD); ?>" 
                <?php if($isCompleted): ?> disabled <?php endif; ?>
                required>
            </div>
          </div>

          <div class="form-col">
            <div class="form-group">
              <label for="voyage_actual_TD">Actual Time of Departure (ATD)</label>
              <input type="time" id="voyage_actual_TD" name="voyage_actual_TD" 
                value="<?php echo e($voyage->voyage_actual_TD); ?>">
            </div>
          </div>
        </div>

        <!-- ROW 4: ARRIVAL DATE, ETA, ATA -->
        <div class="form-row">
          <div class="form-col">
            <div class="form-group">
              <label for="voyage_arrival_date">Arrival Date <span class="text-danger">*</span></label>
              <input 
                type="date" 
                id="voyage_arrival_date" 
                name="voyage_arrival_date" 
                value="<?php echo e($voyage->voyage_arrival_date); ?>" 
                <?php if($isCompleted): ?> disabled <?php endif; ?>
                min="<?php echo e($voyage->voyage_departure_date); ?>" 
                max="<?php echo e(\Carbon\Carbon::today()->addDays(14)->format('Y-m-d')); ?>"
                required>
            </div>
          </div>

          <div class="form-col">
            <div class="form-group">
              <label for="voyage_estimated_TA">Estimated Time of Arrival (ETA) <span class="text-danger">*</span></label>
              <input type="time" id="voyage_estimated_TA" name="voyage_estimated_TA" 
                value="<?php echo e($voyage->voyage_estimated_TA); ?>" 
                <?php if($isCompleted): ?> disabled <?php endif; ?>
                required>
            </div>
          </div>

          <div class="form-col">
            <div class="form-group">
              <label for="voyage_actual_TA">Actual Time of Arrival (ATA)</label>
              <input type="time" id="voyage_actual_TA" name="voyage_actual_TA" 
                value="<?php echo e($voyage->voyage_actual_TA); ?>">
            </div>
          </div>
        </div>

        <!-- ROW 5: DESCRIPTION -->
        <div class="form-row">
          <div class="form-group" style="width: 100%;">
            <label for="voyage_description">Voyage Description</label>
            <textarea id="voyage_description" name="voyage_description" placeholder="Enter voyage description here"><?php echo e($voyage->voyage_description); ?></textarea>
          </div>
        </div>

        <!-- ROW 6: STATUS + ACTIONS -->
        <div class="form-row" style="display: flex; justify-content: space-between; align-items: center; margin-top: 1.5rem;">
          <div class="form-group" style="flex: 0 0 250px;">
            <label for="voyage_status">Status <span class="text-danger">*</span></label>
            <select id="voyage_status" name="voyage_status" required>
              <option value="Scheduled" <?php echo e($voyage->voyage_status == 'Scheduled' ? 'selected' : ''); ?>>Scheduled</option>
              <option value="At Sea" <?php echo e($voyage->voyage_status == 'At Sea' ? 'selected' : ''); ?>>At Sea</option>
              <option value="Completed" <?php echo e($voyage->voyage_status == 'Completed' ? 'selected' : ''); ?>>Completed</option>
              <option value="Cancelled" <?php echo e($voyage->voyage_status == 'Cancelled' ? 'selected' : ''); ?>>Cancelled</option>
              <option value="Archived" <?php echo e($voyage->voyage_status == 'Archived' ? 'selected' : ''); ?>>Archived</option>
            </select>
          </div>

          <div class="form-actions" style="display: flex; gap: 1rem;">
            <button type="submit" class="acs-add-btn">
              <i class="fa-solid fa-save me-2"></i>Save Changes
            </button>
            <a href="<?php echo e(route('admin.voyage_list')); ?>" class="acs-add-btn acs-cancel-btn">
              <i class="fa-solid fa-xmark me-2"></i>Cancel
            </a>
          </div>
        </div>

      </form>
    </div>
  </div>

  <script>
    function setArrivalMin(depDate) {
        const arrival = document.getElementById('voyage_arrival_date');
        if(depDate) {
            arrival.min = depDate;
            if(arrival.value < depDate) {
                arrival.value = depDate;
            }
        }
    }
  </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Sophia\Documents\Capstone\resources\views/authorized/admin/voyage_edit.blade.php ENDPATH**/ ?>