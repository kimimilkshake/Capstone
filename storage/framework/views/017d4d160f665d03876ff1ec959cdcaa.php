<div class="payment-form">
        
        <?php
            $hasCargoWithPictures = $booking->cargoBookings()
                ->whereNotNull('cargo_picture')
                ->where('cargo_picture', '!=', '')
                ->count() > 0;
        ?>

        <?php if($hasCargoWithPictures): ?>
            <div class="alert alert-danger" style="margin-bottom: 20px;">
                <strong>⚠️ Invalid Access</strong>
                <p>This is a user-submitted cargo booking. It must be paid online through the payment link sent to the customer's email address. Staff cannot process payment for user-created bookings through this interface.</p>
            </div>
            <script>
                setTimeout(() => {
                    window.location.href = "<?php echo e(route('homepage')); ?>";
                }, 3000);
            </script>
        <?php else: ?>
            <p><strong>Booking Reference:</strong> <?php echo e($booking->booking_code); ?></p>
            <p><strong>Sender:</strong> <?php echo e($booking->sender->sender_name ?? 'N/A'); ?></p>
            <p><strong>Consignee:</strong> <?php echo e($booking->consignee->consignee_name ?? 'N/A'); ?></p>
            <p><strong>Total Amount:</strong> ₱<?php echo e(number_format($booking->payment->total_amount ?? 0, 2)); ?></p>
        </div>

        <form id="paymentForm">
            <?php echo csrf_field(); ?>
            <div class="payment-options">
                <h6>Select Payment Method</h6>
                <div class="d-flex gap-3">
                    <button type="button" class="btn btn-outline-primary" onclick="processPayment('cash')">Pay with Cash</button>
                    <button type="button" class="btn btn-outline-success" onclick="processPayment('gcash')">Pay with GCash</button>
                </div>
            </div>
        </form>
        <?php endif; ?>
    </div>


<div class="modal fade" id="loadingModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0">
            <div class="modal-body text-center p-5">
                <div class="spinner-border text-primary mb-3" role="status" style="width: 60px; height: 60px;">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <h5 class="mt-3">Processing Payment...</h5>
                <p class="text-muted mt-2">Please wait while we confirm your payment</p>
            </div>
        </div>
    </div>
</div>

<script>
    function processPayment(method) {
        // Show loading modal
        const modal = new bootstrap.Modal(document.getElementById('loadingModal'));
        modal.show();

        // Here you would typically submit the form or make an AJAX request
        // For now, we'll just show the modal
        // You can add your payment processing logic here

        // Example: Submit the form after a short delay
        setTimeout(() => {
            document.getElementById('paymentForm').submit();
        }, 1000);
    }
</script><?php /**PATH C:\Users\kirzt\Documents\GitHub\Capstone\resources\views/authorized/staff/pay_cargo_booking.blade.php ENDPATH**/ ?>