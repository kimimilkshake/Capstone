<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Passenger;
use App\Models\Voyage;
use Carbon\Carbon;

class PassengerController extends Controller
{
    public function index(Request $request)
    {
        $routeFrom = $request->query('route_from');
        $routeTo = $request->query('route_to');
        $departureDate = $request->query('departure_date');
        $voyageId = $request->query('voyage_id');
        $type = $request->query('type'); // 👈 booking type from radio buttons

        // Get voyage information from voyage table with relationships
        $voyage = Voyage::with(['vessel.accommodations', 'routePort'])
            ->where('voyage_id', $voyageId)
            ->first();

        if (!$voyage) {
            return redirect()->route('bookingtype')->with('error', 'Voyage not found.');
        }

        $vesselName = $voyage->vessel->vessel_name ?? 'Unknown Vessel';
        $departureTime = $voyage->voyage_estimated_TD;
        $portOfOrigin = $voyage->routePort->port_origin_name ?? 'Unknown Port';
        $accommodations = $voyage->vessel->accommodations ?? collect();

        if ($departureTime) {
            $departureTime = Carbon::parse($departureTime)->format('g:i A');
        }

        // ✅ If user selected "cargo", load passenger.cargobooking
        // Otherwise load passenger.passengerbooking
        $view = $type === 'cargo'
            ? 'passenger.cargobooking'
            : 'passenger.passengerbooking';

        return view($view, compact(
            'routeFrom',
            'routeTo',
            'departureDate',
            'vesselName',
            'departureTime',
            'portOfOrigin',
            'voyage',
            'accommodations'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'passenger_firstname' => 'required|string|max:100',
            'passenger_midinitial' => 'nullable|string|max:5',
            'passenger_lastname' => 'required|string|max:100',
            'passenger_suffix' => 'nullable|string|max:10',
            'passenger_age' => 'required|integer|min:0',
            'passenger_gender' => 'required|string|in:M,F',
            'passenger_type' => 'required|string',
            'passenger_address' => 'required|string|max:255',
            'passenger_contactno' => 'required|string|max:20',
            'passenger_email' => 'required|email|max:255',
            'passenger_idnumber' => 'required|string|max:50',
        ]);

        Passenger::create($validated);

        return redirect()->route('bookingtype')->with('success', 'Passenger booked successfully!');
    }
}
