<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Voyage;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
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
