<?php
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

// Check voyages
echo "=== VOYAGES ===\n";
$voyages = DB::table('voyage')->get(['voyage_id', 'voyage_code', 'voyage_status']);
foreach ($voyages as $v) {
    echo "$v->voyage_id: $v->voyage_code - Status: $v->voyage_status\n";
}

// Check cargo receipts
echo "\n=== CARGO RECEIPTS ===\n";
$receipts = DB::table('cargo_receipt')->get(['voyage_id', 'booking_ref_no', 'cargo_item_qty']);
foreach ($receipts as $r) {
    echo "Voyage: $r->voyage_id, Booking: $r->booking_ref_no, Qty: $r->cargo_item_qty\n";
}

// Check cargo bookings
echo "\n=== CARGO BOOKINGS ===\n";
$bookings = DB::table('cargo_booking')->get(['booking_ref_no', 'length', 'width', 'height']);
foreach ($bookings as $b) {
    echo "Booking: $b->booking_ref_no, Dims: {$b->length}x{$b->width}x{$b->height}\n";
}
