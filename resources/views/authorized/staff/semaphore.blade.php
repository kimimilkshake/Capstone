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

        <div class="sms-template-row">
          <span class="sms-template-label">Quick templates:</span>
          <button type="button" class="sms-template-button" data-template="Dear Passengers and Cargo Senders,\nDue to the impending arrival of Typhoon [Name] and the corresponding safety warnings issued by local authorities, we are regrettably cancelling the voyage from {{ optional($voyage->routePort)->route_origin ?? '-' }} to {{ optional($voyage->routePort)->route_destination ?? '-' }} scheduled for {{$voyage->voyage_departure_date}} at {{ $voyage->voyage_estimated_TD ? \Carbon\Carbon::parse($voyage->voyage_estimated_TD)->format('h:i A') : '-' }} to ensure the safety of our guests and staff.">Typhoon</button>
          <button type="button" class="sms-template-button" data-template="Dear Passengers and Cargo Senders,\nDue to an unexpected technical issue affecting our vessel, we are regrettably cancelling the voyage from {{ optional($voyage->routePort)->route_origin ?? '-' }} to {{ optional($voyage->routePort)->route_destination ?? '-' }} scheduled for {{$voyage->voyage_departure_date}} at {{ $voyage->voyage_estimated_TD ? \Carbon\Carbon::parse($voyage->voyage_estimated_TD)->format('h:i A') : '-' }} until the issue is resolved. We apologize for the inconvenience and will keep you updated.">Technical</button>
        </div>

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
    function setTemplateMessage(text) {
      const textarea = document.getElementById('message');
      const normalized = text.replace(/\\n/g, '\n');
      textarea.value = normalized;
      textarea.focus();
    }

    document.querySelectorAll('.sms-template-button[data-template]').forEach(button => {
      button.addEventListener('click', () => {
        setTemplateMessage(button.dataset.template);
      });
    });

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
