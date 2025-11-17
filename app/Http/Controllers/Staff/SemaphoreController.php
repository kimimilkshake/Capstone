<?php

namespace App\Http\Controllers\Staff    ;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SemaphoreController extends Controller
{
     public function show()
    {
        return view('authorized.staff.semaphore'); 
    }
}
