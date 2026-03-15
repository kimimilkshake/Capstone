<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\PassengerTicketPdf;

echo "🎫 Testing Multi-Ticket Email Implementation\n";
echo "===========================================\n\n";

// Find a booking with confirmed status and actual passengers
$bookings = \App\Models\Booking::where('booking_status', 'Confirmed')
    ->whereNotNull('voyage_id')
    ->limit(20)
    ->get();

$booking = null;
foreach ($bookings as $b) {
    $ticketCount = DB::table('passenger_ticket')->where('booking_ref_no', $b->booking_ref_no)->count();
    if ($ticketCount > 0) {
        $booking = $b;
        break;
    }
}

if (!$booking) {
    echo "❌ No confirmed booking with passengers found for testing\n";
    exit(1);
}

echo "Found booking: #{$booking->booking_ref_no}\n";

// Get all tickets for this booking
$tickets = DB::table('passenger_ticket')
    ->where('booking_ref_no', $booking->booking_ref_no)
    ->get();

echo "Total passengers: " . count($tickets) . "\n\n";

// Get first passenger (who should receive email)
$firstPassenger = DB::table('passenger')
    ->join('passenger_ticket', 'passenger.passenger_id', '=', 'passenger_ticket.passenger_id')
    ->where('passenger_ticket.booking_ref_no', $booking->booking_ref_no)
    ->orderBy('passenger_ticket.passenger_ticket_id', 'asc')
    ->select('passenger.passenger_firstname', 'passenger.passenger_lastname', 'passenger.passenger_email')
    ->first();

echo "First Passenger (Email Recipient):\n";
echo "  Name: {$firstPassenger->passenger_firstname} {$firstPassenger->passenger_lastname}\n";
echo "  Email: {$firstPassenger->passenger_email}\n\n";

echo "All Passengers who will receive individual tickets:\n";
$testDir = storage_path('app/test_pdfs');
if (!is_dir($testDir)) {
    mkdir($testDir, 0777, true);
}

$successCount = 0;
$failCount = 0;

// Get all passengers for this booking
$allPassengers = DB::table('passenger')
    ->join('passenger_ticket', 'passenger.passenger_id', '=', 'passenger_ticket.passenger_id')
    ->where('passenger_ticket.booking_ref_no', $booking->booking_ref_no)
    ->orderBy('passenger_ticket.passenger_ticket_id', 'asc')
    ->select('passenger.passenger_firstname', 'passenger.passenger_lastname', 'passenger.passenger_email')
    ->distinct()
    ->get();

foreach ($allPassengers as $idx => $passenger) {
    echo ($idx + 1) . ". {$passenger->passenger_firstname} {$passenger->passenger_lastname} ({$passenger->passenger_email})\n";

    // Test generating PDF for this passenger
    try {
        $pdf = PassengerTicketPdf::generate($booking->booking_ref_no, null, null, $passenger->passenger_email);

        if ($pdf) {
            $filename = "ticket_" . str_replace([' ', '@', '.'], ['_', '_at_', '_'], $passenger->passenger_firstname . '_' . $passenger->passenger_lastname) . ".pdf";
            $filepath = "{$testDir}/{$filename}";
            file_put_contents($filepath, $pdf);
            $filesize = filesize($filepath);
            echo "   ✓ PDF generated ({$filesize} bytes)\n";
            $successCount++;
        } else {
            echo "   ✗ Failed to generate PDF\n";
            $failCount++;
        }
    } catch (Exception $e) {
        echo "   ✗ Error: " . $e->getMessage() . "\n";
        $failCount++;
    }
}

echo "\n📧 Email Configuration:\n";
echo "  To: {$firstPassenger->passenger_email}\n";
echo "  Subject: Your Passenger Ticket Confirmed - Booking #{$booking->booking_ref_no}\n";
echo "  Attachments: " . count($allPassengers) . " PDF files (one per passenger)\n";

echo "\n✓ Test Results:\n";
echo "  Succeed: $successCount\n";
echo "  Failed: $failCount\n";
echo "\n📁 Generated PDFs saved to: storage/app/test_pdfs/\n";
