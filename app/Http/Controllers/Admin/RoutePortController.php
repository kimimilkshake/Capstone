<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Port;
use App\Models\RoutePort;
use App\Models\RouteCategory;
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

        $route_port = RoutePort::with(['portOrigin', 'portDestination', 'routeCategory'])
            ->join('route_category', 'route_port.route_category_id', '=', 'route_category.route_category_id')
            ->when($search, function ($query, $search) {
                $query->where('route_origin', 'like', "%{$search}%")
                    ->orWhere('route_destination', 'like', "%{$search}%")
                    ->orWhereHas('portOrigin', fn($q) => $q->where('port_name', 'like', "%{$search}%"))
                    ->orWhereHas('portDestination', fn($q) => $q->where('port_name', 'like', "%{$search}%"));
            })
            ->orderBy('route_port.route_code', 'asc')
            ->paginate(10)
            ->withQueryString(); // keeps search query when paginating

        $route_categories = RouteCategory::with('passengerDiscounts')->orderBy('route_category_name', 'asc')->get();
        $ports = Port::orderBy('port_name')->get();

        return view('authorized.admin.route_port_list', compact('route_port', 'route_categories', 'ports', 'search'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'route_category_id' => 'required|exists:route_category,route_category_id',
            'route_origin' => 'required|string|max:255',
            'route_destination' => 'required|string|max:255',
            'port_origin_id' => 'required|exists:ports,port_id',
            'port_destination_id' => 'required|exists:ports,port_id',
        ]);

        // CREATE ROUTE CODE
        $originCode = strtoupper(substr($validated['route_origin'], 0, 3));
        $destinationCode = strtoupper(substr($validated['route_destination'], 0, 3));
        $validated['route_code'] = $originCode . $destinationCode;

        $route_port = RoutePort::create($validated);

        // Load relationships for port names and terminal names
        $route_port->load(['portOrigin', 'portDestination', 'routeCategory']);

        return response()->json([
            'status' => 'success',
            'message' => 'Route & Port added successfully!',
            'newRoutePort' => [
                'route_port_id' => $route_port->route_port_id,
                'route_category_id' => $route_port->route_category_id,
                'route_origin' => $route_port->route_origin,
                'route_destination' => $route_port->route_destination,
                'route_code' => $route_port->route_code,
                'port_origin_id' => $route_port->port_origin_id,
                'port_origin_name' => $route_port->portOrigin->port_name,
                'port_origin_terminal' => $route_port->portOrigin->terminal_name,
                'port_origin_city' => $route_port->portOrigin->city,
                'port_destination_id' => $route_port->port_destination_id,
                'port_destination_name' => $route_port->portDestination->port_name,
                'port_destination_terminal' => $route_port->portDestination->terminal_name,
                'port_destination_city' => $route_port->portDestination->city,
            ]
        ]);
    }

    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'route_category_id' => 'required|exists:route_category,route_category_id',
                'route_origin' => 'required|string|max:255',
                'route_destination' => 'required|string|max:255',
                'port_origin_id' => 'required|exists:ports,port_id',
                'port_destination_id' => 'required|exists:ports,port_id',
            ]);


            $originCode = strtoupper(substr($validated['route_origin'], 0, 3));
            $destinationCode = strtoupper(substr($validated['route_destination'], 0, 3));
            $validated['route_code'] = $originCode . $destinationCode;
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
                'message' => 'Server error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        RoutePort::destroy($id);
        return redirect()->route('admin.route_port_list')->with('success', 'Route & ports deleted.');
    }
}
