<?php
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$voyageId = $argv[1] ?? 1;

$voyage = DB::table('voyage')->where('voyage_id', $voyageId)->first();
if (!$voyage) {
    echo "Voyage $voyageId not found\n";
    exit(1);
}

echo "Voyage: {$voyage->voyage_code} (ID: {$voyage->voyage_id})\n";

$vessel = DB::table('vessel')->where('vessel_id', $voyage->vessel_id)->first();
if (!$vessel) {
    echo "Vessel for voyage not found\n";
    exit(1);
}

echo "Vessel: {$vessel->vessel_name} (ID: {$vessel->vessel_id})\n";

$hatches = DB::table('hatch')->where('vessel_id', $vessel->vessel_id)->get();
if ($hatches->isEmpty()) {
    echo "No hatches found for vessel\n";
    exit(0);
}

echo "\nHatches:\n";
foreach ($hatches as $h) {
    echo "- Hatch ID: {$h->hatch_id}, Label: {$h->hatch_label}, Length: {$h->hatch_length}m, Width: {$h->hatch_width}m, Height: {$h->hatch_height}m, WeightCap: {$h->hatch_weight_capacity}kg\n";
}
