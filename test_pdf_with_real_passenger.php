<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Booking;
use App\Models\PassengerTicket;
use App\Models\Passenger;
use App\Services\PassengerTicketPdf;

echo "📄 Testing PDF Generation with Real Passenger Data\n\n";

// Get a booking with passengers directly from database
$booking = Booking::with([
    'voyage',
    'voyage.vessel',
    'voyage.vessel.accommodations',
    'voyage.routePort'
])->whereHas('passengerTickets')->first();

if (!$booking) {
    echo "❌ No bookings with passengers found in database\n";
    exit;
}

// Get all passenger tickets for this booking
$tickets = PassengerTicket::where('booking_ref_no', $booking->booking_ref_no)
    ->with('passenger')
    ->get();

if ($tickets->isEmpty()) {
    echo "❌ Booking has no passengers\n";
    exit;
}

$firstPassenger = $tickets->first()->passenger;

echo "✅ Found Passenger:\n";
echo "   Name: " . $firstPassenger->passenger_firstname . " " . $firstPassenger->passenger_lastname . "\n";
echo "   Email: " . $firstPassenger->passenger_email . "\n";
echo "   ID: " . $firstPassenger->passenger_id . "\n";

if (!$booking) {
    echo "❌ Could not find booking for passenger\n";
    exit;
}

echo "\n✅ Found Booking:\n";
echo "   Ref: " . $booking->booking_ref_no . "\n";
echo "   Status: " . $booking->booking_status . "\n";
$ticketCount = \App\Models\PassengerTicket::where('booking_ref_no', $booking->booking_ref_no)->count();
echo "   Passengers: " . $ticketCount . "\n";

// Generate PDF
echo "\n📝 Generating PDF...\n";
try {
    $pdfContent = PassengerTicketPdf::generate($booking->booking_ref_no);

    if ($pdfContent) {
        $filename = 'test_passenger_pdf_' . $passenger->passenger_id . '.pdf';
        file_put_contents($filename, $pdfContent);

        echo "✅ PDF Generated Successfully!\n";
        echo "   File: " . $filename . "\n";
        echo "   Size: " . strlen($pdfContent) . " bytes\n";
        echo "   Passenger: " . $passenger->passenger_firstname . " " . $passenger->passenger_lastname . "\n";
        echo "   Booking Ref: " . $booking->booking_ref_no . "\n";
        echo "\n✓ Ready to add QR codes!\n";
    } else {
        echo "❌ PDF generation returned null\n";
    }
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
