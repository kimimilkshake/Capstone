<?php $__env->startSection('page-title', 'CARGO'); ?>
<?php $__env->startSection('content'); ?>
  <?php echo $__env->make('components.authHeader', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
  <?php echo $__env->make('components.admin_nav', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
  <div class="admin-body">
    <div class="avl-title">
      <h3>VIEW RATES</h3>
    </div>
    
    
    <?php if($errors->any()): ?>
      <div class="alert-wrapper">
        <div class="alert alert-danger">
          <strong>All fields are required.</strong><br>
          <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php echo e($error); ?><br>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
      </div>
    <?php endif; ?>

    
    <?php if(session('success')): ?>
      <div class="alert-wrapper">
        <div class="alert alert-success">
          <?php echo e(session('success')); ?>

        </div>
      </div>
    <?php endif; ?>

    <div class="search-filter-row" style="display: flex; gap: 10px; margin-bottom: 20px;">

      <form class="search-bar" action="<?php echo e(route('admin.cargo_item_list')); ?>"  method="GET" style="flex: 1;">

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
        <button type="button" id="aaddCargoCategoryBtn" class="add-link-btn" title="Add Cargo Category">
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
          <th>With Measurement Range?</th>
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

  <!-- Add Cargo Classification Modal -->
  <div id="aaddCargoCategoryModal" class="modal-overlay" style="display: none">
    <div class="modal-content">
      
      <span class="close-btn" id="acloseCargoCategoryModal">&times;</span>
      <h3>Add Cargo Category</h3>
      <form id="aaddCargoCategoryForm" 
            action="<?php echo e(route('admin.cargo_category_store')); ?>"
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

        const openBtn = document.getElementById('aaddCargoCategoryBtn');
        const modal = document.getElementById('aaddCargoCategoryModal');
        const closeBtn = document.getElementById('acloseCargoCategoryModal');

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
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Sophia\Documents\Capstone\resources\views/authorized/admin/cargo_item_list.blade.php ENDPATH**/ ?>