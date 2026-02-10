<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\CargoItem;

$items = CargoItem::limit(10)->get();
echo "Existing Cargo Items:\n";
foreach ($items as $item) {
    echo "  ID: " . $item->cargo_item_id . ", Desc: " . $item->cargo_item_description . "\n";
}
