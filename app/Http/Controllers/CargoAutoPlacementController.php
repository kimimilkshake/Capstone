<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Voyage;
use App\Models\Vessel;
use App\Models\Hatch;
use App\Models\CargoReceipt;
use App\Models\CargoBooking;

class CargoAutoPlacementController extends Controller
{
    private function isStaff()
    {
        return auth()->guard('staff')->check();
    }

    private function isAdmin()
    {
        return auth()->guard('admin')->check();
    }

    public function show(Request $request)
    {
        $voyages = Voyage::with(['vessel', 'routePort'])
            ->where('voyage_status', '!=', 'Completed')
            ->orderBy('voyage_departure_date', 'desc')
            ->get();

        $selectedVoyageId = $request->input('voyage_id');
        $placementData = null;

        if ($selectedVoyageId) {
            $placementData = $this->getVoyagePlacementData($selectedVoyageId);
        }

        return $this->isStaff()
            ? view('authorized.staff.staff_cargoautoplacement', compact('voyages', 'selectedVoyageId', 'placementData'))
            : view('authorized.admin.admin_cargoautoplacement', compact('voyages', 'selectedVoyageId', 'placementData'));
    }

    private function getVoyagePlacementData($voyageId)
    {
        $voyage = Voyage::with(['vessel.hatches', 'cargoReceipts.cargoBooking.cargoItem'])
            ->findOrFail($voyageId);

        $hatches = $voyage->vessel->hatches;
        
        if ($hatches->isEmpty()) {
            return ['error' => 'No hatches found for this vessel.'];
        }

        $cargoReceipts = $voyage->cargoReceipts;
        
        if ($cargoReceipts->isEmpty()) {
            return ['error' => 'No cargo bookings found for this voyage.'];
        }

        return [
            'voyage' => $voyage,
            'hatches' => $hatches,
            'cargoReceipts' => $cargoReceipts,
        ];
    }


    /**
     * Process cargo placement request using 3DBinPacking API.
     */
    public function place(Request $request)
    {
        $request->validate([
            'voyage_id' => 'required|exists:voyage,voyage_id',
        ]);

        $voyage = Voyage::with(['vessel.hatches', 'cargoReceipts.cargoBooking.cargoItem'])
            ->findOrFail($request->voyage_id);

        $hatches = $voyage->vessel->hatches;
        
        if ($hatches->isEmpty()) {
            return back()->withErrors(['voyage' => 'No hatches found for this vessel.']);
        }

        $cargoReceipts = $voyage->cargoReceipts;
        
        if ($cargoReceipts->isEmpty()) {
            return back()->withErrors(['voyage' => 'No cargo bookings found for this voyage.']);
        }

        // Prepare cargo items
        $cargoItems = [];
        foreach ($cargoReceipts as $receipt) {
            $cargoBooking = CargoBooking::where('booking_ref_no', $receipt->booking_ref_no)->first();
            
            if ($cargoBooking && $cargoBooking->length && $cargoBooking->width && $cargoBooking->height) {
                $cargoItems[] = [
                    'id' => $receipt->cargo_receipt_id,
                    'w' => (float) $cargoBooking->width,
                    'h' => (float) $cargoBooking->height,
                    'd' => (float) $cargoBooking->length,
                    'weight' => (float) ($cargoBooking->weight ?? 0),
                    'q' => (int) ($receipt->cargo_item_qty ?? 1),
                    'item_name' => $receipt->cargoItem->cargo_item_description ?? 'Unknown',
                    'booking_ref' => $receipt->booking_ref_no,
                ];
            }
        }

        if (empty($cargoItems)) {
            return back()->withErrors(['voyage' => 'No valid cargo items with dimensions found.']);
        }

        // Process each hatch with 3DBinPacking API
        $username = config('services.3dbin.username') ?? env('3DBIN_USERNAME');
        $apiKey   = config('services.3dbin.api_key') ?? env('3DBIN_API_KEY');

        if (empty($username) || empty($apiKey)) {
            return back()->withErrors(['api_credentials' => '3DBinPacking credentials are not configured.']);
        }

        $hatchResults = [];
        $remainingItems = $cargoItems;
        
        foreach ($hatches as $index => $hatch) {
            if (empty($remainingItems)) {
                break;
            }

            $result = $this->callBinPackingAPI(
                $username,
                $apiKey,
                $hatch,
                $remainingItems
            );

            if (isset($result['error'])) {
                $hatchResults[] = [
                    'hatch' => $hatch,
                    'error' => $result['error'],
                ];
                continue;
            }

            $hatchResults[] = [
                'hatch' => $hatch,
                'result' => $result,
            ];

            // Remove packed items from remaining items
            if (isset($result['packed_items'])) {
                $packedIds = array_column($result['packed_items'], 'id');
                $remainingItems = array_filter($remainingItems, function($item) use ($packedIds) {
                    return !in_array($item['id'], $packedIds);
                });
                $remainingItems = array_values($remainingItems);
            }
        }

        return back()->with([
            'placement_results' => $hatchResults,
            'remaining_items' => $remainingItems,
            'voyage_id' => $request->voyage_id,
        ]);
    }

    private function callBinPackingAPI($username, $apiKey, $hatch, $items)
    {
        $payload = [
            'username'  => $username,
            'api_key'   => $apiKey,
            'container' => [
                'w' => (float) $hatch->hatch_width,
                'h' => (float) $hatch->hatch_height,
                'd' => (float) $hatch->hatch_length,
            ],
            'items' => array_map(function ($it) {
                return [
                    'id' => (string) $it['id'],
                    'w'  => (float) $it['w'],
                    'h'  => (float) $it['h'],
                    'd'  => (float) $it['d'],
                    'q'  => (int) $it['q'],
                ];
            }, $items),
        ];

        $endpoint = 'https://global-api.3dbinpacking.com/packer/fillContainer';

        try {
            $response = Http::timeout(15)
                ->acceptJson()
                ->post($endpoint, $payload);
        } catch (\Exception $e) {
            Log::error('3DBinPacking request failed', [
                'exception' => $e->getMessage(),
                'hatch_id' => $hatch->hatch_id,
            ]);

            return ['error' => 'Unable to reach 3DBinPacking API.'];
        }

        if ($response->failed()) {
            $apiMessage = null;
            try {
                $body = $response->json();
                if (is_array($body) && isset($body['error'])) {
                    $apiMessage = $body['error'];
                } elseif (is_array($body) && isset($body['message'])) {
                    $apiMessage = $body['message'];
                }
            } catch (\Exception $e) {
                // ignore parse errors
            }

            $errorMsg = $apiMessage ?? '3DBinPacking API request failed.';
            return ['error' => $errorMsg];
        }

        try {
            $result = $response->json();
            return $result;
        } catch (\Exception $e) {
            Log::error('Failed to parse 3DBinPacking response JSON', [
                'exception' => $e->getMessage(),
                'raw' => $response->body(),
            ]);

            return ['error' => 'Invalid response from 3DBinPacking API.'];
        }
    }

    /**
     * Add an empty item row and redirect back with input preserved.
     * This is now simplified since we're using voyage-based placement.
     */
    public function addRow(Request $request)
    {
        return redirect()->route($this->isStaff() ? 'staff.cargo.placement' : 'admin.cargo.placement');
    }

    /**
     * Remove an item row by index and redirect back with input preserved.
     * This is now simplified since we're using voyage-based placement.
     */
    public function removeRow(Request $request)
    {
        return redirect()->route($this->isStaff() ? 'staff.cargo.placement' : 'admin.cargo.placement');
    }
}
