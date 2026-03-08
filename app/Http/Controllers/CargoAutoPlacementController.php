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
        } elseif (strpos($unitLower, 'in') !== false || strpos($unitLower, 'inch') !== false) {
            return (float) $value / 39.3701; // inches to m
        } elseif (strpos($unitLower, 'm') === 0 || strpos($unitLower, 'meter') !== false) {
            return (float) $value; // already in meters
        }

        // Default: assume cm
        return (float) $value / 100;
    }

    public function show(Request $request)
    {
        // Prevent browser caching of this page
        $voyages = Voyage::with(['vessel', 'routePort'])
            ->where('voyage_status', '!=', 'Completed')
            ->orderBy('voyage_departure_date', 'desc')
            ->get();

        $selectedVoyageId = $request->input('voyage_id');
        $placementData = null;

        if ($selectedVoyageId) {
            $placementData = $this->getVoyagePlacementData($selectedVoyageId);
        }

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
     * Process cargo placement request.
     * Note: Previous 3DBinPacking API integration has been removed.
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

        $voyage = Voyage::with(['vessel.hatches', 'cargoReceipts.cargoBooking.cargoItem'])
            ->findOrFail($request->voyage_id);

        $hatches = $voyage->vessel->hatches;

        // Prepare hatches with weight information
        $hatchesData = [];
        foreach ($hatches as $hatch) {
            $maxWeight = (float) $hatch->hatch_weight_capacity;
            $hatchesData[] = [
                'id' => $hatch->hatch_id,
                'label' => $hatch->hatch_label,
                'width' => (float) $hatch->hatch_width,
                'height' => (float) $hatch->hatch_height,
                'depth' => (float) $hatch->hatch_length,
                'maxWeight' => $maxWeight,
                'maxWeightKg' => $maxWeight * 1000, // Convert tons to kg for display
                'volume' => (float) $hatch->hatch_width * $hatch->hatch_height * $hatch->hatch_length,
            ];
        }

        // Prefer confirmed bookings: one visual item per confirmed booking (assumes one cargoBooking per booking)
        $cargoData = [];
        $confirmedBookings = Booking::where('voyage_id', $voyage->voyage_id)
            ->whereRaw("LOWER(booking.booking_status) = ?", ['confirmed'])
            ->with('cargoBookings.measurementUnit', 'cargoBookings.cargoItem')
            ->get();

        foreach ($confirmedBookings as $booking) {
            $cb = $booking->cargoBookings->first();
            if ($cb && $cb->length && $cb->width && $cb->height) {
                $quantity = (int) ($cb->quantity ?? 1);
                $totalWeight = (float) ($cb->weight ?? 0);
                $weightPerItem = $quantity > 0 ? $totalWeight / $quantity : 0;

                // Get measurement unit name
                $unitName = $cb->measurementUnit?->measurement_unit_abbreviation ?? 'cm';

                // Convert dimensions to meters
                $widthM = $this->convertToMeters($cb->width, $unitName);
                $heightM = $this->convertToMeters($cb->height, $unitName);
                $lengthM = $this->convertToMeters($cb->length, $unitName);

                // Create one visual item per quantity unit
                for ($i = 0; $i < $quantity; $i++) {
                    $cargoData[] = [
                        'id' => (string) ($cb->cargo_booking_id ?? $booking->booking_ref_no) . '_' . $i,
                        'booking_ref' => $booking->booking_ref_no,
                        'width' => $widthM,
                        'height' => $heightM,
                        'depth' => $lengthM,
                        'weight' => $weightPerItem,
                        'quantity' => 1,
                        'description' => $cb->cargoItem->cargo_item_description ?? 'Cargo Item',
                        'is_breakable' => (bool) ($cb->cargoItem->is_breakable ?? false),
                        'original_unit' => $unitName,
                        'original_dims' => "{$cb->length} × {$cb->width} × {$cb->height}",
                    ];
                }
            }
        }

        // Fallback: if no confirmed bookings with cargo found, fall back to cargoReceipts as before
        if (empty($cargoData)) {
            $cargoReceipts = $voyage->cargoReceipts()->whereHas('booking', function ($q) {
                $q->whereRaw("LOWER(booking.booking_status) = ?", ['confirmed']);
            })->get();

            if ($hatches->isEmpty() || $cargoReceipts->isEmpty()) {
                return response()->json(['error' => 'Missing hatches or cargo'], 400);
            }

            foreach ($cargoReceipts as $receipt) {
                $bookingRow = CargoBooking::with('measurementUnit')->where('booking_ref_no', $receipt->booking_ref_no)->first();
                if ($bookingRow && $bookingRow->length && $bookingRow->width && $bookingRow->height) {
                    $quantity = (int) ($bookingRow->quantity ?? 1);
                    $totalWeight = (float) ($bookingRow->weight ?? 0);
                    $weightPerItem = $quantity > 0 ? $totalWeight / $quantity : 0;

                    // Get measurement unit name
                    $unitName = $bookingRow->measurementUnit?->measurement_unit_abbreviation ?? 'cm';

                    // Convert dimensions to meters
                    $widthM = $this->convertToMeters($bookingRow->width, $unitName);
                    $heightM = $this->convertToMeters($bookingRow->height, $unitName);
                    $lengthM = $this->convertToMeters($bookingRow->length, $unitName);

                    // Create one visual item per quantity unit
                    for ($i = 0; $i < $quantity; $i++) {
                        $cargoData[] = [
                            'id' => (string) $receipt->cargo_receipt_id . '_' . $i,
                            'booking_ref' => $receipt->booking_ref_no,
                            'width' => $widthM,
                            'height' => $heightM,
                            'depth' => $lengthM,
                            'weight' => $weightPerItem,
                            'quantity' => 1,
                            'description' => $receipt->cargoItem->cargo_item_description ?? 'Cargo Item',
                            'original_unit' => $unitName,
                            'original_dims' => "{$bookingRow->length} × {$bookingRow->width} × {$bookingRow->height}",
                        ];
                    }
                }
            }
        }

        return response()->json([
            'voyage' => [
                'id' => $voyage->voyage_id,
                'code' => $voyage->voyage_code,
                'vessel' => $voyage->vessel->vessel_name,
            ],
            'hatches' => $hatchesData,
            'cargo' => $cargoData,
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
}
