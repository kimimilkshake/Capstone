<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

// Find the most recent voyage with confirmed cargo
$voyages = DB::table('voyage')
    ->select('voyage_id','voyage_code','vessel_id')
    ->orderByDesc('voyage_id')
    ->limit(5)
    ->get();

foreach ($voyages as $v) {
    $receipts = DB::table('cargo_receipt')->where('voyage_id', $v->voyage_id)->count();
    echo "Voyage {$v->voyage_id} ({$v->voyage_code}) vessel={$v->vessel_id} receipts={$receipts}" . PHP_EOL;
}

echo PHP_EOL . '--- Hatches for most recent voyages vessels ---' . PHP_EOL;
$vesselIds = $voyages->pluck('vessel_id')->unique();
foreach ($vesselIds as $vid) {
    $hatches = DB::table('hatch')->where('vessel_id', $vid)->get();
    foreach ($hatches as $h) {
        $packW = max(0, $h->hatch_width - 1.2);
        $packL = max(0, $h->hatch_length - 1.2);
        $zoneW  = $packW / 2;  // half zone width after catwalk
        $engW = 1.60; $engD = 1.30; $gap = 0.05;
        $perZoneW = floor($zoneW / ($engW + 2*$gap));
        $perZoneD = floor($packL / ($engD + 2*$gap));
        $perHatch = 2 * $perZoneW * $perZoneD;
        echo "  Hatch {$h->hatch_id} ({$h->hatch_label}) vessel={$h->vessel_id}: {$h->hatch_width}×{$h->hatch_height}×{$h->hatch_length}m"
            . " | packable=".round($packW,2)."×".round($packL,2)."m"
            . " | halfZone=".round($zoneW,2)."m"
            . " | V10-fit per zone: {$perZoneW}W×{$perZoneD}D=".(int)($perZoneW*$perZoneD)
            . " | per hatch (both zones): {$perHatch}"
            . PHP_EOL;
    }
}

echo PHP_EOL . '--- FULL BIN dims (what packer currently sees) ---' . PHP_EOL;
foreach ($vesselIds as $vid) {
    $hatches = DB::table('hatch')->where('vessel_id', $vid)->get();
    foreach ($hatches as $h) {
        $zoneW  = $h->hatch_width / 2;
        $gap = 0.05; $engW = 1.60; $engD = 1.30;
        $perZoneW = floor($zoneW / ($engW + 2*$gap));
        $perZoneD = floor($h->hatch_length / ($engD + 2*$gap));
        $perHatch = 2 * $perZoneW * $perZoneD;
        echo "  Hatch {$h->hatch_id} ({$h->hatch_label}): FULL {$h->hatch_width}×{$h->hatch_length}m"
            . " | halfZone=".($h->hatch_width/2)."m"
            . " | V10-fit per zone: {$perZoneW}W×{$perZoneD}D=".(int)($perZoneW*$perZoneD)
            . " | per hatch (both zones): {$perHatch}"
            . PHP_EOL;
    }
}
