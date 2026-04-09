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

    <!-- Mobile Navigation -->
    <div class="mobile-nav">
        <button class="mobile-nav-toggle" id="mobileNavToggle">
            <i class="fas fa-bars"></i>
        </button>
        <div class="mobile-nav-dropdown" id="mobileNavDropdown">
            <a href="<?php echo e(url('/')); ?>" class="mobile-nav-link">HOME</a>
            <a href="<?php echo e(route('bookingtype')); ?>" class="mobile-nav-link">BOOK NOW</a>
            <a href="<?php echo e(route('schedules')); ?>" class="mobile-nav-link">SCHEDULES & RATES</a>
            <a href="<?php echo e(route('about')); ?>" class="mobile-nav-link">ABOUT US</a>
            <a href="<?php echo e(route('faqs')); ?>" class="mobile-nav-link">FAQs</a>
            <a href="<?php echo e(route('contact')); ?>" class="mobile-nav-link">CONTACT US</a>
        </div>
    </div>

</header>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const toggle = document.getElementById('mobileNavToggle');
        const dropdown = document.getElementById('mobileNavDropdown');

        if (toggle && dropdown) {
            toggle.addEventListener('click', function(e) {
                e.stopPropagation();
                dropdown.classList.toggle('show');
                toggle.classList.toggle('active');
            });

            // Close dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (!toggle.contains(e.target) && !dropdown.contains(e.target)) {
                    dropdown.classList.remove('show');
                    toggle.classList.remove('active');
                }
            });

            // Close dropdown when clicking a link
            dropdown.querySelectorAll('.mobile-nav-link').forEach(link => {
                link.addEventListener('click', function() {
                    dropdown.classList.remove('show');
                    toggle.classList.remove('active');
                });
            });
        }
    });
</script>
<?php /**PATH C:\Users\clint\Desktop\Capstone\resources\views/components/hero.blade.php ENDPATH**/ ?>