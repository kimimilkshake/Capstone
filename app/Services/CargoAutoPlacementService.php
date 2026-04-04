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

            // ── Per-hatch greedy weight simulation ──────────────────────────
            // Expand each booking line into individual physical items and
            // simulate placing them into hatches in order (hatch 1 first, then 2,
            // etc.), filling heaviest items first. This mirrors the JS packer's
            // sequential fill and enforces each hatch's individual weight limit.
            $physicalItems = [];
            foreach ($cargoItems as $item) {
                for ($i = 0; $i < $item['q']; $i++) {
                    $physicalItems[] = [
                        'name' => $item['item_name'],
                        'weight' => $item['weight_each'],
                    ];
                }
            }
            usort($physicalItems, fn($a, $b) => $b['weight'] <=> $a['weight']); // heaviest first

            // Build hatch available-weight map in hatch_id ascending order
            // so iteration always starts from hatch 1 → hatch 2 → …
            $hatchAvail = [];
            ksort($hatchCapacities); // ensure hatch 1 is first
            foreach ($hatchCapacities as $hatchId => $cap) {
                $hatchAvail[$hatchId] = $cap['availableWeight'];
            }

            $weightOverflowNames = [];
            foreach ($physicalItems as $physItem) {
                // Always try hatches in their natural order (hatch 1 first, then 2, etc.)
                // matching how the JS packer sequentially fills from the first hatch.
                $placed = false;
                foreach ($hatchAvail as $hatchId => $avail) {
                    if ($physItem['weight'] <= $avail + 0.001) { // 1 g tolerance
                        $hatchAvail[$hatchId] -= $physItem['weight'];
                        $placed = true;
                        break;
                    }
                }
                if (!$placed) {
                    $weightOverflowNames[] = $physItem['name'];
                }
            }

            if (!empty($weightOverflowNames)) {
                $uniqueNames = implode(', ', array_unique($weightOverflowNames));
                return [
                    'success' => false,
                    'message' => "Weight capacity exceeded per hatch for: {$uniqueNames}. Each hatch has its own weight limit — these items cannot fit within any single hatch's remaining capacity. Reduce the quantity or move the booking to a different voyage.",
                    'packedItems' => [],
                    'unpackedItems' => $cargoItems,
                    'hatchCapacities' => $hatchCapacities,
                ];
            }

            // ── Floor area check (floor_only items) ───────────────────────
            // floor_only items cannot be stacked and must not land in the 0.6 m
            // catwalks along all 4 hatch walls.
            //
            // Two checks run together:
            //
            // 1. ZONE-SLOT CHECK (grid capacity, always applies):
            //    The packer splits each hatch into LEFT / RIGHT halves for weight
            //    balance.  Real usable slots per hatch =
            //      floor(packableW/2 / itemW) × floor(packableL / itemD) × 2 zones
            //    Raw area maths is too loose and ignores wasted grid-edge space.
            //
            // 2. EXISTING-AREA CHECK (only when other bookings already occupy space):
            //    Subtract the actual floor area used by confirmed floor_only receipts
            //    on this voyage that are NOT in the current batch.  This correctly
            //    handles mixed-item-size loads where slot approximation would be coarse.
            $floorOnlyItems = array_filter($cargoItems, fn($i) => $i['floor_only']);
            if (!empty($floorOnlyItems)) {
                $cw = 0.6; // metres — catwalk on each of the 4 walls

                // ── Gather existing floor_only area on this voyage ────────
                $newBookingIds = $cargoBookingIds;
                $existingFloorReceipts = CargoReceipt::where('voyage_id', $voyageId)
                    ->whereNotIn('cargo_booking_id', $newBookingIds)
                    ->with(['cargoBooking.measurementUnit', 'cargoBooking.cargoItem'])
                    ->get();

                $existingFloorArea = 0.0;
                foreach ($existingFloorReceipts as $er) {
                    $cb = $er->cargoBooking;
                    if (!$cb || !$cb->cargoItem || !$cb->cargoItem->floor_only)
                        continue;
                    if (!$cb->length || !$cb->width)
                        continue;
                    $unit = $cb->measurementUnit?->measurement_unit_abbreviation ?? 'cm';
                    $wM   = self::convertToMeters($cb->width, $unit);
                    $dM   = self::convertToMeters($cb->length, $unit);
                    $qty  = max(1, (int) ($cb->quantity ?? 1));
                    $existingFloorArea += $wM * $dM * $qty;
                }

                // Total packable floor area across all hatches
                $totalPackableFloorArea = 0.0;
                foreach ($hatches as $hatch) {
                    $totalPackableFloorArea +=
                        max(0, (float) $hatch->hatch_width  - 2 * $cw)
                        * max(0, (float) $hatch->hatch_length - 2 * $cw);
                }

                foreach ($floorOnlyItems as $item) {
                    if ($item['w'] <= 0 || $item['d'] <= 0) continue;

                    $itemFootprint = $item['w'] * $item['d'];
                    $neededArea    = $itemFootprint * $item['q'];

                    // ── Check 1: zone-slot grid capacity ─────────────────
                    $totalSlots = 0;
                    foreach ($hatches as $hatch) {
                        $packW = max(0, (float) $hatch->hatch_width  - 2 * $cw);
                        $packL = max(0, (float) $hatch->hatch_length - 2 * $cw);
                        $zoneW = $packW / 2;
                        $cols  = $zoneW > 0 ? (int) floor($zoneW / $item['w']) : 0;
                        $rows  = $packL > 0 ? (int) floor($packL  / $item['d']) : 0;
                        $totalSlots += $cols * $rows * 2;
                    }
                    // Existing items occupy slots — use ceil to stay conservative
                    $usedSlots      = $itemFootprint > 0 ? (int) ceil($existingFloorArea / $itemFootprint) : 0;
                    $availableSlots = max(0, $totalSlots - $usedSlots);

                    if ($item['q'] > $availableSlots) {
                        return [
                            'success' => false,
                            'message' => "Insufficient deck space for floor-only item ({$item['item_name']}). "
                                . "Requested: {$item['q']} — "
                                . "Available grid slots: {$availableSlots} "
                                . "(total: {$totalSlots}, occupied by existing cargo: {$usedSlots}). "
                                . "Reduce the quantity or use a different voyage.",
                            'packedItems' => [],
                            'unpackedItems' => array_values($floorOnlyItems),
                            'hatchCapacities' => $hatchCapacities,
                        ];
                    }

                    // ── Check 2: raw remaining floor area ─────────────────
                    // Catches mixed-load edge cases where grid-slot maths is coarse.
                    $availableFloorArea = max(0, $totalPackableFloorArea - $existingFloorArea);
                    if ($neededArea > $availableFloorArea + 0.01) {
                        $shortfall = round($neededArea - $availableFloorArea, 2);
                        return [
                            'success' => false,
                            'message' => "Insufficient deck floor area for floor-only item ({$item['item_name']}). "
                                . "Required: " . round($neededArea, 2) . "m² — "
                                . "Already used: " . round($existingFloorArea, 2) . "m² — "
                                . "Remaining: " . round($availableFloorArea, 2) . "m² — "
                                . "Shortfall: {$shortfall}m².",
                            'packedItems' => [],
                            'unpackedItems' => array_values($floorOnlyItems),
                            'hatchCapacities' => $hatchCapacities,
                        ];
                    }
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
