<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Booking;
use App\Models\PassengerTicket;
use App\Services\QrCodeGenerator;

echo "🎫 Generating QR Code for Real Passenger\n\n";

// Get a booking with passengers
$booking = Booking::whereHas('passengerTickets')->first();

if (!$booking) {
    echo "❌ No bookings with passengers found\n";
    exit;
}

// Get first passenger ticket
$ticket = PassengerTicket::where('booking_ref_no', $booking->booking_ref_no)
    ->with('passenger')
    ->first();

if (!$ticket) {
    echo "❌ No passenger tickets found\n";
    exit;
}

$passenger = $ticket->passenger;
echo "✅ Found Passenger:\n";
echo "   Name: " . $passenger->passenger_firstname . " " . $passenger->passenger_lastname . "\n";
echo "   ID: " . $passenger->passenger_id . "\n";
echo "   Email: " . $passenger->passenger_email . "\n\n";

echo "✅ Found Booking:\n";
echo "   Ref: " . $booking->booking_ref_no . "\n\n";

// Generate QR code for this passenger
echo "📊 Generating QR Code...\n";
$qrData = $booking->booking_ref_no . ':' . $passenger->passenger_id;
echo "   Data: " . $qrData . "\n\n";

$qrBase64 = QrCodeGenerator::generate($qrData, 300);

if ($qrBase64) {
    echo "✅ QR Code Generated Successfully!\n";
    echo "   Base64 Length: " . strlen($qrBase64) . " chars\n";
    echo "   First 60 chars: " . substr($qrBase64, 0, 60) . "...\n\n";
    
    // Generate data URL
    $dataUrl = QrCodeGenerator::generateAsDataUrl($qrData, 300);
    echo "✅ Data URL Created:\n";
    echo "   Length: " . strlen($dataUrl) . " chars\n";
    
    // Save to disk too
    $filepath = QrCodeGenerator::generateAndSave($qrData, "passenger_" . $passenger->passenger_id);
    if ($filepath) {
        echo "✅ QR Code saved to disk:\n";
        echo "   Path: " . $filepath . "\n";
    }
    
    echo "\n✓ Ready to add to PDF!\n";
} else {
    echo "❌ Failed to generate QR code\n";
}
