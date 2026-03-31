<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RouteCategory;
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
        $routeCategories = RouteCategory::all();

        return view('authorized.admin.route_port_list', compact('routeCategories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'route_category_name' => 'required|string|max:255|unique:route_category,route_category_name',
        ]);

        $routeCategory = RouteCategory::create([
            'route_category_name' => strtoupper($validated['route_category_name']),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Route Category added successfully!',
            'routeCategory' => $routeCategory
        ]);
    }
}
