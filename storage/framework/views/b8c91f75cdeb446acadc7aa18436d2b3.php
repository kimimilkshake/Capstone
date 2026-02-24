<?php $__env->startSection('page-title', 'VOYAGES'); ?>
<?php $__env->startSection('content'); ?>
<?php echo $__env->make('components.authHeader', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php echo $__env->make('components.admin_nav', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<div class="admin-body">
  <div class="avl-title">
    <h3>ROUTES AND PORTS</h3>
  </div>

  <!--SEARCH BAR-->
  <div class="search-add-row" style="display: flex; gap: 10px; margin-bottom: 20px;">
    <form class="search-bar" action="<?php echo e(route('admin.route_port_list')); ?>" method="GET" style="flex: 1;">
      <input type="text" name="search" placeholder="Search by origin, destination, or ports" value="<?php echo e(request('search')); ?>">
      <button type="submit">
        <i class="fa-solid fa-magnifying-glass me-2"></i>Search
      </button>
    </form>

    <div class="add-vessel">
      <button type="button" id="addRouteCodeBtn" class="add-link-btn">
        <i class="fa-solid fa-plus me-2"></i>Add Route Code
      </button>
    </div>

    <div class="add-vessel">
      <button type="button" id="addRoutePortBtn" class="add-link-btn">
        <i class="fa-solid fa-plus me-2"></i>Add Route and Port
      </button>
    </div>
  </div>

  <table class="rp-table">
    <thead>
      <th>Route Code</th>
      <th>Route Origin</th>
      <th>Route Destination</th>
      <th>Port Origin</th>
      <th>Port Destination</th>
      <th>Action</th>
    </thead>
    <tbody>
      <?php $__empty_1 = true; $__currentLoopData = $route_port; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <tr>
          <td><?php echo e($rp->routeCode->route_code_name ?? 'N/A'); ?></td>
          <td><?php echo e($rp->route_origin); ?></td>
          <td><?php echo e($rp->route_destination); ?></td>
          <td>
            <?php echo e($rp->port_origin_name); ?>,
            <?php echo e($rp->port_origin_city); ?>,
            <?php echo e($rp->port_origin_province); ?>

          </td>
          <td>
            <?php echo e($rp->port_destination_name); ?>,
            <?php echo e($rp->port_destination_city); ?>,
            <?php echo e($rp->port_destination_province); ?>

          </td>
          <td>
            <button 
                type="button" 
                class="editRouteBtn link-btn"
                title="Edit Route and Port"
                data-id="<?php echo e($rp->route_port_id); ?>" 
                data-origin="<?php echo e($rp->route_origin); ?>" 
                data-destination="<?php echo e($rp->route_destination); ?>"
                data-port_origin_name="<?php echo e($rp->port_origin_name); ?>"
                data-port_origin_city="<?php echo e($rp->port_origin_city); ?>"
                data-port_origin_province="<?php echo e($rp->port_origin_province); ?>"
                data-port_destination_name="<?php echo e($rp->port_destination_name); ?>"
                data-port_destination_city="<?php echo e($rp->port_destination_city); ?>"
                data-port_destination_province="<?php echo e($rp->port_destination_province); ?>"
                data-route_code_id="<?php echo e($rp->route_code_id); ?>"
            >
                <i class="fa fa-pencil"></i>
            </button>
        </td>

        </tr>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <tr>
          <td colspan="6" class="text-center">No routes and ports found.</td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>

  <div class="mt-3">
    
    <?php echo e($route_port->appends(['search' => request('search')])->links('pagination::bootstrap-5')); ?>

  </div>
</div>

<!-- Add Route Code Modal -->
<div id="addRouteCodeModal" class="modal-overlay" style="display:none;">
  <div class="modal-content">
    <span class="close-btn" id="closeAddRouteCodeModal">&times;</span>
    <h3>Add Route Code</h3>

    <form id="addRouteCodeForm">
      <?php echo csrf_field(); ?>
      <div class="rpmodal-row one-col">
        <div class="rpmodal-col">
          <label>Route Code Name <span class="text-danger">*</span></label>
          <input type="text" name="route_code_name" required>
        </div>
      </div>

      <button type="submit">Add Route Code</button>
    </form>
  </div>
</div>



<!-- Add Route & Port Modal -->
<div id="addRoutePortModal" class="modal-overlay" style="display:none;">
  <div class="modal-content">
    <span class="close-btn" id="closeAddModal">&times;</span>
    <h3>Add Route and Port</h3>

    <form id="addRoutePortForm">
      <?php echo csrf_field(); ?>

      <!-- 2 columns for route -->
      <div class="rpmodal-row three-col">
        <div class="rpmodal-col">
          <label>Route Code <span class="text-danger">*</span></label>
          <select name="route_code_id" id="route_code_id">
            <option value="">Select Route Code</option>
            <?php $__currentLoopData = $route_codes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $code): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($code->route_code_id); ?>"><?php echo e($code->route_code_name); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </select>
        </div>
        <div class="rpmodal-col">
          <label>Route Origin <span class="text-danger">*</span></label>
          <input type="text" name="route_origin" required>
        </div>
        <div class="rpmodal-col">
          <label>Route Destination <span class="text-danger">*</span></label>
          <input type="text" name="route_destination" required>
        </div>
      </div>

      <!-- 3 columns for Port Origin -->
      <div class="rpmodal-row three-col">
        <div class="rpmodal-col">
          <label>Port Origin Name <span class="text-danger">*</span></label>
          <input type="text" name="port_origin_name" required>
        </div>
        <div class="rpmodal-col">
          <label>Port Origin City <span class="text-danger">*</span></label>
          <input type="text" name="port_origin_city" required>
        </div>
        <div class="rpmodal-col">
          <label>Port Origin Province <span class="text-danger">*</span></label>
          <input type="text" name="port_origin_province" required>
        </div>
      </div>

      <!-- 3 columns for Port Destination -->
      <div class="rpmodal-row three-col">
        <div class="rpmodal-col">
          <label>Port Destination Name <span class="text-danger">*</span></label>
          <input type="text" name="port_destination_name" required>
        </div>
        <div class="rpmodal-col">
          <label>Port Destination City <span class="text-danger">*</span></label>
          <input type="text" name="port_destination_city" required>
        </div>
        <div class="rpmodal-col">
          <label>Port Destination Province<span class="text-danger">*</span></label>
          <input type="text" name="port_destination_province" required>
        </div>
      </div>

      <button type="submit">Add Route & Port</button>
    </form>
  </div>
</div>


<!-- Edit Route & Port Modal -->
<div id="editRoutPortModal" class="modal-overlay" style="display:none;">
  <div class="modal-content">
    <span class="close-btn" id="closeEditModal">&times;</span>
    <h3>Edit Route and Port</h3>

    <form id="editRoutePortForm">
      <?php echo csrf_field(); ?>
      <?php echo method_field('PUT'); ?>

      <input type="hidden" name="route_port_id" id="editRoutePortId">


      <div class="rpmodal-row three-col">
        <div class="rpmodal-col">
          <label>Route Code <span class="text-danger">*</span></label>
          <select name="route_code_id" id="editRouteCodeId">
            <option value="">Select Route Code</option>
            <?php $__currentLoopData = $route_codes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $routeCode): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($routeCode->route_code_id); ?>"><?php echo e($routeCode->route_code_name); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </select>
        </div>
        <div class="rpmodal-col">
          <label>Route Origin <span class="text-danger">*</span></label>
          <input type="text" name="route_origin" id="editRouteOrigin" required>
        </div>
        <div class="rpmodal-col">
          <label>Route Destination <span class="text-danger">*</span></label>
          <input type="text" name="route_destination" id="editRouteDestination" required>
        </div>
      </div>

      <div class="rpmodal-row three-col">
        <div class="rpmodal-col">
          <label>Port Origin Name <span class="text-danger">*</span></label>
          <input type="text" name="port_origin_name" id="editPortOriginName" required>
        </div>
        <div class="rpmodal-col">
          <label>Port Origin City <span class="text-danger">*</span></label>
          <input type="text" name="port_origin_city" id="editPortOriginCity" required>
        </div>
        <div class="rpmodal-col">
          <label>Port Origin Province <span class="text-danger">*</span></label>
          <input type="text" name="port_origin_province" id="editPortOriginProvince" required>
        </div>
      </div>
      <div class="rpmodal-row three-col">
        <div class="rpmodal-col">
          <label>Port Destination Name <span class="text-danger">*</span></label>
          <input type="text" name="port_destination_name" id="editPortDestinationName" required>
        </div>
        <div class="rpmodal-col">
          <label>Port Destination City <span class="text-danger">*</span></label>
          <input type="text" name="port_destination_city" id="editPortDestinationCity" required>
        </div>
        <div class="rpmodal-col">
          <label>Port Destination Province<span class="text-danger">*</span></label>
          <input type="text" name="port_destination_province" id="editPortDestinationProvince" required>
        </div>
      </div>
      <button type="submit">Save Changes</button>
    </form>
  </div>
</div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script src="<?php echo e(asset('js/route_port_modal.js')); ?>"></script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\kirzt\Documents\GitHub\Capstone\resources\views/authorized/admin/route_port_list.blade.php ENDPATH**/ ?>