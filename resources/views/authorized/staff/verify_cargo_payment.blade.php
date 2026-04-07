<div class="verification-form">
    {{-- Information note about booking type --}}
    @php
        $hasCargoWithPictures = $booking->cargoBookings()
            ->whereNotNull('cargo_picture')
            ->where('cargo_picture', '!=', '')
            ->count() > 0;
    @endphp

    @if($hasCargoWithPictures)
        <div class="user-submitted-cargo mb-4">
            <strong>ℹ️ User-Submitted Cargo</strong>
            <p>This booking was submitted and paid online by the customer. Please verify the payment details and add any applicable arrastre fees.</p>
        </div>
    @else
        <div class="staff-created-cargo mb-4">
            <strong>ℹ️ Staff-Created Cargo</strong>
            <p>This booking was created by staff and payment has been recorded. Please verify the payment and add any applicable arrastre fees.</p>
        </div>
    @endif

    <div class="booking-summary mb-4">
        <p><strong>Booking Reference:</strong> {{ $booking->booking_code }}</p>
        <p><strong>Sender:</strong> {{ $booking->sender->sender_name }}</p>
        <p><strong>Consignee:</strong> {{ $booking->consignee->consignee_name }}</p>
        <p><strong>Initial Payment Amount:</strong> ₱{{ number_format($booking->payment->total_amount, 2) }}</p>
    </div>

    <form id="verificationForm">
        @csrf
        <div class="mb-3">
            <label for="arrastre" class="form-label">Arrastre Fee (₱)</label>
            <input type="number" class="form-control" id="arrastre" name="arrastre" step="0.01" min="0">
        </div>

        <div class="mb-3">
            <strong>Total Amount: ₱<span id="totalAmount">{{ number_format($booking->payment->total_amount, 2) }}</span></strong>
        </div>

        <div class="d-flex gap-2">
            <button type="button" class="btn btn-success">Submit Verification</button>
            <button type="button" class="btn btn-danger">Cancel Reservation</button>
        </div>
    </form>
</div>