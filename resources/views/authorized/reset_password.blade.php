@extends('layouts.app')
@section('content')
  <section class="vh-100 d-flex align-items-center justify-content-center login-bg"
    style="background-image: url('{{ asset('images/login-bg.png') }}');">
    <div class="container py-5 h-100">
      <div class="row d-flex justify-content-center align-items-center h-100">
        <div class="col-12 col-md-8 col-lg-6 col-xl-5">
          <div class="card">
            <div class="card-body text-center p-4">
              <div class="mb-md-4 mt-md-4 py-0">
                <a href="{{ route(name: 'verify.otp') }}" class="back-icon">
                  <i class="fa-solid fa-chevron-left"></i>
                </a>
                <img src="{{ asset('images/lslc_logo_name2.png') }}" alt="App Logo" class="img-fluid mb-1" style="height:150px">
                <h2 class="fw-bold text-uppercase">CHANGE  PASSWORD</h2>

                <!-- Success or error messages -->
                @if(session('success'))
                  <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                @if($errors->any())
                  <div class="alert alert-danger">{{ $errors->first() }}</div>
                @endif

                <!-- Reset Password Form -->
                <form method="POST" action="{{ route('reset.password') }}">
                  @csrf
                  
                  <!-- Hidden username field from session -->
                  <input type="hidden" name="username" value="{{ session('reset_username') ?? '' }}">

                  <div class="form-floating mb-3">
                    <input type="password" name="new_password" class="form-control" placeholder="New Password" required>
                    <label>New Password</label>
                  </div>

                  <div class="form-floating mb-3">
                    <input type="password" name="confirm_password" class="form-control" placeholder="Confirm Password" required>
                    <label>Confirm Password</label>
                  </div>

                  <button type="submit" class="loginBtn btn btn-lg px-5 mt-1 mb-4">Submit</button>
                </form>

              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>