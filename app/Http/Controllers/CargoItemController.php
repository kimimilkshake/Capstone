<?php

namespace App\Http\Controllers;

use App\Models\CargoItem;
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
        $search = $request->input('search');

        $cargo_items = CargoItem::when($search, function ($query, $search) {
                $query->where('cargo_item_classification', 'like', "%{$search}%")
                      ->orWhere('cargo_item_description', 'like', "%{$search}%")
                      ->orWhere('cargo_item_type', 'like', "%{$search}%");
            })
            ->orderBy('cargo_item_description', 'asc')
            ->paginate(10);

        return $this->isStaff()
            ? view('authorized.staff.scargo_item_list', compact('cargo_items', 'search'))
            : view('authorized.admin.cargo_item_list', compact('cargo_items', 'search'));
    }

    public function create()
    {
        return $this->isStaff()
            ? view('authorized.staff.screate_cargo_item')
            : view('authorized.admin.create_cargo_item');
    }

    public function store(Request $request)
    {
        $request->validate([
            'cargo_item_classification' => 'required|string',
            'cargo_item_description'    => 'required|string',
            'cargo_item_freight'        => 'required|numeric',
            'cargo_item_arrastre'       => 'required|numeric',
            'cargo_item_type'           => 'required|string',
            'cargo_item_volume'         => 'required|numeric',
            'cargo_item_weight'         => 'required|numeric',
            'cargo_item_length'         => 'required|numeric',
            'cargo_item_height'         => 'required|numeric',
            'cargo_item_width'          => 'required|numeric',
        ]);

        CargoItem::create($request->all());

        return redirect()->route($this->isStaff() ? 'staff.cargo_item_list' : 'admin.cargo_item_list')
                         ->with('success', 'Cargo item added successfully.');
    }

    public function edit($id)
    {
        $cargo_item = CargoItem::findOrFail($id);

        return $this->isStaff()
            ? view('authorized.staff.scargo_item_edit', compact('cargo_item'))
            : view('authorized.admin.cargo_item_edit', compact('cargo_item'));
    }

    public function update(Request $request, $id)
    {
        $cargo_item = CargoItem::findOrFail($id);

        $request->validate([
            'cargo_item_classification' => 'required|string',
            'cargo_item_description'    => 'required|string',
            'cargo_item_freight'        => 'required|numeric',
            'cargo_item_arrastre'       => 'required|numeric',
            'cargo_item_type'           => 'required|string',
            'cargo_item_volume'         => 'required|numeric',
            'cargo_item_weight'         => 'required|numeric',
            'cargo_item_length'         => 'required|numeric',
            'cargo_item_height'         => 'required|numeric',
            'cargo_item_width'          => 'required|numeric',
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
