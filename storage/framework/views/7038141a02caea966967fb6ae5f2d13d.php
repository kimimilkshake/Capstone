<div class="toast-container position-fixed top-0 start-50 translate-middle-x p-3" 
     style="z-index: 9999; margin-top: 20px;">
    <?php if(session('success')): ?>
        <div class="toast align-items-center text-white bg-success border-0 fade" role="alert"
             aria-live="assertive" aria-atomic="true" id="successToast">
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
        <div class="toast align-items-center text-white bg-danger border-0 fade" role="alert"
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

<script>
document.addEventListener('DOMContentLoaded', function () {
    const successToastEl = document.getElementById('successToast');
    if (successToastEl) {
        new bootstrap.Toast(successToastEl, { delay: 3000 }).show();
    }

    const errorToastEl = document.getElementById('errorToast');
    if (errorToastEl) {
        new bootstrap.Toast(errorToastEl, { delay: 5000 }).show();
    }
});
</script><?php /**PATH C:\Users\Sophia\Documents\Capstone\resources\views/components/toast.blade.php ENDPATH**/ ?>