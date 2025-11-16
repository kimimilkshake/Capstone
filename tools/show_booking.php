<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$ref = $argv[1] ?? null;
if (!$ref) {
    echo "Usage: php tools/show_booking.php <booking_ref_no>\n";
    exit(1);
}

$booking = DB::table('booking')->where('booking_ref_no', $ref)->first();
if (!$booking) {
    echo "Booking not found\n";
    exit(1);
}
$payment = DB::table('payment')->where('booking_ref_no', $ref)->first();
$tickets = DB::table('passenger_ticket')->where('booking_ref_no', $ref)->get();

echo "BOOKING:\n";
echo json_encode($booking, JSON_PRETTY_PRINT) . "\n\n";

echo "PAYMENT:\n";
echo json_encode($payment, JSON_PRETTY_PRINT) . "\n\n";

echo "TICKETS (" . count($tickets) . "):\n";
foreach ($tickets as $t) {
    echo json_encode($t, JSON_PRETTY_PRINT) . "\n";
}
