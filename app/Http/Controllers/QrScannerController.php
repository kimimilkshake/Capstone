<?php

namespace App\Http\Controllers;

class QrScannerController extends Controller
{
    public function index()
    {
        if (!auth()->guard('staff')->check()) {
            return redirect()->route('scanner.login.form');
        }

        return view('authorized.staff.qr_scanner');
    }
}
