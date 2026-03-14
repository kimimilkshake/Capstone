<?php

namespace App\Http\Controllers;

use App\Models\CargoItem;
use App\Models\RouteCode;
use App\Models\MeasurementUnit;
use App\Models\CargoCategory;
use Illuminate\Http\Request;
use App\Http\Controllers\Traits\AdminOrStaffGuard;

class CargoItemController extends Controller
{
    use AdminOrStaffGuard;
    public function __construct()
    {
        $this->ensureAuthorized();
    }

    public function index(Request $request)
    {
        $query = CargoItem::with(['routeCode', 'measurementUnit']);

        if ($request->filled('search')) {
            $query->where('cargo_item_description', 'like', "%{$request->search}%");
        }

        if ($request->filled('route_code_id')) {
            $query->where('route_code_id', $request->route_code_id);
        }

        $cargo_items = $query->orderBy('cargo_item_description')->paginate(8);

        $route_codes = RouteCode::all();

        return auth()->guard('staff')->check()
            ? view('authorized.staff.scargo_item_list', compact('cargo_items', 'route_codes'))
            : view('authorized.admin.cargo_item_list', compact('cargo_items', 'route_codes'));
    }

    public function create()
    {
        $route_codes = RouteCode::orderBy('route_code_name')->get();
        $measurement_units = MeasurementUnit::orderBy('measurement_unit_name')->get();
        $cargo_categories = CargoCategory::orderBy('cargo_category_name')->get();

        return auth()->guard('staff')->check()
            ? view('authorized.staff.screate_cargo_item', compact('route_codes', 'measurement_units', 'cargo_categories'))
            : view('authorized.admin.create_cargo_item', compact('route_codes', 'measurement_units', 'cargo_categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'measurement_unit_id' => 'nullable|exists:measurement_unit,measurement_unit_id',
            'cargo_category_id' => 'required|exists:cargo_category,cargo_category_id',
            'route_code_id'       => 'required|exists:route_code,route_code_id',
            'cargo_item_description' => 'required|string',
            'cargo_item_freight'  => 'required|numeric',
            'cargo_item_measure_required' => 'required|in:Yes,No',
            'cargo_item_min_length' => 'nullable|numeric',
            'cargo_item_max_length' => 'nullable|numeric',
            'cargo_item_min_width'  => 'nullable|numeric',
            'cargo_item_max_width'  => 'nullable|numeric',
            'cargo_item_min_height' => 'nullable|numeric',
            'cargo_item_max_height' => 'nullable|numeric',
        ]);

        CargoItem::create($validated);

        return redirect()->route(
            auth()->guard('staff')->check()
                ? 'staff.cargo_item_list'
                : 'admin.cargo_item_list'
        )->with('success', 'Cargo item added successfully.');

    }

    public function edit($id)
    {
        $cargo_item = CargoItem::findOrFail($id);
        $route_codes = RouteCode::orderBy('route_code_name')->get();
        $measurement_units = MeasurementUnit::orderBy('measurement_unit_name')->get();
        $cargo_categories = CargoCategory::orderBy('cargo_category_name')->get();

        return auth()->guard('staff')->check()
            ? view('authorized.staff.scargo_item_edit', compact('cargo_item', 'route_codes', 'measurement_units', 'cargo_categories'))
            : view('authorized.admin.cargo_item_edit', compact('cargo_item', 'route_codes', 'measurement_units', 'cargo_categories' ));
    }


    public function update(Request $request, $id)
    {
        $cargo_item = CargoItem::findOrFail($id);

        $validated = $request->validate([
            'measurement_unit_id' => 'nullable|exists:measurement_unit,measurement_unit_id',
            'cargo_category_id' => 'required|exists:cargo_category,cargo_category_id',
            'route_code_id'       => 'required|exists:route_code,route_code_id',
            'cargo_item_description' => 'required|string',
            'cargo_item_freight'  => 'required|numeric',
            'cargo_item_measure_required' => 'required|in:Yes,No',
            'cargo_item_min_length' => 'nullable|numeric',
            'cargo_item_max_length' => 'nullable|numeric',
            'cargo_item_min_width'  => 'nullable|numeric',
            'cargo_item_max_width'  => 'nullable|numeric',
            'cargo_item_min_height' => 'nullable|numeric',
            'cargo_item_max_height' => 'nullable|numeric',
        ]);

        $cargo_item->update($validated);

        return redirect()->route(
            auth()->guard('staff')->check()
                ? 'staff.cargo_item_list'
                : 'admin.cargo_item_list'
        )->with('success', 'Cargo item updated successfully.');
    }

    public function destroy($id)
    {
        CargoItem::destroy($id);

        return redirect()->back()->with('success', 'Cargo item deleted.');
    }
}
