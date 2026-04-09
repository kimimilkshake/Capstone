<?php $__env->startSection('page-title', 'VOYAGES'); ?>
<?php $__env->startSection('content'); ?>
  <?php echo $__env->make('components.authHeader', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
  <?php echo $__env->make('components.admin_nav', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

  <div class="admin-body">

    <div class="search-add-row" style="display: flex; gap: 10px; margin-bottom: 20px; align-items: flex-end;">
      <form class="search-bar" action="<?php echo e(route('admin.voyage_list')); ?>" method="GET"
      style="flex: 1; display: flex; gap: 10px; align-items: flex-end;">

          <!-- SEARCH -->
          <div style="flex: 2; display: flex; flex-direction: column;">
              <label style="font-size: 12px;">Search</label>
              <input 
                  type="text" 
                  name="search" 
                  placeholder="Search by name, code, route, vessel, status..." 
                  value="<?php echo e(request('search')); ?>"
                  style="height: 38px;">
          </div>

          <!-- FROM -->
          <div style="display: flex; flex-direction: column;">
              <label style="font-size: 12px;">From</label>
              <input 
                  type="date" 
                  name="start_date" 
                  value="<?php echo e(request('start_date')); ?>"
                  style="height: 38px;">
          </div>

          <!-- TO -->
          <div style="display: flex; flex-direction: column;">
              <label style="font-size: 12px;">To</label>
              <input 
                  type="date" 
                  name="end_date" 
                  value="<?php echo e(request('end_date')); ?>"
                  style="height: 38px;">
          </div>

          <!-- BUTTON -->
          <div>
              <button type="submit" style="height: 38px; padding: 0 15px;">
                  <i class="fa-solid fa-magnifying-glass me-2"></i>Search
              </button>
          </div>

      </form>

      <div class="add-vessel">
        <a href="<?php echo e(route('admin.create_voyage')); ?>">
          <i class="fa-solid fa-plus me-2"></i>Add Voyage
        </a>
      </div>
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
          <th>PAX/CAP</th>
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
            <td><?php echo e($voyage->passenger_tickets_count); ?>/<?php echo e($voyage->vessel->vessel_total_passenger_capacity); ?></td>
            <td><?php echo e($voyage->vessel->vessel_name); ?></td>
            <td><?php echo e($voyage->voyage_status); ?></td>
            <td>
              <?php if($voyage->voyage_status === 'At Sea'): ?>
                  <a href="#" class="link-btn disabled-voyage" style="opacity: 0.5; cursor: not-allowed;" title="Cannot edit while At Sea">
                      <i class="fa fa-pencil me-1"></i>
                  </a>
              <?php else: ?>
                  <a href="<?php echo e(route('admin.voyage_edit', $voyage->voyage_id)); ?>" class="link-btn">
                      <i class="fa fa-pencil me-1"></i>
                  </a>
              <?php endif; ?>
              <a href="<?php echo e(route('admin.manifest', $voyage->voyage_id)); ?>" title="View Manifest" class="editRouteBtn link-btn" style="text-decoration: none"><i class="fa-solid fa-file me-1"></i></a>
              
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

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\clint\Desktop\Capstone\resources\views/authorized/admin/voyage_list.blade.php ENDPATH**/ ?>