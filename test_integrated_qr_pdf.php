<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Booking;
use App\Models\PassengerTicket;
use App\Services\PassengerTicketPdf;
use Illuminate\Support\Facades\Mail;

echo "📧 Test: Integrated QR Codes in Passenger PDF\n\n";

// Find a recent confirmed booking
$booking = Booking::where('booking_status', 'Confirmed')
    ->whereHas('passengerTickets')
    ->orderBy('created_at', 'desc')
    ->first();

if (!$booking) {
    echo "❌ No confirmed bookings found\n";
    exit;
}

echo "✅ Found Booking: " . $booking->booking_ref_no . "\n";

$tickets = PassengerTicket::where('booking_ref_no', $booking->booking_ref_no)
    ->with('passenger')
    ->get();

echo "✅ Passengers: " . $tickets->count() . "\n\n";

// Generate PDF with integrated QR codes
echo "📄 Generating PDF with Integrated QR Codes...\n";
$pdf = PassengerTicketPdf::generate($booking->booking_ref_no);

if ($pdf) {
    echo "   ✅ PDF generated: " . strlen($pdf) . " bytes\n\n";
    
    // Save to file for inspection
    file_put_contents('test_integrated_qr_ticket.pdf', $pdf);
    echo "   📄 Saved: test_integrated_qr_ticket.pdf\n";
    echo "   Check this file to verify QR codes are displayed\n\n";
    
    // Also test individual passenger PDFs
    echo "📧 Testing Individual Passenger PDFs...\n";
    foreach ($tickets as $ticket) {
        $passengerEmail = $ticket->passenger->passenger_email;
        $passengerPdf = PassengerTicketPdf::generate($booking->booking_ref_no, null, null, $passengerEmail);
        
        if ($passengerPdf) {
            $filename = 'test_passenger_' . $ticket->passenger_id . '.pdf';
            file_put_contents($filename, $passengerPdf);
            echo "   ✅ Passenger {$ticket->passenger->passenger_firstname}: " . strlen($passengerPdf) . " bytes → {$filename}\n";
        } else {
            echo "   ❌ Failed to generate PDF for passenger {$ticket->passenger_id}\n";
        }
    }
    
} else {
    echo "   ❌ Failed to generate PDF\n";
}

echo "\n✅ Test Complete!\n";
