<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Voyage;
use Illuminate\Http\Request;
use App\Http\Controllers\CargoAutoPlacementController;

try {
    // Get a voyage
    $voyage = Voyage::where('voyage_status', '!=', 'Completed')
        ->with('vessel.hatches', 'cargoReceipts.cargoBooking.cargoItem.measurementUnit')
        ->first();
    
    if (!$voyage) {
        echo "No voyages found\n";
        exit(1);
    }
    
    echo "=== TESTING PACKING DATA API ===\n";
    echo "Voyage: {$voyage->voyage_code}\n";
    echo "Vessel: {$voyage->vessel->vessel_name}\n";
    echo "Hatches: " . $voyage->vessel->hatches->count() . "\n";
    
    // Call the API
    $request = Request::create('/api/staff_cargo/packing-data', 'GET', ['voyage_id' => $voyage->voyage_id]);
    $controller = new CargoAutoPlacementController();
    $response = $controller->getPackingData($request);
    $data = json_decode($response->getContent(), true);
    
    echo "\nAPI Response:\n";
    echo "- Voyage: " . $data['voyage']['code'] . "\n";
    echo "- Hatches: " . count($data['hatches']) . "\n";
    echo "- Cargo items: " . count($data['cargo']) . "\n";
    
    if (!empty($data['cargo'])) {
        echo "\nFirst 3 cargo items:\n";
        foreach (array_slice($data['cargo'], 0, 3) as $cargo) {
            echo "  - {$cargo['description']}: {$cargo['width']} × {$cargo['height']} × {$cargo['depth']} m\n";
            echo "    Weight: {$cargo['weight']} kg\n";
            echo "    Unit: {$cargo['original_unit']}\n";
        }
    } else {
        echo "NO CARGO ITEMS IN RESPONSE!\n";
    }
    
    if (!empty($data['hatches'])) {
        echo "\nHatches:\n";
        foreach ($data['hatches'] as $hatch) {
            echo "  - {$hatch['label']}: {$hatch['width']} × {$hatch['height']} × {$hatch['depth']} m\n";
        }
    }
    
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
