<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Passenger;
use Carbon\Carbon;

class PassengerController extends Controller
{
    public function index(Request $request)
    {
        $routeFrom = $request->query('route_from');
        $routeTo = $request->query('route_to');
        $departureDate = $request->query('departure_date');
        $type = $request->query('type'); // 👈 booking type from radio buttons

        $route = \DB::table('vessel_routes')
            ->where('route_from', $routeFrom)
            ->where('route_to', $routeTo)
            ->first();

        $vesselName = $route->vessel_name ?? null;
        $departureTime = $route->departure_time ?? null;
        $portOfOrigin = $route->port_of_origin ?? null;

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
            'portOfOrigin'
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
