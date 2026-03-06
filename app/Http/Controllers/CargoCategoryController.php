<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CargoCategory;
use App\Http\Controllers\Traits\AdminOrStaffGuard;

class CargoCategoryController extends Controller
{
    use AdminOrStaffGuard;
    public function __construct()
    {
        $this->ensureAuthorized();
    }

    // List
    public function index()
    {
        $classifications = CargoCategory::orderBy('cargo_classification_name', 'asc')->paginate(10);

        return auth()->guard('admin')->check()
            ? view('authorized.admin.cargo_item_list', compact('classifications'))
            : view('authorized.staff.cargo_item_list', compact('classifications'));
    }

    // Show create form
    public function create()
    {
        return auth()->guard('admin')->check()
            ? view('authorized.admin.cargo_category_create')
            : view('authorized.staff.cargo_category_create');
    }

    // Store
    public function store(Request $request)
    {
        $request->validate([
            'cargo_category_name' => 'required|string|max:255|unique:cargo_category,cargo_category_name',
        ]);

        CargoCategory::create($request->only('cargo_category_name'));

        return redirect()->route(auth()->guard('admin')->check() ? 'admin.cargo_item_list' : 'staff.cargo_item_list')
                         ->with('success', 'Cargo Category added successfully!');
    }

    // Show edit form
    public function edit($id)
    {
        $category = CargoCategory::findOrFail($id);

        return auth()->guard('admin')->check()
            ? view('authorized.admin.cargo_category_edit', compact('category'))
            : view('authorized.staff.cargo_category_edit', compact('category'));
    }

    // Update
    public function update(Request $request, $id)
    {
        $category = CargoCategory::findOrFail($id);

        $request->validate([
            'cargo_category_name' => 'required|string|max:255|unique:cargo_category,cargo_category_name,'.$id.',cargo_category_id',
        ]);

        $category->update($request->only('cargo_category_name'));

        return redirect()->route(auth()->guard('admin')->check() ? 'admin.cargo_item_list' : 'staff.cargo_item_list')
                         ->with('success', 'Cargo Category updated successfully!');
    }

    // Delete
    public function destroy($id)
    {
        CargoCategory::destroy($id);
        return redirect()->back()->with('success', 'Cargo Category deleted.');
    }
}
