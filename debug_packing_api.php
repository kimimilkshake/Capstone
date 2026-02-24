<?php
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Http\Request;

$voyageId = $argv[1] ?? 1;

$request = Request::create('/api/staff_cargo/packing-data', 'GET', ['voyage_id' => $voyageId]);

$controller = new App\Http\Controllers\CargoAutoPlacementController();
try {
    $response = $controller->getPackingData($request);
    if (is_object($response) && method_exists($response, 'getContent')) {
        echo $response->getContent();
    } else {
        var_export($response);
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
