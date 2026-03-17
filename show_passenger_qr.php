<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Booking;
use App\Models\PassengerTicket;
use App\Services\QrCodeGenerator;

// Get real passenger data
$booking = Booking::whereHas('passengerTickets')->first();
$ticket = PassengerTicket::where('booking_ref_no', $booking->booking_ref_no)
    ->with('passenger')
    ->first();

$passenger = $ticket->passenger;
$qrData = $booking->booking_ref_no . ':' . $passenger->passenger_id;
$qrDataUrl = QrCodeGenerator::generateAsDataUrl($qrData, 350);

$html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Passenger QR Code - $passenger->passenger_firstname $passenger->passenger_lastname</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .container {
            background: white;
            padding: 50px;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            text-align: center;
            max-width: 700px;
        }
        
        .header {
            margin-bottom: 30px;
        }
        
        h1 {
            color: #333;
            margin: 0 0 10px 0;
            font-size: 32px;
        }
        
        .passenger-info {
            background: #f0f9ff;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            border-left: 5px solid #667eea;
        }
        
        .passenger-info h2 {
            margin: 0 0 15px 0;
            color: #667eea;
            font-size: 18px;
        }
        
        .info-row {
            display: flex;
            justify-content: space-around;
            margin: 10px 0;
            flex-wrap: wrap;
        }
        
        .info-item {
            background: white;
            padding: 12px 20px;
            border-radius: 8px;
            margin: 5px;
            flex: 1;
            min-width: 150px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        
        .info-label {
            color: #666;
            font-size: 12px;
            text-transform: uppercase;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        .info-value {
            color: #333;
            font-size: 16px;
            font-weight: bold;
        }
        
        .qr-wrapper {
            background: #f8f8f8;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            border: 2px solid #e0e0e0;
        }
        
        .qr-wrapper h3 {
            margin: 0 0 20px 0;
            color: #333;
            font-size: 18px;
        }
        
        .qr-wrapper img {
            display: block;
            width: 280px;
            height: 280px;
            margin: 0 auto;
            border: 3px solid #667eea;
            border-radius: 10px;
            background: white;
            padding: 10px;
            box-sizing: border-box;
        }
        
        .qr-info {
            background: #fff3cd;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
            border-left: 4px solid #ffc107;
        }
        
        .qr-info p {
            margin: 5px 0;
            color: #856404;
            font-size: 13px;
        }
        
        .booking-badge {
            display: inline-block;
            background: #22c55e;
            color: white;
            padding: 8px 16px;
            border-radius: 50px;
            font-weight: bold;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>✅ Passenger QR Code</h1>
            <p style="color: #666; margin: 10px 0 0 0;">For Boarding Check-in</p>
        </div>
        
        <div class="passenger-info">
            <h2>Passenger Details</h2>
            <div class="info-row">
                <div class="info-item">
                    <div class="info-label">Passenger Name</div>
                    <div class="info-value">$passenger->passenger_firstname $passenger->passenger_lastname</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Passenger ID</div>
                    <div class="info-value">#$passenger->passenger_id</div>
                </div>
            </div>
            <div class="info-row">
                <div class="info-item">
                    <div class="info-label">Booking Reference</div>
                    <div class="info-value">$booking->booking_ref_no</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Email</div>
                    <div class="info-value" style="font-size: 14px;">$passenger->passenger_email</div>
                </div>
            </div>
        </div>
        
        <div class="qr-wrapper">
            <h3>🎫 Scan This QR Code at Boarding Gate</h3>
            <img src="$qrDataUrl" alt="QR Code for $passenger->passenger_firstname">
            <div class="qr-info">
                <p><strong>QR Data:</strong> $qrData</p>
                <p>Format: Booking Reference : Passenger ID</p>
                <p>This unique QR code identifies this specific passenger for the voyage</p>
            </div>
        </div>
        
        <div class="booking-badge">
            ✓ Ready for PDF Ticket
        </div>
    </div>
</body>
</html>
HTML;

file_put_contents('passenger_qr_preview.html', $html);
echo "✅ Generated passenger QR preview!\n";
?>
