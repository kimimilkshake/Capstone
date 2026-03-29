@extends('layouts.app')
@section('content')
    @include('components.hero'){{-- Head Nav --}}
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
            @include('passenger.about_partials.who_we_are') {{-- default fragment --}}
        </div>
    </div>

    @include('components.footer')
@endsection