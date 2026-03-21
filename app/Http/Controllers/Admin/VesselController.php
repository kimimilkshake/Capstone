<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Vessel;
use App\Models\Hatch;
use App\Models\Accommodation;
use App\Http\Controllers\Traits\AdminGuard;
class VesselController extends Controller
{
    use AdminGuard;
    public function __construct()
    {
        $this->ensureAdmin();
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

        $query = Vessel::with(['hatches', 'accommodations']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('vessel_name', 'like', "%{$search}%")
                    ->orWhere('vessel_code', 'like', "%{$search}%")
                    ->orWhere('vessel_id', 'like', "%{$search}%");
            });
        }

        $vessels = $query->orderBy('vessel_code', 'asc')->paginate(10);
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

        $vessel = Vessel::create([
            'admin_id' => $admin->admin_id,
            'vessel_code' => $request->vessel_code,
            'vessel_name' => $request->vessel_name,
            'vessel_total_passenger_capacity' => $totalPassengerCapacity,
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
            foreach ($request->accommodations as $key => $accommodation) {
                if (
                    !empty($accommodation['name']) &&
                    !empty($accommodation['price']) &&
                    !empty($accommodation['cot_range']) // replace capacity
                ) {
                    $cotPlanPath = null;
                    if ($request->hasFile("accommodations.{$key}.cot_plan")) {
                        $file = $request->file("accommodations.{$key}.cot_plan");
                        $cotPlanPath = $file->store('cot_plans', 'public');
                    }

                    $vessel->accommodations()->create([
                        'accommodation_name' => $accommodation['name'],
                        'accommodation_regular_price' => $accommodation['price'],
                        'accommodation_cot_range' => $accommodation['cot_range'],
                        'accommodation_cot_plan_url' => $cotPlanPath,
                    ]);
                }
            }
        }

        $this->generateCotPlanJson($vessel);

        return redirect()->route('admin.vessel_list')
            ->with('success', 'Vessel created successfully!');
    }

    private function generateCotPlanJson(Vessel $vessel): void
    {
        $vessel->loadMissing('accommodations');

        $indexPath = storage_path('cot_plan/cot_plan_index.json');
        $fileName = 'vessel_' . $vessel->vessel_id . '.json';
        $filePath = storage_path('cot_plan/' . $fileName);

        // Load existing JSON to preserve manually-edited bunk assignments
        $existingAccommodations = [];
        if (file_exists($filePath)) {
            $existing = json_decode(file_get_contents($filePath), true);
            if ($existing && isset($existing['accommodations'])) {
                foreach ($existing['accommodations'] as $ea) {
                    $key = strtolower(trim($ea['accommodation_name'] ?? ''));
                    $existingAccommodations[$key] = [
                        'lower_bunks' => $ea['lower_bunks'] ?? [],
                        'upper_bunks' => $ea['upper_bunks'] ?? [],
                        'description' => $ea['description'] ?? '',
                    ];
                }
            }
        }

        // Build per-accommodation entries, preserving existing bunk assignments
        $accommodations = $vessel->accommodations->map(function ($acc, $idx) use ($existingAccommodations) {
            $key = strtolower(trim($acc->accommodation_name));
            $existing = $existingAccommodations[$key] ?? null;
            return [
                'accommodation_id' => $idx + 1,
                'accommodation_name' => $acc->accommodation_name,
                'price' => (float) $acc->accommodation_regular_price,
                'cot_range' => $acc->accommodation_cot_range,
                'description' => $existing ? $existing['description'] : '',
                'lower_bunks' => $existing ? $existing['lower_bunks'] : [],
                'upper_bunks' => $existing ? $existing['upper_bunks'] : [],
            ];
        })->values()->all();

        $vesselData = [
            'vessel_id' => $vessel->vessel_id,
            'vessel_name' => $vessel->vessel_name,
            'vessel_code' => $vessel->vessel_code,
            'accommodations' => $accommodations,
        ];

        file_put_contents($filePath, json_encode($vesselData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        // Update index
        $index = ['vessels' => []];
        if (file_exists($indexPath)) {
            $decoded = json_decode(file_get_contents($indexPath), true);
            if ($decoded && isset($decoded['vessels'])) {
                $index['vessels'] = $decoded['vessels'];
            }
        }

        // Remove existing entry for this vessel (in case of update)
        $index['vessels'] = array_values(array_filter(
            $index['vessels'],
            fn($v) => $v['vessel_id'] !== $vessel->vessel_id
        ));

        $index['vessels'][] = [
            'vessel_id' => $vessel->vessel_id,
            'vessel_name' => $vessel->vessel_name,
            'vessel_code' => $vessel->vessel_code,
            'file' => $fileName,
        ];

        // Sort by vessel_id
        usort($index['vessels'], fn($a, $b) => $a['vessel_id'] - $b['vessel_id']);

        file_put_contents($indexPath, json_encode($index, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
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

        $vessel->update([
            'vessel_code' => $request->vessel_code,
            'vessel_name' => $request->vessel_name,
            'vessel_total_passenger_capacity' => $totalPassengerCapacity,
            'vessel_status' => ucfirst($request->vessel_status),
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
            foreach ($request->accommodations as $key => $a) {
                if (!empty($a['name']) && !empty($a['price']) && !empty($a['cot_range'])) {
                    $cotPlanPath = $a['existing_cot_plan'] ?? null;
                    if ($request->hasFile("accommodations.{$key}.cot_plan")) {
                        $cotPlanPath = $request->file("accommodations.{$key}.cot_plan")->store('cot_plans', 'public');
                    }
                    $vessel->accommodations()->create([
                        'accommodation_name' => $a['name'],
                        'accommodation_regular_price' => $a['price'],
                        'accommodation_cot_range' => $a['cot_range'],
                        'accommodation_cot_plan_url' => $cotPlanPath,
                    ]);
                }
            }
        }

        $this->generateCotPlanJson($vessel);

        return redirect()->route('admin.vessel_list')
            ->with('success', 'Vessel updated successfully.');
    }
}
