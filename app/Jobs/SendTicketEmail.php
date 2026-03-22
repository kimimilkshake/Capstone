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
    public $passengerId; // Optional: for sending only a specific passenger's ticket

    /** Timeout in seconds — PDF generation can be slow */
    public $timeout = 180;

    /** Retry after 60 seconds on failure */
    public $backoff = 60;

    /**
     * Create a new job instance.
     */
    public function __construct($bookingRef, $passengerId = null, $recipientEmail = null)
    {
        $this->bookingRef = $bookingRef;
        $this->passengerId = $passengerId;
        $this->recipientEmail = $recipientEmail;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Verify booking is confirmed and paid first
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

            // If passengerId is specified, send only that passenger's ticket
            if ($this->passengerId) {
                $passenger = DB::table('passenger')
                    ->where('passenger_id', $this->passengerId)
                    ->select('passenger_email', 'passenger_firstname', 'passenger_lastname')
                    ->first();

                if (!$passenger || !$passenger->passenger_email) {
                    Log::warning("SendTicketEmail: Passenger {$this->passengerId} not found for booking {$this->bookingRef}");
                    return;
                }

                $this->recipientEmail = $passenger->passenger_email;

                // Send the individual passenger ticket email
                Mail::to($this->recipientEmail)->send(new \App\Mail\PassengerTicketConfirmed($this->bookingRef, $passenger->passenger_email));

                Log::info("SendTicketEmail: Successfully sent individual ticket email for booking {$this->bookingRef} to passenger {$passenger->passenger_firstname} {$passenger->passenger_lastname} ({$this->recipientEmail})");
            } else {
                // Original behavior: send all passengers' tickets to first passenger's email
                $firstPassenger = DB::table('passenger')
                    ->join('passenger_ticket', 'passenger.passenger_id', '=', 'passenger_ticket.passenger_id')
                    ->where('passenger_ticket.booking_ref_no', $this->bookingRef)
                    ->orderBy('passenger_ticket.passenger_ticket_id', 'asc')
                    ->select('passenger.passenger_email', 'passenger.passenger_firstname', 'passenger.passenger_lastname')
                    ->first();

                if (!$firstPassenger || !$firstPassenger->passenger_email) {
                    Log::warning("SendTicketEmail: No email found for booking {$this->bookingRef}");
                    return;
                }

                $this->recipientEmail = $firstPassenger->passenger_email;

                // Count total passengers in booking
                $passengerCount = DB::table('passenger_ticket')
                    ->where('booking_ref_no', $this->bookingRef)
                    ->count();

                // Send the ticket email with PassengerTicketConfirmed mailable (which includes multiple PDFs)
                Mail::to($this->recipientEmail)->send(new \App\Mail\PassengerTicketConfirmed($this->bookingRef));

                Log::info("SendTicketEmail: Successfully sent ticket email for booking {$this->bookingRef} to {$this->recipientEmail} with {$passengerCount} ticket(s)");
            }

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
