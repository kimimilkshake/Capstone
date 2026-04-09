<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Throwable;

class CargoBookingApproved extends Mailable
{
    use Queueable, SerializesModels;

    public $booking;
    public $sender;
    public $consignee;
    public $cargoItems;
    public $payment;
    public $paymentUrl;

    public function __construct($booking, $sender, $consignee, $cargoItems, $payment = null, $paymentUrl = null)
    {
        $this->booking = $booking;
        $this->sender = $sender;
        $this->consignee = $consignee;
        $this->cargoItems = $cargoItems;
        $this->payment = $payment;
        $this->paymentUrl = $paymentUrl;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Cargo Booking Approved - #' . ($this->booking->booking_ref_no ?? '') . ' - Please Complete Payment',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.cargo_booking_approved',
            with: [
                'booking' => $this->booking,
                'sender' => $this->sender,
                'consignee' => $this->consignee,
                'cargoItems' => $this->cargoItems,
                'payment' => $this->payment,
                'paymentUrl' => $this->paymentUrl,
            ],
        );
    }

    public function attachments(): array
    {
        // No attachments for this email - just payment link
        return [];
    }
}
