<?php
require 'vendor/autoload.php';
require 'bootstrap/app.php';

$receipts = \App\Models\CargoReceipt::all();
foreach ($receipts as $r) {
    echo "Receipt {$r->cargo_receipt_id}: hatch_id=" . ($r->hatch_id ?? 'NULL') . ", booking=" . $r->booking_ref_no . "\n";
}
