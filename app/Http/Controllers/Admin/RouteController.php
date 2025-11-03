<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Route;

class RouteController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $routes = Route::when($search, function ($query, $search) {
                $query->where('route_origin', 'like', "%{$search}%")
                      ->orWhere('route_destination', 'like', "%{$search}%");
            })
            ->orderBy('route_id', 'asc')
            ->paginate(10);

        return view('authorized.admin.route_list', compact('routes', 'search'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'route_origin' => 'required|string|max:255',
            'route_destination' => 'required|string|max:255',
        ]);

        $route = Route::create($validated);

        if ($request->ajax()) {
            return response()->json([
                'message' => 'Route added successfully.',
                'route' => $route
            ], 201);
        }

        return redirect()->route('routes.index')->with('success', 'Route added successfully.');
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'route_origin' => 'required|string|max:255',
            'route_destination' => 'required|string|max:255',
        ]);

        $route = Route::findOrFail($id);
        $route->update($validated);

        if ($request->ajax()) {
            return response()->json([
                'message' => 'Route updated successfully.',
                'route' => $route
            ]);
        }

        return redirect()->route('routes.index')->with('success', 'Route updated successfully.');
    }

    public function destroy($id)
    {
        Route::destroy($id);
        return redirect()->route('routes.index')->with('success', 'Route deleted.');
    }
}
