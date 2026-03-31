<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('components.hero', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="container my-5">
        <div class="row">
            <div class="col-md-6 my-5">
                <img src="<?php echo e(asset('images/travel.svg')); ?>" alt="Body Image" class="img-fluid body-image">
            </div>
            <div class="col-md-6 d-flex align-items-center justify-content-center">
                <p class="body-text">
                    <span class="highlight">CHOOSE</span>
                    <span class="newline">YOUR</span>
                    <span class="newline2">DESIRED BUNK</span>
                    <span class="description">Here at Lapulapu Shipping Lines, we give you the<br>
                        freedom to pick the bunks that suites you best!
                    </span>

                    <a href="<?php echo e(route('bookingtype')); ?>" class="btn btn-outline-primary mt-3 travel-btn">
                        GO TRAVEL
                    </a>
                </p>
            </div>
        </div>
    </div>

    <?php echo $__env->make('components.footer', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\clint\Desktop\Capstone\resources\views/passenger/homepage.blade.php ENDPATH**/ ?>