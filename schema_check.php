<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=lslcis', 'root', '');

echo "=== CARGO_ITEM Columns ===\n";
$cols = $pdo->query('DESCRIBE cargo_item')->fetchAll(PDO::FETCH_ASSOC);
foreach ($cols as $col) {
    echo $col['Field'] . "\n";
}

echo "\n=== CARGO_BOOKING Columns ===\n";
$cols = $pdo->query('DESCRIBE cargo_booking')->fetchAll(PDO::FETCH_ASSOC);
foreach ($cols as $col) {
    echo $col['Field'] . "\n";
}

echo "\n=== Sample Cargo Item Data ===\n";
$items = $pdo->query('SELECT * FROM cargo_item LIMIT 3')->fetchAll(PDO::FETCH_ASSOC);
foreach ($items as $item) {
    foreach ($item as $key => $val) {
        echo "$key=$val | ";
    }
    echo "\n";
}
