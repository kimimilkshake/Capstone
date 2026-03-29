@extends('layouts.app')
@section('content')
    @include('components.hero'){{-- Head Nav --}}
    <div class="faqs-container my-5">
        <h2 class="faqs-title">Frequently Asked Questions</h2>

        <br>
        <!--GENERAL BOARDING AND LOGISTICS FAQs-->
        <h4>General Boarding and Logistics</h4>
        <div class="faq-item">
            <button class="faq-question">
                <span>Where are the vessels of Lapulapu Shipping Lines Corp. docked at the Port of Cebu?</span>
                <span class="faq-toggle">+</span>
            </button>
            <div class="faq-answer">
                Our vessels are docked at <strong>Pier 2, Port of Cebu</strong>.
            </div>
        </div>
        <div class="faq-item">
            <button class="faq-question">
                <span>Where is my E-ticket copy?</span>
                <span class="faq-toggle">+</span>
            </button>
            <div class="faq-answer">
                Your E-ticket will be sent to the email address you used during your booking. Please check your inbox or spam folder. 
            </div>
        </div>
        <div class="faq-item">
            <button class="faq-question">
                <span>My trip has been canceled. Will my ticket be refunded or revalidated?</span>
                <span class="faq-toggle">+</span>
            </button>
            <div class="faq-answer">
                Yes. You may choose to request a refund or have your ticket revalidated <strong>within 30 days</strong>.
            </div>
        </div>

        <br>
        <!--PASSENGER BOOKING FAQs-->
        <h4>Passenger Booking</h4>
        <div class="faq-item">
            <button class="faq-question">
                <span>Are there discounts for passengers who are infants and children?</span>
                <span class="faq-toggle">+</span>
            </button>
            <div class="faq-answer">
                Yes. Fare discounts vary depending on the route:
                <ul>
                    <li><strong>Cebu - Baybay - Cebu Route:</strong>
                        <ul>
                            <li>Children 3–11 years old are charged <strong>half fare</strong>.</li>
                            <li>Children below 3 years old are charged <strong>25% of the regular fare</strong>.</li>
                        </ul>
                    </li>

                    <li><strong>Cebu - Talibon - Cebu Route:</strong>
                        <ul>
                            <li>Children 3–11 years old are charged <strong>half fare</strong>.</li>
                            <li>Children below 3 years old may <strong>travel free of charge</strong>.</li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>

        <div class="faq-item">
            <button class="faq-question">
                <span>How can qualified passengers avail of the Senior Citizen discount and Student discount?</span>
                <span class="faq-toggle">+</span>
            </button>
            <div class="faq-answer">
                Qualified passengers must upload or present a valid ID during the booking process. 
            </div>
        </div>

        <div class="faq-item">
            <button class="faq-question">
                <span>What are the requirements for pregnant passengers?</span>
                <span class="faq-toggle">+</span>
            </button>
            <div class="faq-answer">
                Pregnant passengers must present a medical certificate confirming that they are fit to travel.
            </div>
        </div>

        <div class="faq-item">
            <button class="faq-question">
                <span>What are the requirements for sick passengers?</span>
                <span class="faq-toggle">+</span>
            </button>
            <div class="faq-answer">
                Passengers who are ill must present a medical certificate stating that they are fit to travel.
            </div>
        </div>

        <div class="faq-item">
            <button class="faq-question">
                <span>What is the minimum age for travelling alone?</span>
                <span class="faq-toggle">+</span>
            </button>
            <div class="faq-answer">
                Passengers must be at least <strong>18 years old</strong> to travel alone. 
            </div>
        </div>

        <div class="faq-item">
            <button class="faq-question">
                <span>How many days in advance can I purchase my tickets?</span>
                <span class="faq-toggle">+</span>
            </button>
            <div class="faq-answer">
                Tickets may be purchased up to <strong>7 days</strong> before the sailing date.
            </div>
        </div>

        <div class="faq-item">
            <button class="faq-question">
                <span>Where and how may I ask for a refund of my ticket?</span>
                <span class="faq-toggle">+</span>
            </button>
            <div class="faq-answer">
                Refund requests may be made by contacting our office or visiting our ticketing booth.
            </div>
        </div>

        <div class="faq-item">
            <button class="faq-question">
                <span>I lost my ticket, can you give me a refund or can you issue another ticket?</span>
                <span class="faq-toggle">+</span>
            </button>
            <div class="faq-answer">
                Unfortunately, lost tickets are not eligible for refunds or reissuance.
            </div>
        </div>

        <div class="faq-item">
            <button class="faq-question">
                <span>I want to change my accommodation from Economy to Aircon. How can I upgrade my passenger ticket?</span>
                <span class="faq-toggle">+</span>
            </button>
            <div class="faq-answer">
                Accommodation upgrades may be possible depending on availability. Please contact our office or visit our ticketing booths for assistance. 
            </div>
        </div>

        <div class="faq-item">
            <button class="faq-question">
                <span>How many days from sailing date can I ask for a refund or rebooking of my ticket?</span>
                <span class="faq-toggle">+</span>
            </button>
            <div class="faq-answer">
                Refunds or rebookings may be requested as long as the passenger has not boarded the vessel and the ticket is still within its <strong>30-day</strong> validity period. 
            </div>
        </div>

        <div class="faq-item">
            <button class="faq-question">
                <span>Do you issue an official receipt for the purchase of passenger tickets?</span>
                <span class="faq-toggle">+</span>
            </button>
            <div class="faq-answer">
                The passenger ticket itself serves as the official receipt. 
            </div>
        </div>

        <div class="faq-item">
            <button class="faq-question">
                <span>How long is the validity of my ticket?</span>
                <span class="faq-toggle">+</span>
            </button>
            <div class="faq-answer">
                Passenger tickets are valid for <strong>30 days</strong> after the sailing date. 
            </div>
        </div>

        <div class="faq-item">
            <button class="faq-question">
                <span>What are the requirements upon boarding the vessel?</span>
                <span class="faq-toggle">+</span>
            </button>
            <div class="faq-answer">
                Passengers must present the follwing upon boarding:
                <ul>
                    <li>Valid ID</li>
                    <li>Passenger ticket</li>
                </ul>
            </div>
        </div>

        <div class="faq-item">
            <button class="faq-question">
                <span>Where and what time should I board the vessel?</span>
                <span class="faq-toggle">+</span>
            </button>
            <div class="faq-answer">
                Passngers are advised to arrive at the port at least <strong>2 hours before the scheduled departure time</strong>. Boarding will take place at the designated boarding area of the port. Please check your ticket for specific boarding instructions.
            </div>
        </div>

        <div class="faq-item">
            <button class="faq-question">
                <span>The voyage for a certain sailing date is already fully booked. Can I still board the vessel as a chance passenger?</span>
                <span class="faq-toggle">+</span>
            </button>
            <div class="faq-answer">
                You may inquire about chance passenger availability by visiting our ticketing booths in person. 
            </div>
        </div>

        <div class="faq-item">
            <button class="faq-question">
                <span>Do you provide free meals for the passengers?</span>
                <span class="faq-toggle">+</span>
            </button>
            <div class="faq-answer">
                No. However, a canteen is available on board where passengers may purchase food and drinks.
            </div>
        </div>

        <div class="faq-item">
            <button class="faq-question">
                <span>I want to travel with my pet. Is this possible?</span>
                <span class="faq-toggle">+</span>
            </button>
            <div class="faq-answer">
                Yes, pets are allowed. However: 
                <ul>
                    <li>Pets must be booked as cargo.</li>
                    <li>The pet may stay with you in your designated bunk bed.</li>
                    <li>Pets are not allowed in air-conditioned accommodations.</li>
                </ul>
            </div>
        </div>

        <!--CARGO BOOKING FAQs-->
        <br>
        <h4>Cargo Booking</h4>
        
        <div class="faq-item">
            <button class="faq-question">
                <span>Is there insurance for my cargo?</span>
                <span class="faq-toggle">+</span>
            </button>
            <div class="faq-answer">
                The shipping line does not provide cargo insurance. Cargo owners are responsible for arranging their own insurance. 
            </div>
        </div>
        
        <div class="faq-item">
            <button class="faq-question">
                <span>Can I book cargo without being a passenger on the same trip?</span>
                <span class="faq-toggle">+</span>
            </button>
            <div class="faq-answer">
                Yes, cargo may be booked even when the sender is not travelling on the vessel. 
            </div>
        </div>

        <div class="faq-item">
            <button class="faq-question">
                <span>What is the "Cut-off Time" for cargo loading?</span>
                <span class="faq-toggle">+</span>
            </button>
            <div class="faq-answer">
                The <strong>cargo loading cut-off time is 6:00 PM</strong>, while the office closes at 5:00 PM. So please make sure to <strong>book your cargo before 5:00 PM</strong>.
            </div>
        </div>

        <div class="faq-item">
            <button class="faq-question">
                <span>How many days in advance can I book for cargo?</span>
                <span class="faq-toggle">+</span>
            </button>
            <div class="faq-answer">
                Cargo bookings can be made up to <strong>7 days</strong> in advance
            </div>
        </div>

        <div class="faq-item">
            <button class="faq-question">
                <span>Can I book a Rolling Cargo?</span>
                <span class="faq-toggle">+</span>
            </button>
            <div class="faq-answer">
                No, rolling cargo bookings are not accepted. 
            </div>
        </div>

        <div class="faq-item">
            <button class="faq-question">
                <span>Can I book cargo for cadaver?</span>
                <span class="faq-toggle">+</span>
            </button>
            <div class="faq-answer">
                Yes, cadaver transport is allowed. You must provide the following documents:
                <ul>
                    <li>Death certificate</li>
                    <li>Permit to Transport</li>
                </ul>
            </div>
        </div>

        <div class="faq-item">
            <button class="faq-question">
                <span>What are prohibited cargoes on board?</span>
                <span class="faq-toggle">+</span>
            </button>
            <div class="faq-answer">
                The following cargoes are prohibited on board:
                <ul>
                    <li>Explosives</li>
                    <li>Combustible materials (e.g., gasoline)</li>
                    <li>Acids and Corrosive substances</li>
                    <li>Radioactive materials</li>
                </ul>
            </div>
        </div>

        <div class="faq-item">
            <button class="faq-question">
                <span>What documents are required to ship a motorcycle?</span>
                <span class="faq-toggle">+</span>
            </button>
            <div class="faq-answer">
                You must present the motorcyle's <strong>Official Receipt (OR)</strong> and <strong>Certificate of Registration (CR)</strong>.
            </div>
        </div>

        <div class="faq-item">
            <button class="faq-question">
                <span>Do I need to drain the fuel before shipping my motorcycle?</span>
                <span class="faq-toggle">+</span>
            </button>
            <div class="faq-answer">
                No, draining the fuel is not required.
            </div>
        </div>

        <div class="faq-item">
            <button class="faq-question">
                <span>Does the shipping line provide crates for fragile appliances?</span>
                <span class="faq-toggle">+</span>
            </button>
            <div class="faq-answer">
                No, customers are responsible for providing their own packaging or crates.
            </div>
        </div>

        <div class="faq-item">
            <button class="faq-question">
                <span>Can I ship second-hand appliances?</span>
                <span class="faq-toggle">+</span>
            </button>
            <div class="faq-answer">
                Yes, second-hand appliances may be shipped as cargo. 
            </div>
        </div>

    </div>

    @include('components.footer')
@endsection
