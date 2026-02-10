<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Voyage;

$voyage = Voyage::with(['vessel.hatches'])->find(1);

echo "=== Voyage 1 Vessel Information ===\n";
echo "Vessel: " . ($voyage->vessel->vessel_name ?? 'Unknown') . "\n";
echo "Total Hatches: " . $voyage->vessel->hatches->count() . "\n\n";

foreach ($voyage->vessel->hatches as $hatch) {
    $capacityTons = $hatch->hatch_weight_capacity ?? 0;
    $capacityKg = $capacityTons * 1000;
    $threshold60 = ($capacityKg * 0.6);
    echo "Hatch: {$hatch->hatch_label}\n";
    echo "  Capacity: {$capacityKg}kg ({$capacityTons} tons)\n";
    echo "  60% Threshold: {$threshold60}kg\n";
    echo "  Dimensions: {$hatch->hatch_width}m × {$hatch->hatch_height}m × {$hatch->hatch_length}m\n\n";
}
