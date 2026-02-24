<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RouteCode;
use App\Http\Controllers\Traits\AdminGuard;

class RouteCodeController extends Controller
{
    use AdminGuard;

    public function __construct()
    {
        $this->ensureAdmin();
    }

    /*
        dd([
            'staff_guard' => auth()->guard('staff')->check(),
            'admin_guard' => auth()->guard('admin')->check(),
            'staff_user' => auth()->guard('staff')->user(),
            'admin_user' => auth()->guard('admin')->user(),
        ]);
    */
    private function isAdmin()
    {
        return auth()->guard('admin')->check();
    }

    public function index()
    {
        $routeCodes = RouteCode::all();

        return view('authorized.admin.route_port_list', compact('routeCodes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'route_code_name' => 'required|string|max:255|unique:route_code,route_code_name',
        ]);

        $routeCode = RouteCode::create([
            'route_code_name' => $validated['route_code_name'],
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Route Code added successfully!',
            'routeCode' => $routeCode
        ]);
    }
}
