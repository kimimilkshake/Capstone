<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Voyage;
use App\Models\RoutePort;
use App\Models\Vessel;

class ManifestController extends Controller
{
    public function show($id)
    {
        $voyage = Voyage::with(['routePort', 'vessel'])->findOrFail($id);
        return view('authorized.manifest', compact('voyage'));
    }
}