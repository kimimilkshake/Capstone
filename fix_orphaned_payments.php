<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Payment;
use App\Models\Booking;

echo "=== Checking for Orphaned Pending Payments ===\n\n";

// Find payments that are Pending but their booking is Canceled
$orphanedPayments = Payment::where('payment_status', 'Pending')->get();

if ($orphanedPayments->isEmpty()) {
    echo "No pending payments found.\n";
} else {
    echo "Found {$orphanedPayments->count()} pending payment(s):\n\n";

    foreach ($orphanedPayments as $payment) {
        $booking = Booking::where('booking_ref_no', $payment->booking_ref_no)->first();

        echo "Payment ID: {$payment->payment_id}\n";
        echo "Booking Ref: {$payment->booking_ref_no}\n";
        echo "Payment Status: {$payment->payment_status}\n";

        if ($booking) {
            echo "Booking Status: {$booking->booking_status}\n";

            if ($booking->booking_status === 'Canceled') {
                echo "  → Fixing: Setting payment to Canceled (booking is already canceled)\n";
                $payment->payment_status = 'Canceled';
                $payment->save();
            } else {
                echo "  → Booking is {$booking->booking_status}, leaving payment as is\n";
            }
        } else {
            echo "Booking: NOT FOUND\n";
            echo "  → Fixing: Setting payment to Canceled (booking doesn't exist)\n";
            $payment->payment_status = 'Canceled';
            $payment->save();
        }

        echo "---\n";
    }

    echo "\nCleanup complete!\n";
}
