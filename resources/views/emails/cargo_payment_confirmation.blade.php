<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Cargo Payment Confirmed - Freight Receipt for #{{ $booking->booking_ref_no }}</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f5f5f5; padding: 20px; }
        .container { max-width: 700px; margin: auto; background: #fff; border-radius: 10px; padding: 30px; }
        h2 { color: #28a745; text-align: center; }
        .booking-ref { font-size: 24px; font-weight: bold; text-align: center; margin: 20px 0; color: #333; }
        .success-badge { background: #28a745; color: white; padding: 10px 20px; border-radius: 5px; text-align: center; font-weight: bold; margin: 15px 0; }
        .qr-section { background: #f0f7ff; border: 2px solid #007bff; border-radius: 10px; padding: 25px; margin: 20px 0; text-align: center; }
        .qr-code { margin: 15px 0; }
        .qr-note { font-size: 12px; color: #666; margin-top: 10px; }
        .payment-info { background: #e8f5e9; padding: 15px; border-radius: 5px; margin: 15px 0; }
        .section { margin-bottom: 20px; }
        .section-title { font-weight: bold; margin-bottom: 10px; color: #333; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #f0f0f0; }
        .footer { text-align: center; margin-top: 30px; font-size: 12px; color: #999; }
    </style>
</head>
<body>
<div class="container">
    <h2>✅ Payment Confirmed!</h2>
    
    <div class="booking-ref">Booking Reference: #{{ $booking->booking_ref_no }}</div>
    
    <div class="success-badge">
        Your payment of ₱{{ number_format($payment->total_amount ?? 0, 2) }} has been confirmed.
    </div>
    
    <!-- QR Code for Payment Verification -->
    <div class="qr-section">
        <h3 style="color: #007bff; margin: 0 0 10px 0;">📱 Payment Verification QR Code</h3>
        <p style="font-size: 14px; color: #666; margin: 0 0 15px 0;">Present this QR code at the terminal for verification</p>
        
        @if($qrCodeImage)
            <div class="qr-code">
                <img src="{{ $qrCodeImage }}" alt="Payment Verification QR Code" style="width: 200px; height: 200px;" />
            </div>
        @else
            <div class="qr-code" style="background: #ddd; width: 200px; height: 200px; margin: 0 auto; display: flex; align-items: center; justify-content: center;">
                <span>QR Code</span>
            </div>
        @endif
        
        <p class="qr-note">
            <strong>Booking Reference:</strong> #{{ $booking->booking_ref_no }}<br>
            <small>Scan this QR code at the terminal to verify your payment status.</small>
        </p>
    </div>
    
    <!-- Voyage Information -->
    @if($booking->voyage)
    <div class="section">
        <div class="section-title">🚢 Voyage Information</div>
        <p><strong>Voyage Code:</strong> {{ $booking->voyage->voyage_code }}</p>
        <p><strong>Departure:</strong> {{ \Carbon\Carbon::parse($booking->voyage->voyage_departure_date)->format('M d, Y (D)') }}</p>
        <p><strong>Arrival:</strong> {{ \Carbon\Carbon::parse($booking->voyage->voyage_arrival_date)->format('M d, Y (D)') }}</p>
        <p><strong>Loading Port:</strong> {{ $booking->voyage->loading_port ?? 'N/A' }}</p>
        <p><strong>Unloading Port:</strong> {{ $booking->voyage->unloading_port ?? 'N/A' }}</p>
    </div>
    @endif
    
    <!-- Sender & Consignee -->
    <div class="section">
        <div class="section-title">📦 Sender & Consignee</div>
        <p><strong>Sender:</strong> {{ $sender->sender_name }} ({{ $sender->sender_contactno }})</p>
        <p><strong>Consignee:</strong> {{ $consignee->consignee_name }} ({{ $consignee->consignee_contactno }})</p>
    </div>
    
    <!-- Cargo Items Summary -->
    <div class="section">
        <div class="section-title">📋 Cargo Items Summary</div>
        <table>
            <thead>
                <tr>
                    <th>QTY</th>
                    <th>Description</th>
                    <th>Weight</th>
                </tr>
            </thead>
            <tbody>
                @foreach($cargoItems as $cargo)
                    <tr>
                        <td>{{ $cargo->quantity }}</td>
                        <td>{{ $cargo->cargoItem->cargo_item_description ?? 'N/A' }}</td>
                        <td>{{ $cargo->weight }} kg</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    
    <!-- Payment Information -->
    <div class="payment-info">
        <strong>Payment Details:</strong><br>
        <strong>Amount Paid:</strong> ₱{{ number_format($payment->total_amount ?? 0, 2) }}<br>
        <strong>Payment Status:</strong> {{ $payment->payment_status ?? 'Completed' }}<br>
        <strong>Payment Date:</strong> {{ $payment->payment_date ? \Carbon\Carbon::parse($payment->payment_date)->format('M d, Y H:i') : 'N/A' }}
    </div>
    
    <p style="font-size: 14px; color: #666; text-align: center;">
        <strong>Important:</strong> Please keep this email and present the QR code at the terminal for cargo verification.<br>
        The Bill of Lading PDF is attached to this email for your records.
    </p>
    
    <div class="footer">
        <p>LAPULAPU SHIPPING LINES</p>
        <p>This is an automated message. Please do not reply to this email.</p>
    </div>
</div>
</body>
</html>
