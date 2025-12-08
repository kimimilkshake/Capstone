<?php $__env->startSection('page-title', 'Cargo Auto Placement'); ?>
<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('components.authHeader', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo $__env->make('components.staff_nav', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>


<div class="staff-body container">
  <h3>Cargo Auto Placement</h3>

  <?php if($errors->any()): ?>
    <div class="alert alert-danger">
      <ul class="mb-0">
        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <li><?php echo e($e); ?></li>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </ul>
    </div>
  <?php endif; ?>

  <?php if(session('result')): ?>
    <div class="alert alert-info">
      <h5 class="mb-2">Packing Result</h5>
      <pre class="mb-0" style="white-space:pre-wrap;word-break:break-word;"><?php echo e(json_encode(session('result'), JSON_PRETTY_PRINT)); ?></pre>
    </div>
  <?php endif; ?>

  <form id="placementForm" method="POST" action="<?php echo e(route('cargo.place')); ?>">
    <?php echo csrf_field(); ?>

    <div class="card mb-3">
      <div class="card-body">
        <h5 class="card-title">Hatch Dimensions</h5>
        <div class="row g-2">
          <div class="col-md-3">
            <label class="form-label"><strong>Width</strong></label>
            <input name="hatch_width" class="form-control" required value="<?php echo e(old('hatch_width')); ?>" />
          </div>
          <div class="col-md-3">
            <label class="form-label"><strong>Height</strong></label>
            <input name="hatch_height" class="form-control" required value="<?php echo e(old('hatch_height')); ?>" />
          </div>
          <div class="col-md-3">
            <label class="form-label"><strong>Depth</strong></label>
            <input name="hatch_depth" class="form-control" required value="<?php echo e(old('hatch_depth')); ?>" />
          </div>
        </div>
      </div>
    </div>

    <div class="card mb-3">
      <div class="card-body">
        <h5 class="card-title">Cargo Items</h5>

         <?php
        // Do not create a default empty row here.
        // If there is old input (after addRow/removeRow or validation), use it; otherwise start with no items.
        $oldItems = old('items', []);
        ?>

            <div id="items">
  <?php $__currentLoopData = $oldItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $it): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <div class="item row g-2 align-items-center mb-2" data-index="<?php echo e($index); ?>">
      <div class="col-md-3">
        <input name="items[<?php echo e($index); ?>][id]" class="form-control" placeholder="Item id" required value="<?php echo e($it['id'] ?? ''); ?>" />
      </div>
      <div class="col-md-2">
        <input name="items[<?php echo e($index); ?>][w]" class="form-control" placeholder="Width" required value="<?php echo e($it['w'] ?? ''); ?>" />
      </div>
      <div class="col-md-2">
        <input name="items[<?php echo e($index); ?>][h]" class="form-control" placeholder="Height" required value="<?php echo e($it['h'] ?? ''); ?>" />
      </div>
      <div class="col-md-2">
        <input name="items[<?php echo e($index); ?>][d]" class="form-control" placeholder="Depth" required value="<?php echo e($it['d'] ?? ''); ?>" />
      </div>
      <div class="col-md-1">
        <input name="items[<?php echo e($index); ?>][q]" class="form-control" placeholder="Qty" value="<?php echo e($it['q'] ?? 1); ?>" required />
      </div>
    </div>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>

<div class="mt-2">
  
  <button type="submit" formaction="<?php echo e(route('cargo.addRow')); ?>" formmethod="post" class="btn btn-secondary">Add Item</button>
</div>
    <div class="card mb-3">
      <div class="card-body">
        <h5 class="card-title">Items Summary</h5>
        <div class="table-responsive">
          <table class="table table-bordered" id="itemsTable">
            <thead class="table-light">
              <tr>
                <th style="width:48px">#</th>
                <th>Item id</th>
                <th>Width</th>
                <th>Height</th>
                <th>Depth</th>
                <th style="width:80px">Qty</th>
                <th style="width:110px">Action</th>
              </tr>
            </thead>
            <tbody>
              <?php $__currentLoopData = $oldItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $it): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                  <td><?php echo e($index + 1); ?></td>
                  <td><?php echo e($it['id'] ?? ''); ?></td>
                  <td><?php echo e($it['w'] ?? ''); ?></td>
                  <td><?php echo e($it['h'] ?? ''); ?></td>
                  <td><?php echo e($it['d'] ?? ''); ?></td>
                  <td><?php echo e($it['q'] ?? 1); ?></td>
                  <td>
                    
                    <button type="submit"
                            name="remove_index"
                            value="<?php echo e($index); ?>"
                            formaction="<?php echo e(route('cargo.removeRow')); ?>"
                            formmethod="post"
                            class="btn btn-sm btn-danger"
                    >
                      Remove
                    </button>
                  </td>
                </tr>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="d-flex gap-2">
      <button type="submit" class="btn btn-primary">Check Placement</button>
      <button type="reset" class="btn btn-outline-secondary">Reset</button>
    </div>
  </form>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Sophia\Documents\Capstone\resources\views/authorized/cargoautoplacement.blade.php ENDPATH**/ ?>