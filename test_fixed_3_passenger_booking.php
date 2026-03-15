<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Jobs\SendTicketEmail;

echo "🎫 Testing FIXED 3-Passenger Booking (with voyage_id)\n";
echo "===================================================\n\n";

try {
    DB::beginTransaction();

    // Get a scheduled voyage
    $voyage = DB::table('voyage')
        ->whereIn('voyage_status', ['Active', 'Scheduled'])
        ->first();

    if (!$voyage) {
        echo "❌ No active or scheduled voyage found\n";
        exit(1);
    }

    echo "Using voyage: {$voyage->voyage_code}\n";
    echo "Vessel ID: {$voyage->vessel_id}\n\n";

    // Create a new booking WITH voyage_id (this is the fix!)
    $bookingRefNo = DB::table('booking')->insertGetId([
        'voyage_id' => $voyage->voyage_id,  // ← THIS IS THE FIX
        'booking_date' => now(),
        'booking_status' => 'Confirmed',
        'booking_type' => 'passenger',
        'created_at' => now(),
        'updated_at' => now(),
    ], 'booking_ref_no');

    echo "✓ Booking created: #{$bookingRefNo}\n";
    echo "✓ Voyage ID saved: {$voyage->voyage_id}\n\n";

    // Passenger 1
    $passenger1Id = DB::table('passenger')->insertGetId([
        'passenger_firstname' => 'Jane',
        'passenger_lastname' => 'Smith',
        'passenger_midinitial' => 'M',
        'passenger_age' => 35,
        'passenger_gender' => 'F',
        'passenger_type' => 'Regular',
        'passenger_address' => 'Manila',
        'passenger_contactno' => '09199999999',
        'passenger_email' => 'jane.smith@example.com',
        'passenger_idnumber' => 'ID101',
        'created_at' => now(),
        'updated_at' => now(),
    ], 'passenger_id');

    // Passenger 2
    $passenger2Id = DB::table('passenger')->insertGetId([
        'passenger_firstname' => 'John',
        'passenger_lastname' => 'Doe',
        'passenger_age' => 28,
        'passenger_gender' => 'M',
        'passenger_type' => 'Regular',
        'passenger_address' => 'Tagaytay',
        'passenger_contactno' => '09188888888',
        'passenger_email' => 'john.doe@example.com',
        'passenger_idnumber' => 'ID102',
        'created_at' => now(),
        'updated_at' => now(),
    ], 'passenger_id');

    // Passenger 3
    $passenger3Id = DB::table('passenger')->insertGetId([
        'passenger_firstname' => 'Maria',
        'passenger_lastname' => 'Garcia',
        'passenger_age' => 12,
        'passenger_gender' => 'F',
        'passenger_type' => '3 to 11 years old',
        'passenger_address' => 'Quezon City',
        'passenger_contactno' => '09177777777',
        'passenger_email' => 'maria.garcia@example.com',
        'passenger_idnumber' => 'ID103',
        'created_at' => now(),
        'updated_at' => now(),
    ], 'passenger_id');

    echo "✓ 3 Passengers created\n\n";

    // Create tickets with different COT numbers
    $cots = [201, 202, 203];
    $prices = [1500.00, 1500.00, 750.00];
    $passengerIds = [$passenger1Id, $passenger2Id, $passenger3Id];

    foreach ($passengerIds as $idx => $pId) {
        DB::table('passenger_ticket')->insertGetId([
            'passenger_id' => $pId,
            'voyage_id' => $voyage->voyage_id,
            'booking_ref_no' => $bookingRefNo,
            'pt_cot_no' => $cots[$idx],
            'pt_ticket_price' => $prices[$idx],
            'pt_valid_until' => now()->addMonths(6),
            'created_at' => now(),
            'updated_at' => now(),
        ], 'passenger_ticket_id');
    }

    echo "✓ 3 Tickets created (COT #201, #202, #203)\n\n";

    // Create payment
    $totalAmount = 3750.00;
    DB::table('payment')->insertGetId([
        'booking_ref_no' => $bookingRefNo,
        'total_amount' => $totalAmount,
        'mode_of_payment' => 'GCash',
        'payment_status' => 'Completed',
        'created_at' => now(),
        'updated_at' => now(),
    ], 'payment_id');

    echo "✓ Payment created: ₱{$totalAmount}\n\n";

    DB::commit();

    echo "========================================\n";
    echo "📧 Sending Email\n";
    echo "========================================\n";
    SendTicketEmail::dispatch($bookingRefNo);
    echo "✓ Email job queued!\n\n";

    echo "✅ TEST COMPLETE!\n";
    echo "   - Booking #$bookingRefNo has voyage_id saved\n";
    echo "   - All 3 passengers added\n";
    echo "   - Email with 3 PDFs queued\n";
    echo "   - ⭐ Accommodations should now appear in PDFs!\n";

} catch (Exception $e) {
    DB::rollBack();
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>