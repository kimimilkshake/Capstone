<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=lslcis', 'root', '');

echo "=== CURRENT CARGO FOR VOYAGE 1 ===\n";
$current = $pdo->query('
    SELECT
        ci.cargo_item_description,
        cb.quantity,
        cb.weight,
        (cb.weight * cb.quantity) as total_weight,
        cb.length, cb.width, cb.height
    FROM cargo_booking cb
    JOIN cargo_item ci ON cb.cargo_item_id = ci.cargo_item_id
    JOIN booking b ON cb.booking_ref_no = b.booking_ref_no
    JOIN cargo_receipt cr ON b.booking_id = cr.booking_id
    WHERE cr.voyage_id = 1
')->fetchAll(PDO::FETCH_ASSOC);

$totalWeight = 0;
foreach ($current as $item) {
    $tw = $item['total_weight'] ?? ($item['weight'] * $item['quantity']);
    echo "- {$item['cargo_item_description']}: qty={$item['quantity']}, {$item['weight']}kg per unit, total={$tw}kg\n";
    echo "  Dims: {$item['length']}m x {$item['width']}m x {$item['height']}m\n";
    $totalWeight += $tw;
}
echo "\nTotal: " . $totalWeight . " kg\n";
echo "60% of 600kg capacity: 360 kg\n";
echo "Remaining capacity: " . (360 - $totalWeight) . " kg\n\n";

echo "=== ALL AVAILABLE CARGO ITEMS ===\n";
$items = $pdo->query('SELECT * FROM cargo_item ORDER BY cargo_item_id')->fetchAll(PDO::FETCH_ASSOC);
foreach ($items as $item) {
    echo "ID {$item['cargo_item_id']}: {$item['cargo_item_description']}\n";
}
