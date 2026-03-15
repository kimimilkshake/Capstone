<?php
require 'bootstrap/app.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\PassengerTicketPdf;

echo "=== Testing Individual Passenger PDF Generation ===\n\n";

// Find a booking with multiple passengers
$bookings = \App\Models\Booking::where('booking_status', 'Confirmed')
    ->whereNotNull('voyage_id')
    ->limit(10)
    ->get();

foreach ($bookings as $booking) {
    $passengers = \DB::table('passenger')
        ->join('passenger_ticket', 'passenger.passenger_id', '=', 'passenger_ticket.passenger_id')
        ->where('passenger_ticket.booking_ref_no', $booking->booking_ref_no)
        ->select('passenger.passenger_email', 'passenger.passenger_firstname', 'passenger.passenger_lastname')
        ->distinct()
        ->get();

    if (count($passengers) > 0) {
        echo "Found Booking {$booking->booking_ref_no} with " . count($passengers) . " passenger(s):\n";

        // Test: Generate PDF for each passenger individually
        foreach ($passengers as $passenger) {
            echo "  - Generating PDF for: {$passenger->passenger_firstname} {$passenger->passenger_lastname} ({$passenger->passenger_email})\n";

            $pdf = PassengerTicketPdf::generate($booking->booking_ref_no, null, null, $passenger->passenger_email);

            if ($pdf) {
                $filename = "passenger_" . str_replace('@', '_at_', $passenger->passenger_email) . ".pdf";
                file_put_contents(storage_path("app/test_pdfs/{$filename}"), $pdf);
                $filesize = filesize(storage_path("app/test_pdfs/{$filename}"));
                echo "    ✓ PDF generated successfully ({$filesize} bytes)\n";
            } else {
                echo "    ✗ Failed to generate PDF\n";
            }
        }

        echo "\n";
        break;
    }
}

echo "Test complete! Check storage/app/test_pdfs/ for generated PDFs\n";
