<?php

namespace App\Http\Controllers;

use App\Models\RouteCategory;
use App\Models\Voyage;
use App\Models\Vessel;
use Carbon\Carbon;

class ScheduleController extends Controller
{
    public function index()
    {
        // Load route categories with route ports
        $routeCategories = RouteCategory::with(['routePorts'])->get();

        // For each route_port, build the weekly schedule pattern:
        // 1. Prefer upcoming scheduled voyages
        // 2. Fall back to the most recent past voyages (last 30 days) if none upcoming
        $allRoutePortIds = $routeCategories->flatMap(function ($cat) {
            return $cat->routePorts->pluck('route_port_id');
        });

        // Get upcoming voyages per route_port
        $upcomingVoyages = Voyage::whereIn('route_port_id', $allRoutePortIds)
            ->where('voyage_status', 'Scheduled')
            ->where('voyage_departure_date', '>=', Carbon::today())
            ->with('vessel')
            ->orderBy('voyage_departure_date')
            ->orderBy('voyage_estimated_TD')
            ->get()
            ->groupBy('route_port_id');

        // Get recent past voyages as fallback (last 30 days, not cancelled)
        $recentVoyages = Voyage::whereIn('route_port_id', $allRoutePortIds)
            ->whereIn('voyage_status', ['Completed', 'Scheduled', 'At Sea'])
            ->where('voyage_departure_date', '>=', Carbon::today()->subDays(30))
            ->where('voyage_departure_date', '<', Carbon::today())
            ->with('vessel')
            ->orderByDesc('voyage_departure_date')
            ->get()
            ->groupBy('route_port_id');

        // Build schedule lookup: use upcoming if available, otherwise fall back to recent
        $schedulesByRoutePort = [];
        foreach ($allRoutePortIds as $rpId) {
            $upcoming = $upcomingVoyages->get($rpId);
            if ($upcoming && $upcoming->isNotEmpty()) {
                $schedulesByRoutePort[$rpId] = $upcoming;
            } else {
                $schedulesByRoutePort[$rpId] = $recentVoyages->get($rpId) ?? collect();
            }
        }

        $vesselRouteMap = Voyage::whereIn('route_port_id', $allRoutePortIds)
            ->select('vessel_id', 'route_port_id')
            ->distinct()
            ->get();

        $vesselIds = $vesselRouteMap->pluck('vessel_id')->unique();
        $allVessels = Vessel::whereIn('vessel_id', $vesselIds)
            ->where('vessel_status', 'Active')
            ->with('accommodations')
            ->get()
            ->keyBy('vessel_id');

        // Build lookup: route_category_id -> collection of vessels
        $vesselsByCategory = [];
        foreach ($routeCategories as $category) {
            $rpIds = $category->routePorts->pluck('route_port_id');
            $vIds = $vesselRouteMap->whereIn('route_port_id', $rpIds)->pluck('vessel_id')->unique();
            $vesselsByCategory[$category->route_category_id] = $allVessels->only($vIds->toArray())->values();
        }

        // Get ALL active vessels with accommodations for the rates section
        $allActiveVessels = Vessel::where('vessel_status', 'Active')
            ->with('accommodations')
            ->get()
            ->filter(function ($v) {
                return $v->accommodations->isNotEmpty();
            });

        return view('passenger.schedules', compact('routeCategories', 'vesselsByCategory', 'schedulesByRoutePort', 'allActiveVessels'));
    }
}
