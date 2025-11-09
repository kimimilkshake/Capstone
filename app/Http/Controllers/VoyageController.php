<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Voyage;
use App\Models\Route;
use App\Models\Port;
use App\Models\Vessel;

class VoyageController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $voyages = Voyage::with(['vessel', 'route', 'port'])
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->Where('voyage_code', 'like', "%{$search}%")
                        ->orWhere('voyage_departure_date', 'like', "%{$search}%")
                        ->orWhere('voyage_arrival_date', 'like', "%{$search}%")
                        ->orWhere('voyage_status', 'like', "%{$search}%");
                })
                ->orWhereHas('vessel', function ($q) use ($search) {
                    $q->where('vessel_name', 'like', "%{$search}%");
                })
                ->orWhereHas('route', function ($q) use ($search) {
                    $q->where('route_origin', 'like', "%{$search}%")
                        ->orWhere('route_destination', 'like', "%{$search}%");
                });
            })
            ->orderBy('voyage_departure_date', 'desc')
            ->paginate(10);

        return view('authorized.admin.voyage_list', compact('voyages', 'search'));
    }

    public function create()
    {
        $vessels = Vessel::where('vessel_status', 'Active')->get();
        $routes = Route::all();
        $ports = Port::all();
        return view('authorized.admin.create_voyage', compact('vessels', 'routes', 'ports'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'vessel_id' => 'required|exists:vessel,vessel_id',
            'route_id' => 'required|exists:route,route_id',
            'port_id' => 'required|exists:port,port_id',
            'voyage_departure_date' => 'required|date',
            'voyage_arrival_date' => 'required|date',
            'voyage_estimated_TD' => 'required',
            'voyage_estimated_TA' => 'required',
        ]);

        $vessel = Vessel::findOrFail($request->vessel_id);
        $route = Route::findOrFail($request->route_id);

        $origin = strtoupper(substr($route->route_origin, 0, 3));
        $destination = strtoupper(substr($route->route_destination, 0, 3));
        $year = date('Y', strtotime($request->voyage_departure_date));
        $month = date('m', strtotime($request->voyage_departure_date));

        $baseCode = "{$vessel->vessel_code}{$origin}{$destination}{$year}{$month}";

        $latestVoyage = Voyage::where('voyage_code', 'like', "{$baseCode}-%")
            ->orderBy('voyage_code', 'desc')
            ->first();

        $nextCounter = $latestVoyage
            ? str_pad(intval(substr($latestVoyage->voyage_code, -3)) + 1, 3, '0', STR_PAD_LEFT)
            : '001';

        $voyageCode = "{$baseCode}-{$nextCounter}";

        Voyage::create([
            'vessel_id' => $request->vessel_id,
            'route_id' => $request->route_id,
            'port_id' => $request->port_id,
            'voyage_departure_date' => $request->voyage_departure_date,
            'voyage_arrival_date' => $request->voyage_arrival_date,
            'voyage_estimated_TD' => $request->voyage_estimated_TD,
            'voyage_estimated_TA' => $request->voyage_estimated_TA,
            'voyage_status' => 'Scheduled',
            'voyage_code' => $voyageCode,
        ]);

        return redirect()->route('voyages.index')->with('success', 'Voyage added successfully.');
    }

    public function edit($id)
    {
        $voyage = Voyage::findOrFail($id);
        $vessels = Vessel::where('vessel_status', 'Active')->get();
        $routes = Route::all();
        $ports = Port::all();
        return view('authorized.admin.voyage_edit', compact('voyage', 'vessels', 'routes', 'ports'));
    }

    public function update(Request $request, $id)
    {
        $voyage = Voyage::findOrFail($id);

        $request->validate([
            'vessel_id' => 'required|exists:vessel,vessel_id',
            'route_id' => 'required|exists:route,route_id',
            'port_id' => 'required|exists:port,port_id',
            'voyage_departure_date' => 'required|date',
            'voyage_arrival_date' => 'required|date|after_or_equal:voyage_departure_date',
            'voyage_estimated_TD' => 'required',
            'voyage_estimated_TA' => 'required',
            'voyage_status' => 'required|in:Scheduled,At Sea,Completed,Cancelled,Archived',
        ]);

        $vesselChanged = $voyage->vessel_id != $request->vessel_id;
        $routeChanged = $voyage->route_id != $request->route_id;

        if ($vesselChanged || $routeChanged) {
            $vessel = Vessel::findOrFail($request->vessel_id);
            $route = Route::findOrFail($request->route_id);

            $origin = strtoupper(substr($route->route_origin, 0, 3));
            $destination = strtoupper(substr($route->route_destination, 0, 3));
            $year = date('Y', strtotime($request->voyage_departure_date));
            $month = date('m', strtotime($request->voyage_departure_date));

            $baseCode = "{$vessel->vessel_code}{$origin}{$destination}{$year}{$month}";

            $latestVoyage = Voyage::where('voyage_code', 'like', "{$baseCode}-%")
                ->orderBy('voyage_code', 'desc')
                ->first();

            $nextCounter = $latestVoyage
                ? str_pad(intval(substr($latestVoyage->voyage_code, -3)) + 1, 3, '0', STR_PAD_LEFT)
                : '001';

            $voyage->voyage_code = "{$baseCode}-{$nextCounter}";
        }

        $voyage->update([
            'vessel_id' => $request->vessel_id,
            'route_id' => $request->route_id,
            'port_id' => $request->port_id,
            'voyage_departure_date' => $request->voyage_departure_date,
            'voyage_arrival_date' => $request->voyage_arrival_date,
            'voyage_estimated_TD' => $request->voyage_estimated_TD,
            'voyage_estimated_TA' => $request->voyage_estimated_TA,
            'voyage_actual_TD' => $request->voyage_actual_TD,
            'voyage_actual_TA' => $request->voyage_actual_TA,
            'voyage_description' => $request->voyage_description,
            'voyage_status' => $request->voyage_status,
        ]);

        return redirect()->route('voyages.index')->with('success', 'Voyage updated successfully.');
    }

    public function destroy($id)
    {
        Voyage::destroy($id);
        return redirect()->route('voyages.index')->with('success', 'Voyage deleted.');
    }

   

}
