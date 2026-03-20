<?php $__env->startSection('page-title', 'PROMOS'); ?>
<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('components.authHeader', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo $__env->make('components.admin_nav', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="admin-body">
      
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
        <form class="search-bar" action="<?php echo e(route('admin.promo_list')); ?>" method="GET" style="flex: 1;">
          <input type="text" name="search" placeholder="Search by name..." value="<?php echo e(request('search')); ?>">
          <button type="submit"><i class="fa-solid fa-magnifying-glass me-2"></i>Search</button>
        </form>

        <div class="add-promo">
          <a href="<?php echo e(route('admin.create_promo')); ?>"><i class="fa-solid fa-plus me-2"></i>Add Promo</a>
        </div>
      </div>
      <table class="promo-table">
        <thead>
          <tr>
            <th>Name</th>
            <th>Code</th>
            <th>Discount Rate</th>
            <th>Start Date</th>
            <th>End Date</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php $__empty_1 = true; $__currentLoopData = $promos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr>
              <td><?php echo e($p->promo_name); ?></td>
              <td><?php echo e($p->promo_code); ?></td>
              <td><?php echo e(rtrim(rtrim($p->promo_discount_rate, '0'), '.')); ?>%</td>
              <td><?php echo e($p->promo_start_date); ?></td>
              <td><?php echo e($p->promo_end_date); ?></td>
              <td><?php echo e($p->promo_status); ?></td>
              <td>
                <a href="<?php echo e(route('admin.promo_edit', $p->promo_id)); ?>" class="editRouteBtn link-btn" title="Edit Promo">
                  <i class="fa fa-pencil" aria-hidden="true"></i>
                </a>
              </td>
            </tr>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr>
              <td colspan="7" class="text-center">No promos fuond.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
      <div class="pagination-container">
        <?php echo e($promos->appends(request()->query())->links('pagination::bootstrap-5')); ?>

      </div>
    </div>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Sophia\Documents\Capstone\resources\views/authorized/admin/promo_list.blade.php ENDPATH**/ ?>