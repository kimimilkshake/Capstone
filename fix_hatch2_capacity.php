<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

// Update Hatch 2 to have same capacity as Hatch 1 (0.6 tons = 600kg)
DB::table('hatch')
    ->where('hatch_id', 2)
    ->update(['hatch_weight_capacity' => 0.6]);

echo "✅ Updated Hatch 2 capacity to 0.6 tons (600kg)\n";

// Verify
$hatches = DB::table('hatch')->whereIn('hatch_id', [1, 2])->get();
foreach ($hatches as $h) {
    $capacityKg = $h->hatch_weight_capacity * 1000;
    echo "Hatch {$h->hatch_label}: {$h->hatch_weight_capacity} tons ({$capacityKg}kg)\n";
}
