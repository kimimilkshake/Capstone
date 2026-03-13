<?php
require 'vendor/autoload.php';
$app = require_once('bootstrap/app.php');
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Set receipt 4 to unassigned
\Illuminate\Support\Facades\DB::table('cargo_receipt')
    ->where('cargo_receipt_id', 4)
    ->update(['hatch_id' => NULL]);

echo "Receipt 4 set to hatch_id=NULL\n";

// Verify
$r = \Illuminate\Support\Facades\DB::table('cargo_receipt')->where('cargo_receipt_id', 4)->first();
echo "Receipt 4 now: hatch_id=" . ($r->hatch_id ?? 'NULL') . "\n";

// Show all receipts
echo "\nAll receipts:\n";
$all = \Illuminate\Support\Facades\DB::table('cargo_receipt')
    ->where('voyage_id', 1)
    ->select('cargo_receipt_id', 'hatch_id')
    ->orderBy('cargo_receipt_id')
    ->get();
foreach ($all as $row) {
    echo "Receipt {$row->cargo_receipt_id}: hatch_id=" . ($row->hatch_id ?? 'NULL') . "\n";
}
