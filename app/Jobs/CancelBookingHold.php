<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
        DB::transaction(function () {
            $booking = DB::table('booking')->where('booking_ref_no', $this->bookingRef)->first();
            if (!$booking) {
                Log::info("CancelBookingHold: booking {$this->bookingRef} not found");
                return;
            }

            if (strtolower($booking->booking_status) === 'confirmed') {
                Log::info("CancelBookingHold: booking {$this->bookingRef} already confirmed; skip");
                return;
            }

            $payment = DB::table('payment')->where('booking_ref_no', $this->bookingRef)->first();
            if ($payment && strtolower($payment->payment_status) === 'completed') {
                Log::info("CancelBookingHold: payment completed for booking {$this->bookingRef}; skip");
                return;
            }

            // Ensure hold really expired: check the earliest ticket's pt_valid_until_ts
            $ticket = DB::table('passenger_ticket')
                ->where('booking_ref_no', $this->bookingRef)
                ->orderBy('passenger_ticket_id')
                ->first();
            if ($ticket && $ticket->pt_valid_until_ts) {
                $expiresAt =
                    \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $ticket->pt_valid_until_ts);
                if (now()->lt($expiresAt)) {
                    Log::info("CancelBookingHold: booking {$this->bookingRef} not yet expired (expires {$ticket->pt_valid_until_ts}); skip");
                    return; // still within hold window
                }
            }

            Log::info("CancelBookingHold: CANCELING booking {$this->bookingRef}");
            DB::table('booking')->where('booking_ref_no', $this->bookingRef)->update([
                'booking_status' => 'Canceled',
                'updated_at' => now(),
            ]);

            if ($payment) {
                DB::table('payment')->where('payment_id', $payment->payment_id)->update([
                    'payment_status' => 'Canceled',
                    'updated_at' => now(),
                ]);
            }

            DB::table('passenger_ticket')->where('booking_ref_no', $this->bookingRef)->update([
                'booking_ref_no' => null,
                'payment_id' => null,
                'pt_valid_until_ts' => null,
                'updated_at' => now(),
            ]);
        });
    }
}
