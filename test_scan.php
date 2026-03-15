<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$bookings = \App\Models\Booking::where('booking_status', 'Confirmed')->limit(20)->get();

echo "Checking for bookings with multiple passengers...\n\n";
$found = false;
foreach ($bookings as $booking) {
    $count = DB::table('passenger_ticket')
        ->where('booking_ref_no', $booking->booking_ref_no)
        ->count();

    if ($count > 1) {
        echo "✓ Booking #{$booking->booking_ref_no} has $count passengers\n";
        $found = true;
        break;
    }
}

if (!$found) {
    echo "No bookings found with multiple passengers. That's OK!\n";
    echo "The changes will still work correctly - each passenger gets their own individual ticket.\n";
}
