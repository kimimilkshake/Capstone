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
        $this->voyage = null;
        $this->vessel = null;
        $this->route = null;
        if (!empty($tickets)) {
            $firstTicket = $tickets->first();
            $this->voyage = DB::table('voyage')->where('voyage_id', $firstTicket->voyage_id)->first();

            if ($this->voyage) {
                $this->vessel = DB::table('vessel')->where('vessel_id', $this->voyage->vessel_id)->first();
                $this->route = \App\Models\RoutePort::with(['portOrigin', 'portDestination'])->find($this->voyage->route_port_id);

                // Resolve route rate and route category
                $rcRow = DB::table('voyage')
                    ->join('route_port', 'voyage.route_port_id', '=', 'route_port.route_port_id')
                    ->join('route_category', 'route_port.route_category_id', '=', 'route_category.route_category_id')
                    ->where('voyage.voyage_id', $this->voyage->voyage_id)
                    ->select('route_category.route_rate', 'route_port.route_category_id')
                    ->first();
                $routeRate = $rcRow ? (float) $rcRow->route_rate : 0;
                $routeCategoryId = $rcRow ? $rcRow->route_category_id : null;

                // Load all accommodations for the vessel
                $accommodations = DB::table('accommodation')
                    ->where('vessel_id', $this->voyage->vessel_id)
                    ->get();

                // Enrich each passenger item with accommodation + pricing data
                foreach ($this->allPassengers as &$item) {
                    $ticket = $item['ticket'];
                    $passenger = $item['passenger'];

                    // Find accommodation by cot range
                    $accommodation = null;
                    foreach ($accommodations as $accom) {
                        $ranges = array_map('trim', explode(',', $accom->accommodation_cot_range));
                        foreach ($ranges as $range) {
                            if (strpos($range, '-') !== false) {
                                [$start, $end] = explode('-', $range);
                                if ($ticket->pt_cot_no >= (int) trim($start) && $ticket->pt_cot_no <= (int) trim($end)) {
                                    $accommodation = $accom;
                                    break 2;
                                }
                            } elseif ((int) trim($range) === (int) $ticket->pt_cot_no) {
                                $accommodation = $accom;
                                break 2;
                            }
                        }
                    }

                    // Type discount rate
                    $passType = $passenger ? ($passenger->passenger_type ?? 'Regular') : 'Regular';
                    $typeDiscountRate = 0;
                    if ($routeCategoryId) {
                        $discount = DB::table('route_category_passenger_discounts')
                            ->where('route_category_id', $routeCategoryId)
                            ->where('passenger_type', $passType)
                            ->value('discount_rate');
                        $typeDiscountRate = $discount !== null ? (float) $discount : 0;
                    }

                    // Promo data
                    $promo = null;
                    if ($ticket->promo_id) {
                        $promo = DB::table('promo')->where('promo_id', $ticket->promo_id)->first();
                    }

                    $item['accommodation_name'] = $accommodation ? $accommodation->accommodation_name : null;
                    $item['accommodation_base_price'] = $accommodation ? (float) $accommodation->accommodation_regular_price : null;
                    $item['route_rate'] = $routeRate;
                    $item['type_discount_rate'] = $typeDiscountRate;
                    $item['promo'] = $promo;
                }
                unset($item);
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
