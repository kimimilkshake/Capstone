<?php $__env->startSection('content'); ?>
<?php echo $__env->make('components.hero', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<div class="container my-5">
    <div class="card shadow-sm mx-auto" style="max-width:700px; background-color:#f0f0f0;">
        <div class="card-header bg-success text-white text-center">
            <h5 class="mb-0">CARGO BOOKING SUCCESS</h5>
        </div>

        <div class="card-body text-center">
            <div class="my-4">
                <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
            </div>
            <h4 class="text-success mb-3">Booking Submitted Successfully!</h4>
            <p>Your cargo booking is currently <strong>Pending</strong>.</p>
            <p>Please wait for approval from our staff.</p>
            <p>Actively check your emails for updates on the confirmation.</p>

            <a href="<?php echo e(url('/')); ?>" class="btn btn-primary fw-bold mt-4 px-4 py-2">
                Back to Home
            </a>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Shem\Desktop\Capstone\resources\views/passenger/cargo_success.blade.php ENDPATH**/ ?>