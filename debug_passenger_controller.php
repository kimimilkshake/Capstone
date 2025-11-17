<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Voyage;
use App\Models\Vessel;
use App\Models\RoutePort;
use Carbon\Carbon;

echo "Testing PassengerController logic directly...\n";

// Simulate the request parameters that would come from bookingtype.js
$routeFrom = 'Cebu';
$routeTo = 'Baybay, Leyte';
$departureDate = '2025-11-18';
$voyageId = 1;
$type = 'passenger';

echo "Input parameters:\n";
echo "Route From: $routeFrom\n";
echo "Route To: $routeTo\n";
echo "Departure Date: $departureDate\n";
echo "Voyage ID: $voyageId\n\n";

// Get voyage information from voyage table with relationships
$voyage = Voyage::with(['vessel', 'routePort'])
    ->where('voyage_id', $voyageId)
    ->first();

if (!$voyage) {
    echo "ERROR: Voyage not found!\n";
    exit;
}

echo "Voyage found successfully!\n";

$vesselName = $voyage->vessel->vessel_name ?? 'Unknown Vessel';
$departureTime = $voyage->voyage_estimated_TD;
$portOfOrigin = $voyage->routePort->port_origin_name ?? 'Unknown Port';

echo "Raw departure time from DB: $departureTime\n";

if ($departureTime) {
    $departureTime = Carbon::parse($departureTime)->format('g:i A');
}

echo "\nFinal values to display:\n";
echo "Vessel Name: $vesselName\n";
echo "Route: $routeFrom - $routeTo\n";
echo "Departure Date: $departureDate\n";
echo "Departure Time: $departureTime\n";
echo "Port of Origin: $portOfOrigin\n";
