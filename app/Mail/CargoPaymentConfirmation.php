<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;
use App\Services\BillOfLadingPdf;
use Illuminate\Support\Facades\Log;
use Throwable;

class CargoPaymentConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public $booking;
    public $sender;
    public $consignee;
    public $cargoItems;
    public $payment;

    public function __construct($booking, $sender, $consignee, $cargoItems, $payment)
    {
        $this->booking = $booking;
        $this->sender = $sender;
        $this->consignee = $consignee;
        $this->cargoItems = $cargoItems;
        $this->payment = $payment;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Cargo Payment Confirmed - Freight Receipt for #' . ($this->booking->booking_ref_no ?? ''),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.cargo_payment_confirmation',
            with: [
                'booking' => $this->booking,
                'sender' => $this->sender,
                'consignee' => $this->consignee,
                'cargoItems' => $this->cargoItems,
                'payment' => $this->payment,
            ],
        );
    }

    public function attachments(): array
    {
        $attachments = [];
        
        try {
            Log::info('CargoPaymentConfirmation: Starting PDF generation for booking ' . ($this->booking->booking_ref_no ?? 'unknown'));
            
            $pdf = BillOfLadingPdf::generate($this->booking);

            if ($pdf === null) {
                Log::warning('CargoPaymentConfirmation: PDF generation returned null for booking ' . ($this->booking->booking_ref_no ?? 'unknown'));
                return [];
            }

            $filename = 'freight_receipt_' . ($this->booking->booking_ref_no ?? 'unknown') . '.pdf';
            
            Log::info('CargoPaymentConfirmation: PDF generated successfully, attaching to email');
            
            // Attach generated PDF of the Bill of Lading
            $attachments[] = Attachment::fromData(function () use ($pdf) {
                return $pdf;
            }, $filename)->withMime('application/pdf');
            
            return $attachments;
        } catch (Throwable $e) {
            Log::error('CargoPaymentConfirmation: PDF attachment failed', [
                'booking_ref' => $this->booking->booking_ref_no ?? 'unknown',
                'error' => $e->getMessage()
            ]);
            report($e);
            return [];
        }
    }
}
