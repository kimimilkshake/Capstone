<?php
// Check actual packed items for Voyage 1
$pdo = new PDO('mysql:host=127.0.0.1;dbname=lslcis', 'root', '');

echo "=== VOYAGE 1 CARGO BOOKINGS ===\n";
$sql = "
SELECT
    c.cargo_booking_id,
    ci.cargo_item_description as description,
    c.width, c.height, c.length, c.weight,
    ci.is_breakable
FROM cargo_booking c
JOIN cargo_receipt cr ON c.receipt_id = cr.receipt_id
JOIN cargo_item ci ON c.item_id = ci.cargo_item_id
WHERE cr.voyage_id = 1
ORDER BY c.cargo_booking_id
";

$stmt = $pdo->query($sql);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

$totalWeight = 0;
foreach ($items as $idx => $item) {
    $totalWeight += (float) $item['weight'];
    $fragility = $item['is_breakable'] ? '🔴 BREAK' : '🟢 ROBUST';
    echo sprintf(
        "%2d. %s %s (%0.1fkg): %0.1fw × %0.1fh × %0.1fd\n",
        $idx + 1,
        $fragility,
        substr($item['description'], 0, 25),
        $item['weight'],
        $item['width'],
        $item['height'],
        $item['length']
    );
}

echo "\nTotal weight: " . number_format($totalWeight, 1) . "kg\n";
echo "Hatch capacity: 600kg\n";
echo "Utilization: " . number_format(($totalWeight / 600) * 100, 1) . "%\n";
echo "\nExpected hatch distribution:\n";
echo "  Hatch 1: Should be ~600kg (100% full)\n";
echo "  Hatch 2: Should be ~" . number_format($totalWeight - 600, 1) . "kg overflow\n";
