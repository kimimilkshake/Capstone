<?php

namespace App\Http\Controllers;

use App\Models\Vessel;
use App\Models\Voyage;
use App\Models\RoutePort;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Http\Controllers\Traits\AdminOrStaffGuard;

class VoyageController extends Controller
{
    use AdminOrStaffGuard;
    public function __construct()
    {
        $this->ensureAuthorized();
    }

    // AUTO UPDATE STATUS
    private function autoUpdateVoyageStatus()
    {
        $now = Carbon::now();

        Voyage::whereIn('voyage_status', ['Scheduled', 'At Sea'])
            ->get()
            ->each(function ($voyage) use ($now) {

                $departure = Carbon::parse(
                    $voyage->voyage_departure_date . ' ' . $voyage->voyage_estimated_TD
                );

                $arrival = Carbon::parse(
                    $voyage->voyage_arrival_date . ' ' . $voyage->voyage_estimated_TA
                );

                if ($now->greaterThanOrEqualTo($arrival)) {
                    $voyage->update(['voyage_status' => 'Completed']);
                }
                elseif ($now->greaterThanOrEqualTo($departure)) {
                    $voyage->update(['voyage_status' => 'At Sea']);
                }
            });
    }

    //CONFLICT CHECKER FOR VESSEL SCHEDULE
    private function hasConflict($vesselId, $departureDate, $departureTime, $arrivalDate, $arrivalTime, $ignoreVoyageId = null)
    {
        $newDeparture = Carbon::parse($departureDate . ' ' . $departureTime);
        $newArrival   = Carbon::parse($arrivalDate . ' ' . $arrivalTime);

        $existingVoyages = Voyage::where('vessel_id', $vesselId)
            ->whereNotIn('voyage_status', ['Cancelled', 'Archived'])
            ->when($ignoreVoyageId, function ($query) use ($ignoreVoyageId) {
                $query->where('voyage_id', '!=', $ignoreVoyageId);
            })
            ->get();

        foreach ($existingVoyages as $voyage) {

            $existingDeparture = Carbon::parse(
                $voyage->voyage_departure_date . ' ' . $voyage->voyage_estimated_TD
            );

            $existingArrival = Carbon::parse(
                $voyage->voyage_arrival_date . ' ' . $voyage->voyage_estimated_TA
            );

            if ($newDeparture < $existingArrival && $newArrival > $existingDeparture) {
                return true;
            }
        }

        return false;
    }

    //INDEX
    public function index(Request $request)
    {

        /*
        dd([
            'staff_guard' => auth()->guard('staff')->check(),
            'admin_guard' => auth()->guard('admin')->check(),
            'staff_user' => auth()->guard('staff')->user(),
            'admin_user' => auth()->guard('admin')->user(),
        ]);
        */

        $this->autoUpdateVoyageStatus();
        
        $search = $request->input('search');
        $start_date = $request->input('start_date');
        $end_date   = $request->input('end_date');

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
            ->when($start_date && $end_date, function ($query) use ($start_date, $end_date) {
                $query->whereBetween('voyage_departure_date', [$start_date, $end_date])
                    ->orWhereBetween('voyage_arrival_date', [$start_date, $end_date]);
            })
            ->orderBy('voyage_departure_date', 'desc')
            ->paginate(7);

        if (auth()->guard('admin')->check()) {
            return view('authorized.admin.voyage_list', compact('voyages','search'));
        }

        return view('authorized.staff.svoyage_list', compact('voyages','search'));
    }

    public function create()
    {
        $vessels = Vessel::where('vessel_status', 'Active')
                        ->orderBy('vessel_name')
                        ->get();
        $route_port = RoutePort::orderBy('route_origin')
                        ->orderBy('route_destination')
                        ->get();

        if (auth()->guard('admin')->check()) {
            return view('authorized.admin.create_voyage', compact('vessels', 'route_port'));
        }
        return view('authorized.staff.screate_voyage', compact('vessels', 'route_port'));
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

        $departure = Carbon::parse(
            $request->voyage_departure_date . ' ' . $request->voyage_estimated_TD
        );

        $arrival = Carbon::parse(
            $request->voyage_arrival_date . ' ' . $request->voyage_estimated_TA
        );

        if ($arrival->lessThanOrEqualTo($departure)) {
            return back()->withErrors([
                'time_error' => 'Arrival time must be AFTER the departure time.'
            ])->withInput();
        }

        if ($this->hasConflict(
            $request->vessel_id,
            $request->voyage_departure_date,
            $request->voyage_estimated_TD,
            $request->voyage_arrival_date,
            $request->voyage_estimated_TA
        )) {
            return back()->withErrors([
                'voyage_conflict' => 'This vessel already has a voyage scheduled during this time.'
            ])->withInput();
        }

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

        if (auth()->guard('admin')->check()) {
            return redirect()->route('admin.voyage_list')
                ->with('success','Voyage added successfully.');
        }
        return redirect()->route('staff.voyage_list')
            ->with('success','Voyage added successfully.');

    }

    public function edit($id)
    {
        $voyage = Voyage::findOrFail($id);

        $vessels = Vessel::where('vessel_status', 'Active')
                        ->orderBy('vessel_name')
                        ->get();
        $route_port = RoutePort::orderBy('route_origin')
                        ->orderBy('route_destination')
                        ->get();

        // Flag for blade to know if voyage is completed
        $isCompleted = $voyage->voyage_status === 'Completed';

        // Only allow editing if scheduled OR completed
        if (!$isCompleted && $voyage->voyage_status !== 'Scheduled') {
            return redirect()->route(auth()->guard('staff')->check() ? 'staff.voyage_list' : 'admin.voyage_list')
                             ->with('error', 'Only scheduled voyages can be edited.');
        }

        if (auth()->guard('admin')->check()) {
            return view('authorized.admin.voyage_edit', compact('voyage', 'vessels', 'route_port', 'isCompleted'));
        }
        return view('authorized.staff.svoyage_edit', compact('voyage', 'vessels', 'route_port', 'isCompleted'));
    }

    public function update(Request $request, $id)
    {
        $voyage = Voyage::findOrFail($id);

        // Completely locked statuses
        if (in_array($voyage->voyage_status, ['At Sea', 'Cancelled', 'Archived'])) {
            return redirect()->route(auth()->guard('staff')->check() ? 'staff.voyage_list' : 'admin.voyage_list')
                             ->with('error', 'This voyage can no longer be updated.');
        }

        // Validation rules
        $rules = [
            'voyage_description' => 'nullable|string',
        ];

        if ($voyage->voyage_status !== 'Completed') {
            // Full edit allowed only if NOT completed
            $rules = array_merge($rules, [
                'vessel_id' => 'required|exists:vessel,vessel_id',
                'route_port_id' => 'required|exists:route_port,route_port_id',
                'voyage_departure_date' => 'required|date',
                'voyage_arrival_date' => 'required|date|after_or_equal:voyage_departure_date',
                'voyage_estimated_TD' => 'required',
                'voyage_estimated_TA' => 'required',
                'voyage_status' => 'required|in:Scheduled,At Sea,Completed,Cancelled,Archived',
            ]);
        } else {
            // Completed → only actual times are editable
            $rules = array_merge($rules, [
                'voyage_actual_TD' => 'nullable',
                'voyage_actual_TA' => 'nullable',
                'voyage_status' => 'required|in:Completed,Cancelled',
            ]);
        }

        $request->validate($rules);

        $departure = Carbon::parse($request->voyage_departure_date . ' ' . $request->voyage_estimated_TD);
        $arrival   = Carbon::parse($request->voyage_arrival_date . ' ' . $request->voyage_estimated_TA);

        if ($arrival->lessThanOrEqualTo($departure)) {
            return back()->withErrors([
                'time_error' => 'Arrival date and time must be AFTER departure date and time.'
            ])->withInput();
        }

        if ($this->hasConflict(
            $request->vessel_id,
            $request->voyage_departure_date,
            $request->voyage_estimated_TD,
            $request->voyage_arrival_date,
            $request->voyage_estimated_TA,
            $voyage->voyage_id // ignore current voyage
        )) {
            return back()->withErrors([
                'voyage_conflict' => 'This vessel already has a voyage scheduled during this time.'
            ])->withInput();
        }

        // If vessel or route changed, update voyage code
        if ($voyage->voyage_status !== 'Completed') {
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
        }

        // Update fields based on status
        $updateData = [
            'voyage_description' => $request->voyage_description,
        ];

        if ($voyage->voyage_status === 'Completed') {
            // Only actual times editable
            $updateData['voyage_actual_TD'] = $request->voyage_actual_TD;
            $updateData['voyage_actual_TA'] = $request->voyage_actual_TA;
            $updateData['voyage_status'] = $request->voyage_status;
        } else {
            // Full edit
            $updateData = array_merge($updateData, [
                'vessel_id' => $request->vessel_id,
                'route_port_id' => $request->route_port_id,
                'voyage_departure_date' => $request->voyage_departure_date,
                'voyage_arrival_date' => $request->voyage_arrival_date,
                'voyage_estimated_TD' => $request->voyage_estimated_TD,
                'voyage_estimated_TA' => $request->voyage_estimated_TA,
                'voyage_status' => $request->voyage_status,
            ]);
        }

        $voyage->update($updateData);

        return redirect()->route(auth()->guard('staff')->check() ? 'staff.voyage_list' : 'admin.voyage_list')
                        ->with('success', 'Voyage updated successfully.');
    }


    public function destroy($id)
    {
        $voyage = Voyage::findOrFail($id);

        if ($voyage->voyage_status !== 'Scheduled') {
            return redirect()->route(auth()->guard('staff')->check() ? 'staff.voyage_list' : 'admin.voyage_list')
                             ->with('error', 'Only scheduled voyages can be deleted.');
        }

        $voyage->delete();

        return redirect()->route(auth()->guard('staff')->check() ? 'staff.voyage_list' : 'admin.voyage_list')
                        ->with('success', 'Voyage deleted.');
        
    }
}
