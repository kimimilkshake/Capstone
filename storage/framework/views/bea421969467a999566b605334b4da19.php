<?php $__env->startSection('page-title', 'REVIEW CARGO BOOKINGS'); ?>

<?php $__env->startSection('content'); ?>
<?php echo $__env->make('components.authHeader', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php echo $__env->make('components.staff_nav', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<div class="staff-body">
    <div class="svl-title">
        <h3>REVIEW CARGO BOOKINGS</h3>
    </div>

<div class="search-filter-row mb-4" style="display:flex; gap:10px;">
    <form class="search-bar d-flex gap-2" action="<?php echo e(route('cargo.bookings.pending')); ?>" method="GET" style="flex:1;">
        <input type="text" name="search" class="form-control" placeholder="Search by Ref No., Sender, or Consignee..." value="<?php echo e(request('search')); ?>">
        <button type="submit" class="btn btn-primary">Search</button>
        <?php if(request('search')): ?>
            <a href="<?php echo e(route('cargo.bookings.pending')); ?>" class="btn btn-outline-secondary">Clear</a>
        <?php endif; ?>
    </form>
</div>


    <table class="cargo-item-table">
        <thead>
            <tr>
                <th>Booking Ref #</th>
                <th>Sender</th>
                <th>Consignee</th>
                <th>Status</th>
                <th>Created</th>
                <th>Action</th>
            </tr>
        </thead>

        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $bookings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td><?php echo e($b->booking_code); ?></td>
                    <td><?php echo e(optional($b->sender)->sender_name ?? 'N/A'); ?></td>
                    <td><?php echo e(optional($b->consignee)->consignee_name ?? 'N/A'); ?></td>
                    <td><?php echo e($b->booking_status); ?></td>
                    <td><?php echo e($b->created_at->format('M d, Y')); ?></td>
                    <td class="d-flex gap-1">
                        <a href="<?php echo e(route('cargo.bookings.show', $b->booking_ref_no)); ?>" class="btn btn-sm btn-primary">View</a>
                        <a href="<?php echo e(route('cargo.bookings.edit', $b->booking_ref_no)); ?>" class="btn btn-sm btn-warning">Edit</a>
                        <?php if($b->booking_status === 'Pending'): ?>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="6" class="text-center">No pending bookings.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="pagination-container mt-3">
        <?php echo e($bookings->links('pagination::bootstrap-5')); ?>

    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\kirzt\Documents\GitHub\Capstone\resources\views/authorized/staff/pendingcargo.blade.php ENDPATH**/ ?>