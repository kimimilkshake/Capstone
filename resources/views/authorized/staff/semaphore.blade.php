@extends('layouts.app')
@section('page-title', 'VOYAGES')
@section('content')
  @include('components.authHeader')
  @include('components.staff_nav')
  <div class="staff-body">
    <h2 class="sms-header">SMS Message for Cancellation of Trips</h2>
    <div class="sms-container">
    <form method="POST" action="">
        @csrf
        <label for="message" class="sms-label">Message:</label>
        <textarea id="message" name="message" class="sms-textarea" rows="5"></textarea>
        <button type="submit" class="sms-button">Send Message</button>
    </form>
</div>
</div>
@endsection