<footer class="site-footer">
    <div class="container">
        <div class="row">
            {{-- Brand & Description --}}
            <div class="col-lg-4 col-md-6 mb-4 mb-lg-0">
                <div class="footer-brand">
                    <img src="{{ asset('images/logo_w_name.svg') }}" alt="Lapulapu Shipping Lines" class="footer-logo">
                </div>
                <p class="footer-description mt-3">
                    Providing safe, reliable, and comfortable sea travel across the Visayas regions.
                </p>
                <div class="footer-socials mt-3">
                    <a href="https://www.facebook.com/profile.php?id=100064063478567" class="footer-social-link" aria-label="Facebook"><i class="fab fa-facebook-f"></i> <span class="footer-social-text">Facebook</span></a>
                </div>
            </div>

            {{-- Quick Links --}}
            <div class="col-lg-2 col-md-6 mb-4 mb-lg-0">
                <h5 class="footer-heading">Quick Links</h5>
                <ul class="footer-links">
                    <li><a href="{{ url('/') }}">Home</a></li>
                    <li><a href="{{ route('bookingtype') }}">Book Now</a></li>
                    <li><a href="{{ route('schedules') }}">Schedules & Rates</a></li>
                    <li><a href="{{ route('about') }}">About Us</a></li>
                </ul>
            </div>

            {{-- Support --}}
            <div class="col-lg-2 col-md-6 mb-4 mb-lg-0">
                <h5 class="footer-heading">Support</h5>
                <ul class="footer-links">
                    <li><a href="{{ route('faqs') }}">FAQs</a></li>
                    <li><a href="{{ route('contact') }}">Contact Us</a></li>
                </ul>
            </div>

            {{-- Contact Info --}}
            <div class="col-lg-4 col-md-6">
                <h5 class="footer-heading">Contact Information</h5>
                <ul class="footer-contact">
                    <li>
                        <i class="fas fa-map-marker-alt"></i>
                        <span>872 M.J. Cuenco Ave., Cebu City, Philippines</span>
                    </li>
                    <li>
                        <i class="fas fa-phone"></i>
                        <span>0909 189 7083</span>
                    </li>
                    <li>
                        <i class="fas fa-envelope"></i>
                        <span>lapulapushippinglinescorp@gmail.com</span>
                    </li>
                </ul>
            </div>
        </div>

        <hr class="footer-divider">

        <div class="row">
            <div class="col-12 text-center">
                <p class="footer-copyright mb-0">
                    &copy; {{ date('Y') }} Lapulapu Shipping Lines Corporation. All rights reserved.
                </p>
            </div>
        </div>
    </div>
</footer>
