<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "ACCOMMODATION CHECK FOR BOOKING #26\n";
echo "===================================\n\n";

// Get the booking
$booking = DB::table('booking')
    ->where('booking_ref_no', 26)
    ->first();

if (!$booking) {
    echo "Booking #26 not found\n";
    exit;
}

echo "Booking: #" . $booking->booking_ref_no . "\n";
echo "Voyage ID: " . $booking->voyage_id . "\n\n";

// Get voyage
$voyage = DB::table('voyage')
    ->where('voyage_id', $booking->voyage_id)
    ->first();

if ($voyage) {
    echo "Voyage: " . $voyage->voyage_code . "\n";
    echo "Vessel ID: " . $voyage->vessel_id . "\n\n";

    // Check accommodations
    $accommodations = DB::table('accommodation')
        ->where('vessel_id', $voyage->vessel_id)
        ->get();

    echo "Accommodations: " . count($accommodations) . "\n";
    if (count($accommodations) > 0) {
        foreach ($accommodations as $accom) {
            echo "  - " . $accom->accommodation_name . "\n";
            echo "    COT Range: " . $accom->accommodation_cot_range . "\n";
        }
    } else {
        echo "  ❌ NO ACCOMMODATIONS CONFIGURED FOR THIS VESSEL\n";
    }
} else {
    echo "Voyage not found\n";
}

echo "\n";

// Check tickets
$tickets = DB::table('passenger_ticket')
    ->where('booking_ref_no', 26)
    ->orderBy('created_at')
    ->get();

echo "Tickets:\n";
foreach ($tickets as $ticket) {
    echo "  COT #" . $ticket->pt_cot_no . "\n";
}
?>