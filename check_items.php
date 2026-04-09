<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;

$items = DB::select('SELECT cargo_item_id, cargo_item_description, floor_only, is_breakable, is_stackable FROM cargo_item ORDER BY cargo_item_id');
foreach ($items as $i) {
    echo "#{$i->cargo_item_id} {$i->cargo_item_description} | floor_only={$i->floor_only} breakable={$i->is_breakable} stackable={$i->is_stackable}\n";
}
