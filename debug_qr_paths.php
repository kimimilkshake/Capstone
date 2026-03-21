<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Booking;
use App\Models\PassengerTicket;
use App\Services\PassengerTicketPdf;
use App\Services\QrCodeGenerator;

echo "🔍 DEBUG: QR Code Path Check\n\n";

$booking = Booking::where('booking_status', 'Confirmed')->first();
if (!$booking) {
    echo "❌ No booking\n";
    exit;
}

$tickets = PassengerTicket::where('booking_ref_no', $booking->booking_ref_no)
    ->with('passenger')
    ->get();

echo "Booking: " . $booking->booking_ref_no . "\n";
echo "Passengers: " . count($tickets) . "\n\n";

// Generate QR codes and check paths
echo "🎫 QR Code Paths:\n";
foreach ($tickets as $ticket) {
    $qrData = $booking->booking_ref_no . ':' . $ticket->passenger_id;
    $filename = 'debug_qr_' . $booking->booking_ref_no . '_' . $ticket->passenger_id;
    
    $qrCode = QrCodeGenerator::generateAndStore(
        $booking->booking_ref_no,
        $ticket->passenger_id,
        $qrData,
        $filename
    );
    
    if ($qrCode) {
        $path = $qrCode->qr_code_path;
        $exists = file_exists($path);
        $size = $exists ? filesize($path) : 0;
        
        echo "   Passenger {$ticket->passenger_id}:\n";
        echo "   - Path: $path\n";
        echo "   - Exists: " . ($exists ? "YES" : "NO") . "\n";
        echo "   - Size: $size bytes\n";
        
        // Try reading the file
        if ($exists) {
            $content = @file_get_contents($path, false, null, 0, 8);
            $isPNG = ($content && strpos($content, 'PNG') !== false);
            echo "   - Valid PNG: " . ($isPNG ? "YES" : "NO") . "\n";
        }
        echo "\n";
    }
}

// Try building HTML with img tags
echo "📄 HTML Image Tags Test:\n";
foreach ($tickets as $ticket) {
    $qrCodes = [];
    $q = \App\Models\QrCode::where('booking_ref_no', $booking->booking_ref_no)
        ->where('passenger_id', $ticket->passenger_id)
        ->first();
    
    if ($q) {
        $path = $q->qr_code_path;
        $html = '<img src="' . $path . '" style="width: 100px; height: 100px;">';
        echo "   Passenger {$ticket->passenger_id}: {$html}\n";
    }
}
