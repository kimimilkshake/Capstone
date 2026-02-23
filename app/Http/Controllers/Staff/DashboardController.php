<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Voyage;
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

        // Fetch voyages whose departure date is today
        $voyages = Voyage::with(['vessel', 'routePort'])
            ->whereDate('voyage_departure_date', $today)
            ->orderBy('voyage_departure_date', 'asc')
            ->get();

        return view('authorized.staff.dashboard', compact('voyages'));
    }
}
