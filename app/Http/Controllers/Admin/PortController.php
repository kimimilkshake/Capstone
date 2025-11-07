<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Port;
use Illuminate\Support\Facades\Log;

class PortController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $ports = Port::when($search, function ($query, $search) {
            $query->where('port_name', 'like', "%{$search}%")
                ->orWhere('port_city', 'like', "%{$search}%")
                ->orWhere('port_province', 'like', "%{$search}%");
        })
        ->orderBy('port_id', 'asc')
        ->paginate(10);

        return view('authorized.admin.port_list', compact('ports'));
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'port_name' => 'required|string|max:255',
                'port_city' => 'required|string|max:255',
                'port_province' => 'required|string|max:255',
            ]);

            $port = Port::create($request->only([
                'port_name', 'port_city', 'port_province'
            ]));

            // ✅ Always return JSON for AJAX
            return response()->json([
                'success' => true,
                'message' => 'Port added successfully.',
                'port' => $port
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error adding port: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while adding the port.'
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'port_name' => 'required|string|max:255',
                'port_city' => 'required|string|max:255',
                'port_province' => 'required|string|max:255',
            ]);

            $port = Port::findOrFail($id);
            $port->update($request->only([
                'port_name', 'port_city', 'port_province'
            ]));

            return response()->json([
                'success' => true,
                'message' => 'Port updated successfully.'
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error updating port: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating the port.'
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            Port::destroy($id);

            return response()->json([
                'success' => true,
                'message' => 'Port deleted successfully.'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error deleting port: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deleting the port.'
            ], 500);
        }
    }
}
