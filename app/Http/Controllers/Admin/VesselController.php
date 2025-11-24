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
    public function index(Request $request)
    {
        $query = Vessel::with(['hatches', 'accommodations']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('vessel_name', 'like', "%{$search}%")
                  ->orWhere('vessel_code','like', "%{$search}%")
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

        $cotPlanPath = null;
        if ($request->hasFile('vessel_cot_plan_url')) {
            $cotPlanPath = $request->file('vessel_cot_plan_url')->store('cot_plans', 'public');
        }

        $vessel = Vessel::create([
            'admin_id' => $admin->admin_id,
            'vessel_code' => $request->vessel_code,
            'vessel_name' => $request->vessel_name,
            'vessel_total_passenger_capacity' => 0,
            'vessel_cot_plan_url' => $cotPlanPath,
        ]);

        if ($request->has('hatches')) {
            foreach ($request->hatches as $hatch) {
                if (!empty($hatch['label']) && isset($hatch['area_capacity']) && isset($hatch['weight_capacity'])) {
                    $vessel->hatches()->create([
                        'hatch_label' => $hatch['label'],
                        'hatch_area_capacity' => $hatch['area_capacity'],
                        'hatch_weight_capacity' => $hatch['weight_capacity'],
                    ]);
                }
            }
        }

        if ($request->has('accommodations')) {
            foreach ($request->accommodations as $accommodation) {
                if (!empty($accommodation['name']) && !empty($accommodation['price']) && !empty($accommodation['capacity'])) {
                    $vessel->accommodations()->create([
                        'accommodation_name' => $accommodation['name'],
                        'accommodation_regular_price' => $accommodation['price'],
                        'accommodation_capacity' => $accommodation['capacity'],

                    ]);
                }
            }
        }

        $totalCapacity = $vessel->accommodations()->sum('accommodation_capacity');
        $vessel->update(['vessel_total_passenger_capacity' => $totalCapacity]);

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

        $cotPlanPath = $vessel->vessel_cot_plan_url;
        if ($request->hasFile('vessel_cot_plan_url')) {
            $cotPlanPath = $request->file('vessel_cot_plan_url')->store('cot_plans', 'public');
        }

        $vessel->update([
            'vessel_code' => $request->vessel_code,
            'vessel_name' => $request->vessel_name,
            'vessel_status' => ucfirst($request->vessel_status),
            'vessel_cot_plan_url' => $cotPlanPath,
        ]);

        // Delete old hatches and recreate
        $vessel->hatches()->delete();
        if ($request->has('hatches')) {
            foreach ($request->hatches as $h) {
                if (!empty($h['label']) && isset($h['area_capacity']) && isset($h['weight_capacity'])) {
                    $vessel->hatches()->create([
                        'hatch_label' => $h['label'],
                        'hatch_area_capacity' => $h['area_capacity'],
                        'hatch_weight_capacity' => $h['weight_capacity'],
                    ]);
                }
            }
        }

        // Delete old accommodations and recreate
        $vessel->accommodations()->delete();
        if ($request->has('accommodations')) {
            foreach ($request->accommodations as $a) {
                if (!empty($a['name']) && !empty($a['price']) && !empty($a['capacity'])) {
                    $vessel->accommodations()->create([
                        'accommodation_name' => $a['name'],
                        'accommodation_regular_price' => $a['price'],
                        'accommodation_capacity' => $a['capacity']
                    ]);
                }
            }
        }

        $totalCapacity = $vessel->accommodations()->sum('accommodation_capacity');
        $vessel->update(['vessel_total_passenger_capacity' => $totalCapacity]);

        return redirect()->route('admin.vessel_list')->with('success', 'Vessel updated successfully.');
    }
}
