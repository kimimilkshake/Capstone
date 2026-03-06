<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('components.hero', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <div class="contact-container my-2">
        <div class="contact-row">
            <div class="contact-col">
                <div class="contact-card">
                    <h3 class="contact-title">MAIN OFFICE</h3>
                    <div class="main-office-pics">
                        <img src="<?php echo e(asset('images/office1.png')); ?>" alt="office picture 1" class="main-office-pic">
                        <img src="<?php echo e(asset('images/office2.png')); ?>" alt="office picture 2" class="main-office-pic">
                    </div>
                    <div class="main-office-details">
                        <p><i class="fa-solid fa-building me-3"></i>872 M.J. CUENCO AVENUE, BARANGAY LOREGA,  6000 CEBU CITY, PHILIPPINES</p>
                        <p><i class="fa-solid fa-phone me-3"></i>TEL. #: (032)  232-8864,  232-8865</p>
                        <p><i class="fa-solid fa-fax me-3"></i>FAX #: (032)  232-8863</p>
                        <p><i class="fa-solid fa-mobile-screen me-3"></i>CELLPHONE NO.  09091897083</p>
                        <p><i class="fa-solid fa-envelope me-3"></i>EMAIL: lapulapushippinglinescorp@gmail.com</p>
                        <p><i class="fa-solid fa-business-time me-3"></i>BUSINESS HOURS: 8AM TO 5PM MONDAY TO SATURDAY</p>
                        <a href="https://www.facebook.com/profile.php?id=100064063478567" target="_blank" rel="noopener noreferrer"><i class="fa-solid fa-thumbs-up me-3"></i>Facebook: Lapulapu Shipping Lines Corporation</a>
                    </div>
                    
                </div>
            </div>
            <div class="contact-col">
                <div class="contact-card">
                    <h3 class="contact-title">SEND US A MESSAGE!</h3>
                    <?php if(session('success')): ?>
                        <div class="alert contact-success">
                            <?php echo e(session('success')); ?>

                        </div>
                    <?php endif; ?>
                    <form action="<?php echo e(route('contact.send')); ?>" method="POST">
                        <?php echo csrf_field(); ?>
                        <label for="contact_name">Name:</label>
                        <input type="text" name="name" id="contact_name" required>

                        <label for="contact_email">Email:</label>
                        <input type="email" name="email" id="contact_email" required>

                        <label for="contact_subject">Subject:</label>
                        <input type="text" name="subject" id="contact_subject" required>

                        <label for="contact_message">Message:</label>
                        <textarea name="message" id="contact_message" required></textarea>

                        <button type="submit"><i class="fa-solid fa-paper-plane me-2"></i>SEND!</button>
                    </form>

                </div>
            </div>
        </div>
        <br>
        <div class="contact-row">
            <div class="contact-divider">
                <h3 class="contact-divtitle">TICKETING BOOTHS</h3>
            </div>
        </div>
        <br>
        <div class="contact-row">
            <div class="contact-col">
                <div class="contact-card">
                    <img src="<?php echo e(asset('images/LSLCCebuBooth.jpg')); ?>" alt="Cebu Ticketing Booth Map" class="ticketbooth-pic1">
                    <h4 class="ticketbooth-title">Cebu</h4>
                    <div class="ticketbooth-details">
                        <p>Pier 3 Ticket Office, V. Sotto St., Cebu City. </p>
                        <p>Last unit, CENRO Bldg., beside Julie's Bakeshop.</p>
                        <p>Open from 8:00 AM - 8:00 PM</p>
                    </div>
                </div>
            </div>
            <div class="contact-col">
                <div class="contact-card">
                    <img src="<?php echo e(asset('images/LSLCBaybayBooth.jpg')); ?>" alt="Baybay Ticketing Booth" class="ticketbooth-pic1">
                    <h4 class="ticketbooth-title">Baybay</h4>
                    <div class="ticketbooth-details">
                        <p>Andres Bonifacio St., Baybay City, near Baybay Port</p>
                        <p>Located at the ground floor, Lope Tang Building, beside OPPO Cellphones and Accessories. Near Baybay Port.</p>
                        <p>Open from 8:00 AM - 8:00 PM</p>
                    </div>
                </div>
            </div>
            <div class="contact-col">
                <div class="contact-card">
                    <img src="<?php echo e(asset('images/LSLCTalibonBooth.jpg')); ?>" alt="Talibon Ticketing Booth" class="ticketbooth-pic1">
                    <h4 class="ticketbooth-title">Talibon</h4>
                    <div class="ticketbooth-details">
                        <p>Carlos P. Garcia Ave., near Talibon Port</p>
                        <p>Open on Tuesday, Thursday, Saturday and Sunday from 8:00 AM - 8:00 PM</p>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Sophia\Documents\Capstone\resources\views/passenger/contact.blade.php ENDPATH**/ ?>