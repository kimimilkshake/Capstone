<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Voyage;
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

        return view('authorized.admin.dashboard', compact('voyages'));
    }
}
