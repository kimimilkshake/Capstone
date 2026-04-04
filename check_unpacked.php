<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$receipts = DB::table('cargo_receipt')
    ->join('cargo_booking', 'cargo_receipt.cargo_booking_id', '=', 'cargo_booking.cargo_booking_id')
    ->join('booking', 'cargo_receipt.booking_ref_no', '=', 'booking.booking_ref_no')
    ->join('cargo_item', 'cargo_booking.cargo_item_id', '=', 'cargo_item.cargo_item_id')
    ->leftJoin('measurement_unit', 'cargo_booking.measurement_unit_id', '=', 'measurement_unit.measurement_unit_id')
    ->where('booking.booking_status', 'Confirmed')
    ->select(
        'cargo_receipt.cargo_receipt_id',
        'cargo_receipt.voyage_id',
        'cargo_receipt.hatch_id',
        'cargo_receipt.booking_ref_no',
        'cargo_item.cargo_item_description',
        'cargo_item.floor_only',
        'cargo_item.is_breakable',
        'cargo_item.is_stackable',
        'cargo_booking.quantity',
        'cargo_booking.weight',
        'cargo_booking.length',
        'cargo_booking.width',
        'cargo_booking.height',
        'measurement_unit.measurement_unit_abbreviation as unit'
    )
    ->orderBy('cargo_receipt.voyage_id')
    ->orderBy('cargo_receipt.cargo_receipt_id')
    ->get();

echo "=== CONFIRMED CARGO RECEIPTS ===\n";
foreach ($receipts as $r) {
    echo "Receipt #{$r->cargo_receipt_id} | Voyage {$r->voyage_id} | Hatch {$r->hatch_id} | {$r->cargo_item_description} | qty={$r->quantity} | {$r->length}x{$r->width}x{$r->height} {$r->unit} | {$r->weight}kg | floor_only={$r->floor_only} stackable={$r->is_stackable}\n";
}

echo "\n=== CARGO HATCH PLACEMENT ===\n";
$placements = DB::table('cargo_hatch_placement')
    ->join('cargo_receipt', 'cargo_hatch_placement.cargo_receipt_id', '=', 'cargo_receipt.cargo_receipt_id')
    ->join('cargo_booking', 'cargo_receipt.cargo_booking_id', '=', 'cargo_booking.cargo_booking_id')
    ->join('cargo_item', 'cargo_booking.cargo_item_id', '=', 'cargo_item.cargo_item_id')
    ->select(
        'cargo_hatch_placement.voyage_id',
        'cargo_hatch_placement.hatch_id',
        'cargo_hatch_placement.cargo_receipt_id',
        'cargo_hatch_placement.weight_kg',
        'cargo_hatch_placement.unit_count',
        'cargo_item.cargo_item_description',
        'cargo_item.floor_only'
    )
    ->orderBy('cargo_hatch_placement.voyage_id')
    ->orderBy('cargo_hatch_placement.hatch_id')
    ->get();

foreach ($placements as $p) {
    echo "Voyage {$p->voyage_id} | Hatch {$p->hatch_id} | Receipt #{$p->cargo_receipt_id} | {$p->cargo_item_description} | units={$p->unit_count} | weight={$p->weight_kg}kg | floor_only={$p->floor_only}\n";
}

echo "\n=== HATCH DIMENSIONS ===\n";
$hatches = DB::table('vessel_hatch')->select('hatch_id','hatch_label','hatch_width','hatch_length','hatch_height','hatch_capacity_per_hold')->get();
foreach ($hatches as $h) {
    $packW = max(0, $h->hatch_width - 1.2);
    $packL = max(0, $h->hatch_length - 1.2);
    $floorArea = $packW * $packL;
    echo "Hatch #{$h->hatch_id} [{$h->hatch_label}] {$h->hatch_width}x{$h->hatch_length}x{$h->hatch_height}m | cap={$h->hatch_capacity_per_hold}t | packable floor={$floorArea}m2\n";
}
