<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('components.hero', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <div class="about-container my-4">
        <div class="about-sidebar">
            <ul>
                <li data-section="who_we_are">Who We Are</li>
                <li data-section="what_we_offer">What We Offer</li>
                <li data-section="vision_mission">Vision and Mission</li>
                <li data-section="vessels_about">Vessels</li>
                <li data-section="ports_of_call">Ports of Call</li>
            </ul>
        </div>
        <div class="about-content-fragment" id="about-content">
            <?php echo $__env->make('passenger.about_partials.who_we_are', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?> 
        </div>
    </div>

    <?php echo $__env->make('components.footer', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Shem\Desktop\Capstone\resources\views/passenger/about.blade.php ENDPATH**/ ?>