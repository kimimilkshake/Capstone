<?php

namespace App\Http\Controllers;

use App\Models\CargoItem;
use App\Models\RoutePort;
use Illuminate\Http\Request;

class CargoItemController extends Controller
{
    // Helper methods
    private function isStaff()
    {
        return auth()->guard('staff')->check();
    }

    private function isAdmin()
    {
        return auth()->guard('admin')->check();
    }

    public function index(Request $request)
    {
        $query = CargoItem::query();

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('cargo_item_classification', 'like', "%{$search}%")
                ->orWhere('cargo_item_description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('destination')) {
            $query->whereHas('routePort', function ($q) use ($request) {
                $q->where('route_destination', $request->destination);
            });
        }

        $cargo_items = $query
            ->orderBy('cargo_item_description', 'asc')
            ->paginate(8);

        $destinations = RoutePort::pluck('route_destination')->unique();

        return $this->isStaff()
            ? view('authorized.staff.scargo_item_list', compact('cargo_items', 'destinations'))
            : view('authorized.admin.cargo_item_list', compact('cargo_items', 'destinations'));
    }


    public function create()
    {
        $routes = RoutePort::all();
        return $this->isStaff()
            ? view('authorized.staff.screate_cargo_item', compact('routes'))
            : view('authorized.admin.create_cargo_item', compact('routes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'cargo_item_classification' => 'required|string',
            'cargo_item_description'    => 'required|string',
            'cargo_item_freight'        => 'required|numeric',
            'cargo_item_arrastre'       => 'required|numeric',
            'route_port_id'             => 'required|exists:route_port,route_port_id',
        ]);

        CargoItem::create($request->all());

        return redirect()->route($this->isStaff() ? 'staff.cargo_item_list' : 'admin.cargo_item_list')
                         ->with('success', 'Cargo item added successfully.');
    }

    public function edit($id)
    {
        $cargo_item = CargoItem::findOrFail($id);
        $routes = RoutePort::all();

        return $this->isStaff()
            ? view('authorized.staff.scargo_item_edit', compact('cargo_item', 'routes'))
            : view('authorized.admin.cargo_item_edit', compact('cargo_item', 'routes'));
    }

    public function update(Request $request, $id)
    {
        $cargo_item = CargoItem::findOrFail($id);

        $request->validate([
            'cargo_item_classification' => 'required|string',
            'cargo_item_description'    => 'required|string',
            'cargo_item_freight'        => 'required|numeric',
            'cargo_item_arrastre'       => 'required|numeric',
            'route_port_id'             => 'required|exists:route_port,route_port_id',
        ]);

        $cargo_item->update($request->all());

        return redirect()->route($this->isStaff() ? 'staff.cargo_item_list' : 'admin.cargo_item_list')
                         ->with('success', 'Cargo item updated successfully.');
    }

    public function destroy($id)
    {
        CargoItem::destroy($id);

        return redirect()->route($this->isStaff() ? 'staff.cargo_item_list' : 'admin.cargo_item_list')
                         ->with('success', 'Cargo item deleted.');
    }
}
