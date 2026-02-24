<?php

namespace App\Http\Controllers\Traits;

trait StaffGuard
{
    //Ensure the current user is an authenticated admin
    protected function ensureStaff()
    {
        if (!auth()->guard('staff')->check()) {
            abort(403);
        }
    }

    //Get the currently authenticated admin ID
    protected function staffId()
    {
        return auth()->guard('staff')->id();
    }
}