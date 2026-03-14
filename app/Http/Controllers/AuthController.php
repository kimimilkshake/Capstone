<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendOtpMail;

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
        $redirectRoute = $request->input('redirect_to') === 'scanner'
            ? 'scanner.login.form'
            : 'login.form';

        auth()->guard('admin')->logout(); // Logout admin guard
        auth()->guard('staff')->logout(); // Logout staff guard
        auth()->guard('web')->logout();   // Logout default guard (if any)
        Session::flush(); // clears all session data
        return redirect()->route($redirectRoute);
    }

    public function sendOTP(Request $request)
    {
        $request->validate([
            'username' => 'required'
        ]);

        $username = trim($request->username);

        // Check admin
        $admin = DB::table('admin')->where('admin_user', $username)->first();

        // Check staff
        $staff = DB::table('staff')->where('staff_user', $username)->first();

        if (!$admin && !$staff) {
            return back()->withErrors(['username' => 'User not found'])->withInput();
        }

        // Get email
        $email = $admin ? $admin->admin_email : $staff->staff_email;

        // Generate 4-digit OTP
        $otp = rand(1000, 9999);

        // Save OTP
        $expiresAt = now()->addMinutes(5); // store expiration time in a variable
        DB::table('password_otps')->updateOrInsert(
            ['email' => $email],
            [
                'otp' => $otp,
                'expires_at' => $expiresAt,
                'created_at' => now(),
                'updated_at' => now()
            ]
        );

        // Store username and OTP expiration time in session
        session([
            'reset_username' => $username,      // existing username session
            'otp_expires_at' => $expiresAt      // new: OTP expiration time
        ]);

        // Send OTP email
        Mail::to($email)->send(new SendOtpMail($otp));

        // Redirect to OTP verification page
        return redirect()->route('otp.page')->with('success', 'OTP sent! Please check your email.');
    }

    public function verifyOTP(Request $request)
    {
        $request->validate([
            'otp' => 'required|numeric',
        ]);

        $username = Session::get('reset_username');

        if (!$username) {
            return redirect()->route('authorized.forgot_password')->withErrors(['username' => 'Session expired, please start over.']);
        }

        // Get email
        $admin = DB::table('admin')->where('admin_user', $username)->first();
        $staff = DB::table('staff')->where('staff_user', $username)->first();

        if (!$admin && !$staff) {
            return redirect()->route('authorized.forgot_password')->withErrors(['username' => 'User not found']);
        }

        $email = $admin ? $admin->admin_email : $staff->staff_email;

        $otpEntry = DB::table('password_otps')->where('email', $email)->first();

        if (!$otpEntry || $otpEntry->otp != $request->otp || now()->gt($otpEntry->expires_at)) {
            return back()->withErrors(['otp' => 'Invalid or expired OTP']);
        }

        // OTP is correct → redirect to reset password page
        Session::put('reset_email', $email); // store email for reset
        return redirect()->route('reset.password.page');
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'new_password' => [
                'required',
                'string',
                'min:8',
                'regex:/[a-z]/',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
                'regex:/[@$!%*?&]/',
            ],
            'confirm_password' => 'required|same:new_password',
        ], [
            'new_password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.',
        ]);


        $email = Session::get('reset_email');
        if (!$email) {
            return redirect()->route('authorized.forgot_password')->withErrors(['email' => 'Session expired, start over.']);
        }

        $admin = DB::table('admin')->where('admin_email', $email)->first();
        $staff = DB::table('staff')->where('staff_email', $email)->first();

        $hashed = Hash::make($request->new_password);

        if ($admin) {
            DB::table('admin')->where('admin_email', $email)->update([
                'admin_password' => $hashed
            ]);
        } elseif ($staff) {
            DB::table('staff')->where('staff_email', $email)->update([
                'staff_password' => $hashed
            ]);
        }

        // Clean up OTP & session
        DB::table('password_otps')->where('email', $email)->delete();
        Session::forget('reset_email');
        Session::forget('reset_username');

        return redirect()->route('login.form')->with('success', 'Password reset successfully. Please login.');
    }

}
