<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

$items = DB::table('cargo_item')
    ->where('cargo_item_description', 'LIKE', 'Engine%')
    ->select('cargo_item_id', 'cargo_item_description', 'floor_only', 'is_breakable', 'is_stackable')
    ->get();

foreach ($items as $i) {
    echo $i->cargo_item_id . ' | ' . $i->cargo_item_description
        . ' | floor_only=' . $i->floor_only
        . ' | breakable=' . $i->is_breakable
        . ' | stackable=' . $i->is_stackable . PHP_EOL;
}

echo PHP_EOL . 'Checking specific booking CBBK26001...' . PHP_EOL;
$receipt = DB::table('cargo_receipt')
    ->where('booking_ref_no', 'CBBK26001')
    ->first();
if ($receipt) {
    echo 'Receipt found: cargo_booking_id=' . $receipt->cargo_booking_id . PHP_EOL;
    $booking = DB::table('cargo_booking')->where('cargo_booking_id', $receipt->cargo_booking_id)->first();
    if ($booking) {
        echo 'Booking cargo_item_id=' . $booking->cargo_item_id . PHP_EOL;
        $item = DB::table('cargo_item')->where('cargo_item_id', $booking->cargo_item_id)->first();
        if ($item) {
            echo 'Item: ' . $item->cargo_item_description
                . ' | floor_only=' . $item->floor_only
                . ' | breakable=' . $item->is_breakable
                . ' | stackable=' . $item->is_stackable . PHP_EOL;
        } else {
            echo 'cargo_item NOT FOUND for id=' . $booking->cargo_item_id . PHP_EOL;
        }
    } else {
        echo 'Booking NOT FOUND' . PHP_EOL;
    }
} else {
    echo 'Receipt with booking_ref_no CBBK26001 not found' . PHP_EOL;
}
