<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Booking;
use App\Models\CargoReceipt;

echo "=== COMPREHENSIVE DATABASE CLEANUP ===\n\n";

// 1. Check all bookings on voyage 1
echo "1. All bookings on Voyage 1:\n";
$allBookings = Booking::where('voyage_id', 1)->get();
foreach ($allBookings as $b) {
    echo "   Ref {$b->booking_ref_no}: status={$b->booking_status}, qty in CB={$b->cargo_item_qty}\n";
}

echo "\n2. All cargo receipts on Voyage 1:\n";
$allReceipts = CargoReceipt::where('voyage_id', 1)->get();
foreach ($allReceipts as $r) {
    echo "   Receipt {$r->cargo_receipt_id}: booking_ref={$r->booking_ref_no}\n";
}

echo "\n3. DELETING ALL bookings and receipts for Voyage 1...\n";
CargoReceipt::where('voyage_id', 1)->delete();
Booking::where('voyage_id', 1)->delete();
echo "   ✓ Deleted all bookings and receipts\n";

echo "\n4. VERIFYING cleanup...\n";
$remainingBookings = Booking::where('voyage_id', 1)->count();
$remainingReceipts = CargoReceipt::where('voyage_id', 1)->count();
echo "   Remaining bookings: $remainingBookings\n";
echo "   Remaining receipts: $remainingReceipts\n";

echo "\n5. Fixing Hatch 2 capacity...\n";
DB::table('hatch')->where('hatch_id', 2)->update(['hatch_weight_capacity' => 0.6]);
$hatches = DB::table('hatch')->whereIn('hatch_id', [1, 2])->get();
foreach ($hatches as $h) {
    echo "   Hatch {$h->hatch_id}: {$h->hatch_weight_capacity} tons\n";
}

echo "\n✅ CLEANUP COMPLETE!\n";
