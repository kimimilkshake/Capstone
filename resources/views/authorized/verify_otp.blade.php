@extends('layouts.app')
@section('content')
<section class="vh-100 d-flex align-items-center justify-content-center login-bg" style="background-image: url('{{ asset('images/login-bg.png') }}');">
  <div class="container py-5 h-100">
    <div class="row d-flex justify-content-center align-items-center h-100">
      <div class="col-12 col-md-6 col-lg-5">
        <div class="card">
          <div class="card-body text-center p-4">
            <a href="{{ route('authorized.forgot_password') }}" class="back-icon">
                  <i class="fa-solid fa-chevron-left"></i>
                </a>
                <img src="{{ asset('images/lslc_logo_name2.png') }}" alt="App Logo" class="img-fluid mb-1" style="height:150px">
            <h2 class="fw-bold mb-4">VERIFY OTP</h2>

            @if(session('success'))
              <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if($errors->any())
              <div class="alert alert-danger">
                {{ $errors->first() }}
              </div>
            @endif

            <form method="POST" action="{{ route('verify.otp') }}">
              @csrf
              <input type="hidden" name="username" value="{{ session('username') ?? '' }}">

              <div class="form-floating mb-3">
                <input type="text" name="otp" class="form-control" placeholder="Enter OTP" required>
                <label>OTP</label>
              </div>

              <!-- Countdown Timer -->
              <p id="otp-timer" class="text-danger fw-bold mb-4">You have 05:00 minutes left</p>
              


              <button type="submit" class="loginBtn btn btn-lg px-5 mt-1 mb-4">Verify OTP</button>
            </form>

          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<script>
  // Get OTP expiration time from session
  const expiresAt = new Date("{{ session('otp_expires_at') }}").getTime();

  const timerDisplay = document.getElementById('otp-timer');

  function startTimer() {
    const countdown = setInterval(() => {
      const now = new Date().getTime();
      let distance = Math.floor((expiresAt - now) / 1000); // seconds left

      if (distance <= 0) {
        clearInterval(countdown);
        timerDisplay.textContent = "OTP expired! Please request a new one.";
        // Disable input and button
        document.querySelector('input[name="otp"]').disabled = true;
        document.querySelector('button[type="submit"]').disabled = true;
        return;
      }

      let minutes = Math.floor(distance / 60);
      let seconds = distance % 60;
      timerDisplay.textContent = `You have ${minutes.toString().padStart(2,'0')}:${seconds.toString().padStart(2,'0')} minutes left`;

    }, 1000);
  }

  startTimer();
</script>

@endsection