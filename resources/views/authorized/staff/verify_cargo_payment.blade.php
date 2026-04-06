<div class="verification-form">
    <h4>Cargo Payment Verification</h4>
    <div class="booking-summary mb-4">
        <h5>Booking Details</h5>
        <p><strong>Booking Reference:</strong> {{ $booking->booking_ref_no }}</p>
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