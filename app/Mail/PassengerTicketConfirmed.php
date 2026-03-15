<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Attachment;
use App\Services\PassengerTicketPdf;
use Throwable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PassengerTicketConfirmed extends Mailable
{
    use Queueable, SerializesModels;

    public $bookingRef;
    public $booking;
    public $allPassengers;
    public $payment;
    public $voyage;
    public $vessel;
    public $route;
    public $filterPassengerEmail; // Optional: filter to only one passenger

    public function __construct($bookingRef, $filterPassengerEmail = null)
    {
        $this->bookingRef = $bookingRef;
        $this->filterPassengerEmail = $filterPassengerEmail;
        $this->loadBookingData();
    }

    /**
     * Load all booking data using raw DB queries (reliable approach)
     */
    private function loadBookingData()
    {
        // Load booking
        $this->booking = DB::table('booking')->where('booking_ref_no', $this->bookingRef)->first();

        // Load payment
        $this->payment = DB::table('payment')->where('booking_ref_no', $this->bookingRef)->first();

        // Load ALL passenger tickets with passenger data
        $tickets = DB::table('passenger_ticket')
            ->where('booking_ref_no', $this->bookingRef)
            ->orderBy('passenger_ticket_id', 'asc')
            ->get();

        $this->allPassengers = [];
        foreach ($tickets as $ticket) {
            $passenger = DB::table('passenger')->where('passenger_id', $ticket->passenger_id)->first();

            if ($passenger) {
                // If filterPassengerEmail is specified, only include that passenger
                if ($this->filterPassengerEmail && $passenger->passenger_email !== $this->filterPassengerEmail) {
                    continue;
                }

                $this->allPassengers[] = [
                    'ticket' => $ticket,
                    'passenger' => $passenger,
                ];
            }
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


    public function envelope(): Envelope
    {
        $routeText = 'Unknown Route';
        if ($this->route) {
            $routeText = $this->route->route_origin . ' → ' . $this->route->route_destination;
        }

        return new Envelope(
            subject: 'Your Passenger Ticket Confirmed - Booking #' . $this->bookingRef . ' (' . $routeText . ')',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.passenger_ticket_confirmed',
            with: [
                'bookingRef' => $this->bookingRef,
                'booking' => $this->booking,
                'allPassengers' => $this->allPassengers,
                'payment' => $this->payment,
                'voyage' => $this->voyage,
                'vessel' => $this->vessel,
                'route' => $this->route,
            ],
        );
    }

    public function attachments(): array
    {
        $attachments = [];

        try {
            // Generate one PDF per passenger (or just the filtered one if specified)
            foreach ($this->allPassengers as $passengerData) {
                $passenger = $passengerData['passenger'];
                $passengerEmail = $passenger->passenger_email;
                $passengerFirstName = $passenger->passenger_firstname;

                // Generate individual PDF for this passenger
                $pdf = PassengerTicketPdf::generate($this->bookingRef, null, null, $passengerEmail);

                if ($pdf !== null) {
                    // Create filename: ticket_FirstName_LastName.pdf
                    $filename = 'ticket_' . str_replace(' ', '_', $passengerFirstName) . '_' . str_replace(' ', '_', $passenger->passenger_lastname) . '.pdf';

                    $attachments[] = Attachment::fromData(function () use ($pdf) {
                        return $pdf;
                    }, $filename)->withMime('application/pdf');

                    if ($this->filterPassengerEmail) {
                        Log::info("PassengerTicketConfirmed: Individual PDF generated for {$passengerFirstName} {$passenger->passenger_lastname} (requested copy)");
                    } else {
                        Log::info("PassengerTicketConfirmed: PDF generated for {$passengerFirstName} {$passenger->passenger_lastname}");
                    }
                } else {
                    Log::warning("PassengerTicketConfirmed: Failed to generate PDF for passenger {$passengerEmail}");
                }
            }

            if (empty($attachments)) {
                Log::warning("PassengerTicketConfirmed: No PDFs generated for booking {$this->bookingRef}");
            }

            return $attachments;

        } catch (Throwable $e) {
            Log::error("PassengerTicketConfirmed: Error generating PDFs for booking {$this->bookingRef}: " . $e->getMessage());
            report($e);
            return [];
        }
    }
}
