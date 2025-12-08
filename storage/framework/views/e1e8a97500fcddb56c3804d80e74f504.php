<?php $__env->startSection('page-title', 'STAFF'); ?>
<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('components.authHeader', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo $__env->make('components.admin_nav', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <div class="admin-body">
        <div class="asl-title">
            <h3>STAFF LIST</h3>
        </div>
    
        <div class="search-filter-row" style="display: flex; gap: 10px; margin-bottom: 20px;">
            <form class="search-bar" action="<?php echo e(route('admin.staff_list')); ?>" method="GET" style="flex: 1;">
                <input type="text" name="search" placeholder="Search by name..." value="<?php echo e(request('search')); ?>">
                <button type="submit"><i class="fa-solid fa-magnifying-glass me-2"></i>Search</button>
            </form>

            <form method="GET" action="<?php echo e(route('admin.staff_list')); ?>">
                <select name="status" onchange="this.form.submit()"> 
                    <option value="">Filter by Status</option>
                    <option value="Active" <?php echo e(request('status') == 'Active' ? 'selected' : ''); ?>>Active</option>
                    <option value="Inactive" <?php echo e(request('status') == 'Inactive' ? 'selected' : ''); ?>>Inactive</option>
                </select>
            </form>
        </div>


        <table class="staff-table">
            <thead>
                <tr>
                    <th>Staff No.</th>
                    <th>Name</th>
                    <th>Date of Birth</th>
                    <th>Age</th>
                    <th>Gender</th>
                    <th>Username</th>
                    <th>Email Address</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $staff; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td><?php echo e($s->staff_id); ?></td>
                        <td><?php echo e($s->staff_name); ?></td>
                        <td><?php echo e($s->staff_dob); ?></td>
                        <td>
                            <?php echo e(\Carbon\Carbon::parse($s->staff_dob)->age); ?>

                        </td>
                        <td><?php echo e($s->staff_gender); ?></td>
                        <td><?php echo e($s->staff_user); ?></td>
                        <td><?php echo e($s->staff_email); ?></td>
                        <td><?php echo e($s->staff_status); ?></td>
                        <td>
                        <a href="<?php echo e(route('admin.staff_edit', $s->staff_id)); ?>" class="edit-icon">
                            <!-- Using Font Awesome pencil icon -->
                            <i class="fa fa-pencil" aria-hidden="true"></i>
                        </a>
                    </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="9" class="text-center">No staff accounts found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        <div class="pagination-container">
            <?php echo e($staff->appends(request()->query())->links('pagination::bootstrap-5')); ?>

        </div>
    </div>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Sophia\Documents\Capstone\resources\views/authorized/admin/staff_list.blade.php ENDPATH**/ ?>