<?php $__env->startSection('page-title', 'CARGO ITEMS'); ?>
<?php $__env->startSection('content'); ?>
  <?php echo $__env->make('components.authHeader', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
  <?php echo $__env->make('components.staff_nav', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
  <div class="staff-body">
    
    <!-- Floating Toast Container - Below navbar on the right side -->
        <div class="toast-container position-fixed p-3" style="z-index: 9999; top: 80px; right: 20px;">
            <?php if(session('success')): ?>
                <div class="toast align-items-center text-white bg-success border-0 show" role="alert" aria-live="assertive"
                    aria-atomic="true" id="successToast">
                    <div class="d-flex">
                        <div class="toast-body">
                            <i class="fas fa-check-circle me-2"></i><?php echo e(session('success')); ?>

                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
                            aria-label="Close"></button>
                    </div>
                </div>
            <?php endif; ?>
            <?php if($errors->any()): ?>
                <div class="toast align-items-center text-white bg-danger border-0 show" role="alert"
                    aria-live="assertive" aria-atomic="true" id="errorToast">
                    <div class="d-flex">
                        <div class="toast-body">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php echo e($error); ?>

                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
                            aria-label="Close"></button>
                    </div>
                </div>
            <?php endif; ?>
        </div>

    <div class="search-filter-row" style="display: flex; gap: 10px; margin-bottom: 20px;">

      <form class="search-bar" action="<?php echo e(route('staff.cargo_item_list')); ?>"  method="GET" style="flex: 1;">

        <input type="text" name="search" placeholder="Search..." value="<?php echo e(request('search')); ?>" style="margin-right: 10px;">
        <select name="route_code_id">
            <option value="">All Route Codes</option>
            <?php $__currentLoopData = $route_codes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $routeCode): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($routeCode->route_code_id); ?>" 
                    <?php echo e(request('route_code_id') == $routeCode->route_code_id ? 'selected' : ''); ?>>
                    <?php echo e($routeCode->route_code_name); ?>

                </option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>

        <select name="cargo_category_id">
            <option value="">All Categories</option>
            <?php $__currentLoopData = $cargo_categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($category->cargo_category_id); ?>" 
                    <?php echo e(request('cargo_category_id') == $category->cargo_category_id ? 'selected' : ''); ?>>
                    <?php echo e($category->cargo_category_name); ?>

                </option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
        <button type="submit">Filter</button>
      </form>

      <div class="add-vessel">
        <button type="button" id="saddCargoCategoryBtn" class="add-link-btn" title="Add Cargo Category">
          <i class="fa-solid fa-plus me-2"></i><i class="fa-solid fa-boxes-packing"></i>
        </button>
      </div>

    </div>

    <table class="cargo-item-table">
      <thead>
        <tr>
          <th>Route Code</th>
          <th>Category</th>
          <th>Description</th>
          <th>Freight</th>
          <th>With Measurement </th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php $__empty_1 = true; $__currentLoopData = $cargo_items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
          <tr>
            <td><?php echo e($c->routeCode->route_code_name ?? 'N/A'); ?></td>
            <td><?php echo e($c->cargo_category->cargo_category_name ?? 'N/A'); ?></td>
            <td><?php echo e($c->cargo_item_description); ?></td>
            <td><?php echo e($c->cargo_item_freight); ?></td>
            <td><?php echo e($c->cargo_item_measure_required); ?></td>
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

  <!-- Add Cargo Classification Modal -->
  <div id="saddCargoCategoryModal" class="modal-overlay" style="display: none">
    <div class="modal-content">
      
      <span class="close-btn" id="scloseCargoCategoryModal">&times;</span>
      <h3>Add Cargo Category</h3>
      <form id="saddCargoCategoryForm" 
            action="<?php echo e(route('staff.cargo_category_store')); ?>"
            method="POST">
        <?php echo csrf_field(); ?>
        <div class="rpmodal-row one-col">
          <div class="rpmodal-col">
            <label>Cargo Category Name <span class="text-danger">*</span></label>
            <input type="text" name="cargo_category_name" required>
          </div>
        </div>
        <button type="submit">Add Cargo Category</button>
      </form>
    </div>
  </div>

  <script>

    document.addEventListener('DOMContentLoaded', function () {

        const openBtn = document.getElementById('saddCargoCategoryBtn');
        const modal = document.getElementById('saddCargoCategoryModal');
        const closeBtn = document.getElementById('scloseCargoCategoryModal');

        // Open modal
        openBtn.addEventListener('click', function () {
            modal.style.display = 'flex';
        });

        // Close modal (X button)
        closeBtn.addEventListener('click', function () {
            modal.style.display = 'none';
        });

        // Close modal when clicking outside content
        window.addEventListener('click', function (e) {
            if (e.target === modal) {
                modal.style.display = 'none';
            }
        });

    });
    </script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Sophia\Documents\Capstone\resources\views/authorized/staff/scargo_item_list.blade.php ENDPATH**/ ?>