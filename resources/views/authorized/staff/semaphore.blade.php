@extends('layouts.app')
@section('page-title', 'VOYAGES')
@section('content')
  @include('components.authHeader')
  @include('components.staff_nav')
  
  <div class="staff-body">
    <h2 class="sms-header">SMS Message for Cancellation of Trips</h2>

    <div class="sms-container">
      <form method="POST" action="{{ route('staff.semaphore.send') }}" id="smsForm">
        @csrf
        <input type="hidden" name="voyage_id" value="{{ $voyage_id }}">
        <label for="message" class="sms-label">Message:</label>
        <textarea id="message" name="message" class="sms-textarea" rows="5" required>{{ old('message') }}</textarea>
        <button type="submit" class="sms-button">Send Message</button>
      </form>
    </div>

    @if(session('status'))
      <div class="alert alert-success alert-autodismiss">
        {{ session('status') }}
      </div>
    @endif

    @error('recipients')
      <div class="alert alert-danger alert-autodismiss">
        {{ $message }}
      </div>
    @enderror
  </div>

  <script>
    document.getElementById('smsForm').addEventListener('submit', function(e) {
      const msg = document.getElementById('message').value.trim();
      
      if (!msg) {
        e.preventDefault();
        alert('Message cannot be empty.');
        return;
      }
      
      if (!confirm('Are you sure you want to send this message to all selected recipients?')) {
        e.preventDefault();
      }
    });
  </script>
@endsection
