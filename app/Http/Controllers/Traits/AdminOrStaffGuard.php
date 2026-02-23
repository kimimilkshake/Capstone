<?php

namespace App\Http\Controllers\Traits;

trait AdminOrStaffGuard
{
    protected function ensureAuthorized()
    {
        if (
            !auth()->guard('admin')->check() &&
            !auth()->guard('staff')->check()
        ) {
            abort(403);
        }
    }
}