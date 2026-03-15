<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\PassengerTicketPdf;
use App\Models\PassengerTicket;

echo "🎫 Individual Passenger PDF Test\n";
echo "================================\n\n";

// Find a booking with multiple passengers
$bookings = \App\Models\Booking::where('booking_status', 'Confirmed')
    ->whereNotNull('voyage_id')
    ->limit(10)
    ->get();

$testBooking = null;
foreach ($bookings as $booking) {
    $uniqueEmails = DB::table('passenger')
        ->join('passenger_ticket', 'passenger.passenger_id', '=', 'passenger_ticket.passenger_id')
        ->where('passenger_ticket.booking_ref_no', $booking->booking_ref_no)
        ->select('passenger.passenger_email', 'passenger.passenger_firstname', 'passenger.passenger_lastname')
        ->distinct()
        ->get();

    if (count($uniqueEmails) >= 1) {
        $testBooking = $booking;
        echo "Found booking #" . $booking->booking_ref_no . " with " . count($uniqueEmails) . " unique passenger email(s):\n";
        foreach ($uniqueEmails as $passenger) {
            echo "  ✓ {$passenger->passenger_firstname} {$passenger->passenger_lastname} ({$passenger->passenger_email})\n";
        }
        echo "\n";
        break;
    }
}

if (!$testBooking) {
    echo "❌ No suitable booking found for testing\n";
    exit(1);
}

// Create test directory
$testDir = storage_path('app/test_pdfs');
if (!is_dir($testDir)) {
    mkdir($testDir, 0777, true);
}

echo "Testing PDF generation...\n";
echo "========================\n\n";

// Get all unique emails again
$emails = DB::table('passenger')
    ->join('passenger_ticket', 'passenger.passenger_id', '=', 'passenger_ticket.passenger_id')
    ->where('passenger_ticket.booking_ref_no', $testBooking->booking_ref_no)
    ->select('passenger.passenger_email', 'passenger.passenger_firstname')
    ->distinct()
    ->get();

$successCount = 0;
$failCount = 0;

foreach ($emails as $passenger) {
    echo "Generating PDF for: {$passenger->passenger_firstname} ({$passenger->passenger_email})\n";

    try {
        $pdf = PassengerTicketPdf::generate($testBooking->booking_ref_no, null, null, $passenger->passenger_email);

        if ($pdf) {
            $filename = str_replace(['@', '.'], ['_at_', '_'], $passenger->passenger_email);
            $filepath = "{$testDir}/{$filename}.pdf";
            file_put_contents($filepath, $pdf);
            $filesize = filesize($filepath);

            // Verify PDF content - check if it contains the passenger name
            if (strpos($pdf, $passenger->passenger_firstname) !== false) {
                echo "  ✓ PDF generated successfully ({$filesize} bytes)\n";
                echo "  ✓ PDF contains passenger name\n";
                $successCount++;
            } else {
                echo "  ⚠ PDF generated but doesn't contain passenger name!\n";
                $failCount++;
            }
        } else {
            echo "  ✗ Failed to generate PDF\n";
            $failCount++;
        }
    } catch (Exception $e) {
        echo "  ✗ Error: " . $e->getMessage() . "\n";
        $failCount++;
    }
    echo "\n";
}

echo "Test Results\n";
echo "============\n";
echo "✓ Succeed: $successCount\n";
echo "✗ Failed: $failCount\n";
echo "\nGenerated PDFs are in: storage/app/test_pdfs/\n";
