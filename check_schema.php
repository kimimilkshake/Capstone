<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "BOOKING TABLE SCHEMA:\n";
$columns = DB::select("DESCRIBE booking");
foreach ($columns as $col) {
    echo "{$col->Field}: {$col->Type} {$col->Null} {$col->Key}\n";
}

echo "\n\nCARGO_RECEIPT TABLE SCHEMA:\n";
$columns = DB::select("DESCRIBE cargo_receipt");
foreach ($columns as $col) {
    echo "{$col->Field}: {$col->Type} {$col->Null} {$col->Key}\n";
}
