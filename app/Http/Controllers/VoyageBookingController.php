<?php

namespace App\Http\Controllers;

use App\Models\Voyage;
use Carbon\Carbon;
use Illuminate\Http\Request;

class VoyageBookingController extends Controller
{
    public function index()
    {
        // Get voyages - only future/upcoming voyages within next 7 days
        // Voyages are hidden 2 hours before departure time
        $voyages = Voyage::with(['vessel', 'routePort'])
            ->where('voyage_status', 'Scheduled')
            ->where(function ($query) {
                // Show voyages from tomorrow onwards
                $query->whereDate('voyage_departure_date', '>', today())
                    // OR show today's voyages that depart more than 2 hours from now
                    ->orWhere(function ($q) {
                    $q->whereDate('voyage_departure_date', '=', today())
                        ->where('voyage_estimated_TD', '>', now()->addHours(2)->format('H:i:s'));
                });
            })
            // Only show voyages within the next 7 days
            ->whereDate('voyage_departure_date', '<=', today()->addDays(7))
            ->orderBy('voyage_departure_date')
            ->orderBy('voyage_estimated_TD')
            ->get()
            ->map(function ($voyage) {
                return [
                    'voyage_id' => $voyage->voyage_id,
                    'route_from' => $voyage->routePort->route_origin ?? 'Unknown',
                    'route_to' => $voyage->routePort->route_destination ?? 'Unknown',
                    'departure_date' => $voyage->voyage_departure_date,
                    'departure_time' => $voyage->voyage_estimated_TD,
                    'arrival_date' => $voyage->voyage_arrival_date,
                    'arrival_time' => $voyage->voyage_estimated_TA,
                    'vessel_name' => $voyage->vessel->vessel_name ?? 'Unknown',
                    'port_of_origin' => $voyage->routePort->port_origin_name ?? 'Unknown',
                    'port_destination' => $voyage->routePort->port_destination_name ?? 'Unknown',
                    'voyage_code' => $voyage->voyage_code,
                    'voyage_description' => $voyage->voyage_description,
                ];
            });

        return view('passenger.bookingtype', compact('voyages'));
    }
}
