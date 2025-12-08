<header class="hero">

    <div class="hero-logo">
        <img src="<?php echo e(asset('images/logo_w_name.svg')); ?>" alt="Logo">
    </div>

    <nav class="hero-nav">
        <ul class="nav justify-content-center">
            <li class="nav-item"><a class="nav-link" href="<?php echo e(url('/')); ?>">HOME</a></li>
            <li class="nav-item"><a class="nav-link" href="<?php echo e(route('bookingtype')); ?>">BOOK NOW</a></li>
            <li class="nav-item"><a class="nav-link" href="<?php echo e(route('schedules')); ?>">SCHEDULES & RATES</a></li>
            <li class="nav-item"><a class="nav-link" href="<?php echo e(route('about')); ?>">ABOUT US</a></li>
            <li class="nav-item"><a class="nav-link" href="<?php echo e(route('faqs')); ?>">FAQs</a></li>
            <li class="nav-item"><a class="nav-link" href="<?php echo e(route('contact')); ?>">CONTACT US</a></li>
        </ul>
    </nav>

</header>
<?php /**PATH C:\Users\Sophia\Documents\Capstone\resources\views/components/hero.blade.php ENDPATH**/ ?>