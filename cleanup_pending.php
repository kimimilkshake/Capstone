<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Booking;
use App\Models\Payment;
use Carbon\Carbon;

echo "=== Cleaning Up Old Pending Bookings ===\n\n";

$fiveMinutesAgo = Carbon::now()->subMinutes(5);

$oldPendingBookings = Booking::where('booking_status', 'Pending')
    ->where('created_at', '<', $fiveMinutesAgo)
    ->get();

if ($oldPendingBookings->isEmpty()) {
    echo "No old pending bookings to clean up.\n";
} else {
    echo "Found {$oldPendingBookings->count()} old pending booking(s):\n\n";

    foreach ($oldPendingBookings as $booking) {
        echo "Canceling Booking Ref: {$booking->booking_ref_no}\n";
        echo "  Created: {$booking->created_at}\n";
        echo "  Age: " . $booking->created_at->diffForHumans() . "\n";

        // Update booking status
        $booking->booking_status = 'Canceled';
        $booking->save();

        // Update payment status
        $payment = Payment::where('booking_ref_no', $booking->booking_ref_no)->first();
        if ($payment) {
            $payment->payment_status = 'Canceled';
            $payment->save();
            echo "  Payment status updated to Canceled\n";
        }

        echo "  ✓ Booking canceled\n\n";
    }

    echo "Cleanup complete!\n";
}
