<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
use Illuminate\Support\Facades\DB;

$deleted = DB::table('cargo_hatch_placement')->where('voyage_id', 1)->delete();
echo 'Deleted ' . $deleted . ' placement rows for voyage 1' . PHP_EOL;

// Also reset hatch_id on cargo_receipt so nothing is "pinned"
$updated = DB::table('cargo_receipt')->where('voyage_id', 1)->update(['hatch_id' => null]);
echo 'Reset hatch_id for ' . $updated . ' receipts' . PHP_EOL;
