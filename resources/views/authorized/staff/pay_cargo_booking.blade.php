<div class="payment-form">
    <h4>Process Payment for Cargo Booking</h4>
    <div class="booking-summary mb-4">
        <h5>Booking Details</h5>
        <p><strong>Booking Reference:</strong> {{ $booking->booking_ref_no }}</p>
        <p><strong>Sender:</strong> {{ $booking->sender->sender_name ?? 'N/A' }}</p>
        <p><strong>Consignee:</strong> {{ $booking->consignee->consignee_name ?? 'N/A' }}</p>
        <p><strong>Total Amount:</strong> ₱{{ number_format($booking->payment->total_amount ?? 0, 2) }}</p>
    </div>

    <form id="paymentForm">
        @csrf
        <div class="payment-options">
            <h6>Select Payment Method</h6>
            <div class="d-flex gap-3">
                <button type="button" class="btn btn-outline-primary">Pay with Cash</button>
                <button type="button" class="btn btn-outline-success">Pay with GCash</button>
            </div>
        </div>
    </form>
</div>