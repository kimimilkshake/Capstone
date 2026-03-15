<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "🔍 DEBUGGING BOOKING #26\n";
echo "=======================\n\n";

$booking = DB::table('booking')->where('booking_ref_no', 26)->first();

if ($booking) {
    echo "Booking exists!\n\n";
    echo "All fields:\n";
    $fields = (array) $booking;
    foreach ($fields as $key => $value) {
        echo "  " . $key . ": " . ($value === null ? "NULL" : $value) . "\n";
    }
} else {
    echo "Booking NOT found\n";
}
?>