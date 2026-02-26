<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=lslcis', 'root', '');

echo "=== CARGO_ITEM TABLE COLUMNS ===\n";
$stmt = $pdo->query('SHOW COLUMNS FROM cargo_item');
while ($col = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "  {$col['Field']}: {$col['Type']}\n";
}

echo "\n=== ALL CARGO ITEMS ===\n";
$items = $pdo->query('SELECT * FROM cargo_item LIMIT 20')->fetchAll();
foreach ($items as $item) {
    print_r($item);
}
