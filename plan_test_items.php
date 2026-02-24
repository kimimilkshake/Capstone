<?php
require 'vendor/autoload.php';

try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=lslcis', 'root', '');

    // Get existing cargo totals for voyage 1
    $result = $pdo->query('
        SELECT SUM(cb.weight * b.qty) as total_weight
        FROM cargo_booking cb
        JOIN booking b ON cb.booking_id = b.booking_id
        JOIN cargo_receipt cr ON b.booking_id = cr.booking_id
        WHERE cr.voyage_id = 1
    ')->fetch(PDO::FETCH_ASSOC);

    $currentWeight = $result['total_weight'] ?? 0;
    $hatchCapacity = 600; // kg (0.60 tons)
    $sixtyPercent = 0.6 * $hatchCapacity;
    $remaining = $sixtyPercent - $currentWeight;

    echo "Current weight in Hatch 1: " . (int) $currentWeight . " kg\n";
    echo "60% capacity limit: " . $sixtyPercent . " kg\n";
    echo "Already exceeding by: " . ((int) $currentWeight - $sixtyPercent) . " kg\n\n";

    echo "SOLUTION: Create scenario with lighter items within 60% limit\n";
    echo "New scenario: Target items that fit in ~360kg\n\n";

    // Let's create a test scenario with lighter items
    // Get or create cargo items
    $smallItems = [
        ['name' => 'Electronics Box', 'weight' => 25, 'length' => 0.6, 'width' => 0.5, 'height' => 0.4],
        ['name' => 'Textiles Bundle', 'weight' => 30, 'length' => 0.8, 'width' => 0.6, 'height' => 0.5],
        ['name' => 'Plastic Cases', 'weight' => 20, 'length' => 0.5, 'width' => 0.5, 'height' => 0.6],
        ['name' => 'Metal Parts Box', 'weight' => 35, 'length' => 0.7, 'width' => 0.6, 'height' => 0.5],
        ['name' => 'Paper Rolls', 'weight' => 28, 'length' => 0.9, 'width' => 0.4, 'height' => 0.4],
        ['name' => 'Rubber Sheets', 'weight' => 22, 'length' => 1.0, 'width' => 0.5, 'height' => 0.3],
        ['name' => 'Ceramic Tiles', 'weight' => 40, 'length' => 0.6, 'width' => 0.6, 'height' => 0.8],
        ['name' => 'Foam Blocks', 'weight' => 15, 'length' => 1.2, 'width' => 0.6, 'height' => 0.4],
    ];

    $totalNewWeight = array_sum(array_column($smallItems, 'weight'));
    echo "Suggested new test items total: " . $totalNewWeight . " kg\n";
    echo "This fits within 60% limit: " . ($totalNewWeight <= $sixtyPercent ? "YES" : "NO") . "\n\n";

    echo "Items that can be added:\n";
    foreach ($smallItems as $idx => $item) {
        echo ($idx + 1) . ". {$item['name']}: {$item['weight']}kg ({$item['length']}m x {$item['width']}m x {$item['height']}m)\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
