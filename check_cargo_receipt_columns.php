<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$columns = DB::select("DESCRIBE cargo_receipt");
echo "Cargo Receipt Table Columns:\n";
foreach ($columns as $col) {
    echo "  {$col->Field} ({$col->Type})\n";
}
