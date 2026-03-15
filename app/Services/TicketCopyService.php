<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Jobs\SendTicketEmail;

class TicketCopyService
{
    /**
     * Request a ticket copy for a passenger
     *
     * Searches for matching tickets by:
     * 1. Starting from passenger_ticket table
     * 2. Matching voyage by date and route
     * 3. Getting passenger_id from passenger_ticket
     * 4. Finding passenger email
     * 5. Verifying booking is Confirmed and payment is Completed
     */
    public function requestTicketCopy($email, $departureDate, $routeFrom, $routeTo, $passengerId = null)
    {
        Log::info("TicketCopyService: Searching for email={$email}, date={$departureDate}, route={$routeFrom}->{$routeTo}, passengerId={$passengerId}");

        // Step 1: Find route_port_id
        $routePort = DB::table('route_port')
            ->where('route_origin', $routeFrom)
            ->where('route_destination', $routeTo)
            ->first();

        if (!$routePort) {
            Log::warning("TicketCopyService: Route not found for {$routeFrom}->{$routeTo}");
            return [
                'success' => false,
                'message' => 'Route not found. Please select a valid route.'
            ];
        }

        Log::info("TicketCopyService: Found route_port_id={$routePort->route_port_id}");

        // Step 2: Find voyages for this route and date
        $voyages = DB::table('voyage')
            ->where('route_port_id', $routePort->route_port_id)
            ->whereDate('voyage_departure_date', $departureDate)
            ->pluck('voyage_id')
            ->toArray();

        if (empty($voyages)) {
            Log::warning("TicketCopyService: No voyages found for route_port_id={$routePort->route_port_id}, date={$departureDate}");
            return [
                'success' => false,
                'message' => 'No voyages found for this route and date.'
            ];
        }

        Log::info("TicketCopyService: Found " . count($voyages) . " voyages for this route and date: " . implode(', ', $voyages));

        // Step 3: Start from passenger_ticket, find all passengers in these voyages with matching email
        // This is the critical query - starting from passenger_ticket as source of truth
        $matchingTickets = DB::table('passenger_ticket as pt')
            ->whereIn('pt.voyage_id', $voyages)
            ->join('passenger as p', 'pt.passenger_id', '=', 'p.passenger_id')
            ->where('p.passenger_email', $email)
            ->join('booking as b', 'pt.booking_ref_no', '=', 'b.booking_ref_no')
            ->join('payment as pay', 'pt.booking_ref_no', '=', 'pay.booking_ref_no')
            ->where('b.booking_status', 'Confirmed')
            ->where('pay.payment_status', 'Completed')
            ->select(
                'pt.passenger_ticket_id',
                'pt.passenger_id',
                'p.passenger_firstname',
                'p.passenger_lastname',
                'p.passenger_type',
                'p.passenger_email',
                'pt.booking_ref_no',
                'b.created_at as booking_date'
            )
            ->orderByDesc('b.created_at')
            ->get();

        Log::info("TicketCopyService: Found " . $matchingTickets->count() . " matching passenger tickets");

        if ($matchingTickets->isNotEmpty()) {
            foreach ($matchingTickets as $ticket) {
                Log::info("  - PT ID: {$ticket->passenger_ticket_id}, Passenger: {$ticket->passenger_firstname} {$ticket->passenger_lastname} ({$ticket->passenger_id}), Email: {$ticket->passenger_email}, Booking: {$ticket->booking_ref_no}");
            }
        }

        if ($matchingTickets->isEmpty()) {
            Log::warning("TicketCopyService: No matching tickets found");
            return [
                'success' => false,
                'message' => 'No confirmed booking with completed payment found.'
            ];
        }

        // Step 4: If passenger_id specified, send to that specific passenger only
        if ($passengerId) {
            $selectedTicket = $matchingTickets
                ->where('passenger_id', $passengerId)
                ->first();

            if (!$selectedTicket) {
                Log::warning("TicketCopyService: Selected passenger_id={$passengerId} not found in matching tickets");
                return [
                    'success' => false,
                    'message' => 'Selected passenger not found in this booking.'
                ];
            }

            Log::info("TicketCopyService: Dispatching email for specific passenger {$selectedTicket->passenger_firstname} {$selectedTicket->passenger_lastname}");
            SendTicketEmail::dispatch($selectedTicket->booking_ref_no, $selectedTicket->passenger_id);

            return [
                'success' => true,
                'message' => "Ticket copy for {$selectedTicket->passenger_firstname} {$selectedTicket->passenger_lastname} has been sent to {$selectedTicket->passenger_email}."
            ];
        }

        // Step 5: Return list of passengers for selection
        $passengers = $matchingTickets->map(function ($ticket) {
            return [
                'passenger_id' => $ticket->passenger_id,
                'name' => "{$ticket->passenger_firstname} {$ticket->passenger_lastname}",
                'type' => $ticket->passenger_type,
                'booking_ref_no' => $ticket->booking_ref_no
            ];
        })->unique('passenger_id')->values();

        // If only 1 passenger, send directly
        if ($passengers->count() === 1) {
            $selectedPassenger = $passengers->first();
            Log::info("TicketCopyService: Only one passenger found, sending directly");
            SendTicketEmail::dispatch($selectedPassenger['booking_ref_no'], $selectedPassenger['passenger_id']);

            return [
                'success' => true,
                'message' => "Ticket copy for {$selectedPassenger['name']} has been sent to {$email}.",
                'direct_send' => true
            ];
        }

        // Multiple passengers - return list for user to select
        Log::info("TicketCopyService: Multiple passengers found (" . $passengers->count() . "), returning list for selection");
        return [
            'success' => true,
            'message' => 'Multiple passengers found. Please select which passenger you are:',
            'passengers' => $passengers->toArray(),
            'pending_selection' => true
        ];
    }
}
