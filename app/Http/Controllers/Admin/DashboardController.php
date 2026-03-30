<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Voyage;
use App\Models\PassengerTicket;
use App\Models\CargoReceipt;
use App\Models\Payment;
use Carbon\Carbon;
use App\Http\Controllers\Traits\AdminGuard;

class DashboardController extends Controller
{
    use AdminGuard;

    public function __construct()
    {
        $this->ensureAdmin();
    }

    public function index()
    {
        // Today's date
        $today = Carbon::today();

        $passengerBookings = PassengerTicket::whereDate('created_at', $today)
            ->whereIn('booking_ref_no', function($query) {
                $query->select('booking_ref_no')
                      ->from('booking')
                      ->where('booking_status', 'Confirmed');
            })
            ->distinct('booking_ref_no')
            ->count('booking_ref_no');

        $cargoBookings = CargoReceipt::whereDate('created_at', $today)
            ->whereHas('payment', function($q) {
                $q->where('payment_status', 'Completed');
            })
            ->count();

        $totalSales = Payment::whereDate('payment_date', $today)
            ->where('payment_status', 'Completed')
            ->sum('total_amount');

        $voyages = Voyage::with(['vessel', 'routePort'])
            ->withCount([
                'passengerTickets as passenger_tickets_count' => function ($query) {
                    $query->whereIn('booking_ref_no', function ($q) {
                        $q->select('booking_ref_no')
                          ->from('booking')
                          ->where('booking_status', 'Confirmed');
                    });
                }
            ])
            ->whereDate('voyage_departure_date', $today)
            ->orderBy('voyage_departure_date', 'asc')
            ->get();

        return view(
            'authorized.admin.dashboard',
            compact('voyages', 'passengerBookings', 'cargoBookings', 'totalSales')
        );
    }
}