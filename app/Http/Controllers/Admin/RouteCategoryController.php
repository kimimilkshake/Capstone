<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RouteCategory;
use App\Models\RouteCategoryDiscount;
use App\Http\Controllers\Traits\AdminGuard;

class RouteCategoryController extends Controller
{
    use AdminGuard;

    public function __construct()
    {
        $this->ensureAdmin();
    }

    private function isAdmin()
    {
        return auth()->guard('admin')->check();
    }

    /*
        dd([
            'staff_guard' => auth()->guard('staff')->check(),
            'admin_guard' => auth()->guard('admin')->check(),
            'staff_user' => auth()->guard('staff')->user(),
            'admin_user' => auth()->guard('admin')->user(),
        ]);
    */

    public function index()
    {
        $route_categories = RouteCategory::with('passengerDiscounts')->get();

        return view('authorized.admin.route_port_list', compact('route_categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'route_category_name' => 'required|string|max:255|unique:route_category,route_category_name',
            'route_rate' => 'nullable|numeric|min:0|max:100',
        ]);

        $routeCategory = RouteCategory::create([
            'route_category_name' => strtoupper($validated['route_category_name']),
            'route_rate' => isset($validated['route_rate']) && $validated['route_rate'] !== '' ? $validated['route_rate'] : null,
        ]);

        foreach ($request->input('discounts', []) as $d) {
            if (!empty($d['passenger_type']) && isset($d['discount_rate']) && $d['discount_rate'] !== '') {
                $routeCategory->passengerDiscounts()->create([
                    'passenger_type' => $d['passenger_type'],
                    'discount_rate' => $d['discount_rate'],
                ]);
            }
        }

        $routeCategory->load('passengerDiscounts');
        return response()->json([
            'status' => 'success',
            'message' => 'Route Category added successfully!',
            'routeCategory' => $routeCategory
        ]);
    }

    public function update(Request $request, $id)
    {
        $routeCategory = RouteCategory::findOrFail($id);

        $validated = $request->validate([
            'route_category_name' => 'required|string|max:255|unique:route_category,route_category_name,' . $id . ',route_category_id',
            'route_rate' => 'nullable|numeric|min:0|max:100',
        ]);

        $routeCategory->update([
            'route_category_name' => strtoupper($validated['route_category_name']),
            'route_rate' => isset($validated['route_rate']) && $validated['route_rate'] !== '' ? $validated['route_rate'] : null,
        ]);

        $routeCategory->passengerDiscounts()->delete();
        foreach ($request->input('discounts', []) as $d) {
            if (!empty($d['passenger_type']) && isset($d['discount_rate']) && $d['discount_rate'] !== '') {
                $routeCategory->passengerDiscounts()->create([
                    'passenger_type' => $d['passenger_type'],
                    'discount_rate' => $d['discount_rate'],
                ]);
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Route Category updated successfully!',
            'routeCategory' => $routeCategory->fresh()->load('passengerDiscounts')
        ]);
    }

    public function discounts($id)
    {
        $routeCategory = RouteCategory::with('passengerDiscounts')->findOrFail($id);
        return response()->json($routeCategory->passengerDiscounts);
    }
}
