<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Booking;
use App\Models\PassengerTicket;
use App\Services\PassengerTicketPdf;
use App\Services\QrCodeGenerator;
use Illuminate\Support\Facades\Mail;

echo "📧 Test: Send Booked Ticket Email with QR Code PDF\n\n";

// Find a recent confirmed booking
$booking = Booking::where('booking_status', 'Confirmed')
    ->whereHas('passengerTickets')
    ->orderBy('created_at', 'desc')
    ->first();

if (!$booking) {
    echo "❌ No confirmed bookings found\n";
    exit;
}

echo "✅ Found Booking:\n";
echo "   Ref: " . $booking->booking_ref_no . "\n";
echo "   Status: " . $booking->booking_status . "\n";
echo "   Created: " . $booking->created_at . "\n\n";

// Get passengers
$tickets = PassengerTicket::where('booking_ref_no', $booking->booking_ref_no)
    ->with('passenger')
    ->get();

echo "✅ Passengers:\n";
foreach ($tickets as $ticket) {
    echo "   - " . $ticket->passenger->passenger_firstname . " " . $ticket->passenger->passenger_lastname . " (" . $ticket->passenger->passenger_email . ")\n";
}
echo "\n";

// Generate QR codes for PDF - use file paths, not data URLs
echo "🎫 Generating QR Codes for PDF...\n";
$qrCodes = [];
foreach ($tickets as $ticket) {
    $qrData = $booking->booking_ref_no . ':' . $ticket->passenger_id;
    // Save to disk and get absolute path for DOMPDF
    $filename = 'qr_' . $booking->booking_ref_no . '_' . $ticket->passenger_id;
    $filepath = QrCodeGenerator::generateAndSave($qrData, $filename);
    
    if ($filepath && file_exists($filepath)) {
        // Store absolute path for DOMPDF
        $qrCodes[$ticket->passenger_id] = $filepath;
        echo "   ✓ QR Code for passenger " . $ticket->passenger_id . "\n";
    } else {
        echo "   ❌ Failed to generate QR for passenger " . $ticket->passenger_id . "\n";
    }
}
echo "\n";

// Generate PDF with QR codes
echo "📄 Generating PDF with QR Codes...\n";
try {
    // Load booking with all relationships
    $booking = Booking::with([
        'voyage',
        'voyage.vessel',
        'voyage.vessel.accommodations',
        'voyage.routePort',
        'passengerTickets.passenger',
        'passengerTickets.promo'
    ])->where('booking_ref_no', $booking->booking_ref_no)->first();

    // Create HTML with QR codes - inline, no template dependency
    $ticketsHtml = '';
    foreach ($booking->passengerTickets as $ticket) {
        if (isset($qrCodes[$ticket->passenger_id])) {
            $qrPath = $qrCodes[$ticket->passenger_id];
            $ticketsHtml .= '<div style="margin-bottom: 20px; border: 1px solid #ccc; padding: 15px;">';
            $ticketsHtml .= '<p><strong>' . $ticket->passenger->passenger_firstname . ' ' . $ticket->passenger->passenger_lastname . '</strong></p>';
            $ticketsHtml .= '<p>COT: ' . $ticket->pt_cot_no . '</p>';
            $ticketsHtml .= '<img src="' . $qrPath . '" style="width: 150px; height: 150px; border: 1px solid #000;">';
            $ticketsHtml .= '</div>';
        }
    }

    $html = <<<HTML
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; }
            .header { background: #f0f0f0; padding: 20px; margin-bottom: 30px; }
            .qr-section { margin-top: 30px; border-top: 2px solid #333; padding-top: 20px; }
        </style>
    </head>
    <body>
        <div class="header">
            <h1>Boarding Tickets</h1>
            <p>Booking Reference: {$booking->booking_ref_no}</p>
        </div>
        <div class="qr-section">
            <h3>Your QR Codes for Boarding</h3>
            {$ticketsHtml}
        </div>
    </body>
    </html>
    HTML;

    // Generate PDF
    $pdf = app('dompdf.wrapper');
    $dompdf = $pdf->getDomPDF();
    $dompdf->set_option('defaultFont', 'DejaVu Sans');
    $dompdf->set_option('isHtml5ParserEnabled', true);
    $dompdf->set_option('isRemoteEnabled', true);
    $dompdf->set_option('dpi', 96);

    $pdf->loadHTML($html)->setPaper('A4', 'portrait');
    $pdfContent = $pdf->output();

    if ($pdfContent) {
        echo "   ✅ PDF generated: " . strlen($pdfContent) . " bytes\n\n";
    } else {
        echo "   ❌ PDF generation failed\n";
        exit;
    }
} catch (\Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
    exit;
}

// Prepare email data
echo "📧 Preparing Email...\n";
$firstPassenger = $tickets->first()->passenger;
$toEmail = $firstPassenger->passenger_email;

echo "   To: " . $toEmail . "\n";
echo "   Subject: Boarding Ticket - Booking #" . $booking->booking_ref_no . "\n\n";

// Send email with attachment
echo "📨 Sending Email...\n";
try {
    // Create raw email
    Mail::raw('Please find your boarding ticket PDF attached. Each passenger has a unique QR code for quick check-in at the gate.', function ($message) use ($toEmail, $booking, $pdfContent) {
        $message->to($toEmail)
                ->subject('Your Boarding Ticket - Booking #' . $booking->booking_ref_no)
                ->attachData($pdfContent, 'boarding_ticket_' . $booking->booking_ref_no . '.pdf', [
                    'mime' => 'application/pdf'
                ]);
    });

    echo "   ✅ Email sent successfully!\n";
    echo "   To: " . $toEmail . "\n";
    echo "   Booking: " . $booking->booking_ref_no . "\n";
    echo "   Passengers: " . $tickets->count() . "\n";
    echo "   PDF Size: " . strlen($pdfContent) . " bytes\n";
    echo "   QR Codes Included: " . count($qrCodes) . "\n";
    echo "\n✅ Test Complete!\n";

} catch (\Exception $e) {
    echo "   ❌ Failed to send email: " . $e->getMessage() . "\n";
    exit;
}
