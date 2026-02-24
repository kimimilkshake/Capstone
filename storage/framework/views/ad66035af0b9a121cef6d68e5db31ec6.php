<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('components.hero', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <div class="faqs-container my-5">
        <h3 class="faqs-title">Frequently Asked Questions</h3>

        <div class="faq-item">
            <button class="faq-question">
                <span>Are there discounts for passengers who are infants and children?</span>
                <span class="faq-toggle">+</span>
            </button>
            <div class="faq-answer">
                Yes, infants or children ranging from 0 to 2 years old are allowed to board free of charge.
                Children from 3 to 11 years old are given fifty percent (50%) discount on the regular fare.
            </div>
        </div>

        <div class="faq-item">
            <button class="faq-question">
                <span>How can qualified passengers avail of the Senior Citizen discount and Student discount?</span>
                <span class="faq-toggle">+</span>
            </button>
            <div class="faq-answer">
                Just present a valid Senior Citizen or Student ID at the ticketing office before purchase.
            </div>
        </div>

    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Sophia\Documents\Capstone\resources\views/passenger/faqs.blade.php ENDPATH**/ ?>