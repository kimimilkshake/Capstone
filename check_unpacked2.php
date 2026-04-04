<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== CONFIRMED CARGO RECEIPTS ===\n";
$receipts = DB::select("
    SELECT cr.cargo_receipt_id, cr.voyage_id, cr.hatch_id, cr.booking_ref_no,
           ci.cargo_item_description, ci.floor_only, ci.is_stackable,
           cb.quantity, cb.weight, cb.length, cb.width, cb.height,
           mu.measurement_unit_abbreviation as unit
    FROM cargo_receipt cr
    JOIN cargo_booking cb ON cr.cargo_booking_id = cb.cargo_booking_id
    JOIN booking b ON cr.booking_ref_no = b.booking_ref_no
    JOIN cargo_item ci ON cb.cargo_item_id = ci.cargo_item_id
    LEFT JOIN measurement_unit mu ON cb.measurement_unit_id = mu.measurement_unit_id
    WHERE b.booking_status = 'Confirmed'
    ORDER BY cr.voyage_id, cr.cargo_receipt_id
");
foreach ($receipts as $r) {
    echo "Receipt #{$r->cargo_receipt_id} | Voyage {$r->voyage_id} | Hatch {$r->hatch_id} | {$r->cargo_item_description} | qty={$r->quantity} | {$r->length}x{$r->width}x{$r->height} {$r->unit} | {$r->weight}kg | floor_only={$r->floor_only} stackable={$r->is_stackable}\n";
}

echo "\n=== CARGO HATCH PLACEMENT ===\n";
$placements = DB::select("
    SELECT chp.voyage_id, chp.hatch_id, chp.cargo_receipt_id, chp.weight_kg, chp.unit_count,
           ci.cargo_item_description, ci.floor_only
    FROM cargo_hatch_placement chp
    JOIN cargo_receipt cr ON chp.cargo_receipt_id = cr.cargo_receipt_id
    JOIN cargo_booking cb ON cr.cargo_booking_id = cb.cargo_booking_id
    JOIN cargo_item ci ON cb.cargo_item_id = ci.cargo_item_id
    ORDER BY chp.voyage_id, chp.hatch_id
");
foreach ($placements as $p) {
    echo "Voyage {$p->voyage_id} | Hatch {$p->hatch_id} | Receipt #{$p->cargo_receipt_id} | {$p->cargo_item_description} | units={$p->unit_count} | weight={$p->weight_kg}kg | floor_only={$p->floor_only}\n";
}

echo "\n=== HATCH DIMENSIONS ===\n";
$hatches = DB::select("SELECT hatch_id, hatch_label, hatch_width, hatch_length, hatch_height, hatch_capacity_per_hold FROM hatch ORDER BY hatch_id");
foreach ($hatches as $h) {
    $packW = max(0, $h->hatch_width - 1.2);
    $packL = max(0, $h->hatch_length - 1.2);
    echo "Hatch #{$h->hatch_id} [{$h->hatch_label}] {$h->hatch_width}x{$h->hatch_length}x{$h->hatch_height}m | cap={$h->hatch_capacity_per_hold}t | packable floor=" . round($packW*$packL,2) . "m2\n";
}
