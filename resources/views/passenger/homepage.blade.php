@extends('layouts.app')
@section('content')
    @include('components.hero'){{-- Head Nav --}}

    <!-- Centered Dropdown Success Alert (No Auto-dismiss) -->
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show text-center" role="alert"
            style="position: fixed; z-index: 9999; top: 80px; left: 0; right: 0; margin-left: auto; margin-right: auto; width: 90%; max-width: 800px; box-shadow: 0 8px 16px rgba(0,0,0,0.2); font-size: 1.1rem; padding: 1.5rem;">
            <button type="button" class="btn-close position-absolute top-0 end-0 m-2" data-bs-dismiss="alert"
                aria-label="Close"></button>
            <i class="fas fa-check-circle mb-2" style="font-size: 3rem; color: #198754;"></i>
            <h5 class="mb-2"><strong>Booking Successful!</strong></h5>
            <p class="mb-0">{{ session('success') }}</p>
        </div>
    @endif

    <!-- Centered Dropdown Error Alert (No Auto-dismiss) -->
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show text-center" role="alert"
            style="position: fixed; z-index: 9999; top: 80px; left: 0; right: 0; margin-left: auto; margin-right: auto; width: 90%; max-width: 800px; box-shadow: 0 8px 16px rgba(0,0,0,0.2); font-size: 1.1rem; padding: 1.5rem;">
            <button type="button" class="btn-close position-absolute top-0 end-0 m-2" data-bs-dismiss="alert"
                aria-label="Close"></button>
            <i class="fas fa-exclamation-circle mb-2" style="font-size: 3rem; color: #dc3545;"></i>
            <h5 class="mb-2"><strong>Payment Failed</strong></h5>
            <p class="mb-0">{{ session('error') }}</p>
        </div>
    @endif

    <div class="container my-5">
        <div class="row">
            <div class="col-md-6 my-5">
                <img src="{{ asset('images/travel.svg') }}" alt="Body Image" class="img-fluid body-image">
            </div>
            <div class="col-md-6 d-flex align-items-center justify-content-center">
                <p class="body-text">
                    <span class="highlight">CHOOSE</span>
                    <span class="newline">YOUR</span>
                    <span class="newline2">DESIRED BUNK</span>
                    <span class="description">Here at Lapulapu Shipping Lines, we give you the<br>
                        freedom to pick the bunks that suites you best!
                    </span>

                    <a href="{{ route('bookingtype') }}" class="btn btn-outline-primary mt-3 travel-btn">
                        GO TRAVEL
                    </a>
                </p>
            </div>
        </div>
    </div>
@endsection
