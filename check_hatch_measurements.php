<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Voyage;
use App\Models\Vessel;
use App\Models\Hatch;

// Check voyage 1
$voyage = Voyage::find(1);
if (!$voyage) {
    echo "Voyage 1 not found\n";
    exit;
}

echo "=== Voyage 1 ===\n";
echo "Vessel ID: {$voyage->vessel_id}\n";
echo "Vessel Name: {$voyage->vessel->vessel_name}\n\n";

$hatches = $voyage->vessel->hatches;
echo "=== Hatches for Vessel ===\n";
echo "Total Hatches: " . $hatches->count() . "\n";
foreach ($hatches as $hatch) {
    echo "Hatch ID: {$hatch->hatch_id}\n";
    echo "  Label: {$hatch->hatch_label}\n";
    echo "  Width (hatch_width): {$hatch->hatch_width}m\n";
    echo "  Height (hatch_height): {$hatch->hatch_height}m\n";
    echo "  Depth/Length (hatch_length): {$hatch->hatch_length}m\n";
    echo "  Max Weight (hatch_weight_capacity): {$hatch->hatch_weight_capacity} tons\n";
    echo "  Volume: " . ($hatch->hatch_width * $hatch->hatch_height * $hatch->hatch_length) . " m³\n\n";
}

// Check raw database
echo "=== Raw Hatch Table Data ===\n";
$rawHatches = \Illuminate\Support\Facades\DB::table('hatch')->whereIn('vessel_id', [$voyage->vessel_id])->get();
foreach ($rawHatches as $h) {
    echo "Hatch ID: {$h->hatch_id}, Vessel: {$h->vessel_id}\n";
    echo "  Width: {$h->hatch_width}, Height: {$h->hatch_height}, Length: {$h->hatch_length}\n";
}

// Call the API endpoint
echo "\n=== API Response (getPackingData) ===\n";
$controller = new \App\Http\Controllers\CargoAutoPlacementController();
$request = new \Illuminate\Http\Request(['voyage_id' => 1]);
$response = $controller->getPackingData($request);
$data = json_decode($response->getContent(), true);

echo "Hatches returned by API:\n";
foreach ($data['hatches'] as $h) {
    echo "Hatch ID: {$h['id']}, Label: {$h['label']}\n";
    echo "  Width: {$h['width']}m, Height: {$h['height']}m, Depth: {$h['depth']}m\n";
    echo "  Volume: " . ($h['width'] * $h['height'] * $h['depth']) . " m³\n";
    echo "  Max Weight: {$h['maxWeight']}\n\n";
}
