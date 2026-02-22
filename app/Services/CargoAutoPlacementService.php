<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Voyage;
use App\Models\CargoBooking;

class CargoAutoPlacementService
{
    /**
     * Validate if cargo items from a booking can fit in the voyage's hatches
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

            // Fetch cargo bookings
            $cargoBookings = CargoBooking::whereIn('cargo_booking_id', $cargoBookingIds)->get();
            
            if ($cargoBookings->isEmpty()) {
                return [
                    'success' => false,
                    'message' => 'No cargo bookings found.',
                    'packedItems' => [],
                    'unpackedItems' => []
                ];
            }

            // Prepare cargo items for API
            $cargoItems = [];
            foreach ($cargoBookings as $cargo) {
                if ($cargo->length && $cargo->width && $cargo->height) {
                    $cargoItems[] = [
                        'id' => $cargo->cargo_booking_id,
                        'w' => (float) $cargo->width,
                        'h' => (float) $cargo->height,
                        'd' => (float) $cargo->length,
                        'weight' => (float) ($cargo->weight ?? 0),
                        'q' => (int) ($cargo->quantity ?? 1),
                        'item_name' => $cargo->cargoItem->cargo_item_description ?? 'Unknown',
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

            // Call 3DBinPacking API for each hatch
            $username = config('services.3dbin.username') ?? env('3DBIN_USERNAME');
            $apiKey = config('services.3dbin.api_key') ?? env('3DBIN_API_KEY');

            if (empty($username) || empty($apiKey)) {
                Log::warning('3DBinPacking credentials not configured');
                // Fallback: allow placement if API not configured
                return [
                    'success' => true,
                    'message' => 'Auto-placement validation skipped (API not configured)',
                    'packedItems' => $cargoItems,
                    'unpackedItems' => [],
                    'skipValidation' => true
                ];
            }

            $allPackedItems = [];
            $remainingItems = $cargoItems;

            foreach ($voyage->vessel->hatches as $hatch) {
                if (empty($remainingItems)) {
                    break;
                }

                $result = self::callBinPackingAPI($username, $apiKey, $hatch, $remainingItems);

                if (isset($result['error'])) {
                    Log::warning('Hatch packing error: ' . $result['error']);
                    continue;
                }

                if (!empty($result['packedItems'])) {
                    $allPackedItems = array_merge($allPackedItems, $result['packedItems']);
                }

                if (!empty($result['unpackedItems'])) {
                    $remainingItems = $result['unpackedItems'];
                } else {
                    $remainingItems = [];
                }
            }

            if (empty($remainingItems)) {
                return [
                    'success' => true,
                    'message' => 'All cargo items can fit in available hatches',
                    'packedItems' => $allPackedItems,
                    'unpackedItems' => []
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Some cargo items cannot fit in available hatches. ' . count($remainingItems) . ' items require additional space.',
                    'packedItems' => $allPackedItems,
                    'unpackedItems' => $remainingItems
                ];
            }

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

    /**
     * Call 3DBinPacking API for a single hatch
     */
    private static function callBinPackingAPI($username, $apiKey, $hatch, $cargoItems)
    {
        try {
            $bins = [
                [
                    'number' => 1,
                    'name' => $hatch->hatch_label,
                    'x' => $hatch->hatch_width,
                    'y' => $hatch->hatch_height,
                    'z' => $hatch->hatch_length,
                    'max_weight' => $hatch->hatch_weight_capacity ? $hatch->hatch_weight_capacity * 1000 : 600000, // Convert tons to kg
                ]
            ];

            $items = [];
            foreach ($cargoItems as $item) {
                $items[] = [
                    'name' => $item['item_name'],
                    'width' => $item['w'],
                    'height' => $item['h'],
                    'depth' => $item['d'],
                    'weight' => $item['weight'],
                    'quantity' => $item['q'],
                    'id' => $item['id'],
                ];
            }

            $payload = [
                'bins' => $bins,
                'items' => $items,
                'algorithm' => 1,
            ];

            $response = Http::timeout(15)
                ->withBasicAuth($username, $apiKey)
                ->post('https://api.3dbinpacking.com/api/put_items', $payload);

            if (!$response->successful()) {
                return ['error' => 'API request failed: ' . $response->status()];
            }

            $responseData = $response->json();

            if (!isset($responseData['response']) || empty($responseData['response'])) {
                return ['error' => 'Invalid API response'];
            }

            $packedItems = [];
            $unpackedItems = [];

            foreach ($responseData['response'] as $binResult) {
                if (!empty($binResult['items'])) {
                    foreach ($binResult['items'] as $packedItem) {
                        $packedItems[] = [
                            'id' => $packedItem['id'],
                            'name' => $packedItem['name'],
                            'binId' => $binResult['number'],
                            'x' => $packedItem['pivot'][0],
                            'y' => $packedItem['pivot'][1],
                            'z' => $packedItem['pivot'][2],
                            'fitted' => true,
                        ];
                    }
                }

                if (!empty($binResult['unfit_items'])) {
                    foreach ($binResult['unfit_items'] as $unpackedItem) {
                        $unpackedItems[] = [
                            'id' => $unpackedItem['id'],
                            'name' => $unpackedItem['name'],
                        ];
                    }
                }
            }

            return [
                'packedItems' => $packedItems,
                'unpackedItems' => $unpackedItems,
            ];

        } catch (\Exception $e) {
            Log::error('3DBinPacking API error: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }
}
