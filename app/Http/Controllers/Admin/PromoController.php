<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Promo;

class PromoController extends Controller
{
    //Show list of promos with optional search
    public function index(Request $request)
    {
        $query = Promo::query();

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('promo_name', 'like', "%{$search}%")
                  ->orWhere('promo_code', 'like', "%{$search}%");
            });
        }

        $promos = $query
            ->orderBy('promo_id', 'asc')
            ->paginate(8);


        return view('authorized.admin.promo_list', compact('promos'));
    }

    // ✅ Show create promo form
    public function create()
    {
        return view('authorized.admin.create_promo');
    }

    // ✅ Store new promo
    public function store(Request $request)
    {
        $request->validate([
            'promo_type' => 'required|in:Discount,Freebie',
            'promo_name' => 'required|string|max:50',
            'promo_code' => 'required|string|unique:promo,promo_code',
            'promo_description' => 'required|string|max:255',
            'promo_start_date' => 'required|date',
            'promo_end_date' => 'required|date|after_or_equal:promo_start_date',
            //'promo_status' => 'required|in:Active,Inactive',
            'promo_discount_rate' => 'nullable|numeric|min:0|max:100',
        ]);

        Promo::create($request->all());

        return redirect()->route('admin.promo_list')->with('success', 'Promo created successfully!');
    }

    // ✅ Show edit form
    public function edit($id)
    {
        $promo = Promo::findOrFail($id);
        return view('authorized.admin.promo_edit', compact('promo'));
    }

    // ✅ Update promo
    public function update(Request $request, $id)
    {
        $promo = Promo::findOrFail($id);

        $request->validate([
            'promo_type' => 'required|in:Discount,Freebie',
            'promo_name' => 'required|string|max:50',
            'promo_code' => 'required|string|unique:promo,promo_code,' . $id . ',promo_id',
            'promo_description' => 'required|string|max:255',
            'promo_start_date' => 'required|date',
            'promo_end_date' => 'required|date|after_or_equal:promo_start_date',
            'promo_status' => 'required|in:Active,Inactive',
            'promo_discount_rate' => 'nullable|numeric|min:0|max:100',
        ]);

        $promo->update($request->all());

        return redirect()->route('admin.promo_list')->with('success', 'Promo updated successfully!');
    }

    // ✅ Optional delete function
    public function destroy($id)
    {
        $promo = Promo::findOrFail($id);
        $promo->delete();

        return redirect()->route('admin.promo_list')->with('success', 'Promo deleted successfully!');
    }
}
