<?php

namespace App\Http\Controllers;

use App\Models\Voyage;
use Carbon\Carbon;
use Illuminate\Http\Request;

class VoyageBookingController extends Controller
{
    public function index()
    {
        // Get voyages within next 8 days with status Scheduled
        $startDate = Carbon::now()->startOfDay();
        $endDate = Carbon::now()->addDays(8)->endOfDay();

        $voyages = Voyage::with(['vessel', 'routePort'])
            ->where('voyage_status', 'Scheduled')
            ->whereBetween('voyage_departure_date', [$startDate, $endDate])
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
