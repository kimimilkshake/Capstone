<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Jobs\SendTicketEmail;

echo "🎫 Testing 2-Passenger Booking\n";
echo "=============================\n\n";

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

    // Create a new booking (booking_ref_no will be auto-generated)
    $bookingRefNo = DB::table('booking')->insertGetId([
        'voyage_id' => $voyage->voyage_id,
        'booking_status' => 'Confirmed',
        'created_at' => now(),
        'updated_at' => now(),
    ], 'booking_ref_no');

    echo "✓ Booking created: #{$bookingRefNo}\n\n";

    // Passenger 1: Shem Cardoza (should receive email - FIRST PASSENGER)
    $passenger1Id = DB::table('passenger')->insertGetId([
        'passenger_firstname' => 'Shem',
        'passenger_lastname' => 'Cardoza',
        'passenger_midinitial' => 'R',
        'passenger_age' => 21,
        'passenger_gender' => 'M',
        'passenger_address' => 'Cebu City',
        'passenger_contactno' => '09123456789',
        'passenger_email' => 'shemcardoza7@gmail.com',
        'passenger_idnumber' => 'ID001',
        'created_at' => now(),
        'updated_at' => now(),
    ], 'passenger_id');

    // Passenger 2: Heisenberg Fritz (should NOT receive email - SECOND PASSENGER)
    $passenger2Id = DB::table('passenger')->insertGetId([
        'passenger_firstname' => 'Heisenberg',
        'passenger_lastname' => 'Fritz',
        'passenger_age' => 8,
        'passenger_gender' => 'M',
        'passenger_address' => 'Baybay, Leyte',
        'passenger_contactno' => '09987654321',
        'passenger_email' => 'heisenbergfritz@gmail.com',
        'passenger_idnumber' => 'ID002',
        'created_at' => now(),
        'updated_at' => now(),
    ], 'passenger_id');

    echo "✓ Passengers created (in order):\n";
    echo "  1️⃣ Shem Cardoza (shemcardoza7@gmail.com) - WILL RECEIVE EMAIL\n";
    echo "  2️⃣ Heisenberg Fritz (heisenbergfritz@gmail.com) - Will NOT receive email\n\n";

    // Create tickets
    $ticket1Id = DB::table('passenger_ticket')->insertGetId([
        'passenger_id' => $passenger1Id,
        'voyage_id' => $voyage->voyage_id,
        'booking_ref_no' => $bookingRefNo,
        'pt_cot_no' => 101,
        'pt_ticket_price' => 1500.00,
        'pt_valid_until' => now()->addMonths(6),
        'created_at' => now(),
        'updated_at' => now(),
    ], 'passenger_ticket_id');

    $ticket2Id = DB::table('passenger_ticket')->insertGetId([
        'passenger_id' => $passenger2Id,
        'voyage_id' => $voyage->voyage_id,
        'booking_ref_no' => $bookingRefNo,
        'pt_cot_no' => 102,
        'pt_ticket_price' => 750.00,
        'pt_valid_until' => now()->addMonths(6),
        'created_at' => now(),
        'updated_at' => now(),
    ], 'passenger_ticket_id');

    echo "✓ Tickets created:\n";
    echo "  Ticket 1: ₱1,500.00 (COT #101) - Shem Cardoza\n";
    echo "  Ticket 2: ₱750.00 (COT #102) - Heisenberg Fritz\n\n";

    // Create payment
    $totalAmount = 2250.00;
    $paymentId = DB::table('payment')->insertGetId([
        'booking_ref_no' => $bookingRefNo,
        'total_amount' => $totalAmount,
        'mode_of_payment' => 'GCash',
        'payment_status' => 'Completed',
        'created_at' => now(),
        'updated_at' => now(),
    ], 'payment_id');

    echo "✓ Payment created: ₱{$totalAmount} (GCash)\n\n";

    DB::commit();

    echo "========================================\n";
    echo "📧 TEST: Email Configuration\n";
    echo "========================================\n";
    echo "Email Recipient: shemcardoza7@gmail.com\n";
    echo "Subject: Your Passenger Ticket Confirmed - Booking #{$bookingRefNo}\n\n";

    echo "Email Attachments (2 PDFs):\n";
    echo "  1. ticket_Shem_Cardoza.pdf\n";
    echo "  2. ticket_Heisenberg_Fritz.pdf\n\n";

    echo "Email Body includes:\n";
    echo "  ✓ Voyage: {$voyage->voyage_code}\n";
    echo "  ✓ Passenger table with BOTH passengers listed\n";
    echo "  ✓ Total amount paid: ₱{$totalAmount}\n";
    echo "  ✓ Important reminders\n\n";

    echo "========================================\n";
    echo "🚀 Dispatching Email Job\n";
    echo "========================================\n";
    SendTicketEmail::dispatch($bookingRefNo);
    echo "✓ Email job queued!\n\n";

    echo "⏳ To process the queue:\n";
    echo "   Run: php artisan queue:work\n\n";

    echo "📬 Expected Result:\n";
    echo "   - Email sent to: shemcardoza7@gmail.com ✓\n";
    echo "   - Email sent to: heisenbergfritz@gmail.com ✗ (NOT sent)\n";
    echo "   - 2 PDF tickets attached to the email ✓\n";
    echo "   - Each PDF shows individual passenger info ✓\n";

} catch (Exception $e) {
    DB::rollBack();
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
