<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "✅ QR Codes Table Check\n\n";

if (Schema::hasTable('qr_codes')) {
    echo "✅ Table exists: qr_codes\n";
    $count = DB::table('qr_codes')->count();
    echo "✅ Current records: $count\n\n";

    echo "🔍 Table columns:\n";
    $columns = DB::select("DESCRIBE qr_codes");
    foreach ($columns as $col) {
        echo "   - {$col->Field} ({$col->Type})\n";
    }
} else {
    echo "❌ Table does not exist: qr_codes\n";
}
