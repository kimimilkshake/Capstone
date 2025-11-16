@extends('layouts.app')
@section('content')
    @include('components.hero'){{-- Head Nav --}}
    <div class="contact-container my-5">
        <div class="contact-row">
            <div class="contact-col">
                <div class="contact-card">
                    <h3 class="contact-title">MAIN OFFICE</h3>
                    <img src="{{ asset('images/office1.png') }}" alt="Logo" class="main-office-pic"><img src="{{ asset('images/office2.png') }}" alt="Logo" class="main-office-pic">
                    <p><i class="fa-solid fa-building me-3"></i>872 M.J. CUENCO AVENUE, BARANGAY LOREGA,  6000 CEBU CITY, PHILIPPINES</p>
                    <p><i class="fa-solid fa-phone me-3"></i>TEL. #: (032)  232-8864,  232-8865,  416-7288</p>
                    <p><i class="fa-solid fa-fax me-3"></i>FAX #: (032)  232-8863,  416-7043</p>
                    <p><i class="fa-solid fa-mobile-screen me-3"></i>CELLPHONE NO.  09091897083</p>
                    <p><i class="fa-solid fa-envelope me-3"></i>EMAIL: lapulines@yahoo.com</p>
                    <p><i class="fa-solid fa-business-time me-3"></i>BUSINESS HOURS: 8AM TO 5PM MONDAY TO SATURDAY</p>
                    <a href="https://www.facebook.com/profile.php?id=100064063478567" target="_blank" rel="noopener noreferrer"><i class="fa-solid fa-thumbs-up me-3"></i>Facebook: Lapulapu Shipping Lines Corporation</a>
                </div>
            </div>
            <div class="contact-col">
                <div class="contact-card">
                    <h3 class="contact-title">SEND US A MESSAGE!</h3>
                    <form action="#" method="POST">
                        @csrf
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
                    <img src="{{ asset('images/LSLCCebuBoothMap.png') }}" alt="Cebu Ticketing Booth Map" class="ticketbooth-pic1">
                    <h4 class="ticketbooth-title">Pier 3 Ticket Office, Cebu</h4>
                    <p>Only on-date tickets are sold here, no advance tickets.</p>
                    <p>V. Sotto St.,</p>
                </div>
            </div>
            <div class="contact-col">
                <div class="contact-card">
                    <img src="{{ asset('images/LSLCBaybayBooth.jpg') }}" alt="Baybay Ticketing Booth" class="ticketbooth-pic1">
                    <h4 class="ticketbooth-title">Lapulapu Shipping Lines Ticket Office, Baybay</h4>
                    <p>Located at the ground floor, Lope Tang Building, beside OPPO Cellphones and Accessories</p>
                    <p>Andres Bonifacio St., near Baybay Port</p>
                    <p>Open on Thursdays from 8:00 AM - 8:00 PM</p>
                    <p>Only on-date tickets are sold here, no advance tickets.</p>
                </div>
            </div>
            <div class="contact-col">
                <div class="contact-card">
                    <img src="{{ asset('images/LSLCTalibonBooth.jpg') }}" alt="Talibon Ticketing Booth" class="ticketbooth-pic1">
                    <h4 class="ticketbooth-title">Lapulapu Shipping Lines Ticket Office, Talibon</h4>
                    <p>ticket outlet is near Talibon Port</p>
                    <p>Open on Tuesday, Thursday, and Saturday from 8:00 AM - 8:00 PM</p>
                    <p>Only on-date tickets are sold here, no advance tickets.</p>
                </div>
            </div>
        </div>
    </div>
@endsection