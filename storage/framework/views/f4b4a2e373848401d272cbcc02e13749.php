<?php $__env->startSection('page-title', 'CARGO'); ?>
<?php $__env->startSection('content'); ?>
  <?php echo $__env->make('components.authHeader', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
  <?php echo $__env->make('components.staff_nav', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
  <div class="staff-body">
    <div class="svl-title">
      <h3>VIEW RATES</h3>
    </div>

    <div class="search-filter-row" style="display: flex; gap: 10px; margin-bottom: 20px;">
      <form class="search-bar" action="<?php echo e(route('staff.cargo_item_list')); ?>"  method="GET" style="flex: 1;">

        <input type="text" name="search" placeholder="Search..." value="<?php echo e(request('search')); ?>" style="margin-right: 10px;">
        <select name="destination">
          <option value="">All Routes</option>
          <?php $__currentLoopData = $destinations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dest): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($dest); ?>" <?php echo e(request('destination') == $dest ? 'selected' : ''); ?>>
                <?php echo e($dest); ?>

            </option>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
        <button type="submit">Filter</button>
      </form>
    </div>

    <table class="cargo-item-table">
      <thead>
        <tr>
          <th>Classification</th>
          <th>Description</th>
          <th>Freight</th>
          <th>Arrastre</th>
          <th>Destination</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php $__empty_1 = true; $__currentLoopData = $cargo_items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
          <tr>
            <td><?php echo e($c->cargo_item_classification); ?></td>
            <td><?php echo e($c->cargo_item_description); ?></td>
            <td><?php echo e($c->cargo_item_freight); ?></td>
            <td><?php echo e($c->cargo_item_arrastre); ?></td>
            <td><?php echo e($c->routePort->route_destination ?? 'N/A'); ?></td>
            <td>
              <a href="<?php echo e(route('staff.cargo_item_edit', $c->cargo_item_id)); ?>" class="editRouteBtn link-btn" title="Edit Cargo Item">
                <i class="fa fa-pencil" aria-hidden="true"></i>
              </a>
            </td>
          </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
          <tr>
            <td colspan="6" class="text-center">No cargo items found.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
    <div class="pagination-container">
      <?php echo e($cargo_items->appends(request()->query())->links('pagination::bootstrap-5')); ?>

    </div>

  </div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\clint\Desktop\Capstone\resources\views/authorized/staff/scargo_item_list.blade.php ENDPATH**/ ?>