<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Booking;
use App\Models\Payment;

echo "=== Pending Bookings ===\n\n";

$bookings = Booking::where('booking_status', 'Pending')
    ->orderBy('created_at', 'desc')
    ->get();

if ($bookings->isEmpty()) {
    echo "No pending bookings found.\n";
} else {
    foreach ($bookings as $booking) {
        echo "Booking Ref: {$booking->booking_ref_no}\n";
        echo "Created: {$booking->created_at}\n";
        echo "Status: {$booking->booking_status}\n";

        $payment = Payment::where('booking_ref_no', $booking->booking_ref_no)->first();
        if ($payment) {
            echo "Payment Status: {$payment->payment_status}\n";
            echo "Payment Mode: {$payment->mode_of_payment}\n";
        } else {
            echo "Payment: Not found\n";
        }

        echo "---\n";
    }

    echo "\nTotal pending bookings: " . $bookings->count() . "\n";
}
