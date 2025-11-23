<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Voyage;

echo "Detailed voyage information...\n";

$voyage = Voyage::with(['vessel', 'routePort'])->first();

if ($voyage) {
    echo "Voyage ID: " . $voyage->voyage_id . "\n";
    echo "Vessel Name: " . ($voyage->vessel ? $voyage->vessel->vessel_name : 'No vessel') . "\n";
    echo "Route: " . ($voyage->routePort ? $voyage->routePort->route_origin . ' - ' . $voyage->routePort->route_destination : 'No route') . "\n";
    echo "Departure Date: " . $voyage->voyage_departure_date . "\n";
    echo "Estimated TD: " . $voyage->voyage_estimated_TD . "\n";
    echo "Port Origin Name: " . ($voyage->routePort ? $voyage->routePort->port_origin_name : 'No port') . "\n";
    echo "Status: " . $voyage->voyage_status . "\n";

    // Show raw voyage data
    echo "\nRaw voyage fields:\n";
    foreach ($voyage->getAttributes() as $key => $value) {
        echo "$key: $value\n";
    }

    // Show raw route port data
    if ($voyage->routePort) {
        echo "\nRaw route port fields:\n";
        foreach ($voyage->routePort->getAttributes() as $key => $value) {
            echo "$key: $value\n";
        }
    }
}
