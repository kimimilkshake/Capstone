<?php $__env->startSection('page-title', 'VOYAGES'); ?>
<?php $__env->startSection('content'); ?>
  <?php echo $__env->make('components.authHeader', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
  <?php echo $__env->make('components.admin_nav', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

  <div class="admin-body">
    <div class="avl-title">
      <h3>CREATE VOYAGE</h3>
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

      
      <form action="<?php echo e(route('admin.store_voyage')); ?>" method="POST">
        <?php echo csrf_field(); ?>

        <!--ROW 1: ROUTE, PORT, AND VESSEL-->
        <div class="form-row">
          
          <div class="form-col">
            <div class="form-group">
              <label for="route_port_id">Route <span class="text-danger">*</span></label>
              <select id="route_port_id" name="route_port_id" required>
                <option value="" disabled selected>Select Route</option>
                <?php $__currentLoopData = $route_port; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                  <option value="<?php echo e($rp->route_port_id); ?>">
                    <?php echo e($rp->route_origin); ?> → <?php echo e($rp->route_destination); ?>

                  </option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
              </select>
            </div>
          </div>

          
          <div class="form-col">
            <div class="form-group">
              <label for="vessel_id">Vessel <span class="text-danger">*</span></label>
              <select id="vessel_id" name="vessel_id" required>
                <option value="" disabled selected>Select Vessel</option>
                <?php $__currentLoopData = $vessels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vessel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                  <option value="<?php echo e($vessel->vessel_id); ?>"><?php echo e($vessel->vessel_name); ?></option>
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

        <!--ROW 3: DEPARTURE DATE AND ARRIVAL DATE-->
        <div class="form-row">
          <div class="form-col">
            <div class="form-group">
              <label for="voyage_departure_date">Departure Date <span class="text-danger">*</span></label>
              <input 
                  id="voyage_departure_date" 
                  type="date" 
                  name="voyage_departure_date" 
                  required
                  min="<?php echo e(\Carbon\Carbon::today()->format('Y-m-d')); ?>"
                  max="<?php echo e(\Carbon\Carbon::today()->addDays(8)->format('Y-m-d')); ?>"
              >
            </div>
          </div>

          <div class="form-col">
            <div class="form-group">
              <label for="voyage_arrival_date">Arrival Date</label>
              <input 
                id="voyage_arrival_date" 
                type="date" 
                name="voyage_arrival_date" 
                required
                min="<?php echo e(\Carbon\Carbon::today()->format('Y-m-d')); ?>"
                max="<?php echo e(\Carbon\Carbon::today()->addDays(8)->format('Y-m-d')); ?>"
              >
            </div>
          </div>
        </div>

        <!--ROW 4: ESTIMATED TD AND ESTIMATED TA-->
        <div class="form-row">
          <div class="form-col">
            <div class="form-group">
              <label for="voyage_estimated_TD">Estimated Time of Departure (ETD) <span class="text-danger">*</span></label>
              <input id="voyage_estimated_TD" type="time" name="voyage_estimated_TD" required>
            </div>
          </div>

          <div class="form-col">
            <div class="form-group">
              <label for="voyage_estimated_TA">Estimated Time of Arrival (ETA) <span class="text-danger">*</span></label>
              <input id="voyage_estimated_TA" type="time" name="voyage_estimated_TA" required>
            </div>
          </div>
        </div>

        <!--ACTIONS-->
        <div class="form-actions" style="display: flex; gap: 1rem; justify-content: center; margin-top: 1.5rem;">
          <button type="submit" class="acs-add-btn">
            <i class="fa-solid fa-plus me-2"></i>ADD
          </button>
          <a href="<?php echo e(route('admin.voyage_list')); ?>" class="acs-add-btn acs-cancel-btn">
            <i class="fa-solid fa-xmark me-2"></i>CANCEL
          </a>
        </div>
      </form>
      
    </div>
  </div>

  <script>
    const routePorts = {
      <?php $__currentLoopData = $route_port; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          "<?php echo e($rp->route_port_id); ?>": {
              origin: "<?php echo e($rp->port_origin_name); ?>, <?php echo e($rp->port_origin_city); ?>, <?php echo e($rp->port_origin_province); ?>",
              destination: "<?php echo e($rp->port_destination_name); ?>, <?php echo e($rp->port_destination_city); ?>, <?php echo e($rp->port_destination_province); ?>"
          },
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    };

    const routeSelect = document.getElementById('route_port_id');
    const portOriginInput = document.getElementById('port_origin_info');
    const portDestinationInput = document.getElementById('port_destination_info');

    routeSelect.addEventListener('change', function() {
        const selectedId = this.value;

        if(routePorts[selectedId]) {
            portOriginInput.value = routePorts[selectedId].origin;
            portDestinationInput.value = routePorts[selectedId].destination;
        } else {
            portOriginInput.value = '';
            portDestinationInput.value = '';
        }
    });
  </script>


<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Shem\Desktop\Capstone\resources\views/authorized/admin/create_voyage.blade.php ENDPATH**/ ?>