<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class CancelBookingHold implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $bookingRef;

    /**
     * Create a new job instance.
     */
    public function __construct($bookingRef)
    {
        $this->bookingRef = $bookingRef;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Re-check payment and booking status. If still pending, cancel and free up tickets.
        DB::transaction(function () {
            $booking = DB::table('booking')->where('booking_ref_no', $this->bookingRef)->first();
            if (!$booking)
                return;

            // If booking already confirmed, do nothing
            if (strtolower($booking->booking_status) === 'confirmed')
                return;

            // Check payment
            $payment = DB::table('payment')->where('booking_ref_no', $this->bookingRef)->first();
            if ($payment && strtolower($payment->payment_status) === 'completed') {
                // Payment already completed -> keep booking
                return;
            }

            // Cancel booking
            DB::table('booking')->where('booking_ref_no', $this->bookingRef)->update([
                'booking_status' => 'Canceled',
                'updated_at' => now(),
            ]);

            // Cancel payment if exists
            if ($payment) {
                DB::table('payment')->where('payment_id', $payment->payment_id)->update([
                    'payment_status' => 'Canceled',
                    'updated_at' => now(),
                ]);
            }

            // Free up the passenger_ticket rows so the cots become available again for others.
            // We clear the booking_ref_no, payment_id and the pt_valid_until_ts timestamp.
            DB::table('passenger_ticket')->where('booking_ref_no', $this->bookingRef)->update([
                'booking_ref_no' => null,
                'payment_id' => null,
                'pt_valid_until_ts' => null,
                'updated_at' => now(),
            ]);
        });
    }
}
