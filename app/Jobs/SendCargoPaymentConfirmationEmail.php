<?php

namespace App\Jobs;

use App\Mail\CargoPaymentConfirmation;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Sender;
use App\Models\Consignee;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendCargoPaymentConfirmationEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $bookingRef;
    public $qrCodeData;

    /**
     * The number of times the job may be attempted.
     */
    public $tries = 3;

    /**
     * The maximum number of seconds the job can run.
     */
    public $timeout = 120;

    /**
     * Create a new job instance.
     */
    public function __construct($bookingRef, $qrCodeData = null)
    {
        $this->bookingRef = $bookingRef;
        $this->qrCodeData = $qrCodeData;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info("SendCargoPaymentConfirmationEmail: Processing booking {$this->bookingRef}");

        try {
            // Fetch booking with related data using DB for consistency
            $booking = Booking::findByBookingRef($this->bookingRef);
            
            if (!$booking) {
                // Try alternative approach with query builder
                $bookingData = DB::table('booking')
                    ->where('booking_ref_no', $this->bookingRef)
                    ->first();
                    
                if (!$bookingData) {
                    Log::warning("SendCargoPaymentConfirmationEmail: Booking {$this->bookingRef} not found");
                    return;
                }
                
                // Get full booking with relationships
                $booking = Booking::with([
                    'sender',
                    'consignee',
                    'voyage.routePort',
                    'voyage.vessel',
                    'cargoBookings.cargoItem',
                    'cargoBookings.cargoClassification',
                    'cargoBookings.measurementUnit',
                    'cargoBookings.approvedByStaff'
                ])->where('booking_ref_no', $this->bookingRef)->first();
            }

            if (!$booking) {
                Log::warning("SendCargoPaymentConfirmationEmail: Booking {$this->bookingRef} not found");
                return;
            }

            // Check if booking is cargo type
            $bookingType = strtolower($booking->booking_type ?? '');
            if ($bookingType !== 'cargo') {
                Log::info("SendCargoPaymentConfirmationEmail: Booking {$this->bookingRef} is not a cargo booking (type: {$booking->booking_type}), skipping");
                return;
            }

            // Get payment record
            $payment = Payment::where('booking_ref_no', $this->bookingRef)->first();

            if (!$payment) {
                Log::warning("SendCargoPaymentConfirmationEmail: Payment for booking {$this->bookingRef} not found");
                return;
            }

            // Check payment status
            $paymentStatus = strtolower($payment->payment_status ?? '');
            if ($paymentStatus !== 'initial') {
                Log::info("SendCargoPaymentConfirmationEmail: Payment for booking {$this->bookingRef} not initial (status: {$payment->payment_status}), skipping email");
                return;
            }

            // Get sender - check both relationship and direct query
            $sender = $booking->sender;
            if (!$sender) {
                $senderData = DB::table('sender')->where('sender_id', $booking->sender_id)->first();
                if ($senderData) {
                    $sender = Sender::find($senderData->sender_id);
                }
            }
            
            // Get consignee
            $consignee = $booking->consignee;
            if (!$consignee) {
                $consigneeData = DB::table('consignee')->where('consignee_id', $booking->consignee_id)->first();
                if ($consigneeData) {
                    $consignee = Consignee::find($consigneeData->consignee_id);
                }
            }

            if (!$sender || !$sender->sender_email) {
                Log::warning("SendCargoPaymentConfirmationEmail: Sender email not found for booking {$this->bookingRef}");
                return;
            }

            // Ensure cargo bookings are loaded
            $cargoBookings = $booking->cargoBookings;
            if ($cargoBookings->isEmpty()) {
                Log::warning("SendCargoPaymentConfirmationEmail: No cargo bookings found for booking {$this->bookingRef}");
                return;
            }

            // Send the confirmation email with Freight Receipt PDF
            try {
                $email = new CargoPaymentConfirmation(
                    $booking,
                    $sender,
                    $consignee,
                    $cargoBookings,
                    $payment
                );
                
                Mail::to($sender->sender_email)->send($email);

                Log::info("SendCargoPaymentConfirmationEmail: Successfully sent payment confirmation email for booking {$this->bookingRef} to {$sender->sender_email}");
            } catch (\Exception $e) {
                Log::error("SendCargoPaymentConfirmationEmail: Failed to send email for booking {$this->bookingRef}: " . $e->getMessage());
                throw $e;
            }
        } catch (\Exception $e) {
            Log::error("SendCargoPaymentConfirmationEmail: Exception processing booking {$this->bookingRef}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("SendCargoPaymentConfirmationEmail permanently failed for booking {$this->bookingRef}: " . $exception->getMessage());
    }
}
