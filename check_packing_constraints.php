<?php
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Http\Request;
use App\Http\Controllers\CargoAutoPlacementController;

$voyageId = $argv[1] ?? 1;
$request = Request::create('/dev/visualizer-debug', 'GET', ['voyage_id' => $voyageId]);
$controller = new CargoAutoPlacementController();
$response = $controller->getPackingData($request);
if (is_object($response) && method_exists($response, 'getContent')) {
    $data = json_decode($response->getContent(), true);
} else {
    echo "Failed to get packing data\n";
    exit(1);
}

$hatches = $data['hatches'] ?? [];
$cargo = $data['cargo'] ?? [];

echo "Voyage: {$data['voyage']['code']} (Vessel: {$data['voyage']['vessel']})\n\n";

$totalHatchVolume = 0.0;
foreach ($hatches as $h) {
    $vol = ($h['width'] * $h['height'] * $h['depth']);
    $totalHatchVolume += $vol;
    echo "Hatch {$h['id']} (Label: {$h['label']}): W={$h['width']}m H={$h['height']}m D={$h['depth']}m Vol={$vol} m^3 MaxWeight={$h['maxWeight']}\n";
}

echo "\nCargo types:\n";
$totalCargoVolume = 0.0;
foreach ($cargo as $c) {
    $vol = ($c['width'] * $c['height'] * $c['depth']) * ($c['quantity'] ?? 1);
    $totalCargoVolume += $vol;
    echo "- Item {$c['id']}: {$c['description']} qty={$c['quantity']} each W={$c['width']} H={$c['height']} D={$c['depth']} vol_each=" . ($c['width'] * $c['height'] * $c['depth']) . " total_vol={$vol}\n";

    // check single-item fit (allow rotations)
    $fits = false;
    $dims = [$c['width'], $c['height'], $c['depth']];
    // generate permutations
    $perms = [];
    sort($dims);
    // permutations of dims (6)
    $from = [$c['width'], $c['height'], $c['depth']];
    $perms = [
        [$from[0], $from[1], $from[2]],
        [$from[0], $from[2], $from[1]],
        [$from[1], $from[0], $from[2]],
        [$from[1], $from[2], $from[0]],
        [$from[2], $from[0], $from[1]],
        [$from[2], $from[1], $from[0]],
    ];

    foreach ($hatches as $h) {
        foreach ($perms as $p) {
            if ($p[0] <= $h['width'] && $p[1] <= $h['height'] && $p[2] <= $h['depth']) {
                $fits = true;
                break 2;
            }
        }
    }
    echo "  -> single item fits in a hatch? " . ($fits ? 'YES' : 'NO') . "\n";
}

echo "\nTotal hatch volume: {$totalHatchVolume} m^3\n";
echo "Total cargo volume: {$totalCargoVolume} m^3\n";
if ($totalCargoVolume > $totalHatchVolume) {
    echo "WARNING: Cargo total volume exceeds hatch total volume by " . ($totalCargoVolume - $totalHatchVolume) . " m^3\n";
} else {
    echo "Cargo total volume fits in hatches (by volume).\n";
}
