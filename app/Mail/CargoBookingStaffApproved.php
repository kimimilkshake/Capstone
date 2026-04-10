<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Throwable;

class CargoBookingStaffApproved extends Mailable
{
    use Queueable, SerializesModels;

    public $booking;
    public $sender;
    public $consignee;
    public $cargoItems;

    public function __construct($booking, $sender, $consignee, $cargoItems)
    {
        $this->booking = $booking;
        $this->sender = $sender;
        $this->consignee = $consignee;
        $this->cargoItems = $cargoItems;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Cargo Booking Approved - #' . ($this->booking->booking_ref_no ?? '') . ' - Awaiting Payment',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.cargo_booking_staff_approved',
            with: [
                'booking' => $this->booking,
                'sender' => $this->sender,
                'consignee' => $this->consignee,
                'cargoItems' => $this->cargoItems,
            ],
        );
    }

    public function attachments(): array
    {
        // No attachments for this email
        return [];
    }
}