<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

$b = DB::table('cargo_booking')->where('booking_ref_no', 'CBBK26001')->first();
if ($b) {
    echo 'Found in cargo_booking:' . PHP_EOL;
    print_r((array)$b);
} else {
    echo 'Not found in cargo_booking. All cargo bookings:' . PHP_EOL;
    $all = DB::table('cargo_booking')
        ->select('cargo_booking_id', 'booking_ref_no', 'booking_status', 'cargo_item_id', 'quantity')
        ->orderBy('cargo_booking_id')
        ->get();
    foreach ($all as $r) {
        echo $r->cargo_booking_id . ' | ' . $r->booking_ref_no . ' | status=' . $r->booking_status . ' | item_id=' . $r->cargo_item_id . ' | qty=' . $r->quantity . PHP_EOL;
    }
}
