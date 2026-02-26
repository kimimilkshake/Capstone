<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "========================================\n";
echo "FINAL CLEANUP: DELETE OLD BOOKINGS 1-7\n";
echo "========================================\n\n";

// 1. Delete cargo_receipts for bookings 1-7
echo "1️⃣ DELETING CARGO RECEIPTS for bookings 1-7:\n";
$deleted_receipts = DB::table('cargo_receipt')
    ->whereIn('booking_ref_no', [1, 2, 3, 4, 5, 6, 7])
    ->where('voyage_id', 1)
    ->delete();
echo "   Deleted: $deleted_receipts receipts\n";

// 2. Delete bookings 1-7
echo "\n2️⃣ DELETING BOOKINGS 1-7:\n";
$deleted_bookings = DB::table('booking')
    ->whereIn('booking_ref_no', [1, 2, 3, 4, 5, 6, 7])
    ->where('voyage_id', 1)
    ->delete();
echo "   Deleted: $deleted_bookings bookings\n";

// 3. Verify remaining bookings
echo "\n3️⃣ REMAINING CONFIRMED BOOKINGS ON VOYAGE 1:\n";
$remaining = DB::table('booking')
    ->where('voyage_id', 1)
    ->whereRaw("LOWER(booking_status) = ?", ['confirmed'])
    ->orderBy('booking_ref_no')
    ->get();

echo "   Total: " . count($remaining) . "\n";
foreach ($remaining as $b) {
    echo "   Ref {$b->booking_ref_no}: cargo_item_id={$b->cargo_item_id}\n";
}

// 4. Verify cargo_bookings now exist
echo "\n4️⃣ CARGO BOOKINGS FOR REMAINING BOOKINGS:\n";
if (count($remaining) > 0) {
    $refs = array_column((array) $remaining, 'booking_ref_no');
    $cbs = DB::table('cargo_booking')
        ->whereIn('booking_ref_no', $refs)
        ->orderBy('booking_ref_no')
        ->get();

    echo "   Total: " . count($cbs) . "\n";
    $total_weight = 0;
    foreach ($cbs as $cb) {
        echo "   Ref {$cb->booking_ref_no}: {$cb->weight}kg, {$cb->width}×{$cb->height}×{$cb->length}m\n";
        $total_weight += $cb->weight;
    }
    echo "   Total Weight: {$total_weight}kg\n";
}

// 5. Verify cargo_receipts
echo "\n5️⃣ CARGO RECEIPTS FOR REMAINING BOOKINGS:\n";
$receipts = DB::table('cargo_receipt')
    ->where('voyage_id', 1)
    ->orderBy('booking_ref_no')
    ->get();

echo "   Total: " . count($receipts) . "\n";
foreach ($receipts as $r) {
    echo "   Receipt {$r->cargo_receipt_id}: booking_ref={$r->booking_ref_no}, qty={$r->cargo_item_qty}\n";
}

// 6. Verify hatches
echo "\n6️⃣ HATCH CAPACITIES:\n";
$hatches = DB::table('hatch')->whereIn('hatch_id', [1, 2])->get();
foreach ($hatches as $h) {
    echo "   Hatch {$h->hatch_id}: {$h->hatch_weight_capacity}t\n";
}

echo "\n========================================\n";
echo "✅ CLEANUP COMPLETE!\n";
echo "Now you have 6 clean confirmed bookings.\n";
echo "Hard-refresh browser and test again.\n";
echo "========================================\n";
