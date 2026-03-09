<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{
    // Show login page
    public function showLoginForm()
    {
        return view('authorized.login'); // Blade file in views/authorized/login.blade.php
    }

    // Show scanner login page
    public function showScannerLoginForm()
    {
        if (auth()->guard('staff')->check()) {
            return redirect()->route('scanner.page');
        }

        return view('authorized.scanner_login');
    }

    // Handle login
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $username = trim($request->username);
        $password = trim($request->password);

        // Check admin
        $admin = DB::table('admin')->where('admin_user', $username)->first();
        if ($admin && Hash::check($password, $admin->admin_password)) {

            // Log in using Auth guard so controllers like VesselController recognize it
            auth()->guard('admin')->loginUsingId($admin->admin_id);

            // (Optional) Still store session vars if you use them elsewhere
            Session::put('user_id', $admin->admin_id);
            Session::put('user_role', 'admin');
            Session::put('username', $admin->admin_user);
            Session::put('user_name', $admin->admin_name);

            return redirect()->route('admin.dashboard');
        }


        // Check staff
        $staff = DB::table('staff')->where('staff_user', $username)->first();
        if ($staff) {

            // Check if inactive
            if ($staff->staff_status === 'Inactive') {
                return back()->withErrors(['username' => 'Your account is inactive. Please contact admin.'])->withInput();
            }

            // Check password
            if (Hash::check($password, $staff->staff_password)) {

                // 🔥 IMPORTANT: login using staff guard
                auth()->guard('staff')->loginUsingId($staff->staff_id);

                // Store session (optional)
                Session::put('user_id', $staff->staff_id);
                Session::put('user_role', 'staff');
                Session::put('username', $staff->staff_user);
                Session::put('user_name', $staff->staff_name);

                return redirect()->route('staff.dashboard');
            }
        }

        return back()->withErrors(['username' => 'Invalid username or password'])->withInput();
    }

    // Handle dedicated staff login for QR scanner page
    public function scannerLogin(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $username = trim($request->username);
        $password = trim($request->password);

        $staff = DB::table('staff')->where('staff_user', $username)->first();
        if (!$staff || !Hash::check($password, $staff->staff_password)) {
            return back()->withErrors(['username' => 'Invalid username or password'])->withInput();
        }

        if ($staff->staff_status === 'Inactive') {
            return back()->withErrors(['username' => 'Your account is inactive. Please contact admin.'])->withInput();
        }

        auth()->guard('staff')->loginUsingId($staff->staff_id);

        Session::put('user_id', $staff->staff_id);
        Session::put('user_role', 'staff');
        Session::put('username', $staff->staff_user);
        Session::put('user_name', $staff->staff_name);

        return redirect()->route('scanner.page');
    }

    // Dashboard page
    public function dashboard()
    {
        if (!Session::has('user_id')) {
            return redirect()->route('login.form');
        }

        if (Session::get('user_role') === 'admin') {
            return redirect()->route('admin.dashboard');
        } elseif (Session::get('user_role') === 'staff') {
            return redirect()->route('staff.dashboard');
        }

        return redirect()->route('login.form');
    }

    public function logout(Request $request)
    {
        auth()->guard('admin')->logout(); // Logout admin guard
        auth()->guard('staff')->logout(); // Logout staff guard
        auth()->guard('web')->logout();   // Logout default guard (if any)
        Session::flush(); // clears all session data
        return redirect()->route('login.form');
    }

}
