<?php

namespace App\Jobs;

use App\Mail\TicketMailable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendTicketEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $bookingRef;
    public $recipientEmail;

    /**
     * Create a new job instance.
     */
    public function __construct($bookingRef, $recipientEmail = null)
    {
        $this->bookingRef = $bookingRef;
        $this->recipientEmail = $recipientEmail;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Get recipient email if not provided
            if (!$this->recipientEmail) {
                $firstPassenger = DB::table('passenger')
                    ->join('passenger_ticket', 'passenger.passenger_id', '=', 'passenger_ticket.passenger_id')
                    ->where('passenger_ticket.booking_ref_no', $this->bookingRef)
                    ->select('passenger.passenger_email', 'passenger.passenger_firstname', 'passenger.passenger_lastname')
                    ->first();

                if (!$firstPassenger || !$firstPassenger->passenger_email) {
                    Log::warning("SendTicketEmail: No email found for booking {$this->bookingRef}");
                    return;
                }

                $this->recipientEmail = $firstPassenger->passenger_email;
            }

            // Verify booking is confirmed and paid
            $booking = DB::table('booking')->where('booking_ref_no', $this->bookingRef)->first();
            $payment = DB::table('payment')->where('booking_ref_no', $this->bookingRef)->first();

            if (!$booking || strtolower($booking->booking_status) !== 'confirmed') {
                Log::info("SendTicketEmail: Booking {$this->bookingRef} not confirmed, skipping email");
                return;
            }

            if (!$payment || strtolower($payment->payment_status) !== 'completed') {
                Log::info("SendTicketEmail: Payment for booking {$this->bookingRef} not completed, skipping email");
                return;
            }

            // Count passengers with this email
            $passengerCount = DB::table('passenger')
                ->join('passenger_ticket', 'passenger.passenger_id', '=', 'passenger_ticket.passenger_id')
                ->where('passenger_ticket.booking_ref_no', $this->bookingRef)
                ->where('passenger.passenger_email', $this->recipientEmail)
                ->count();

            // Send the ticket email (TicketMailable will only include passengers with this email)
            Mail::to($this->recipientEmail)->send(new TicketMailable($this->bookingRef, $this->recipientEmail));

            Log::info("SendTicketEmail: Successfully sent ticket email for booking {$this->bookingRef} to {$this->recipientEmail} ({$passengerCount} passenger(s))");

        } catch (\Exception $e) {
            Log::error("SendTicketEmail failed for booking {$this->bookingRef}: " . $e->getMessage());
            throw $e; // Re-throw to trigger retry mechanism
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("SendTicketEmail permanently failed for booking {$this->bookingRef}: " . $exception->getMessage());
    }
}
