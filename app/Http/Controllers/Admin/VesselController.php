<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Vessel;
use App\Models\Hatch;
use App\Models\Accommodation;

class VesselController extends Controller
{
    private function isAdmin()
    {
        return auth()->guard('admin')->check();
    }

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

        if (!$this->isAdmin()) {
            abort(403);
        }
        
        $query = Vessel::with(['hatches', 'accommodations']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('vessel_name', 'like', "%{$search}%")
                    ->orWhere('vessel_code', 'like', "%{$search}%")
                    ->orWhere('vessel_id', 'like', "%{$search}%");
            });
        }

        $vessels = $query->orderBy('vessel_id', 'asc')->paginate(10);
        $vessels->appends($request->all());

        return view('authorized.admin.vessel_list', compact('vessels'));
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
        
        return view('authorized.admin.create_vessel');
    }

    public function store(Request $request)
    {
        $request->validate([
            'vessel_code' => 'required|string|max:10',
            'vessel_name' => 'required|string|max:255',
        ]);

        $admin = Auth::guard('admin')->user();
        if (!$admin) {
            return back()->withErrors(['auth' => 'You must be logged in as an admin.']);
        }

        // Calculate total passenger capacity from accommodation cot ranges
        $totalPassengerCapacity = 0;
        if ($request->has('accommodations')) {
            foreach ($request->accommodations as $accommodation) {
                if (!empty($accommodation['cot_range'])) {
                    // Parse comma-separated cot ranges (e.g., "1-50, 60-70")
                    $ranges = array_map('trim', explode(',', $accommodation['cot_range']));

                    foreach ($ranges as $range) {
                        if (strpos($range, '-') !== false) {
                            $parts = explode('-', $range);
                            if (count($parts) === 2) {
                                $start = (int) trim($parts[0]);
                                $end = (int) trim($parts[1]);
                                // Count cots in this range (e.g., 1-50 = 50 cots, 60-70 = 11 cots)
                                $totalPassengerCapacity += ($end - $start + 1);
                            }
                        }
                    }
                }
            }
        }

        $cotPlanPath = null;
        if ($request->hasFile('vessel_cot_plan_url')) {
            $cotPlanPath = $request->file('vessel_cot_plan_url')->store('cot_plans', 'public');
        }

        $vessel = Vessel::create([
            'admin_id' => $admin->admin_id,
            'vessel_code' => $request->vessel_code,
            'vessel_name' => $request->vessel_name,
            'vessel_total_passenger_capacity' => $totalPassengerCapacity,
            'vessel_cot_plan_url' => $cotPlanPath,
        ]);


        if ($request->has('hatches')) {
            foreach ($request->hatches as $hatch) {
                if (
                    !empty($hatch['label']) &&
                    isset($hatch['length']) &&
                    isset($hatch['width']) &&
                    isset($hatch['height']) &&
                    isset($hatch['area_capacity'])
                ) {
                    $vessel->hatches()->create([
                        'hatch_label' => $hatch['label'],
                        'hatch_length' => $hatch['length'],
                        'hatch_width' => $hatch['width'],
                        'hatch_height' => $hatch['height'],
                        'hatch_area_capacity' => $hatch['area_capacity'],
                        'hatch_capacity_per_hold' => $hatch['capacity_per_hold'],

                        // optional field
                        'hatch_weight_capacity' =>
                            $hatch['weight_capacity'] !== null && $hatch['weight_capacity'] !== ""
                            ? $hatch['weight_capacity']
                            : null,
                    ]);
                }
            }
        }

        if ($request->has('accommodations')) {
            foreach ($request->accommodations as $accommodation) {
                if (
                    !empty($accommodation['name']) &&
                    !empty($accommodation['price']) &&
                    !empty($accommodation['cot_range']) // replace capacity
                ) {
                    $vessel->accommodations()->create([
                        'accommodation_name' => $accommodation['name'],
                        'accommodation_regular_price' => $accommodation['price'],
                        'accommodation_cot_range' => $accommodation['cot_range'], // new field
                    ]);
                }
            }
        }

        return redirect()->route('admin.vessel_list')
            ->with('success', 'Vessel created successfully!');
    }

    public function edit($id)
    {
        $vessel = Vessel::with(['hatches', 'accommodations'])->findOrFail($id);
        return view('authorized.admin.vessel_edit', compact('vessel'));
    }

    public function update(Request $request, $id)
    {
        $vessel = Vessel::findOrFail($id);

        $request->validate([
            'vessel_code' => 'required|string|max:10',
            'vessel_name' => 'required|string|max:255',
            'vessel_status' => 'required|in:Active,Inactive',
        ]);

        // Calculate total passenger capacity from accommodation cot ranges
        $totalPassengerCapacity = 0;
        if ($request->has('accommodations')) {
            foreach ($request->accommodations as $accommodation) {
                if (!empty($accommodation['cot_range'])) {
                    // Parse comma-separated cot ranges (e.g., "1-50, 60-70")
                    $ranges = array_map('trim', explode(',', $accommodation['cot_range']));

                    foreach ($ranges as $range) {
                        if (strpos($range, '-') !== false) {
                            $parts = explode('-', $range);
                            if (count($parts) === 2) {
                                $start = (int) trim($parts[0]);
                                $end = (int) trim($parts[1]);
                                // Count cots in this range (e.g., 1-50 = 50 cots, 60-70 = 11 cots)
                                $totalPassengerCapacity += ($end - $start + 1);
                            }
                        }
                    }
                }
            }
        }

        $cotPlanPath = $vessel->vessel_cot_plan_url;
        if ($request->hasFile('vessel_cot_plan_url')) {
            $cotPlanPath = $request->file('vessel_cot_plan_url')->store('cot_plans', 'public');
        }

        $vessel->update([
            'vessel_code' => $request->vessel_code,
            'vessel_name' => $request->vessel_name,
            'vessel_total_passenger_capacity' => $totalPassengerCapacity,
            'vessel_status' => ucfirst($request->vessel_status),
            'vessel_cot_plan_url' => $cotPlanPath,
        ]);

        $vessel->hatches()->delete();

        if ($request->has('hatches')) {
            foreach ($request->hatches as $h) {

                if (
                    !empty($h['label']) &&
                    isset($h['length']) &&
                    isset($h['width']) &&
                    isset($h['height']) &&
                    isset($h['area_capacity']) &&
                    isset($h['capacity_per_hold'])
                ) {

                    $vessel->hatches()->create([
                        'hatch_label' => $h['label'],
                        'hatch_length' => $h['length'],
                        'hatch_width' => $h['width'],
                        'hatch_height' => $h['height'],
                        'hatch_area_capacity' => $h['area_capacity'],
                        'hatch_capacity_per_hold' => $h['capacity_per_hold'],
                        'hatch_weight_capacity' => $h['weight_capacity'] ?? null,
                    ]);
                }
            }
        }

        $vessel->accommodations()->delete();

        if ($request->has('accommodations')) {
            foreach ($request->accommodations as $a) {
                if (!empty($a['name']) && !empty($a['price']) && !empty($a['cot_range'])) {
                    $vessel->accommodations()->create([
                        'accommodation_name' => $a['name'],
                        'accommodation_regular_price' => $a['price'],
                        'accommodation_cot_range' => $a['cot_range'],
                    ]);
                }
            }
        }

        return redirect()->route('admin.vessel_list')
            ->with('success', 'Vessel updated successfully.');
    }
}
