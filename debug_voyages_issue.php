<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "🔍 DEBUG: CHECKING RECENT BOOKINGS\n";
echo "==================================\n\n";

// Get last 5 bookings
$bookings = DB::table('booking')
    ->orderByDesc('booking_ref_no')
    ->limit(5)
    ->get();

echo "Recent Bookings:\n";
foreach ($bookings as $booking) {
    echo "\nBooking #" . $booking->booking_ref_no . ":\n";
    echo "  Status: " . $booking->booking_status . "\n";
    echo "  Voyage ID: " . ($booking->voyage_id ? $booking->voyage_id : "NULL ❌") . "\n";
    echo "  Created: " . $booking->created_at . "\n";

    // Get voyage
    if ($booking->voyage_id) {
        $voyage = DB::table('voyage')->where('voyage_id', $booking->voyage_id)->first();
        if ($voyage) {
            echo "  Voyage: " . $voyage->voyage_code . "\n";
        } else {
            echo "  Voyage: ❌ VOYAGE ID NOT IN DB!\n";
        }
    }

    // Get passenger count
    $passengerCount = DB::table('passenger_ticket')
        ->where('booking_ref_no', $booking->booking_ref_no)
        ->count();
    echo "  Passengers: " . $passengerCount . "\n";
}

echo "\n\n";
echo "📊 VOYAGES IN DATABASE:\n";
echo "========================\n";

$voyages = DB::table('voyage')
    ->whereIn('voyage_status', ['Active', 'Scheduled'])
    ->limit(5)
    ->get();

echo "Active/Scheduled Voyages:\n";
foreach ($voyages as $v) {
    echo "  - " . $v->voyage_code . " (ID: " . $v->voyage_id . ")\n";
    echo "    Departure: " . $v->voyage_departure_date . "\n";
}

if (count($voyages) == 0) {
    echo "❌ NO ACTIVE OR SCHEDULED VOYAGES FOUND!\n";
}
?>