<?php

namespace App\Http\Controllers;

use App\Models\Vessel;
use App\Models\Voyage;
use App\Models\RoutePort;
use Illuminate\Http\Request;

class VoyageController extends Controller
{
    // Helper methods to check which guard is logged in
    private function isStaff()
    {
        return auth()->guard('staff')->check();
    }

    private function isAdmin()
    {
        return auth()->guard('admin')->check();
    }

    public function index(Request $request)
    {
        $search = $request->input('search');
        $date = $request->input('date');

        $voyages = Voyage::with(['vessel', 'routePort'])
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('voyage_code', 'like', "%{$search}%")
                      ->orWhere('voyage_status', 'like', "%{$search}%")
                      ->orWhereHas('vessel', fn($v) => $v->where('vessel_name', 'like', "%{$search}%"))
                      ->orWhereHas('routePort', fn($r) =>
                          $r->where('route_origin', 'like', "%{$search}%")
                            ->orWhere('route_destination', 'like', "%{$search}%")
                      );
                });
            })
            ->when($date, function ($query, $date) {
                $query->whereDate('voyage_departure_date', $date)
                      ->orWhereDate('voyage_arrival_date', $date);
            })
            ->orderBy('voyage_departure_date', 'desc')
            ->paginate(10);

        return $this->isStaff()
            ? view('authorized.staff.svoyage_list', compact('voyages', 'search'))
            : view('authorized.admin.voyage_list', compact('voyages', 'search'));
    }

    public function create()
    {
        /*
        dd([
            'staff_guard' => auth()->guard('staff')->check(),
            'admin_guard' => auth()->guard('admin')->check(),
            'staff_user' => auth()->guard('staff')->user(),
            'admin_user' => auth()->guard('admin')->user(),
        ]);
        */

        $vessels = Vessel::where('vessel_status', 'Active')->get();
        $route_port = RoutePort::all();

        return $this->isStaff()
            ? view('authorized.staff.screate_voyage', compact('vessels', 'route_port'))
            : view('authorized.admin.create_voyage', compact('vessels', 'route_port'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'vessel_id' => 'required|exists:vessel,vessel_id',
            'route_port_id' => 'required|exists:route_port,route_port_id',
            'voyage_departure_date' => 'required|date',
            'voyage_arrival_date' => 'required|date',
            'voyage_estimated_TD' => 'required',
            'voyage_estimated_TA' => 'required',
        ]);

        $vessel = Vessel::findOrFail($request->vessel_id);
        $route_port = RoutePort::findOrFail($request->route_port_id);

        $origin = strtoupper(substr($route_port->route_origin, 0, 3));
        $destination = strtoupper(substr($route_port->route_destination, 0, 3));
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
            'route_port_id' => $request->route_port_id,
            'voyage_departure_date' => $request->voyage_departure_date,
            'voyage_arrival_date' => $request->voyage_arrival_date,
            'voyage_estimated_TD' => $request->voyage_estimated_TD,
            'voyage_estimated_TA' => $request->voyage_estimated_TA,
            'voyage_status' => 'Scheduled',
            'voyage_code' => $voyageCode,
        ]);

        return redirect()->route($this->isStaff() ? 'staff.voyage_list' : 'admin.voyage_list')
                 ->with('success', 'Voyage added successfully.');

    }

    public function edit($id)
    {
        $voyage = Voyage::findOrFail($id);
        $vessels = Vessel::where('vessel_status', 'Active')->get();
        $route_port = RoutePort::all();

        return $this->isStaff()
            ? view('authorized.staff.svoyage_edit', compact('voyage', 'vessels', 'route_port'))
            : view('authorized.admin.voyage_edit', compact('voyage', 'vessels', 'route_port'));
    }

    public function update(Request $request, $id)
    {
        $voyage = Voyage::findOrFail($id);

        $request->validate([
            'vessel_id' => 'required|exists:vessel,vessel_id',
            'route_port_id' => 'required|exists:route_port,route_port_id',
            'voyage_departure_date' => 'required|date',
            'voyage_arrival_date' => 'required|date|after_or_equal:voyage_departure_date',
            'voyage_estimated_TD' => 'required',
            'voyage_estimated_TA' => 'required',
            'voyage_status' => 'required|in:Scheduled,At Sea,Completed,Cancelled,Archived',
        ]);

        $vesselChanged = $voyage->vessel_id != $request->vessel_id;
        $routeChanged = $voyage->route_port_id != $request->route_port_id;

        if ($vesselChanged || $routeChanged) {
            $vessel = Vessel::findOrFail($request->vessel_id);
            $route_port = RoutePort::findOrFail($request->route_port_id);

            $origin = strtoupper(substr($route_port->route_origin, 0, 3));
            $destination = strtoupper(substr($route_port->route_destination, 0, 3));
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
            'route_port_id' => $request->route_port_id,
            'voyage_departure_date' => $request->voyage_departure_date,
            'voyage_arrival_date' => $request->voyage_arrival_date,
            'voyage_estimated_TD' => $request->voyage_estimated_TD,
            'voyage_estimated_TA' => $request->voyage_estimated_TA,
            'voyage_actual_TD' => $request->voyage_actual_TD,
            'voyage_actual_TA' => $request->voyage_actual_TA,
            'voyage_description' => $request->voyage_description,
            'voyage_status' => $request->voyage_status,
        ]);

        return redirect()->route($this->isStaff() ? 'staff.voyage_list' : 'admin.voyage_list')
                         ->with('success', 'Voyage updated successfully.');
    }

    public function destroy($id)
    {
        Voyage::destroy($id);
        return redirect()->route($this->isStaff() ? 'staff.voyage_list' : 'admin.voyage_list')
                         ->with('success', 'Voyage deleted.');
    }
}
