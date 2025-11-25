<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Staff;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class StaffController extends Controller
{
    public function index(Request $request)
    {
        $query = Staff::query();

        // Search by name
        if ($request->has('search') && $request->search != '') {
            $query->where('staff_name', 'like', '%' . $request->search . '%');
        }

        // Filter by status
        if ($request->has('status') && $request->status != '') {
            $query->where('staff_status', $request->status);
        }

        $staff = $query
            ->orderBy('staff_id', 'asc')
            ->paginate(8);


        return view('authorized.admin.staff_list', compact('staff'));
    }

    public function create()
    {
        return view('authorized.admin.create_staff');
    }

    public function store(Request $request)
    {
        $request->validate([
            'staff_name' => 'required|string|max:50',
            'staff_user' => 'required|string|max:10|unique:staff,staff_user',
            'staff_password' => 'required|string',
            'staff_dob' => 'required|date',
            'staff_gender' => 'required|in:M,F',
            'staff_email' => 'required|email|unique:staff,staff_email',
            //'staff_status' => 'required|in:Active,Inactive',
        ]);

        Staff::create([
            'admin_id' => 1, // replace with Auth::guard('admin')->id() if guard is defined
            'staff_name' => $request->staff_name,
            'staff_user' => $request->staff_user,
            'staff_password' => Hash::make($request->staff_password),
            'staff_dob' => $request->staff_dob,
            'staff_gender' => $request->staff_gender,
            'staff_email' => $request->staff_email,
            'staff_status' =>'Active', // match enum in DB
        ]);


        return redirect()->route('admin.staff_list')->with('success', 'Staff account created successfully!');
    }

    // Show the edit form
    public function edit($id)
    {
        $staff = Staff::findOrFail($id); // find staff by ID or throw 404
        return view('authorized.admin.staff_edit', compact('staff'));
    }

    // Handle form submission to update staff
    public function update(Request $request, $id)
    {
        $staff = Staff::findOrFail($id);

        $request->validate([
            'staff_name' => 'required|string|max:50',
            'staff_user' => 'required|string|max:10|unique:staff,staff_user,' . $id . ',staff_id',
            'staff_dob' => 'required|date',
            'staff_gender' => 'required|in:M,F',
            'staff_email' => 'required|email|unique:staff,staff_email,' . $id . ',staff_id',
            'staff_status' => 'required|in:Active,Inactive',
        ]);

        $staff->update([
            'staff_name' => $request->staff_name,
            'staff_user' => $request->staff_user,
            'staff_dob' => $request->staff_dob,
            'staff_gender' => $request->staff_gender,
            'staff_email' => $request->staff_email,
            'staff_status' => $request->staff_status,
        ]);

        return redirect()->route('admin.staff_list')->with('success', 'Staff updated successfully!');
    }

}
