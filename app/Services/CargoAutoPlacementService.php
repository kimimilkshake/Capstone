<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\Voyage;
use App\Models\CargoBooking;
use App\Models\CargoReceipt;

class CargoAutoPlacementService
{
    /**
     * Helper: Convert dimensions from booking unit to meters
     */
    private static function convertToMeters($value, $unitName = 'cm')
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
    /**
     * Validate if cargo items from a booking can fit in the voyage's hatches
     * Note: 3DBinPacking API integration has been removed.
     * Dimensions are converted from cm/inches to meters for visualization.
     *
     * @param int $voyageId
     * @param array $cargoBookingIds - Array of cargo_booking IDs to validate
     * @return array ['success' => bool, 'message' => string, 'packedItems' => array, 'unpackedItems' => array]
     */
    public static function validateCargoPlacement($voyageId, $cargoBookingIds)
    {
        try {
            $voyage = Voyage::with(['vessel.hatches'])->findOrFail($voyageId);

            if (!$voyage->vessel->hatches || $voyage->vessel->hatches->isEmpty()) {
                return [
                    'success' => false,
                    'message' => 'No hatches found for this vessel.',
                    'packedItems' => [],
                    'unpackedItems' => []
                ];
            }

            // Fetch cargo bookings with measurement unit
            $cargoBookings = CargoBooking::with(['measurementUnit', 'cargoItem'])->whereIn('cargo_booking_id', $cargoBookingIds)->get();

            if ($cargoBookings->isEmpty()) {
                return [
                    'success' => false,
                    'message' => 'No cargo bookings found.',
                    'packedItems' => [],
                    'unpackedItems' => []
                ];
            }

            // Calculate total cargo weight
            $totalCargoWeight = 0;
            $cargoItems = [];

            foreach ($cargoBookings as $cargo) {
                if ($cargo->length && $cargo->width && $cargo->height) {
                    // Get measurement unit (default: cm)
                    $unitName = $cargo->measurementUnit?->measurement_unit_abbreviation ?? 'cm';

                    // Convert dimensions to meters
                    $widthM = self::convertToMeters($cargo->width, $unitName);
                    $heightM = self::convertToMeters($cargo->height, $unitName);
                    $lengthM = self::convertToMeters($cargo->length, $unitName);

                    $cargoWeight = (float) ($cargo->weight ?? 0);
                    $qty = max(1, (int) ($cargo->quantity ?? 1));
                    // weight in DB is the TOTAL for the booking line (all items combined)
                    $totalCargoWeight += $cargoWeight;

                    $cargoItems[] = [
                        'id' => $cargo->cargo_booking_id,
                        'w' => (float) $widthM,
                        'h' => (float) $heightM,
                        'd' => (float) $lengthM,
                        'weight' => $cargoWeight,          // booking-line total
                        'weight_each' => $cargoWeight / $qty,   // per single item
                        'q' => $qty,
                        'item_name' => $cargo->cargoItem->cargo_item_description ?? 'Unknown',
                        'is_breakable' => (bool) ($cargo->cargoItem->is_breakable ?? false),
                        'floor_only' => (bool) ($cargo->cargoItem->floor_only ?? false),
                        'original_unit' => $unitName,
                        'original_dims' => "{$cargo->length} × {$cargo->width} × {$cargo->height}",
                    ];
                }
            }

            if (empty($cargoItems)) {
                return [
                    'success' => false,
                    'message' => 'No cargo items with valid dimensions found.',
                    'packedItems' => [],
                    'unpackedItems' => $cargoItems
                ];
            }

            // Build hatch capacity map (weight + volume)
            $hatches = $voyage->vessel->hatches;
            $totalAvailableWeight = 0;
            $totalAvailableVolume = 0;
            $hatchCapacities = [];

            foreach ($hatches as $hatch) {
                $maxWeightKg = (float) $hatch->hatch_capacity_per_hold * 1000; // tons → kg

                // Hatch physical volume in m³ (dimensions stored in metres)
                // Subtract crew catwalk volume (0.6 m walkways on all 4 walls).
                // Net packable box: (W-1.2) × (L-1.2) × H  (catwalks on ±X and ±Z)
                $catwalkW = 0.6;
                $hw = (float) $hatch->hatch_width;
                $hh = (float) $hatch->hatch_height;
                $hl = (float) $hatch->hatch_length;
                $packableW = max(0, $hw - 2 * $catwalkW);
                $packableL = max(0, $hl - 2 * $catwalkW);
                $hatchVolume = $packableW * $hh * $packableL;

                // Get already-used weight for this hatch on this voyage
                $usedWeight = (float) DB::table('cargo_receipt')
                    ->join('cargo_booking', 'cargo_receipt.cargo_booking_id', '=', 'cargo_booking.cargo_booking_id')
                    ->where('cargo_receipt.hatch_id', $hatch->hatch_id)
                    ->where('cargo_receipt.voyage_id', $voyageId)
                    ->sum('cargo_booking.weight');

                // Used volume: load existing receipts with cargo bookings and convert units properly
                $usedVolume = 0.0;
                $existingReceipts = CargoReceipt::where('hatch_id', $hatch->hatch_id)
                    ->where('voyage_id', $voyageId)
                    ->with('cargoBooking.measurementUnit')
                    ->get();
                foreach ($existingReceipts as $receipt) {
                    $cb = $receipt->cargoBooking;
                    if ($cb && $cb->length && $cb->width && $cb->height) {
                        $unit = $cb->measurementUnit?->measurement_unit_abbreviation ?? 'cm';
                        $wM2 = self::convertToMeters($cb->width, $unit);
                        $hM2 = self::convertToMeters($cb->height, $unit);
                        $lM2 = self::convertToMeters($cb->length, $unit);
                        $usedVolume += $wM2 * $hM2 * $lM2 * max(1, (int) ($cb->quantity ?? 1));
                    }
                }

                $availableWeight = max(0, $maxWeightKg - $usedWeight);
                $availableVolume = max(0, $hatchVolume - $usedVolume);

                $totalAvailableWeight += $availableWeight;
                $totalAvailableVolume += $availableVolume;

                $hatchCapacities[$hatch->hatch_id] = [
                    'label' => $hatch->hatch_label,
                    'maxWeight' => $maxWeightKg,
                    'currentWeight' => $usedWeight,
                    'availableWeight' => $availableWeight,
                    'totalVolume' => $hatchVolume,
                    'usedVolume' => $usedVolume,
                    'availableVolume' => $availableVolume,
                ];
            }

            // ── Weight check ─────────────────────────────────────────────
            if ($totalCargoWeight > $totalAvailableWeight) {
                $shortfall = $totalCargoWeight - $totalAvailableWeight;
                return [
                    'success' => false,
                    'message' => "Weight capacity exceeded. New cargo: {$totalCargoWeight}kg — Available: {$totalAvailableWeight}kg — Shortfall: " . round($shortfall, 1) . "kg. These items cannot be loaded on this voyage.",
                    'packedItems' => [],
                    'unpackedItems' => $cargoItems,
                    'hatchCapacities' => $hatchCapacities,
                ];
            }

            // ── Volume / space check ──────────────────────────────────────
            $totalCargoVolume = array_sum(array_map(
                fn($i) => $i['w'] * $i['h'] * $i['d'] * $i['q'],
                $cargoItems
            ));

            if ($totalCargoVolume > $totalAvailableVolume) {
                $shortfall = $totalCargoVolume - $totalAvailableVolume;
                return [
                    'success' => false,
                    'message' => "Insufficient hatch space. New cargo volume: " . round($totalCargoVolume, 2) . "m³ — Available: " . round($totalAvailableVolume, 2) . "m³ — Shortfall: " . round($shortfall, 2) . "m³. These items cannot physically fit.",
                    'packedItems' => [],
                    'unpackedItems' => $cargoItems,
                    'hatchCapacities' => $hatchCapacities,
                ];
            }

            // ── Per-item dimension check ──────────────────────────────────
            // Every single item must physically fit inside at least one hatch's
            // packable interior (after subtracting 0.6 m catwalks on all 4 walls).
            // • Non-breakable items: packer can auto-rotate (6 orientations checked).
            // • Breakable items: must keep original orientation — only 1 orientation checked.
            // • floor_only items: packer never rotates them — only 1 orientation checked.
            $oversizedItems = [];
            foreach ($cargoItems as $item) {
                $w = $item['w'];
                $h = $item['h'];
                $d = $item['d'];
                $noRotate = $item['is_breakable'] || $item['floor_only'];
                $orientations = $noRotate
                    ? [[$w, $h, $d]]
                    : [
                        [$w, $h, $d],
                        [$w, $d, $h],
                        [$h, $w, $d],
                        [$h, $d, $w],
                        [$d, $w, $h],
                        [$d, $h, $w],
                    ];
                $fitsInAnyHatch = false;
                $cw = 0.6;
                foreach ($hatches as $hatch) {
                    // Usable interior after catwalks on all 4 walls
                    $hw = max(0, (float) $hatch->hatch_width - 2 * $cw);
                    $hh = (float) $hatch->hatch_height;
                    $hl = max(0, (float) $hatch->hatch_length - 2 * $cw);
                    foreach ($orientations as [$iw, $ih, $id]) {
                        if ($iw <= $hw + 0.01 && $ih <= $hh + 0.01 && $id <= $hl + 0.01) {
                            $fitsInAnyHatch = true;
                            break 2;
                        }
                    }
                }
                if (!$fitsInAnyHatch) {
                    $oversizedItems[] = $item;
                }
            }
            if (!empty($oversizedItems)) {
                $names = implode(', ', array_map(
                    fn($i) => "{$i['item_name']} ({$i['w']}×{$i['h']}×{$i['d']}m)",
                    $oversizedItems
                ));
                return [
                    'success' => false,
                    'message' => "Item(s) too large to fit in any hatch: {$names}. Check item dimensions against hatch dimensions.",
                    'packedItems' => [],
                    'unpackedItems' => $oversizedItems,
                    'hatchCapacities' => $hatchCapacities,
                ];
            }

            // ── Per-item weight check ─────────────────────────────────────
            // The packer distributes items freely across all hatches, so check
            // each item's weight_each against the TOTAL available weight across
            // all hatches combined — not per individual hatch.
            $itemsThatCantFit = [];
            foreach ($cargoItems as $item) {
                if ($item['weight_each'] > $totalAvailableWeight) {
                    $itemsThatCantFit[] = $item;
                }
            }

            if (!empty($itemsThatCantFit)) {
                $names = implode(', ', array_map(fn($i) => $i['item_name'], $itemsThatCantFit));
                return [
                    'success' => false,
                    'message' => "No single hatch has enough remaining weight for: {$names}. Reduce the booking quantity or bump to the next voyage.",
                    'packedItems' => [],
                    'unpackedItems' => $itemsThatCantFit,
                    'hatchCapacities' => $hatchCapacities,
                ];
            }

            // ── Floor area check (floor_only items) ───────────────────────
            // floor_only items (vehicles, livestock, machinery) cannot be stacked — they
            // need actual deck space (floor area), not just volumetric space.
            // Check: sum of all floor_only item footprints ≤ total packable hatch floor area
            // (after subtracting 0.6 m crew catwalks on all 4 walls per hatch).
            $floorOnlyItems = array_filter($cargoItems, fn($i) => $i['floor_only']);
            if (!empty($floorOnlyItems)) {
                $catwalkW2 = 0.6;
                // Packable floor area per hatch = (W-1.2) × (L-1.2)
                $totalHatchFloorArea = array_sum(array_map(
                    fn($h) => max(0, (float) $h->hatch_width - 2 * $catwalkW2)
                    * max(0, (float) $h->hatch_length - 2 * $catwalkW2),
                    $hatches->all()
                ));

                // Sum footprint (w × d) × qty for each floor_only item
                $neededFloorArea = array_sum(array_map(
                    fn($i) => $i['w'] * $i['d'] * $i['q'],
                    $floorOnlyItems
                ));

                if ($neededFloorArea > $totalHatchFloorArea + 0.01) {
                    $shortfall = round($neededFloorArea - $totalHatchFloorArea, 2);
                    $names = implode(', ', array_map(fn($i) => $i['item_name'], $floorOnlyItems));
                    return [
                        'success' => false,
                        'message' => "Insufficient deck floor space for floor-only items ({$names}). Required: " . round($neededFloorArea, 2) . "m² — Available: " . round($totalHatchFloorArea, 2) . "m² — Shortfall: {$shortfall}m².",
                        'packedItems' => [],
                        'unpackedItems' => array_values($floorOnlyItems),
                        'hatchCapacities' => $hatchCapacities,
                    ];
                }
            }

            // ── All checks passed ─────────────────────────────────────────
            return [
                'success' => true,
                'message' => "Placement validated. Total cargo: {$totalCargoWeight}kg / " . round($totalCargoVolume, 2) . "m³. Available: {$totalAvailableWeight}kg / " . round($totalAvailableVolume, 2) . "m³.",
                'packedItems' => $cargoItems,
                'unpackedItems' => [],
                'hatchCapacities' => $hatchCapacities,
                'skipValidation' => true,
            ];

        } catch (\Exception $e) {
            Log::error('Cargo placement validation error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error validating cargo placement: ' . $e->getMessage(),
                'packedItems' => [],
                'unpackedItems' => []
            ];
        }
    }


}
