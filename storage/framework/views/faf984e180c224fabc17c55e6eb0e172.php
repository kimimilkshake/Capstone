<?php $__env->startSection('page-title', 'VESSEL'); ?>
<?php $__env->startSection('content'); ?>
  <?php echo $__env->make('components.authHeader', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
  <?php echo $__env->make('components.admin_nav', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
  <div class="admin-body">
    <div class="avl-title">
      <h3>VESSEL LIST</h3>
    </div>
    <div class="search-add-row" style="display: flex; gap: 10px; margin-bottom: 20px;">
      <form class="search-bar" action="<?php echo e(route('admin.vessel_list')); ?>" method="GET" style="flex: 1;">
          <input type="text" name="search" placeholder="Search by name..." value="<?php echo e(request('search')); ?>">
          <button type="submit"><i class="fa-solid fa-magnifying-glass me-2"></i>Search</button>
      </form>

      <div class="add-vessel">
        <a href="<?php echo e(route('admin.create_vessel')); ?>"><i class="fa-solid fa-plus me-2"></i>Add Vessel</a>
      </div>
    </div>

    <table class="vessel-table">
      <thead>
        <tr>
          <th>Code</th>
          <th>Name</th>
          <th>No. of Hatches</th>
          <th>No. of Accommodations</th>
          <th>Passenger Capacity</th>
          <th>Status</th>
          <th>Action</>
        </tr>
      </thead>
      <tbody>
        <?php $__empty_1 = true; $__currentLoopData = $vessels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
          <tr>
            <td><?php echo e($v->vessel_code); ?></td>
            <td><?php echo e($v->vessel_name); ?></td>
            <td><?php echo e($v->hatches->count()); ?></td>
            <td><?php echo e($v->accommodations->count()); ?></td>
            <td><?php echo e($v->vessel_total_passenger_capacity); ?></td>
            <td><?php echo e($v->vessel_status); ?></td>
            <td>
              <a href="<?php echo e(route('admin.vessel_edit', $v->vessel_id)); ?>" class="editRouteBtn link-btn" title="Edit Vessel">
                <i class="fa fa-pencil" aria-hidden="true"></i>
              </a>
            </td>
          </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
          <tr>
            <td colspan="6" class="text-center">No vessels found.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
    <div class="pagination-container">
      <?php echo e($vessels->appends(request()->query())->links('pagination::bootstrap-5')); ?>

    </div>

  </div>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\clint\Desktop\Capstone\resources\views/authorized/admin/vessel_list.blade.php ENDPATH**/ ?>