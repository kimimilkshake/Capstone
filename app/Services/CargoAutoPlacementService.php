<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Voyage;
use App\Models\CargoBooking;

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
                    $totalCargoWeight += $cargoWeight;

                    $cargoItems[] = [
                        'id' => $cargo->cargo_booking_id,
                        'w' => (float) $widthM,
                        'h' => (float) $heightM,
                        'd' => (float) $lengthM,
                        'weight' => $cargoWeight,
                        'q' => (int) ($cargo->quantity ?? 1),
                        'item_name' => $cargo->cargoItem->cargo_item_description ?? 'Unknown',
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

            // Check if cargo fits in available hatch capacity
            $hatches = $voyage->vessel->hatches;
            $totalAvailableCapacity = 0;
            $hatchCapacities = [];

            foreach ($hatches as $hatch) {
                $maxWeightKg = (float) $hatch->hatch_capacity_per_hold * 1000; // Convert tons to kg

                // Get current weight used in this hatch
                $currentWeight = \Illuminate\Support\Facades\DB::table('cargo_receipt')
                    ->join('cargo_booking', 'cargo_receipt.cargo_booking_id', '=', 'cargo_booking.cargo_booking_id')
                    ->where('cargo_receipt.hatch_id', $hatch->hatch_id)
                    ->where('cargo_receipt.voyage_id', $voyageId)
                    ->sum('cargo_booking.weight');

                $availableWeight = $maxWeightKg - ($currentWeight ?? 0);
                $totalAvailableCapacity += $availableWeight;

                $hatchCapacities[$hatch->hatch_id] = [
                    'label' => $hatch->hatch_label,
                    'maxWeight' => $maxWeightKg,
                    'currentWeight' => $currentWeight ?? 0,
                    'availableWeight' => $availableWeight
                ];
            }

            // Check if total cargo weight exceeds total available capacity
            if ($totalCargoWeight > $totalAvailableCapacity) {
                $shortfall = $totalCargoWeight - $totalAvailableCapacity;
                return [
                    'success' => false,
                    'message' => "Cargo exceeds available hatch capacity. Total cargo weight: {$totalCargoWeight}kg. Available capacity: {$totalAvailableCapacity}kg. Shortfall: {$shortfall}kg.",
                    'packedItems' => [],
                    'unpackedItems' => $cargoItems,
                    'hatchCapacities' => $hatchCapacities
                ];
            }

            // Check if each individual cargo item can fit in at least one hatch
            $itemsThatCantFit = [];
            foreach ($cargoItems as $item) {
                $itemWeight = $item['weight'];
                $canFitInAnyHatch = false;

                foreach ($hatchCapacities as $hatchId => $hatchInfo) {
                    if ($itemWeight <= $hatchInfo['availableWeight']) {
                        $canFitInAnyHatch = true;
                        break;
                    }
                }

                if (!$canFitInAnyHatch) {
                    $itemsThatCantFit[] = $item;
                }
            }

            if (!empty($itemsThatCantFit)) {
                return [
                    'success' => false,
                    'message' => "Insufficient weight allowance",
                    'packedItems' => [],
                    'unpackedItems' => $itemsThatCantFit,
                    'hatchCapacities' => $hatchCapacities
                ];
            }

            // All cargo weight fits - ready for placement
            return [
                'success' => true,
                'message' => "Cargo weight validation passed. Total cargo: {$totalCargoWeight}kg. Available capacity: {$totalAvailableCapacity}kg.",
                'packedItems' => $cargoItems,
                'unpackedItems' => [],
                'hatchCapacities' => $hatchCapacities,
                'skipValidation' => true
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
