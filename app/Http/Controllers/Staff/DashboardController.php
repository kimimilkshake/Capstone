<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Voyage;
use App\Models\PassengerTicket;
use App\Models\CargoReceipt;
use App\Models\Payment;
use Carbon\Carbon;
use App\Http\Controllers\Traits\StaffGuard;

class DashboardController extends Controller
{
    use StaffGuard;

    public function __construct()
    {
        $this->ensureStaff();
    }

    public function index()
    {

        /*
        dd([
            'staff_guard' => auth()->guard('staff')->check(),
            'admin_guard' => auth()->guard('admin')->check(),
            'staff_user' => auth()->guard('staff')->user(),
            'admin_user' => auth()->guard('admin')->user(),
        ]);
        */
        
        // Get today's date
        $today = Carbon::today();

        // Passenger bookings confirmed today
        $passengerBookings = PassengerTicket::whereDate('created_at', $today)
            ->whereIn('booking_ref_no', function($query) {
                $query->select('booking_ref_no')
                      ->from('booking')
                      ->where('booking_status', 'Confirmed');
            })
            ->count();

        // Cargo receipts approved today (payment completed)
        $cargoBookings = CargoReceipt::whereDate('created_at', $today)
            ->whereHas('payment', function($q) {
                $q->where('payment_status', 'Completed');
            })
            ->count();

        // Total sales today (sum of completed payments)
        $totalSales = Payment::whereDate('payment_date', $today)
            ->where('payment_status', 'Completed')
            ->sum('total_amount');

        // Fetch voyages whose departure date is today
        $voyages = Voyage::with(['vessel', 'routePort'])
            ->whereDate('voyage_departure_date', $today)
            ->orderBy('voyage_departure_date', 'asc')
            ->get();

        return view('authorized.staff.dashboard', compact(
            'voyages',
            'passengerBookings',
            'cargoBookings',
            'totalSales'
        ));
    }
}
