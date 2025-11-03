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
    /**
     * Display a listing of vessels.
     */
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
        return view('authorized.admin.create_vessel');
    }

    public function store(Request $request)
    {
        $request->validate([
            'vessel_code' => 'required|string|max:10',
            'vessel_name' => 'required|string|max:255',
            'vessel_total_passenger_capacity' => 'required|integer',
        ]);

        $admin = Auth::guard('admin')->user();
        if (!$admin) {
            return back()->withErrors(['auth' => 'You must be logged in as an admin to perform this action.']);
        }

        $cotPlanPath = null;
        if ($request->hasFile('vessel_cot_plan_url')) {
            $cotPlanPath = $request->file('vessel_cot_plan_url')->store('cot_plans', 'public');
        }

        $vessel = Vessel::create([
            'admin_id' => $admin->admin_id, // <-- Use the admin_id from the admin guard
            'vessel_code' => $request->vessel_code,
            'vessel_name' => $request->vessel_name,
            'vessel_total_passenger_capacity' => $request->vessel_total_passenger_capacity,
            'vessel_cot_plan_url' => $cotPlanPath,
        ]);

        if ($request->has('hatches')) {
            foreach ($request->hatches as $hatch) {
                if (!empty($hatch['label']) && !empty($hatch['capacity'])) {
                    Hatch::create([
                        'vessel_id' => $vessel->vessel_id,
                        'hatch_label' => $hatch['label'],
                        'hatch_capacity' => $hatch['capacity'],
                    ]);
                }
            }
        }

        if ($request->has('accommodations')) {
            foreach ($request->accommodations as $accommodation) {
                if (!empty($accommodation['name']) && !empty($accommodation['price'])) {
                    Accommodation::create([
                        'vessel_id' => $vessel->vessel_id,
                        'accommodation_name' => $accommodation['name'],
                        'accommodation_regular_price' => $accommodation['price'],
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

        // Validate basic fields
        $request->validate([
            'vessel_code' => 'required|string|max:10',
            'vessel_name' => 'required|string|max:255',
            'vessel_total_passenger_capacity' => 'required|integer',
            'vessel_status' => 'required|in:Active,Inactive',
        ]);

        // Handle cot plan upload
        $cotPlanPath = $vessel->vessel_cot_plan_url; // keep existing path
        if ($request->hasFile('vessel_cot_plan_url')) {
            $cotPlanPath = $request->file('vessel_cot_plan_url')->store('cot_plans', 'public');
        }

        // Update vessel fields
        $vessel->update([
            'vessel_code' => $request->vessel_code,
            'vessel_name' => $request->vessel_name,
            'vessel_total_passenger_capacity' => $request->vessel_total_passenger_capacity,
            'vessel_status' => ucfirst($request->vessel_status), // capitalize for consistency
            'vessel_cot_plan_url' => $cotPlanPath,
        ]);

        // Update hatches
        $vessel->hatches()->delete();
        if ($request->has('hatches')) {
            foreach ($request->hatches as $h) {
                if (!empty($h['label']) && !empty($h['capacity'])) {
                    $vessel->hatches()->create([
                        'hatch_label' => $h['label'],
                        'hatch_capacity' => $h['capacity'],
                    ]);
                }
            }
        }

        // Update accommodations
        $vessel->accommodations()->delete();
        if ($request->has('accommodations')) {
            foreach ($request->accommodations as $a) {
                if (!empty($a['name']) && !empty($a['price'])) {
                    $vessel->accommodations()->create([
                        'accommodation_name' => $a['name'],
                        'accommodation_regular_price' => $a['price'], // ✅ correct column name
                    ]);
                }
            }
        }

        return redirect()->route('admin.vessel_list')->with('success', 'Vessel updated successfully.');
    }




}
