<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Helpers\CotPlanHelper;

// Test 1: Check if helper works
echo "=== Testing CotPlanHelper ===\n";
$vesselPlan = CotPlanHelper::getVesselPlan(1);
if ($vesselPlan) {
    echo "✓ Vessel 1 plan loaded successfully\n";
    echo "Accommodations: " . count($vesselPlan['accommodations']) . "\n";
    foreach ($vesselPlan['accommodations'] as $acc) {
        echo "  - ID: {$acc['accommodation_id']}, Name: {$acc['accommodation_name']}\n";
    }
} else {
    echo "✗ Failed to load vessel plan\n";
}

// Test 2: Test the API
echo "\n=== Testing API Response ===\n";
$request = new \Illuminate\Http\Request([
    'voyage_id' => '1'
]);

$controller = new \App\Http\Controllers\BookingController();
try {
    $response = $controller->getAvailableCotsByAccommodation($request);
    $data = json_decode($response->getContent(), true);

    if ($data['success']) {
        echo "✓ API response successful\n";
        echo "Accommodations returned: " . count($data['accommodations']) . "\n";
        foreach ($data['accommodations'] as $acc) {
            echo "  - {$acc['accommodation_name']}: " . count($acc['available_cots']) . " COTs available\n";
            if (count($acc['available_cots']) > 0) {
                echo "    First COT: " . json_encode($acc['available_cots'][0]) . "\n";
            }
        }
    } else {
        echo "✗ API error: " . ($data['message'] ?? 'Unknown') . "\n";
    }
} catch (\Exception $e) {
    echo "✗ Exception: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
