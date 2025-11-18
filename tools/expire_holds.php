<?php
// Simple fallback script to cancel expired booking holds if queue worker was not running.
// Run manually: php tools/expire_holds.php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

$now = Carbon::now();
$affected = 0;

// Find pending bookings whose first ticket expired and payment still pending
$bookings = DB::table('booking')->where('booking_status', 'Pending')->get();
foreach ($bookings as $b) {
    $payment = DB::table('payment')->where('booking_ref_no', $b->booking_ref_no)->first();
    if ($payment && strtolower($payment->payment_status) !== 'pending') {
        continue; // already paid or canceled
    }
    $ticket = DB::table('passenger_ticket')
        ->where('booking_ref_no', $b->booking_ref_no)
        ->orderBy('passenger_ticket_id')
        ->first();
    if (!$ticket || !$ticket->pt_valid_until_ts) {
        continue;
    }
    $expiresAt = Carbon::createFromFormat('Y-m-d H:i:s', $ticket->pt_valid_until_ts);
    if ($now->gte($expiresAt)) {
        DB::table('booking')->where('booking_ref_no', $b->booking_ref_no)->update([
            'booking_status' => 'Canceled',
            'updated_at' => now(),
        ]);
        if ($payment) {
            DB::table('payment')->where('payment_id', $payment->payment_id)->update([
                'payment_status' => 'Canceled',
                'updated_at' => now(),
            ]);
        }
        DB::table('passenger_ticket')->where('booking_ref_no', $b->booking_ref_no)->update([
            'booking_ref_no' => null,
            'payment_id' => null,
            'pt_valid_until_ts' => null,
            'updated_at' => now(),
        ]);
        $affected++;
        echo "Canceled expired booking {$b->booking_ref_no}\n";
    }
}

echo "Total canceled: {$affected}\n";
