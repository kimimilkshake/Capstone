<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Booking;
use App\Models\PassengerTicket;
use App\Services\QrCodeGenerator;

echo "🧪 TEST: PDF with QR Codes\n\n";

// Find a recent confirmed booking
$booking = Booking::where('booking_status', 'Confirmed')
    ->whereHas('passengerTickets')
    ->orderBy('created_at', 'desc')
    ->first();

if (!$booking) {
    echo "❌ No confirmed bookings found\n";
    exit;
}

// Load full relationships
$booking = Booking::with([
    'voyage',
    'voyage.vessel',
    'voyage.vessel.accommodations',
    'voyage.routePort',
    'passengerTickets.passenger',
    'passengerTickets.promo'
])->where('booking_ref_no', $booking->booking_ref_no)->first();

echo "✅ Found Booking: " . $booking->booking_ref_no . "\n";

// Get tickets
$tickets = PassengerTicket::where('booking_ref_no', $booking->booking_ref_no)
    ->with('passenger')
    ->get();

echo "✅ Passengers: " . $tickets->count() . "\n\n";

// Generate QR codes - save to disk
echo "🎫 Generating QR Codes...\n";
$qrCodes = [];

foreach ($tickets as $ticket) {
    $qrData = $booking->booking_ref_no . ':' . $ticket->passenger_id;
    // Note: generateAndSave handles directory creation and .png extension
    $filename = 'test_qr_' . $booking->booking_ref_no . '_' . $ticket->passenger_id;
    $filepath = QrCodeGenerator::generateAndSave($qrData, $filename);

    if ($filepath && file_exists($filepath)) {
        $qrCodes[$ticket->passenger_id] = $filepath;
        $filesize = filesize($filepath);
        echo "   ✓ Passenger " . $ticket->passenger_id . ": $filepath ($filesize bytes)\n";
    } else {
        echo "   ❌ Failed to save QR for passenger " . $ticket->passenger_id . "\n";
        if ($filepath) {
            echo "      Path returned: $filepath, exists: " . (file_exists($filepath) ? 'yes' : 'no') . "\n";
        }
    }
}

if (empty($qrCodes)) {
    echo "   ⚠️  No QR codes generated successfully\n";
} else {
    echo "   ✅ Successfully generated " . count($qrCodes) . " QR code(s)\n";
}
echo "\n";

// Create test HTML template with QR codes
echo "📄 Creating Test PDF Template...\n";
$html = <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Test Ticket with QR</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .header { background: #f0f0f0; padding: 20px; margin-bottom: 20px; }
        .ticket { border: 1px solid #ccc; padding: 15px; margin-bottom: 15px; }
        .qr-section { margin-top: 40px; padding-top: 20px; border-top: 2px solid #333; }
        .qr-container { display: flex; flex-wrap: wrap; gap: 20px; justify-content: center; }
        .qr-item { text-align: center; border: 1px solid #ddd; padding: 15px; width: 180px; }
        .qr-item img { width: 150px; height: 150px; margin-bottom: 10px; }
        .qr-label { font-size: 12px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Test Booking Ticket with QR Codes</h1>
        <p>Booking Reference: [BOOKING_REF]</p>
    </div>

    <div class="ticket">
        <h2>Passenger Information</h2>
        <p>Total Passengers: [PASSENGER_COUNT]</p>
    </div>

    <div class="qr-section">
        <h3 style="text-align: center;">BOARDING QR CODES - Scan at Gate</h3>
        <div class="qr-container">
[QR_CODES_HTML]
        </div>
    </div>
</body>
</html>
HTML;

// Build QR codes HTML - use file paths
$qrCodesHtml = '';
foreach ($tickets as $ticket) {
    if (isset($qrCodes[$ticket->passenger_id])) {
        $filepath = $qrCodes[$ticket->passenger_id];
        // Convert to absolute path for DOMPDF
        $absolutePath = realpath($filepath);

        $qrCodesHtml .= '            <div class="qr-item">' . "\n";
        $qrCodesHtml .= '                <img src="' . $absolutePath . '" alt="QR Code">' . "\n";
        $qrCodesHtml .= '                <div class="qr-label">' . "\n";
        $qrCodesHtml .= '                    ' . $ticket->passenger->passenger_firstname . ' ' . $ticket->passenger->passenger_lastname . '<br>' . "\n";
        $qrCodesHtml .= '                    COT ' . $ticket->pt_cot_no . "\n";
        $qrCodesHtml .= '                </div>' . "\n";
        $qrCodesHtml .= '            </div>' . "\n";
    }
}

// Replace placeholders
$html = str_replace('[BOOKING_REF]', $booking->booking_ref_no, $html);
$html = str_replace('[PASSENGER_COUNT]', $tickets->count(), $html);
$html = str_replace('[QR_CODES_HTML]', $qrCodesHtml, $html);

echo "✅ HTML Template created\n\n";

// Generate PDF
echo "📋 Generating PDF...\n";
try {
    $pdf = app('dompdf.wrapper');
    $dompdf = $pdf->getDomPDF();
    $dompdf->set_option('defaultFont', 'DejaVu Sans');
    $dompdf->set_option('isHtml5ParserEnabled', true);
    $dompdf->set_option('isRemoteEnabled', true);

    $pdf->loadHTML($html)->setPaper('A4', 'portrait');
    $pdfContent = $pdf->output();

    if ($pdfContent) {
        echo "   ✅ PDF generated: " . strlen($pdfContent) . " bytes\n\n";

        // Save test PDF
        $filename = 'test_ticket_with_qr_codes.pdf';
        file_put_contents($filename, $pdfContent);
        echo "✅ Test PDF saved: " . $filename . "\n";
        echo "   Check this file to see if QR codes are rendered\n";
    } else {
        echo "   ❌ PDF generation failed\n";
    }
} catch (\Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
}
