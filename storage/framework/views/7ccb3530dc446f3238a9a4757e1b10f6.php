<?php $__env->startSection('page-title', 'CARGO'); ?>
<?php $__env->startSection('content'); ?>
  <?php echo $__env->make('components.authHeader', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
  <?php echo $__env->make('components.admin_nav', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
  <div class="admin-body">
    <div class="avl-title">
      <h3>VIEW RATES</h3>
    </div>

    <div class="search-filter-row" style="display: flex; gap: 10px; margin-bottom: 20px;">
      <form class="search-bar" action="<?php echo e(route('admin.cargo_item_list')); ?>"  method="GET" style="flex: 1;">

        <input type="text" name="search" placeholder="Search..." value="<?php echo e(request('search')); ?>" style="margin-right: 10px;">
        <select name="route_code_id">
          <option value="">Select Route Code</option>
          <?php $__currentLoopData = $route_codes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $routeCode): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <option value="<?php echo e($routeCode->route_code_id); ?>">
                    <?php echo e($routeCode->route_code_name); ?>

                  </option>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
        <button type="submit">Filter</button>
      </form>
    </div>

    <table class="cargo-item-table">
      <thead>
        <tr>
          <th>Route Code</th>
          <th>Description</th>
          <th>Freight</th>
          <th>Arrastre</th>
          <th>With Measurement Range?</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php $__empty_1 = true; $__currentLoopData = $cargo_items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
          <tr>
            <td><?php echo e($c->routeCode->route_code_name ?? 'N/A'); ?></td>
            <td><?php echo e($c->cargo_item_description); ?></td>
            <td><?php echo e($c->cargo_item_freight); ?></td>
            <td><?php echo e($c->cargo_item_arrastre); ?></td>
            <td><?php echo e($c->cargo_item_measure_required); ?></td>
            <td>
              <a href="<?php echo e(route('admin.cargo_item_edit', $c->cargo_item_id)); ?>" class="edit-icon">
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
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Sophia\Documents\Capstone\resources\views/authorized/admin/cargo_item_list.blade.php ENDPATH**/ ?>