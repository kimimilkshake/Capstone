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
        } elseif (strpos($unitLower, 'in') !== false || strpos($unitLower, 'inch') !== false) {
            return (float) $value / 39.3701; // inches to m
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

            // Prepare cargo items with unit conversion
            $cargoItems = [];
            foreach ($cargoBookings as $cargo) {
                if ($cargo->length && $cargo->width && $cargo->height) {
                    // Get measurement unit (default: cm)
                    $unitName = $cargo->measurementUnit?->measurement_unit_abbreviation ?? 'cm';

                    // Convert dimensions to meters
                    $widthM = self::convertToMeters($cargo->width, $unitName);
                    $heightM = self::convertToMeters($cargo->height, $unitName);
                    $lengthM = self::convertToMeters($cargo->length, $unitName);

                    $cargoItems[] = [
                        'id' => $cargo->cargo_booking_id,
                        'w' => (float) $widthM,
                        'h' => (float) $heightM,
                        'd' => (float) $lengthM,
                        'weight' => (float) ($cargo->weight ?? 0),
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

            // All cargo items are available for manual placement
            return [
                'success' => true,
                'message' => 'Cargo items are ready for manual placement review (dimensions converted to meters).',
                'packedItems' => $cargoItems,
                'unpackedItems' => [],
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
