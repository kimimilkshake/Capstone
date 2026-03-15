<?php
require 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Payment;
use App\Models\Booking;

echo "=== RECENT PAYMENTS ===\n";
$payments = Payment::orderByDesc('payment_id')->limit(5)->get();
foreach ($payments as $p) {
    echo "\nPayment ID: {$p->payment_id}\n";
    echo "  Booking Ref: {$p->booking_ref_no}\n";
    echo "  Status: {$p->payment_status}\n";
    echo "  Transaction Code: {$p->transaction_code}\n";
    echo "  Total Amount: {$p->total_amount}\n";
    
    $booking = Booking::where('booking_ref_no', $p->booking_ref_no)->first();
    if ($booking) {
        echo "  Booking Status: {$booking->booking_status}\n";
    }
}
