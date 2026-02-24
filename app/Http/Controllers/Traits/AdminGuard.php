<?php

namespace App\Http\Controllers\Traits;

trait AdminGuard
{
    //Ensure the current user is an authenticated admin
    protected function ensureAdmin()
    {
        if (!auth()->guard('admin')->check()) {
            abort(403);
        }
    }

    //Get the currently authenticated admin ID
    protected function adminId()
    {
        return auth()->guard('admin')->id();
    }
}