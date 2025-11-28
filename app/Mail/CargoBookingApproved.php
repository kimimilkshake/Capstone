<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CargoBookingApproved extends Mailable
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

    public function build()
    {
        return $this->subject('Cargo Booking Approved')
                    ->view('emails.cargo_booking_approved');
    }
}
