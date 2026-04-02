<?php $__env->startSection('page-title', 'EDIT VOYAGE'); ?>
<?php $__env->startSection('content'); ?>
  <?php echo $__env->make('components.authHeader', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
  <?php echo $__env->make('components.staff_nav', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

  <div class="staff-body">

    <div class="acs-form_container">
      
      <form action="<?php echo e(route('staff.voyage_update', $voyage->voyage_id)); ?>" method="POST" enctype="multipart/form-data" id="editVoyageForm">
        <?php echo csrf_field(); ?>
        <?php echo method_field('PUT'); ?>

        <?php
          $isLocked = in_array($voyage->voyage_status, ['Completed', 'Cancelled']);
          $isCancelled = $voyage->voyage_status === 'Cancelled';
        ?>

        <div class="voyage_code">
          <h3>VOYAGE CODE: <?php echo e($voyage->voyage_code); ?></h3>
        </div>

        <?php if($isCancelled): ?>
          <p style="color:red; font-weight:600;">
            This voyage is cancelled. Only actual deparute and arrival dates and times can be edited.
          </p>
        <?php endif; ?>

        <!-- ROW 1: ROUTE, PORT, VESSEL -->
        <div class="form-row">
          <div class="form-col">
            <div class="form-group">
              <label for="route_port_id">Route <span class="text-danger">*</span></label>
              <select name="route_port_id" id="route_port_id" required <?php if($isLocked): ?> disabled <?php endif; ?>>
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
              <select name="vessel_id" id="vessel_id" required <?php if($isLocked): ?> disabled <?php endif; ?>>
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

        <!-- ROW 3: DEPARTURE DATE, ETD, ADD, ATD -->
        <div class="form-row">
          <div class="form-col">
            <div class="form-group">
              <label for="voyage_departure_date">Departure Date <span class="text-danger">*</span></label>
              <input 
                type="date" 
                id="voyage_departure_date" 
                name="voyage_departure_date" 
                value="<?php echo e($voyage->voyage_departure_date); ?>" 
                <?php if($isLocked): ?> disabled <?php endif; ?>
                min="<?php echo e(\Carbon\Carbon::today()->format('Y-m-d')); ?>"
                onchange="setArrivalMin(this.value)"
                required>
            </div>
          </div>

          <div class="form-col">
            <div class="form-group">
              <label for="voyage_estimated_TD">Estimated Time of Departure (ETD) <span class="text-danger">*</span></label>
              <input type="time" id="voyage_estimated_TD" name="voyage_estimated_TD" 
                value="<?php echo e($voyage->voyage_estimated_TD); ?>" 
                <?php if($isLocked): ?> disabled <?php endif; ?>
                required>
            </div>
          </div>

          <div class="form-col">
            <div class="form-group">
              <label for="">Actual Date of Departure</label>
              <input type="date" id="voyage_actual_departure_date" name="voyage_actual_departure_date" 
                value="<?php echo e($voyage->voyage_actual_departure_date); ?>" >
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

        <!-- ROW 4: ARRIVAL DATE, ETA, ADA, ATA -->
        <div class="form-row">
          <div class="form-col">
            <div class="form-group">
              <label for="voyage_arrival_date">Arrival Date <span class="text-danger">*</span></label>
              <input 
                type="date" 
                id="voyage_arrival_date" 
                name="voyage_arrival_date" 
                value="<?php echo e($voyage->voyage_arrival_date); ?>" 
                <?php if($isLocked): ?> disabled <?php endif; ?>
                min="<?php echo e($voyage->voyage_departure_date); ?>" 
                required>
            </div>
          </div>

          <div class="form-col">
            <div class="form-group">
              <label for="voyage_estimated_TA">Estimated Time of Arrival (ETA) <span class="text-danger">*</span></label>
              <input 
                type="time" 
                id="voyage_estimated_TA" 
                name="voyage_estimated_TA" 
                value="<?php echo e($voyage->voyage_estimated_TA); ?>" 
                <?php if($isLocked): ?> disabled <?php endif; ?>
                required>
            </div>
          </div>

          <div class="form-col">
            <div class="form-group">
              <label for="">Actual Date of Arrival</label>
              <input 
                type="date" 
                id="voyage_actual_arrival_date" 
                name="voyage_actual_arrival_date" 
                value="<?php echo e($voyage->voyage_actual_arrival_date); ?>">
            </div>
          </div>

          <div class="form-col">
            <div class="form-group">
              <label for="voyage_actual_TA">Actual Time of Arrival (ATA)</label>
              <input 
                type="time" 
                id="voyage_actual_TA" 
                name="voyage_actual_TA" 
                value="<?php echo e($voyage->voyage_actual_TA); ?>">
            </div>
          </div>
        </div>

        <!-- ROW 5: DESCRIPTION -->
        <div class="form-row">
          <div class="form-group" style="width: 100%;">
            <label for="voyage_description">Voyage Description <span class="text-danger">*</span></label>
            <textarea id="voyage_description" name="voyage_description" placeholder="Enter voyage description here"><?php echo e($voyage->voyage_description); ?></textarea>
          </div>
        </div>

        <!-- ROW 6: STATUS + ACTIONS -->
        <div class="form-row" style="display: flex; justify-content: space-between; align-items: center; margin-top: 1.5rem;">
          <div class="form-group" style="flex: 0 0 250px;">
            <label for="voyage_status">Status</label>
            <select id="voyage_status" name="voyage_status" required <?php if($isCancelled): ?> disabled <?php endif; ?>>
              <option value="Scheduled" <?php echo e($voyage->voyage_status == 'Scheduled' ? 'selected' : ''); ?>>Scheduled</option>
              <option value="At Sea" <?php echo e($voyage->voyage_status == 'At Sea' ? 'selected' : ''); ?>>At Sea</option>
              <option value="Completed" <?php echo e($voyage->voyage_status == 'Completed' ? 'selected' : ''); ?>>Completed</option>
              <option value="Cancelled" <?php echo e($voyage->voyage_status == 'Cancelled' ? 'selected' : ''); ?>>Cancelled</option>
              <option value="Archived" <?php echo e($voyage->voyage_status == 'Archived' ? 'selected' : ''); ?>>Archived</option>
            </select>

            <?php if($isCancelled): ?>
                <input type="hidden" name="voyage_status" value="Cancelled">
            <?php endif; ?>
          </div>

          <div class="form-actions" style="display: flex; gap: 1rem;">
            <button type="submit" class="acs-add-btn update-btn" id="saveEditBtn">
              <i class="fa-solid fa-save me-2"></i>Save Changes
            </button>
            <a href="<?php echo e(route('staff.voyage_list')); ?>" class="acs-add-btn acs-cancel-btn">
              <i class="fa-solid fa-xmark me-2"></i>Cancel
            </a>
          </div>
        </div>

      </form>
    </div>
  </div>

  <script>
    const depDateInput = document.getElementById('voyage_departure_date');
    const arrDateInput = document.getElementById('voyage_arrival_date');
    const etdInput = document.getElementById('voyage_estimated_TD');
    const etaInput = document.getElementById('voyage_estimated_TA');

    const routePorts = {
        <?php $__currentLoopData = $route_port; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            "<?php echo e($rp->route_port_id); ?>": {
                origin: "<?php echo e($rp->portOrigin?->terminal_name ?? ''); ?>, <?php echo e($rp->portOrigin?->city ?? ''); ?>, <?php echo e($rp->portOrigin?->province ?? ''); ?>",
                destination: "<?php echo e($rp->portDestination?->terminal_name ?? ''); ?>, <?php echo e($rp->portDestination?->city ?? ''); ?>, <?php echo e($rp->portDestination?->province ?? ''); ?>"
            },
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    };

    function updatePortDisplay(routePortId) {
        const originInput = document.getElementById('port_origin_info');
        const destinationInput = document.getElementById('port_destination_info');

        if (routePorts[routePortId]) {
            originInput.value = routePorts[routePortId].origin;
            destinationInput.value = routePorts[routePortId].destination;
        } else {
            originInput.value = '';
            destinationInput.value = '';
        }
    }

    const routeSelect = document.getElementById('route_port_id');

    routeSelect.addEventListener('change', function () {
        updatePortDisplay(this.value);
    });

    window.addEventListener('DOMContentLoaded', function () {
        updatePortDisplay(routeSelect.value);
    });


    // normalize date (remove time)
    function normalizeDate(d) {
        const date = new Date(d);
        date.setHours(0,0,0,0);
        return date;
    }

    // Instant validation for dates
    function validateDatesInstant() {
        if (!depDateInput.value || !arrDateInput.value) return;

        const depDate = normalizeDate(depDateInput.value);
        const arrDate = normalizeDate(arrDateInput.value);

        if (arrDate < depDate) {
            showToast('Arrival date cannot be earlier than departure date.', 'danger');
            arrDateInput.value = depDateInput.value; // set to departure date instead of empty
        }
    }

    // Instant validation for time (if same day)
    function validateTimeInstant() {
        if (!depDateInput.value || !arrDateInput.value) return;
        if (!etdInput.value || !etaInput.value) return;

        const depDate = normalizeDate(depDateInput.value);
        const arrDate = normalizeDate(arrDateInput.value);

        if (depDate.getTime() === arrDate.getTime()) {
            if (etaInput.value <= etdInput.value) {
                showToast('ETA must be later than ETD if same day.', 'danger');
                etaInput.value = '';
            }
        }
    }

    // Modified setArrivalMin for instant prompt
    function setArrivalMin(depDate) {
        const arrival = arrDateInput;
        if(depDate) {
            arrival.min = depDate;

            if(arrival.value && normalizeDate(arrival.value) < normalizeDate(depDate)) {
                showToast('Arrival date must be on or after the departure date.', 'danger');
                arrival.value = depDate; // reset to departure date
            }
        }
    }

    // Event listeners
    depDateInput.addEventListener('change', () => {
        setArrivalMin(depDateInput.value);
        validateDatesInstant();
        validateTimeInstant();
    });

    arrDateInput.addEventListener('change', () => {
        validateDatesInstant();
        validateTimeInstant();
    });

    etdInput.addEventListener('change', validateTimeInstant);
    etaInput.addEventListener('change', validateTimeInstant);
  </script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\clint\Desktop\Capstone\resources\views/authorized/staff/svoyage_edit.blade.php ENDPATH**/ ?>