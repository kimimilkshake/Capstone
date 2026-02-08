<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Booking;
use App\Models\Payment;

echo "=== Force Cleanup All Pending Bookings ===\n\n";

$pendingBookings = Booking::where('booking_status', 'Pending')->get();

if ($pendingBookings->isEmpty()) {
    echo "No pending bookings found.\n";
} else {
    echo "Found {$pendingBookings->count()} pending booking(s). Canceling all...\n\n";

    foreach ($pendingBookings as $booking) {
        echo "Booking Ref: {$booking->booking_ref_no} - Created: {$booking->created_at}\n";

        $booking->booking_status = 'Canceled';
        $booking->save();

        $payment = Payment::where('booking_ref_no', $booking->booking_ref_no)->first();
        if ($payment) {
            $payment->payment_status = 'Canceled';
            $payment->save();
        }

        echo "  ✓ Canceled\n";
    }

    echo "\nAll pending bookings have been canceled!\n";
}
