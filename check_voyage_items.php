<?php
// Check what's actually packed for Voyage 1
$pdo = new PDO('mysql:host=127.0.0.1;dbname=lslcis', 'root', '');

echo "=== VOYAGE 1 PACKED CARGO ===\n";
$sql = "
SELECT
    b.booking_ref_no,
    ci.cargo_item_description,
    cb.width, cb.height, cb.length, cb.weight,
    ci.is_breakable
FROM booking b
JOIN cargo_booking cb ON b.booking_ref_no = cb.booking_ref_no
JOIN cargo_item ci ON cb.cargo_item_id = ci.cargo_item_id
WHERE b.voyage_id = 1
  AND LOWER(b.booking_status) = 'confirmed'
ORDER BY cb.cargo_booking_id
";

$stmt = $pdo->query($sql);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

$totalWeight = 0;
foreach ($items as $idx => $item) {
    $totalWeight += (float) $item['weight'];
    $fragility = $item['is_breakable'] ? '🔴 BREAK' : '🟢 ROBUST';
    echo sprintf(
        "%2d. %s %s (%.1fkg): %.1fw × %.1fh × %.1fd\n",
        $idx + 1,
        $fragility,
        substr($item['cargo_item_description'], 0, 25),
        $item['weight'],
        $item['width'],
        $item['height'],
        $item['length']
    );
}

echo "\n==== TOTALS ====\n";
echo "Total items: " . count($items) . "\n";
echo "Total weight: " . number_format($totalWeight, 1) . "kg\n";
echo "Hatch capacity: 600kg\n";
echo "Expected utilization: " . number_format(($totalWeight / 600) * 100, 1) . "%\n";
if ($totalWeight > 600) {
    echo "Excess: " . number_format($totalWeight - 600, 1) . "kg (should overflow to Hatch 2)\n";
}
