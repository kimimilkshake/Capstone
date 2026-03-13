<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$receipts = DB::table('cargo_receipt')->where('voyage_id', 1)->get();
echo "Checking cargo receipts for voyage 1:\n";
foreach($receipts as $r) {
    echo "Receipt {$r->cargo_receipt_id}: hatch_id = " . ($r->hatch_id ?? 'NULL') . "\n";
}
