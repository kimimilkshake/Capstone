<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RoutePort;
use App\Models\RouteCode;
use App\Http\Controllers\Traits\AdminGuard;

class RoutePortController extends Controller
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

        $search = $request->input('search');

        $route_port = RoutePort::select('route_port.*')
            ->join('route_code', 'route_port.route_code_id', '=', 'route_code.route_code_id')
            ->when($search, function ($query, $search) {
                $query->where('route_origin', 'like', "%{$search}%")
                    ->orWhere('route_destination', 'like', "%{$search}%")
                    ->orWhere('port_origin_name', 'like', "%{$search}%")
                    ->orWhere('port_destination_name', 'like', "%{$search}%");
            })
            ->orderBy('route_code.route_code_name', 'asc') // sort by route code name
            ->paginate(7)
            ->withQueryString(); // keeps search query when paginating

        $route_codes = RouteCode::orderBy('route_code_name', 'asc')->get();

        return view('authorized.admin.route_port_list', compact('route_port', 'route_codes', 'search'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'route_code_id' => 'required|exists:route_code,route_code_id',
            'route_origin' => 'required|string|max:255',
            'route_destination' => 'required|string|max:255',
            'port_origin_name' => 'required|string|max:255',
            'port_origin_city' => 'required|string|max:255',
            'port_origin_province' => 'required|string|max:255',
            'port_destination_name' => 'required|string|max:255',
            'port_destination_city' => 'required|string|max:255',
            'port_destination_province' => 'required|string|max:255',
        ]);

        $route_port = RoutePort::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Route & Port added successfully!',
            'route_port' => $route_port
        ]);
    }

    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'route_code_id' => 'required|exists:route_code,route_code_id',
                'route_origin' => 'required|string|max:255',
                'route_destination' => 'required|string|max:255',
                'port_origin_name' => 'required|string|max:255',
                'port_origin_city' => 'required|string|max:255',
                'port_origin_province' => 'required|string|max:255',
                'port_destination_name' => 'required|string|max:255',
                'port_destination_city' => 'required|string|max:255',
                'port_destination_province' => 'required|string|max:255',
            ]);


            $route_port = RoutePort::findOrFail($id);
            $route_port->update($validated);

            return response()->json([
                'status' => 'success',
                'message' => 'Route & Port updated successfully!',
                'route_port' => $route_port
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Server error: '.$e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        RoutePort::destroy($id);
        return redirect()->route('route_port.index')->with('success', 'Route & ports deleted.');
    }
}
