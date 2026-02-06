<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Attachment;
use App\Services\BillOfLadingPdf;

class CargoBookingApproved extends Mailable
{
    use Queueable, SerializesModels;

    public $booking;
    public $sender;
    public $consignee;
    public $cargoItems;
    public $payment;

    public function __construct($booking, $sender, $consignee, $cargoItems, $payment = null)
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
            subject: 'Cargo Booking Approved - #' . ($this->booking->booking_ref_no ?? ''),
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
            ],
        );
    }

    public function attachments(): array
    {
        // attach generated PDF of the Bill of Lading
        return [
            Attachment::fromData(function () {
                return BillOfLadingPdf::generate($this->booking);
            }, 'bill_of_lading.pdf')->withMime('application/pdf')
        ];
    }
}
