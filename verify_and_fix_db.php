<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "========================================\n";
echo "DATABASE VERIFICATION & FIX\n";
echo "========================================\n\n";

// 1. Check hatches
echo "1️⃣ HATCH STATUS:\n";
$hatches = DB::table('hatch')->whereIn('hatch_id', [1, 2])->get();
foreach ($hatches as $h) {
    $status = $h->hatch_weight_capacity >= 0.6 ? '✓' : '✗ BAD';
    echo "   Hatch {$h->hatch_id}: {$h->hatch_weight_capacity}t {$status}\n";
}

// 2. Check ALL bookings on voyage 1
echo "\n2️⃣ ALL BOOKINGS ON VOYAGE 1:\n";
$allBookings = DB::table('booking')
    ->where('voyage_id', 1)
    ->orderBy('booking_ref_no')
    ->get();

echo "   Total: " . count($allBookings) . "\n";
foreach ($allBookings as $b) {
    echo "   Ref {$b->booking_ref_no}: status={$b->booking_status}, cargo_item_id={$b->cargo_item_id}\n";
}

// 3. Check CONFIRMED bookings only
echo "\n3️⃣ CONFIRMED BOOKINGS ON VOYAGE 1:\n";
$confirmedBookings = DB::table('booking')
    ->where('voyage_id', 1)
    ->whereRaw("LOWER(booking_status) = ?", ['confirmed'])
    ->get();

echo "   Total: " . count($confirmedBookings) . "\n";
foreach ($confirmedBookings as $b) {
    echo "   Ref {$b->booking_ref_no}: cargo_item_id={$b->cargo_item_id}\n";
}

// 4. Check cargo_bookings for confirmed bookings
echo "\n4️⃣ CARGO BOOKINGS FOR CONFIRMED BOOKINGS:\n";
if (count($confirmedBookings) > 0) {
    $refs = array_column((array) $confirmedBookings, 'booking_ref_no');
    $cbs = DB::table('cargo_booking')
        ->whereIn('booking_ref_no', $refs)
        ->get();

    echo "   Total: " . count($cbs) . "\n";
    foreach ($cbs as $cb) {
        echo "   Ref {$cb->booking_ref_no}: weight={$cb->weight}kg, {$cb->width}×{$cb->height}×{$cb->length}m\n";
    }
}

// 5. Check cargo_items (to see if there are duplicates with different quantities)
echo "\n5️⃣ ALL CARGO ITEMS (FIRST 10):\n";
$items = DB::table('cargo_item')->limit(10)->get();
foreach ($items as $item) {
    echo "   ID {$item->cargo_item_id}: {$item->cargo_item_description}\n";
}

// 6. Check cargo_receipts
echo "\n6️⃣ CARGO RECEIPTS ON VOYAGE 1:\n";
$receipts = DB::table('cargo_receipt')
    ->where('voyage_id', 1)
    ->get();

echo "   Total: " . count($receipts) . "\n";
foreach ($receipts as $r) {
    echo "   Receipt {$r->cargo_receipt_id}: booking_ref={$r->booking_ref_no}, cargo_item_id={$r->cargo_item_id}, qty={$r->cargo_item_qty}\n";
}

// 7. FIX: If Hatch 2 is 0, fix it
echo "\n7️⃣ FIXING HATCH 2 CAPACITY:\n";
$hatch2 = DB::table('hatch')->where('hatch_id', 2)->first();
if ($hatch2 && $hatch2->hatch_weight_capacity == 0) {
    DB::table('hatch')->where('hatch_id', 2)->update(['hatch_weight_capacity' => 0.6]);
    echo "   ✓ Updated Hatch 2 to 0.6t\n";
} else {
    echo "   ✓ Hatch 2 already correct\n";
}

echo "\n========================================\n";
echo "If confirmed bookings exist with qty=1 each, API should work correctly.\n";
echo "If hatch 2 still shows 0, the issue is database persistence.\n";
echo "========================================\n";
