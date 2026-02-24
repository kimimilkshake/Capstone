<?php
require 'vendor/autoload.php';

try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=lslcis', 'root', '');

    echo "=== CURRENT CARGO FOR VOYAGE 1 ===\n";
    $current = $pdo->query('
        SELECT ci.cargo_item_name, cb.weight, b.booking_id
        FROM booking b
        JOIN cargo_receipt cr ON b.booking_id = cr.booking_id
        JOIN cargo_booking cb ON b.booking_id = cb.booking_id
        JOIN cargo_item ci ON cb.cargo_item_id = ci.cargo_item_id
        WHERE cr.voyage_id = 1
    ')->fetchAll(PDO::FETCH_ASSOC);

    $totalWeight = 0;
    foreach ($current as $item) {
        echo "- {$item['cargo_item_name']}: {$item['weight']}kg\n";
        $totalWeight += $item['weight'];
    }
    echo "\nTotal: " . $totalWeight . " kg\n";
    echo "60% limit: 360 kg\n";
    echo "Status: " . ($totalWeight > 360 ? "EXCEEDS 60% LIMIT" : "Within limit") . "\n\n";

    // Get cargo items
    echo "=== AVAILABLE CARGO ITEMS ===\n";
    $items = $pdo->query('SELECT * FROM cargo_item')->fetchAll(PDO::FETCH_ASSOC);
    foreach ($items as $item) {
        echo "ID {$item['cargo_item_id']}: {$item['cargo_item_name']} - {$item['cargo_item_weight']}kg\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
    var_dump($e);
}
