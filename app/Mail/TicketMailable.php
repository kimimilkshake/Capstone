<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TicketMailable extends Mailable
{
    use Queueable, SerializesModels;

    public $bookingRef;
    public $booking;
    public $payment;
    public $passengers;
    public $voyage;
    public $vessel;
    public $route;

    /**
     * Create a new message instance.
     */
    public function __construct($bookingRef)
    {
        $this->bookingRef = $bookingRef;
        $this->loadBookingData();
    }

    /**
     * Load all booking data
     */
    private function loadBookingData()
    {
        // Load booking
        $this->booking = DB::table('booking')->where('booking_ref_no', $this->bookingRef)->first();

        // Load payment
        $this->payment = DB::table('payment')->where('booking_ref_no', $this->bookingRef)->first();

        // Load passenger tickets with passenger and voyage data
        $tickets = DB::table('passenger_ticket')
            ->where('booking_ref_no', $this->bookingRef)
            ->get();

        $this->passengers = [];
        foreach ($tickets as $ticket) {
            $passenger = DB::table('passenger')->where('passenger_id', $ticket->passenger_id)->first();
            $this->passengers[] = [
                'ticket' => $ticket,
                'passenger' => $passenger,
            ];
        }

        // Load voyage details (from first ticket)
        if (!empty($tickets)) {
            $firstTicket = $tickets->first();
            $this->voyage = DB::table('voyage')->where('voyage_id', $firstTicket->voyage_id)->first();

            if ($this->voyage) {
                // Load vessel and route details
                $this->vessel = DB::table('vessel')->where('vessel_id', $this->voyage->vessel_id)->first();
                $this->route = DB::table('route_port')->where('route_port_id', $this->voyage->route_port_id)->first();
            }
        }
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $routeText = 'Unknown Route';
        if ($this->route) {
            $routeText = $this->route->route_origin . ' → ' . $this->route->route_destination;
        }

        return new Envelope(
            subject: 'Your Ferry Ticket - Booking #' . $this->bookingRef . ' (' . $routeText . ')',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.ticket',
            with: [
                'bookingRef' => $this->bookingRef,
                'booking' => $this->booking,
                'payment' => $this->payment,
                'passengers' => $this->passengers,
                'voyage' => $this->voyage,
                'vessel' => $this->vessel,
                'route' => $this->route,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
