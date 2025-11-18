<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Voyage;
use App\Models\Vessel;
use App\Models\RoutePort;

echo "Checking existing data...\n";

$vessels = Vessel::all();
echo "Vessels: " . $vessels->count() . "\n";
if ($vessels->count() > 0) {
    echo "Sample vessel: " . $vessels->first()->vessel_name . "\n";
}

$routePorts = RoutePort::all();
echo "Route Ports: " . $routePorts->count() . "\n";
if ($routePorts->count() > 0) {
    $rp = $routePorts->first();
    echo "Sample route: " . $rp->route_origin . " - " . $rp->route_destination . "\n";
}

$voyages = Voyage::all();
echo "Voyages: " . $voyages->count() . "\n";
if ($voyages->count() > 0) {
    $v = $voyages->first();
    echo "Sample voyage: ID " . $v->voyage_id . ", Date: " . $v->voyage_departure_date . "\n";
}

// If no voyage data, create a sample
if ($voyages->count() == 0 && $vessels->count() > 0 && $routePorts->count() > 0) {
    echo "Creating sample voyage data...\n";

    $voyage = Voyage::create([
        'vessel_id' => $vessels->first()->vessel_id,
        'route_port_id' => $routePorts->first()->route_port_id,
        'voyage_departure_date' => '2025-11-18',
        'voyage_arrival_date' => '2025-11-18',
        'voyage_estimated_TD' => '11:29:00',
        'voyage_estimated_TA' => '15:30:00',
        'voyage_status' => 'Scheduled',
        'voyage_description' => 'Regular passenger service',
        'voyage_code' => 'V001'
    ]);

    echo "Created voyage ID: " . $voyage->voyage_id . "\n";
}
