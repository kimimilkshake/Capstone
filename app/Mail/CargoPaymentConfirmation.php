<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;
use App\Services\FreightReceiptPdf;
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
    public $pdf;

    public function __construct($booking, $sender, $consignee, $cargoItems, $payment)
    {
        $this->booking = $booking;
        $this->sender = $sender;
        $this->consignee = $consignee;
        $this->cargoItems = $cargoItems;
        $this->payment = $payment;

        // Generate PDF in constructor
        try {
            Log::info('CargoPaymentConfirmation: Generating PDF for booking ' . ($booking->booking_ref_no ?? 'unknown'));
            $this->pdf = FreightReceiptPdf::generate($booking);
            if ($this->pdf) {
                Log::info('CargoPaymentConfirmation: PDF generated successfully');
            } else {
                Log::warning('CargoPaymentConfirmation: PDF generation returned null');
            }
        } catch (Throwable $e) {
            Log::error('CargoPaymentConfirmation: PDF generation failed in constructor', [
                'booking_ref' => $booking->booking_ref_no ?? 'unknown',
                'error' => $e->getMessage()
            ]);
            $this->pdf = null;
        }
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
                'pdfAttached' => $this->pdf !== null,
            ],
        );
    }

    public function attachments(): array
    {
        $attachments = [];
        
        if ($this->pdf) {
            $filename = 'freight_receipt_' . ($this->booking->booking_ref_no ?? 'unknown') . '.pdf';
            
            Log::info('CargoPaymentConfirmation: Attaching PDF to email');
            
            // Attach generated PDF of the Freight Receipt
            $attachments[] = Attachment::fromData(function () {
                return $this->pdf;
            }, $filename)->withMime('application/pdf');
        } else {
            Log::warning('CargoPaymentConfirmation: No PDF to attach');
        }
        
        return $attachments;
    }
}
