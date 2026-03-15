<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Vessel;

$vessels = Vessel::with('accommodations')->get();

echo "=== ALL VESSELS ===\n\n";

foreach ($vessels as $vessel) {
    echo "Vessel ID: " . $vessel->vessel_id . "\n";
    echo "Vessel Name: " . $vessel->vessel_name . "\n";
    echo "Vessel Code: " . $vessel->vessel_code . "\n";
    echo "Total Capacity: " . $vessel->vessel_total_passenger_capacity . "\n";
    echo "Status: " . $vessel->vessel_status . "\n";
    echo "COT Plan URL: " . ($vessel->vessel_cot_plan_url ?? 'N/A') . "\n";
    echo "Accommodations:\n";

    foreach ($vessel->accommodations as $acc) {
        echo "  - ID: " . $acc->accommodation_id . "\n";
        echo "    Name: " . $acc->accommodation_name . "\n";
        echo "    Price: " . $acc->accommodation_regular_price . "\n";
        echo "    COT Range: " . $acc->accommodation_cot_range . "\n";
    }
    echo "\n---\n\n";
}
