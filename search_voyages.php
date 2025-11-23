<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Voyage;

echo "Searching for voyages with 11:29 departure...\n";

// Check all voyages regardless of status or date
$allVoyages = Voyage::with(['vessel', 'routePort'])->get();
echo "Total voyages in database: " . $allVoyages->count() . "\n\n";

foreach ($allVoyages as $voyage) {
    echo "Voyage ID: " . $voyage->voyage_id . "\n";
    echo "Departure Date: " . $voyage->voyage_departure_date . "\n";
    echo "Estimated TD: " . $voyage->voyage_estimated_TD . "\n";
    echo "Status: " . $voyage->voyage_status . "\n";
    echo "Vessel: " . ($voyage->vessel ? $voyage->vessel->vessel_name : 'NULL') . "\n";
    echo "Route Port: " . ($voyage->routePort ? $voyage->routePort->route_origin . " - " . $voyage->routePort->route_destination : 'NULL') . "\n";
    echo "Port Origin: " . ($voyage->routePort ? $voyage->routePort->port_origin_name : 'NULL') . "\n";
    echo "---\n";
}

// Also check if there are any with time containing 11:29
$elevenTwentyNine = Voyage::where('voyage_estimated_TD', 'LIKE', '%11:29%')->get();
echo "\nVoyages with 11:29 time: " . $elevenTwentyNine->count() . "\n";
