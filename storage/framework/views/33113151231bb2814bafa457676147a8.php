<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cargo Payment - #<?php echo e($booking->booking_ref_no); ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #485b8c;
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        .header {
            background: #485b8c;
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            font-size: 28px;
            margin-bottom: 10px;
        }
        .header p {
            opacity: 0.9;
            font-size: 14px;
        }
        .booking-ref {
            background: rgba(255,255,255,0.2);
            padding: 10px 20px;
            border-radius: 10px;
            margin-top: 15px;
            display: inline-block;
        }
        .booking-ref span {
            font-size: 20px;
            font-weight: bold;
            letter-spacing: 2px;
        }
        .content {
            padding: 30px;
        }
        .info-section {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .info-section h3 {
            color: #333;
            margin-bottom: 15px;
            font-size: 16px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .info-label {
            color: #666;
            font-size: 14px;
        }
        .info-value {
            color: #333;
            font-weight: 600;
            font-size: 14px;
            text-align: right;
        }
        .payment-section {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            border-radius: 15px;
            padding: 25px;
            text-align: center;
            margin: 20px 0;
        }
        .amount-label {
            font-size: 14px;
            color: #666;
            margin-bottom: 5px;
        }
        .amount {
            font-size: 42px;
            font-weight: bold;
            color: #28a745;
            margin: 10px 0;
        }
        .payment-btn {
            background: #28a745;
            color: white;
            border: none;
            padding: 15px 40px;
            font-size: 18px;
            font-weight: bold;
            border-radius: 50px;
            cursor: pointer;
            margin-top: 20px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.4);
        }
        .payment-btn:hover {
            background: #218838;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(40, 167, 69, 0.6);
        }
        .payment-btn:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
        .loading {
            display: none;
            text-align: center;
            margin-top: 20px;
        }
        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #28a745;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto 10px;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-pending {
            background: #ffc107;
            color: #333;
        }
        .status-completed {
            background: #28a745;
            color: white;
        }
        .footer {
            background: #f8f9fa;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #999;
        }
        .note {
            background: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 10px;
            padding: 15px;
            margin-top: 20px;
            font-size: 13px;
            color: #856404;
        }
        .note strong {
            display: block;
            margin-bottom: 5px;
        }
        .success-message {
            display: none;
            background: #d4edda;
            border: 1px solid #c3e6cb;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            color: #155724;
        }
        .success-message h3 {
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🚢 LAPULAPU SHIPPING LINES</h1>
            <p>Cargo Booking Payment</p>
        </div>

        <div class="content">
            <!-- Success Message (hidden by default) -->
            <div id="successMessage" class="success-message">
                <h3>✅ Payment Already Completed!</h3>
                <p>Your payment has already been processed. Please check your email for the Freight Receipt.</p>
            </div>

            <?php if(strtolower($payment->payment_status ?? '') === 'initial' || strtolower($payment->payment_status ?? '') === 'completed'): ?>
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        document.getElementById('successMessage').style.display = 'block';
                    });
                </script>
            <?php else: ?>
                <!-- Booking Information -->
                <div class="info-section">
                    <h3>📋 Booking Details</h3>
                    <div class="info-row">
                        <span class="info-label">Booking Type</span>
                        <span class="info-value"><?php echo e(ucfirst($booking->booking_type ?? 'Cargo')); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Status</span>
                        <span class="info-value">
                            <span class="status-badge status-<?php echo e(strtolower($booking->booking_status ?? 'pending')); ?>">
                                <?php echo e($booking->booking_status ?? 'Pending'); ?>

                            </span>
                        </span>
                    </div>
                    <?php if($voyage): ?>
                    <?php
                        $originPort = $voyage->routePort?->portOrigin;
                        $originDisplay = $originPort ? ($originPort->terminal_name ?? '') . ' ' . ($originPort->port_name ?? '') . ', ' . ($originPort->city ?? '') : 'N/A';
                        $destPort = $voyage->routePort?->portDestination;
                        $destDisplay = $destPort ? ($destPort->terminal_name ?? '') . ' ' . ($destPort->port_name ?? '') . ', ' . ($destPort->city ?? '') : 'N/A';
                    ?>
                    <div class="info-row">
                        <span class="info-label">Port of Origin</span>
                        <span class="info-value"><?php echo e(trim($originDisplay)); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Port of Destination</span>
                        <span class="info-value"><?php echo e(trim($destDisplay)); ?></span>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Sender Information -->
                <?php if($sender): ?>
                <div class="info-section">
                    <h3>👤 Sender Information</h3>
                    <div class="info-row">
                        <span class="info-label">Name</span>
                        <span class="info-value"><?php echo e($sender->sender_name ?? 'N/A'); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Contact Number</span>
                        <span class="info-value"><?php echo e($sender->sender_contactno ?? 'N/A'); ?></span>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Payment Information -->
                <div class="payment-section">
                    <p class="amount-label">Amount to Pay</p>
                    <p class="amount">₱<?php echo e(number_format($payment->total_amount ?? 0, 2)); ?></p>
                    <p style="color: #666; font-size: 13px;">
                        Payment Status: 
                        <span class="status-badge status-<?php echo e(strtolower($payment->payment_status ?? 'pending')); ?>">
                            <?php echo e($payment->payment_status ?? 'Pending'); ?>

                        </span>
                    </p>
                </div>

                <!-- Pay Button -->
                <div style="text-align: center;">
                    <button id="payButton" class="payment-btn" onclick="processPayment()">
                        Pay with GCash
                    </button>
                    
                    <div id="loading" class="loading">
                        <div class="spinner"></div>
                        <p>Redirecting to GCash...</p>
                    </div>
                </div>

                <!-- Note -->
                <div class="note">
                    <strong>📌 Important Notice:</strong>
                    After successful payment, you will receive an email with the Freight Receipt for your records. Arrastre and other fees will be paid upon arrival at the office. Please ensure to bring a copy of your booking reference and payment confirmation when you approach the office for your cargo booking.
                </div>
            <?php endif; ?>
        </div>

        <div class="footer">
            <p>LAPULAPU SHIPPING LINES - Safe and Reliable Cargo Services</p>
            <p>For inquiries, please contact our customer service.</p>
        </div>
    </div>

    <script>
        async function processPayment() {
            const button = document.getElementById('payButton');
            const loading = document.getElementById('loading');
            
            button.disabled = true;
            button.textContent = 'Processing...';
            loading.style.display = 'block';

            try {
                const response = await fetch('/paymongo/payment/<?php echo e($booking->booking_ref_no); ?>/process', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
                    }
                });

                const data = await response.json();

                if (data.success && data.checkout_url) {
                    // Redirect to GCash checkout
                    window.location.href = data.checkout_url;
                } else {
                    alert('Payment error: ' + (data.message || 'Unknown error'));
                    button.disabled = false;
                    button.textContent = 'Pay with GCash';
                    loading.style.display = 'none';
                }
            } catch (error) {
                console.error('Payment error:', error);
                alert('An error occurred. Please try again.');
                button.disabled = false;
                button.textContent = 'Pay with GCash';
                loading.style.display = 'none';
            }
        }
    </script>
</body>
</html>
<?php /**PATH C:\Users\kirzt\Documents\GitHub\Capstone\resources\views/payments/cargo_payment.blade.php ENDPATH**/ ?>