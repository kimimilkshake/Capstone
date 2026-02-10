<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CargoClassification;

class CargoClassificationController extends Controller
{
    // Helper guards
    private function isAdmin() { return auth()->guard('admin')->check(); }
    private function isStaff() { return auth()->guard('staff')->check(); }

    // List
    public function index()
    {
        $classifications = CargoClassification::orderBy('cargo_classification_name', 'asc')->paginate(10);

        return $this->isAdmin()
            ? view('authorized.admin.cargo_classification_list', compact('classifications'))
            : view('authorized.staff.cargo_classification_list', compact('classifications'));
    }

    // Show create form
    public function create()
    {
        return $this->isAdmin()
            ? view('authorized.admin.cargo_classification_create')
            : view('authorized.staff.cargo_classification_create');
    }

    // Store
    public function store(Request $request)
    {
        $request->validate([
            'cargo_classification_name' => 'required|string|max:255|unique:cargo_classification,cargo_classification_name',
        ]);

        CargoClassification::create($request->only('cargo_classification_name'));

        return redirect()->route($this->isAdmin() ? 'admin.cargo_classification_list' : 'staff.cargo_classification_list')
                         ->with('success', 'Cargo Classification added successfully!');
    }

    // Show edit form
    public function edit($id)
    {
        $classification = CargoClassification::findOrFail($id);

        return $this->isAdmin()
            ? view('authorized.admin.cargo_classification_edit', compact('classification'))
            : view('authorized.staff.cargo_classification_edit', compact('classification'));
    }

    // Update
    public function update(Request $request, $id)
    {
        $classification = CargoClassification::findOrFail($id);

        $request->validate([
            'cargo_classification_name' => 'required|string|max:255|unique:cargo_classification,cargo_classification_name,'.$id.',cargo_classification_id',
        ]);

        $classification->update($request->only('cargo_classification_name'));

        return redirect()->route($this->isAdmin() ? 'admin.cargo_classification_list' : 'staff.cargo_classification_list')
                         ->with('success', 'Cargo Classification updated successfully!');
    }

    // Delete
    public function destroy($id)
    {
        CargoClassification::destroy($id);
        return redirect()->back()->with('success', 'Cargo Classification deleted.');
    }
}
