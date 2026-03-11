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
                Secret, trip mo lang!
            </div>
        </div>
        <div class="faq-item">
            <button class="faq-question">
                <span>Where is my E-ticket copy?</span>
                <span class="faq-toggle">+</span>
            </button>
            <div class="faq-answer">
                Secret, trip mo lang!
            </div>
        </div>
        <div class="faq-item">
            <button class="faq-question">
                <span>My trip has been canceled. Will my ticket be refunded or revalidated?</span>
                <span class="faq-toggle">+</span>
            </button>
            <div class="faq-answer">
                Secret, trip mo lang!
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

        <div class="faq-item">
            <button class="faq-question">
                <span>What are the requirements for pregnant passengers?</span>
                <span class="faq-toggle">+</span>
            </button>
            <div class="faq-answer">
                Secret, trip mo lang!
            </div>
        </div>

        <div class="faq-item">
            <button class="faq-question">
                <span>What is the minimum age for travelling alone?</span>
                <span class="faq-toggle">+</span>
            </button>
            <div class="faq-answer">
                Secret, trip mo lang!
            </div>
        </div>

        <div class="faq-item">
            <button class="faq-question">
                <span>How many days in advance can I purchase my tickets?</span>
                <span class="faq-toggle">+</span>
            </button>
            <div class="faq-answer">
                Secret, trip mo lang!
            </div>
        </div>

        <div class="faq-item">
            <button class="faq-question">
                <span>Where and how may I ask for a refund of my ticket?</span>
                <span class="faq-toggle">+</span>
            </button>
            <div class="faq-answer">
                Secret, trip mo lang!
            </div>
        </div>

        <div class="faq-item">
            <button class="faq-question">
                <span>I lost my ticket, can you give me a refund or can you issue another ticket?</span>
                <span class="faq-toggle">+</span>
            </button>
            <div class="faq-answer">
                Secret, trip mo lang!
            </div>
        </div>

        <div class="faq-item">
            <button class="faq-question">
                <span>I want to change my accommodation from Economy to Aircon. How can I upgrade my passenger ticket?</span>
                <span class="faq-toggle">+</span>
            </button>
            <div class="faq-answer">
                Secret, trip mo lang!
            </div>
        </div>

        <div class="faq-item">
            <button class="faq-question">
                <span>How many days from sailing date can I ask for a refund or rebooking of my ticket?</span>
                <span class="faq-toggle">+</span>
            </button>
            <div class="faq-answer">
                Secret, trip mo lang!
            </div>
        </div>

        <div class="faq-item">
            <button class="faq-question">
                <span>Do you issue an official receipt for the purchase of passenger tickets?</span>
                <span class="faq-toggle">+</span>
            </button>
            <div class="faq-answer">
                Secret, trip mo lang!
            </div>
        </div>

        <div class="faq-item">
            <button class="faq-question">
                <span>How long is the validity of my ticket?</span>
                <span class="faq-toggle">+</span>
            </button>
            <div class="faq-answer">
                Secret, trip mo lang!
            </div>
        </div>

        <div class="faq-item">
            <button class="faq-question">
                <span>What are the requirements upon boarding the vessel?</span>
                <span class="faq-toggle">+</span>
            </button>
            <div class="faq-answer">
                Secret, trip mo lang!
            </div>
        </div>

        <div class="faq-item">
            <button class="faq-question">
                <span>Where and what time should I board the vessel?</span>
                <span class="faq-toggle">+</span>
            </button>
            <div class="faq-answer">
                Secret, trip mo lang!
            </div>
        </div>

        <div class="faq-item">
            <button class="faq-question">
                <span>The voyage for a certain sailing date is already fully booked. Can I still board the vessel as a chance passenger?</span>
                <span class="faq-toggle">+</span>
            </button>
            <div class="faq-answer">
                Secret, trip mo lang!
            </div>
        </div>

        <div class="faq-item">
            <button class="faq-question">
                <span>Do you provide free meals for the passengers?</span>
                <span class="faq-toggle">+</span>
            </button>
            <div class="faq-answer">
                Secret, trip mo lang!
            </div>
        </div>

        <div class="faq-item">
            <button class="faq-question">
                <span>I want to travel with my pet. Is this possible?</span>
                <span class="faq-toggle">+</span>
            </button>
            <div class="faq-answer">
                Secret, trip mo lang!
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
                Secret, trip mo lang!
            </div>
        </div>
        
        <div class="faq-item">
            <button class="faq-question">
                <span>Can I book cargo without being a passenger on the same trip?</span>
                <span class="faq-toggle">+</span>
            </button>
            <div class="faq-answer">
                Secret, trip mo lang!
            </div>
        </div>

        <div class="faq-item">
            <button class="faq-question">
                <span>What is the "Cut-off Time" for cargo loading?</span>
                <span class="faq-toggle">+</span>
            </button>
            <div class="faq-answer">
                Secret, trip mo lang!
            </div>
        </div>

        <div class="faq-item">
            <button class="faq-question">
                <span>How many days in advance can I book for cargo?</span>
                <span class="faq-toggle">+</span>
            </button>
            <div class="faq-answer">
                Secret, trip mo lang!
            </div>
        </div>

        <div class="faq-item">
            <button class="faq-question">
                <span>Can I book a Rolling Cargo?</span>
                <span class="faq-toggle">+</span>
            </button>
            <div class="faq-answer">
                Secret, trip mo lang!
            </div>
        </div>

        <div class="faq-item">
            <button class="faq-question">
                <span>Can I book cargo for cadaver?</span>
                <span class="faq-toggle">+</span>
            </button>
            <div class="faq-answer">
                Secret, trip mo lang!
            </div>
        </div>

        <div class="faq-item">
            <button class="faq-question">
                <span>What are prohibited cargoes on board?</span>
                <span class="faq-toggle">+</span>
            </button>
            <div class="faq-answer">
                Secret, trip mo lang!
            </div>
        </div>

        <div class="faq-item">
            <button class="faq-question">
                <span>What documents are required to ship a motorcycle?</span>
                <span class="faq-toggle">+</span>
            </button>
            <div class="faq-answer">
                Secret, trip mo lang!
            </div>
        </div>

        <div class="faq-item">
            <button class="faq-question">
                <span>Do I need to drain the fuel before shipping my motorcycle?</span>
                <span class="faq-toggle">+</span>
            </button>
            <div class="faq-answer">
                Secret, trip mo lang!
            </div>
        </div>

        <div class="faq-item">
            <button class="faq-question">
                <span>Does the shipping line provide crates for fragile appliances?</span>
                <span class="faq-toggle">+</span>
            </button>
            <div class="faq-answer">
                Secret, trip mo lang!
            </div>
        </div>

        <div class="faq-item">
            <button class="faq-question">
                <span>Can I ship second-hand appliances?</span>
                <span class="faq-toggle">+</span>
            </button>
            <div class="faq-answer">
                Secret, trip mo lang!
            </div>
        </div>

    </div>
@endsection
