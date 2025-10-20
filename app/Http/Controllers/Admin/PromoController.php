<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Promo;

class PromoController extends Controller
{
    // List all promos
    public function index(Request $request)
    {
        $query = Promo::query();

        // Search by promo code or description
        if ($request->has('search') && $request->search != '') {
            $query->where('promo_code', 'like', '%' . $request->search . '%')
            ->orWhere('promo_description', 'like', '%' . $request->search . '%');
        }

        // Filter by status
        if ($request->has('status') && $request->status != '') {
            $query->where('promo_status', $request->status);
        }

        $promos = $query->get();

        return view('authorized.admin.promo_list', compact('promos'));
    }

    // Show create form
    public function create()
    {
        return view('authorized.admin.create_promo');
    }

    // Store new promo
    public function store(Request $request)
    {
        $request->validate([
            'promo_type' => 'required|in:Discount,Freebie',
            'promo_code' => 'required|string|unique:promo,promo_code',
            'promo_description' => 'required|string|max:255',
            'promo_start_date' => 'required|date',
            'promo_end_date' => 'required|date|after_or_equal:promo_start_date',
            'promo_status' => 'required|in:Active,Inactive',
            'promo_discount_rate' => 'required|numeric|min:0',
        ]);

        Promo::create($request->all());

        return redirect()->route('admin.promo_list')->with('success', 'Promo created successfully!');
    }

    // Show edit form
    public function edit($id)
    {
        $promo = Promo::findOrFail($id);
        return view('authorized.admin.edit_promo', compact('promo'));
    }

    // Update promo
    public function update(Request $request, $id)
    {
        $promo = Promo::findOrFail($id);

        $request->validate([
            'promo_type' => 'required|in:Discount,Freebie',
            'promo_code' => 'required|string|unique:promo,promo_code,' . $id . ',promo_id',
            'promo_description' => 'required|string|max:255',
            'promo_start_date' => 'required|date',
            'promo_end_date' => 'required|date|after_or_equal:promo_start_date',
            'promo_status' => 'required|in:Active,Inactive',
            'promo_discount_rate' => 'required|numeric|min:0',
        ]);

        $promo->update($request->all());

        return redirect()->route('admin.promo_list')->with('success', 'Promo updated successfully!');
    }

    // Optional: delete promo
    public function destroy($id)
    {
        $promo = Promo::findOrFail($id);
        $promo->delete();

        return redirect()->route('admin.promo_list')->with('success', 'Promo deleted successfully!');
    }
}
