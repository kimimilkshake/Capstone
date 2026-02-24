<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=lslcis', 'root', '');

echo "=== CURRENT CARGO BOOKINGS ===\n";
// cargo_booking uses booking_ref_no, not booking_id
$bookings = $pdo->query('
    SELECT cb.*, ci.cargo_item_description
    FROM cargo_booking cb
    JOIN cargo_item ci ON cb.cargo_item_id = ci.cargo_item_id
')->fetchAll(PDO::FETCH_ASSOC);

$totalWeight = 0;
foreach ($bookings as $b) {
    $itemTotal = $b['weight'] * $b['quantity'];
    echo "{$b['cargo_item_description']}: qty={$b['quantity']}, {$b['weight']}kg ea. = {$itemTotal}kg total, dims: {$b['length']}x{$b['width']}x{$b['height']}\n";
    $totalWeight += $itemTotal;
}

echo "\nTotal weight: " . $totalWeight . " kg\n";
echo "60% of 600kg capacity: 360 kg\n";
echo "Over limit by: " . ($totalWeight - 360) . " kg\n";

echo "\n=== ALL CARGO ITEMS ===\n";
$items = $pdo->query('SELECT cargo_item_id, cargo_item_description FROM cargo_item')->fetchAll(PDO::FETCH_ASSOC);
foreach ($items as $item) {
    echo "ID {$item['cargo_item_id']}: {$item['cargo_item_description']}\n";
}
