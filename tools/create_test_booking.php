<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

$voyage = DB::table('voyage')->first();
if (!$voyage) {
    echo "NO_VOYAGE\n";
    exit(1);
}

DB::beginTransaction();
try {
    $bookingId = DB::table('booking')->insertGetId([
        'booking_date' => now(),
        'booking_status' => 'Pending',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $paymentId = DB::table('payment')->insertGetId([
        'booking_ref_no' => $bookingId,
        'mode_of_payment' => 'Gcash',
        'payment_date' => now(),
        'total_amount' => 0,
        'payment_status' => 'Pending',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // create a sample passenger
    $passengerId = DB::table('passenger')->insertGetId([
        'passenger_firstname' => 'Test',
        'passenger_midinitial' => 'T',
        'passenger_lastname' => 'User',
        'passenger_suffix' => null,
        'passenger_age' => 30,
        'passenger_gender' => 'M',
        'passenger_type' => 'Regular',
        'passenger_address' => 'Test Address',
        'passenger_contactno' => '09171234567',
        'passenger_email' => 'test+booking@example.invalid',
        'passenger_idnumber' => 'TEST1234',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // find an available cot (1..50)
    $used = DB::table('passenger_ticket')->where('voyage_id', $voyage->voyage_id)->pluck('pt_cot_no')->toArray();
    $cot = null;
    for ($i = 1; $i <= 50; $i++) {
        if (!in_array($i, $used)) {
            $cot = $i;
            break;
        }
    }
    if (!$cot)
        $cot = 1;

    $validUntil = Carbon::now()->addMinutes(8);

    $price = 500.00;
    $ptId = DB::table('passenger_ticket')->insertGetId([
        'passenger_id' => $passengerId,
        'voyage_id' => $voyage->voyage_id,
        'promo_id' => null,
        'payment_id' => $paymentId,
        'booking_ref_no' => $bookingId,
        'pt_valid_until' => $validUntil->toDateString(),
        'pt_valid_until_ts' => $validUntil->toDateTimeString(),
        'pt_cot_no' => $cot,
        'pt_ticket_price' => $price,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('payment')->where('payment_id', $paymentId)->update(['total_amount' => $price, 'updated_at' => now()]);

    DB::commit();

    // Dispatching cancel job via DB requires worker; we rely on running worker.
    echo "CREATED_BOOKING:" . $bookingId . "\n";
    echo "CONFIRM_URL:" . url('/passenger/confirmbooking/' . $bookingId) . "\n";
    echo "PT_VALID_UNTIL:" . $validUntil->toDateTimeString() . "\n";
    echo "COT:" . $cot . "\n";
} catch (Exception $e) {
    DB::rollBack();
    echo "ERROR:" . $e->getMessage() . "\n";
    exit(1);
}
