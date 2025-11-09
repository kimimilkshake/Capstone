<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Voyage;
use App\Models\Route;
use App\Models\Port;
use App\Models\Vessel;

class ManifestController extends Controller
{
    public function show($id)
    {
        $voyage = Voyage::with(['route', 'vessel', 'port'])->findOrFail($id);
        return view('authorized.manifest', compact('voyage'));
    }
}