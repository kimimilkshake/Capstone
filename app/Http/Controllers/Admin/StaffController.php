<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Staff;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Traits\AdminGuard;

class StaffController extends Controller
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

        $this->ensureAdmin();

        $query = Staff::query();

        // Search by name
        if ($request->has('search') && $request->search != '') {
            $query->where('staff_name', 'like', '%' . $request->search . '%');
        }

        // Default to Active when no status param; 'all' = show all staff
        $status = $request->input('status', 'Active');
        if ($status === 'all') {
            $query->whereIn('staff_status', ['Active', 'Inactive']);
        } else {
            $query->where('staff_status', $status);
        }

        $staff = $query
            ->orderBy('staff_name', 'asc')
            ->paginate(10);


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
            'admin_id' => auth()->guard('admin')->id(),
            'staff_name' => $request->staff_name,
            'staff_user' => $request->staff_user,
            'staff_password' => Hash::make($request->staff_password),
            'staff_dob' => $request->staff_dob,
            'staff_gender' => $request->staff_gender,
            'staff_email' => $request->staff_email,
            'staff_status' => 'Active', // match enum in DB
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
