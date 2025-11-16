<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$rows = DB::table('booking')->where('booking_status', 'Canceled')->orderBy('created_at', 'desc')->limit(10)->get();
if ($rows->isEmpty()) {
    echo "NO_CANCELED\n";
    exit(0);
}
foreach ($rows as $r) {
    $min = DB::table('passenger_ticket')->where('booking_ref_no', $r->booking_ref_no)->min('pt_valid_until_ts');
    echo 'ref:' . $r->booking_ref_no . ' created:' . $r->created_at . ' min_valid_until:' . ($min ?: 'NULL') . "\n";
}
