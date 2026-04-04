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
use App\Models\Booking;

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

    /**
     * Helper: Convert dimensions from booking unit to meters
     */
    private function convertToMeters($value, $unitName = 'cm')
    {
        if (!$value)
            return 0;

        $unitLower = strtolower(trim($unitName ?? 'cm'));

        // Convert to meters based on unit
        if (strpos($unitLower, 'cm') !== false || strpos($unitLower, 'centimeter') !== false) {
            return (float) $value / 100; // cm to m
        } elseif (strpos($unitLower, 'mm') !== false || strpos($unitLower, 'millimeter') !== false) {
            return (float) $value / 1000; // mm to m
        } elseif (strpos($unitLower, 'in') !== false || strpos($unitLower, 'inch') !== false) {
            return (float) $value / 39.3701; // inches to m
        } elseif (strpos($unitLower, 'ft') !== false || strpos($unitLower, 'feet') !== false || strpos($unitLower, 'foot') !== false) {
            return (float) $value / 3.28084; // feet to m
        } elseif (strpos($unitLower, 'm') === 0 || strpos($unitLower, 'meter') !== false) {
            return (float) $value; // already in meters
        }

        // Default: assume cm
        return (float) $value / 100;
    }

    public function show(Request $request)
    {
        // Prevent browser caching of this page
        $viewPast = $request->input('view') === 'past';

        if ($viewPast) {
            // Show past voyages from the last 7 days
            $voyages = Voyage::with(['vessel', 'routePort'])
                ->where('voyage_status', '!=', 'Completed')
                ->whereBetween('voyage_departure_date', [today()->subDays(7), today()])
                ->where(function ($query) {
                    // Only today's voyages that have already departed
                    $query->whereDate('voyage_departure_date', '<', today())
                        // OR today's voyages where departure time has passed
                        ->orWhere(function ($q) {
                        $q->whereDate('voyage_departure_date', '=', today())
                            ->where('voyage_estimated_TD', '<', now()->format('H:i:s'));
                    });
                })
                ->orderBy('voyage_departure_date', 'desc')
                ->orderBy('voyage_estimated_TD', 'desc')
                ->get();
        } else {
            // Show upcoming voyages
            $voyages = Voyage::with(['vessel', 'routePort'])
                ->where('voyage_status', '!=', 'Completed')
                ->where(function ($query) {
                    // Show voyages from tomorrow onwards
                    $query->whereDate('voyage_departure_date', '>', today())
                        // OR show today's voyages that haven't departed yet
                        ->orWhere(function ($q) {
                        $q->whereDate('voyage_departure_date', '=', today())
                            ->where('voyage_estimated_TD', '>=', now()->format('H:i:s'));
                    });
                })
                ->orderBy('voyage_departure_date', 'asc')
                ->orderBy('voyage_estimated_TD', 'asc')
                ->get();
        }

        $selectedVoyageId = $request->input('voyage_id');

        // If no voyage_id, show the voyage selection page
        if (!$selectedVoyageId) {
            $view = $this->isStaff()
                ? view('authorized.staff.staff_cargo_placement_select', compact('voyages', 'viewPast'))
                : view('authorized.admin.admin_cargo_placement_select', compact('voyages', 'viewPast'));

            return response($view)
                ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
                ->header('Pragma', 'no-cache')
                ->header('Expires', '0');
        }

        // If voyage_id is provided, show the placement visualization page
        $placementData = $this->getVoyagePlacementData($selectedVoyageId);

        $view = $this->isStaff()
            ? view('authorized.staff.staff_cargoautoplacement', compact('voyages', 'selectedVoyageId', 'placementData'))
            : view('authorized.admin.admin_cargoautoplacement', compact('voyages', 'selectedVoyageId', 'placementData'));

        return response($view)
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    private function getVoyagePlacementData($voyageId)
    {
        $voyage = Voyage::with(['vessel.hatches', 'routePort', 'cargoReceipts.cargoBooking.cargoItem', 'cargoReceipts.booking'])
            ->findOrFail($voyageId);

        $hatches = $voyage->vessel->hatches;

        if ($hatches->isEmpty()) {
            return ['error' => 'No hatches found for this vessel.'];
        }

        // Order by booking_ref_no to show items in booking order (earliest bookings first)
        $cargoReceipts = $voyage->cargoReceipts()->with('booking')->orderBy('booking_ref_no', 'asc')->get();

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
     * Process cargo placement request.
     * Note: Previous 3DBinPacking API integration has been removed.
     */
    public function place(Request $request)
    {
        $request->validate([
            'voyage_id' => 'required|exists:voyage,voyage_id',
        ]);

        $voyage = Voyage::with(['vessel.hatches', 'cargoReceipts.cargoBooking.cargoItem', 'cargoReceipts.booking'])
            ->findOrFail($request->voyage_id);

        $hatches = $voyage->vessel->hatches;

        if ($hatches->isEmpty()) {
            return back()->withErrors(['voyage' => 'No hatches found for this vessel.']);
        }

        $cargoReceipts = $voyage->cargoReceipts;

        if ($cargoReceipts->isEmpty()) {
            return back()->withErrors(['voyage' => 'No cargo bookings found for this voyage.']);
        }

        // Prepare cargo items with unit conversion
        $cargoItems = [];
        foreach ($cargoReceipts as $receipt) {
            $cargoBooking = CargoBooking::with('measurementUnit')
                ->where('booking_ref_no', $receipt->booking_ref_no)->first();

            if ($cargoBooking && $cargoBooking->length && $cargoBooking->width && $cargoBooking->height) {
                // Get measurement unit (default: cm)
                $unitName = $cargoBooking->measurementUnit?->measurement_unit_abbreviation ?? 'cm';

                // Convert dimensions to meters
                $widthM = $this->convertToMeters($cargoBooking->width, $unitName);
                $heightM = $this->convertToMeters($cargoBooking->height, $unitName);
                $lengthM = $this->convertToMeters($cargoBooking->length, $unitName);

                $cargoItems[] = [
                    'id' => $receipt->cargo_receipt_id,
                    'voyage_id' => $request->voyage_id,
                    'vessel_id' => $voyage->vessel_id,
                    'w' => (float) $widthM,
                    'h' => (float) $heightM,
                    'd' => (float) $lengthM,
                    'weight' => (float) ($cargoBooking->weight ?? 0),
                    'q' => (int) ($receipt->cargo_item_qty ?? 1),
                    'item_name' => $receipt->cargoItem->cargo_item_description ?? 'Unknown',
                    'booking_ref' => $receipt->booking_ref_no,
                    'original_unit' => $unitName,
                    'original_dims' => "{$cargoBooking->length} × {$cargoBooking->width} × {$cargoBooking->height}",
                ];
            }
        }

        if (empty($cargoItems)) {
            return back()->withErrors(['voyage' => 'No valid cargo items with dimensions found.']);
        }

        // Prepare results for display
        $hatchResults = [];

        foreach ($hatches as $hatch) {
            $hatchResults[] = [
                'hatch' => $hatch,
                'result' => [
                    'packed_items' => [],
                    'message' => 'Auto-placement data prepared. Manual placement visualization available.'
                ],
            ];
        }

        return back()->with([
            'placement_results' => $hatchResults,
            'remaining_items' => $cargoItems,
            'voyage_id' => $request->voyage_id,
            'info' => 'Auto-placement API has been removed. All items available for manual placement review.',
        ]);
    }



    /**
     * Get packing data for visualization (JSON API endpoint)
     */
    public function getPackingData(Request $request)
    {
        $request->validate([
            'voyage_id' => 'required|exists:voyage,voyage_id',
        ]);

        $voyageId = $request->voyage_id;
        $voyage = Voyage::with(['vessel.hatches', 'cargoReceipts.cargoBooking.cargoItem', 'cargoReceipts.booking'])
            ->findOrFail($voyageId);

        $hatches = $voyage->vessel->hatches;

        // Prepare hatches with weight information
        $hatchesData = [];
        foreach ($hatches as $hatch) {
            // Use hatch_capacity_per_hold (in tons) as the maximum weight limit
            $maxWeightTons = (float) $hatch->hatch_capacity_per_hold;
            $maxWeightKg = $maxWeightTons * 1000; // Convert tons to kg for cargo items

            $hatchesData[] = [
                'id' => $hatch->hatch_id,
                'label' => $hatch->hatch_label,
                'width' => (float) $hatch->hatch_width,
                'height' => (float) $hatch->hatch_height,
                'depth' => (float) $hatch->hatch_length,
                'maxWeight' => $maxWeightKg, // Use TOTAL capacity, not available
                'maxWeightKg' => $maxWeightKg,
                'totalCapacity' => $maxWeightKg,
                'volume' => (float) $hatch->hatch_width * $hatch->hatch_height * $hatch->hatch_length,
            ];
        }

        // Get cargo receipts for the voyage - fetch ALL confirmed items
        $cargoData = [];

        $cargoReceipts = $voyage->cargoReceipts()
            ->whereHas('booking', function ($q) {
                $q->whereRaw("LOWER(booking.booking_status) = ?", ['confirmed']);
            })
            ->orderBy('booking_ref_no', 'asc')
            ->get();

        if ($hatches->isEmpty()) {
            return response()->json(['error' => 'Missing hatches'], 400);
        }

        // Load all saved hatch assignments for this voyage so the packer can
        // restore the previous layout instead of reassigning everything from scratch.
        $savedPlacements = \DB::table('cargo_hatch_placement')
            ->where('voyage_id', $voyageId)
            ->select('cargo_receipt_id', 'hatch_id', 'unit_count')
            ->get()
            ->groupBy('cargo_receipt_id');

        foreach ($cargoReceipts as $receipt) {
            $bookingRow = CargoBooking::with('measurementUnit')->where('cargo_booking_id', $receipt->cargo_booking_id)->first();
            if ($bookingRow && $bookingRow->length && $bookingRow->width && $bookingRow->height) {
                $quantity = (int) ($bookingRow->quantity ?? 1);
                $totalWeight = (float) ($bookingRow->weight ?? 0);
                $weightPerItem = $quantity > 0 ? $totalWeight / $quantity : 0;

                $unitName = $bookingRow->measurementUnit?->measurement_unit_abbreviation ?? 'cm';
                $widthM = $this->convertToMeters($bookingRow->width, $unitName);
                $heightM = $this->convertToMeters($bookingRow->height, $unitName);
                $lengthM = $this->convertToMeters($bookingRow->length, $unitName);

                $receiptId = $receipt->cargo_receipt_id;
                $savedRows = $savedPlacements->get($receiptId);
                $savedTotal = $savedRows ? $savedRows->sum('unit_count') : 0;

                // Always expand to the full booking quantity so that previously-unpacked
                // items are not silently dropped on page reload.
                // Items with a saved hatch assignment are locked to that hatch (hatch_id set).
                // Remaining items (quantity > savedTotal) are packed freely (hatch_id null).
                $itemBase = [
                    'receipt_id' => $receiptId,
                    'booking_ref' => $receipt->booking_ref_no,
                    'voyage_id' => $voyageId,
                    'vessel_id' => $voyage->vessel_id,
                    'width' => $widthM,
                    'height' => $heightM,
                    'depth' => $lengthM,
                    'weight' => $weightPerItem,
                    'quantity' => 1,
                    'description' => $bookingRow->cargoItem->cargo_item_description ?? 'Cargo Item',
                    'is_breakable' => (bool) ($bookingRow->cargoItem->is_breakable ?? false),
                    'floor_only' => (bool) ($bookingRow->cargoItem->floor_only ?? false),
                    'is_stackable' => (bool) ($bookingRow->cargoItem->is_stackable ?? true),
                    'original_unit' => $unitName,
                    'original_dims' => "{$bookingRow->length} × {$bookingRow->width} × {$bookingRow->height}",
                ];

                $itemIndex = 0;
                // First: re-expand saved (placed) items with their locked hatch assignment.
                if ($savedRows && $savedTotal > 0) {
                    foreach ($savedRows as $savedRow) {
                        for ($i = 0; $i < (int) $savedRow->unit_count; $i++) {
                            $cargoData[] = array_merge($itemBase, [
                                'id' => (string) $receiptId . '_' . $itemIndex++,
                                'hatch_id' => $savedRow->hatch_id,
                            ]);
                        }
                    }
                }
                // Then: append any remaining items (previously unpacked) as free-assign.
                for ($i = $itemIndex; $i < $quantity; $i++) {
                    $cargoData[] = array_merge($itemBase, [
                        'id' => (string) $receiptId . '_' . $i,
                        'hatch_id' => null,
                    ]);
                }
            }
        }

        // Debug: Log what's being sent
        \Log::info('Packing Data - Total Hatches: ' . count($hatchesData));
        \Log::info('Packing Data - Cargo Items Count: ' . count($cargoData));

        // True when every item has a saved hatch assignment — no re-save needed.
        $allSaved = count($cargoData) > 0 && collect($cargoData)->every(fn($item) => $item['hatch_id'] !== null);

        return response()->json([
            'voyage' => [
                'id' => $voyage->voyage_id,
                'code' => $voyage->voyage_code,
                'vessel' => $voyage->vessel->vessel_name,
            ],
            'hatches' => $hatchesData,
            'cargo' => $cargoData, // Items carry hatch_id when saved, null for new bookings
            'allSaved' => $allSaved, // JS uses this to skip re-saving unchanged placements
        ])->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
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

    /**
     * Save placement results - updates cargo_receipt.hatch_id for each packed item
     */
    public function savePlacement(Request $request)
    {
        $request->validate([
            'voyage_id' => 'required|exists:voyage,voyage_id',
            'placements' => 'required|array', // Array of {receiptId, hatchId}
        ]);

        $voyageId = $request->voyage_id;
        $placements = $request->placements;

        // Debug logging
        \Log::info('savePlacement called with ' . count($placements) . ' placements:');
        foreach ($placements as $p) {
            \Log::info('  ReceiptId: ' . $p['receiptId'] . ', HatchId: ' . $p['hatchId'] . ', Weight: ' . ($p['weight'] ?? 'N/A'));
        }

        try {
            // Aggregate per-unit placements → per-receipt per-hatch totals
            // Each packed item carries weightPerItem; multiple items from the same receipt
            // may land in different hatches when the booking is split.
            $byReceiptHatch = []; // [receiptId][hatchId] => ['weight' => float, 'units' => int]
            foreach ($placements as $placement) {
                $receiptId = $placement['receiptId'] ?? null;
                $hatchId = $placement['hatchId'] ?? null;
                $weight = (float) ($placement['weight'] ?? 0);
                if (!$receiptId || !$hatchId)
                    continue;
                $byReceiptHatch[$receiptId][$hatchId]['weight'] = ($byReceiptHatch[$receiptId][$hatchId]['weight'] ?? 0) + $weight;
                $byReceiptHatch[$receiptId][$hatchId]['units'] = ($byReceiptHatch[$receiptId][$hatchId]['units'] ?? 0) + 1;
            }

            // Clear previous placement records for this voyage
            $receiptIds = array_keys($byReceiptHatch);
            \DB::table('cargo_hatch_placement')
                ->where('voyage_id', $voyageId)
                ->whereIn('cargo_receipt_id', $receiptIds)
                ->delete();

            // Write accurate split weights into cargo_hatch_placement
            foreach ($byReceiptHatch as $receiptId => $hatches) {
                // Primary hatch = highest unit count (for cargo_receipt.hatch_id)
                $primaryHatch = array_key_first($hatches);
                $maxUnits = 0;
                foreach ($hatches as $hatchId => $data) {
                    if ($data['units'] > $maxUnits) {
                        $maxUnits = $data['units'];
                        $primaryHatch = $hatchId;
                    }
                    \DB::table('cargo_hatch_placement')->insert([
                        'voyage_id' => $voyageId,
                        'hatch_id' => $hatchId,
                        'cargo_receipt_id' => $receiptId,
                        'weight_kg' => $data['weight'],
                        'unit_count' => $data['units'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
                // Store majority hatch in cargo_receipt for reporting compatibility
                CargoReceipt::where('cargo_receipt_id', $receiptId)
                    ->where('voyage_id', $voyageId)
                    ->update(['hatch_id' => $primaryHatch]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Placement saved successfully',
                'count' => count($placements),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error saving placement: ' . $e->getMessage(),
            ], 500);
        }
    }
}
