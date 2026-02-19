<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Voyage;
use Carbon\Carbon;

class DashboardController extends Controller
{
    private function isStaff()
    {
        return auth()->guard('staff')->check();
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

        if (!$this->isStaff()) {
            abort(403);
        }
        
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
