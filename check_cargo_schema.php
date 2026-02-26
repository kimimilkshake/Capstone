<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=lslcis', 'root', '');

echo "=== CARGO_ITEM TABLE STRUCTURE ===\n";
$cols = $pdo->query('DESCRIBE cargo_item')->fetchAll(PDO::FETCH_ASSOC);
foreach ($cols as $col) {
    echo "{$col['Field']}: {$col['Type']} {$col['Null']} {$col['Key']}\n";
}

echo "\n=== SAMPLE CARGO ITEMS ===\n";
$items = $pdo->query('SELECT * FROM cargo_item LIMIT 5')->fetchAll(PDO::FETCH_ASSOC);
foreach ($items as $item) {
    echo "ID {$item['cargo_item_id']}: {$item['cargo_item_description']}\n";
}
