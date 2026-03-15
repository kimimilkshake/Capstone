<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "🎫 TEST: REQUEST INDIVIDUAL TICKET COPY\n";
echo "=======================================\n\n";

// Get a confirmed booking with multiple passengers
$booking = DB::table('booking')
    ->join('passenger_ticket', 'booking.booking_ref_no', '=', 'passenger_ticket.booking_ref_no')
    ->where('booking.booking_status', 'Confirmed')
    ->groupBy('booking.booking_ref_no', 'booking.voyage_id')
    ->havingRaw('COUNT(passenger_ticket.passenger_id) > 1')
    ->orderByDesc('booking.created_at')
    ->select('booking.booking_ref_no', 'booking.voyage_id', DB::raw('COUNT(passenger_ticket.passenger_id) as passenger_count'))
    ->first();

if (!$booking) {
    echo "❌ No multi-passenger booking found\n";
    exit;
}

echo "Using Booking #" . $booking->booking_ref_no . " with " . $booking->passenger_count . " passengers\n\n";

// Get all passengers in this booking
$passengers = DB::table('passenger')
    ->join('passenger_ticket', 'passenger.passenger_id', '=', 'passenger_ticket.passenger_id')
    ->where('passenger_ticket.booking_ref_no', $booking->booking_ref_no)
    ->orderBy('passenger_ticket.created_at')
    ->select(
        'passenger.passenger_id',
        'passenger.passenger_firstname',
        'passenger.passenger_lastname',
        'passenger.passenger_type',
        'passenger.passenger_email'
    )
    ->get();

echo "Passengers:\n";
foreach ($passengers as $p) {
    echo "  • " . $p->passenger_firstname . " " . $p->passenger_lastname . " (" . $p->passenger_type . ")\n";
    echo "    Email: " . $p->passenger_email . "\n";
}

// Get voyage details
$voyage = DB::table('voyage')->where('voyage_id', $booking->voyage_id)->first();
if ($voyage) {
    echo "\nVoyage: " . $voyage->voyage_code . "\n";
    echo "Departure: " . $voyage->voyage_departure_date . "\n";
}

echo "\n========================================\n";
echo "📧 TEST SCENARIO 1: Auto-send (1 passenger)\n";
echo "========================================\n";

// Test requesting copy with only one passenger matching
$testPassenger = $passengers->first();
echo "Requesting ticket for: " . $testPassenger->passenger_email . "\n";
echo "Departure Date: " . $voyage->voyage_departure_date . "\n\n";

echo "Expected: ✅ Auto-sends ticket (only 1 passenger matches)\n";

echo "\n========================================\n";
echo "📧 TEST SCENARIO 2: Show selection (multiple passengers)\n";
echo "========================================\n";

// Test requesting copy with email and date (multiple passengers)
$departureDate = $voyage->voyage_departure_date;
echo "Email: " . $testPassenger->passenger_email . "\n";
echo "Departure Date: " . $departureDate . "\n\n";

// Get all passengers with this email and voyage
$allWithEmail = DB::table('passenger')
    ->join('passenger_ticket', 'passenger.passenger_id', '=', 'passenger_ticket.passenger_id')
    ->join('voyage', 'passenger_ticket.voyage_id', '=', 'voyage.voyage_id')
    ->where('passenger.passenger_email', $testPassenger->passenger_email)
    ->whereDate('voyage.voyage_departure_date', $departureDate)
    ->select(
        'passenger.passenger_id',
        'passenger.passenger_firstname',
        'passenger.passenger_lastname',
        'passenger.passenger_type'
    )
    ->get();

if (count($allWithEmail) > 1) {
    echo "Found " . count($allWithEmail) . " passengers from this email on that date:\n\n";
    foreach ($allWithEmail as $index => $p) {
        echo "  [" . ($index + 1) . "] " . $p->passenger_firstname . " " . $p->passenger_lastname . " (" . $p->passenger_type . ")\n";
    }
    echo "\nExpected: ✅ Show list for passenger to select their name\n";
    echo "When selected (e.g., click [2]): ✅ Send ONLY that passenger's ticket\n";
} else {
    echo "Only 1 passenger with this email: " . $allWithEmail->first()->passenger_firstname . "\n";
    echo "Expected: ✅ Auto-send their ticket\n";
}

echo "\n========================================\n";
echo "📧 TEST SCENARIO 3: Most recent booking\n";
echo "========================================\n";

echo "If same passenger booked multiple times on same departure date:\n";
echo "  • System queries: ORDER BY booking.created_at DESC\n";
echo "  • Expected: ✅ Uses MOST RECENT booking for that passenger\n";

echo "\n========================================\n";
echo "✅ FEATURE OVERVIEW:\n";
echo "========================================\n";
echo "✓ Step 1: Passenger provides email + departure date\n";
echo "✓ Step 2: System checks for matches\n";
echo "  - If 1 passenger: Auto-send ticket\n";
echo "  - If multiple: Show list to select\n";
echo "✓ Step 3: Passenger selection sent\n";
echo "✓ Step 4: Only THAT passenger's PDF ticket sent\n";
echo "✓ Step 5: Email sent to passenger's own email\n\n";

echo "✅ Benefits:\n";
echo "  • Each passenger gets only THEIR ticket\n";
echo "  • No confusion with other passengers' PDFs\n";
echo "  • Handles multiple people booking together\n";
echo "  • Handles duplicate bookings (uses most recent)\n";

?>