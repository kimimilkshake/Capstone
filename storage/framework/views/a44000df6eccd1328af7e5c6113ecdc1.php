<div id="globalToastContainer" 
     class="toast-container position-fixed top-0 start-50 translate-middle-x p-3" 
     style="z-index: 9999; margin-top: 20px;">

    
    <?php if(session('success')): ?>
        <div class="toast align-items-center text-white bg-success border-0 fade" role="alert"
             aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="fas fa-check-circle me-2"></i><?php echo e(session('success')); ?>

                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    <?php endif; ?>

    
    <?php if($errors->any()): ?>
        <div class="toast align-items-center text-white bg-danger border-0 fade" role="alert"
             aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php echo e($error); ?>

                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    <?php endif; ?>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    // ✅ AUTO SHOW ALL SESSION TOASTS
    document.querySelectorAll('.toast').forEach(toastEl => {
        new bootstrap.Toast(toastEl, { delay: 3000 }).show();
    });

});

// ✅ GLOBAL FUNCTION FOR AJAX (THIS IS THE IMPORTANT PART)
function showToast(message, type = 'success') {
    const container = document.getElementById('globalToastContainer');

    const toastEl = document.createElement('div');
    toastEl.className = `toast align-items-center text-white bg-${type} border-0 fade`;
    toastEl.role = 'alert';

    toastEl.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                ${type === 'success'
                    ? '<i class="fas fa-check-circle me-2"></i>'
                    : '<i class="fas fa-exclamation-circle me-2"></i>'}
                ${message}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;

    container.appendChild(toastEl);

    const toast = new bootstrap.Toast(toastEl, { delay: 3000 });
    toast.show();

    // remove after hiding (cleanup)
    toastEl.addEventListener('hidden.bs.toast', () => {
        toastEl.remove();
    });
}
</script><?php /**PATH C:\Users\clint\Desktop\Capstone\resources\views/components/toast.blade.php ENDPATH**/ ?>