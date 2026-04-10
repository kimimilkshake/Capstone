<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RoutePort;
use App\Models\Voyage;
use App\Models\PassengerTicket;
use App\Models\CargoReceipt;
use App\Models\Payment;
use Carbon\Carbon;
use App\Http\Controllers\Traits\AdminGuard;

class GenerateReportsController extends Controller
{
    use AdminGuard;

    public function __construct()
    {
        $this->ensureAdmin();
    }

    // Pass Request $request here
    public function index(Request $request)
    {
        $routes = RoutePort::orderBy('route_origin', 'asc')
            ->orderBy('route_destination', 'asc')
            ->get();

        // Filters
        $selectedRoute = $request->input('route') ?: null;
        $selectedBookingType = $request->input('booking_type') ?: null;
        $startDate = $request->input('start_date') ?: null;
        $endDate = $request->input('end_date') ?: null;

        // Flags for Blade
        $isAllRoutes = !$selectedRoute;
        $isPassenger = $selectedBookingType === 'passenger';
        $isCargo = $selectedBookingType === 'cargo';

        // Fetch voyages with relations
        $voyages = Voyage::with(['routePort', 'vessel'])
            ->when($startDate, fn($q) => $q->whereDate('voyage_departure_date', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('voyage_arrival_date', '<=', $endDate))
            ->when($selectedRoute, fn($q) => $q->where('route_port_id', $selectedRoute))
            ->paginate(5);

        // Optional: add counts / sums for passenger tickets & cargo revenue
        foreach ($voyages as $voyage) {
           
            // PAX/CAP
                $voyage->passenger_tickets_count = PassengerTicket::where('voyage_id', $voyage->id)->count();

            /*
            // Cargo Stats
            $voyage->cargo_SKS = CargoReceipt::where('voyage_id', $voyage->id)->sum('sks');
            $voyage->cargo_VAR = CargoReceipt::where('voyage_id', $voyage->id)->sum('var');
            $voyage->cargo_MC = CargoReceipt::where('voyage_id', $voyage->id)->sum('mc');
            

            // Revenue
            $voyage->passenger_revenue = PassengerTicket::where('voyage_id', $voyage->id)->sum('total_price');
            $voyage->cargo_revenue = CargoReceipt::where('voyage_id', $voyage->id)->sum('total_price');
            */
        }

        return view(
            'authorized.admin.generate_reports',
            compact(
                'routes',
                'selectedRoute',
                'selectedBookingType',
                'startDate',
                'endDate',
                'isAllRoutes',
                'isPassenger',
                'isCargo',
                'voyages'
            )
        );
    }
}