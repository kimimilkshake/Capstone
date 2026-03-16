<?php $__env->startSection('page-title', 'VOYAGES'); ?>
<?php $__env->startSection('content'); ?>
  <?php echo $__env->make('components.authHeader', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
  <?php echo $__env->make('components.staff_nav', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
  <div class="staff-body">
    <div class="svl-title">
      <h3>SEARCH VOYAGE</h3>
    </div>

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

    <div class="search-add-row" style="display: flex; gap: 10px; margin-bottom: 20px;">
      <form class="search-bar" action="<?php echo e(route('staff.voyage_list')); ?>" method="GET" style="flex: 1;">
          <input 
              type="text" 
              name="search" 
              placeholder="Search by name, code, route, vessel, status..." 
              value="<?php echo e(request('search')); ?>">
          <input 
              type="date" 
              name="start_date" 
              value="<?php echo e(request('start_date')); ?>"
              style="margin-left:10px;"
              placeholder="Start date">
          <input 
              type="date" 
              name="end_date" 
              value="<?php echo e(request('end_date')); ?>"
              style="margin-left:10px;"
              placeholder="End date">
          <button type="submit">
              <i class="fa-solid fa-magnifying-glass me-2"></i>Search
          </button>
      </form>
    </div>

    <table class="voyage-table">
      <thead>
        <tr>
          <th>Voyage Code</th>
          <th>Route</th>
          <th>Departure Date</th>
          <th>ETD</th>
          <th>Arrival Date</th>
          <th>ETA</th>
          <th>Vessel</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php $__empty_1 = true; $__currentLoopData = $voyages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $voyage): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
          <tr>
            <td><?php echo e($voyage->voyage_code); ?></td>
            <td>
              <?php echo e($voyage->routePort->route_origin); ?> → 
              <?php echo e($voyage->routePort->route_destination); ?>

            </td>
            <td><?php echo e(\Carbon\Carbon::parse($voyage->voyage_departure_date)->format('M j, Y, D')); ?></td>
            <td><?php echo e(\Carbon\Carbon::parse($voyage->voyage_estimated_TD)->format('g:iA')); ?></td>
            <td><?php echo e(\Carbon\Carbon::parse($voyage->voyage_arrival_date)->format('M j, Y, D')); ?></td>
            <td><?php echo e(\Carbon\Carbon::parse($voyage->voyage_estimated_TA)->format('g:iA')); ?></td>
            <td><?php echo e($voyage->vessel->vessel_name); ?></td>
            <td><?php echo e($voyage->voyage_status); ?></td>
            <td>
              <a href="<?php echo e(route('staff.voyage_edit', $voyage->voyage_id)); ?>" title="Edit Voyage" class="editRouteBtn link-btn"><i class="fa fa-pencil me-1"></i></a>
              <a href="<?php echo e(route('staff.manifest', $voyage->voyage_id)); ?>" title="View Manifest" class="editRouteBtn link-btn"><i class="fa-solid fa-file me-1"></i></a>
              <a href="<?php echo e(route('staff.semaphore', $voyage->voyage_id)); ?>" title="Send Message" class="editRouteBtn link-btn"><i class="fa-solid fa-message"></i></a>
            </td>
          </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
          <tr>
            <td colspan="9" class="text-center">No voyages found.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>

    
    <div class="mt-3">
      <?php echo e($voyages->appends(['search' => request('search')])->links('pagination::bootstrap-5')); ?>

    </div>
  </div>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Sophia\Documents\Capstone\resources\views/authorized/staff/svoyage_list.blade.php ENDPATH**/ ?>