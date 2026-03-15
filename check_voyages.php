<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$voyages = DB::table('voyage')->limit(10)->get();

echo "Available Voyages:\n";
foreach ($voyages as $v) {
    echo "  {$v->voyage_code} - Status: {$v->voyage_status}\n";
}

// Find one with Scheduled or Active status
$activeVoyage = DB::table('voyage')
    ->whereIn('voyage_status', ['Scheduled', 'Active'])
    ->first();

if ($activeVoyage) {
    echo "\nUsing voyage: {$activeVoyage->voyage_code}\n";
} else {
    echo "\nNo Scheduled or Active voyages found. Using any voyage.\n";
    $anyVoyage = DB::table('voyage')->first();
    if ($anyVoyage) {
        echo "Using: {$anyVoyage->voyage_code} (Status: {$anyVoyage->voyage_status})\n";
    }
}
