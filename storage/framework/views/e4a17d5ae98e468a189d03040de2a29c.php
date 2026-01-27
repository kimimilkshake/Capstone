<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('components.hero', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="container text-center my-5"> 
        <div class="row">
            <div class="col-md-6">
                <img src="<?php echo e(asset('images/sched.svg')); ?>" alt="Image 1" class="img-fluid side-by-side">
            </div>
            <div class="col-md-6">
                <img src="<?php echo e(asset('images/rates.svg')); ?>" alt="Image 2" class="img-fluid side-by-side">
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Sophia\Documents\Capstone\resources\views/passenger/schedules.blade.php ENDPATH**/ ?>