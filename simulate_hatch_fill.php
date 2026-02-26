<?php
// Direct database query to check packing
$pdo = new PDO('mysql:host=127.0.0.1;dbname=lslcis', 'root', '');

$sql = "
  SELECT
      cb.cargo_booking_id,
      ci.cargo_item_description,
      cb.width, cb.height, cb.length, cb.weight,
      ci.is_breakable
  FROM booking b
  JOIN cargo_booking cb ON b.booking_ref_no = cb.booking_ref_no
  JOIN cargo_item ci ON cb.cargo_item_id = ci.cargo_item_id
  WHERE b.voyage_id = 1
    AND LOWER(b.booking_status) = 'confirmed'
  ORDER BY cb.weight DESC
";

$items = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

echo "=== VOYAGE 1 PACKING SIMULATION ===\n";
echo "Total items: " . count($items) . "\n\n";

$hatch1Weight = 0;
$hatch2Weight = 0;
$maxCapacity = 600;

foreach ($items as $idx => $item) {
    $frag = $item['is_breakable'] ? '🔴' : '🟢';
    $weight = (float) $item['weight'];

    if ($hatch1Weight + $weight <= $maxCapacity) {
        echo "✅ HATCH 1: +{$weight}kg $frag " . substr($item['cargo_item_description'], 0, 35) . "\n";
        $hatch1Weight += $weight;
    } else {
        echo "⬜ HATCH 2: +{$weight}kg $frag " . substr($item['cargo_item_description'], 0, 35) . "\n";
        $hatch2Weight += $weight;
    }
}

echo "\n=== UTILIZATION ===\n";
echo "Hatch 1: {$hatch1Weight}kg / {$maxCapacity}kg (" . number_format(($hatch1Weight / $maxCapacity) * 100, 1) . "%)\n";
echo "Hatch 2: {$hatch2Weight}kg / {$maxCapacity}kg (" . number_format(($hatch2Weight / $maxCapacity) * 100, 1) . "%)\n";
echo "Total: " . ($hatch1Weight + $hatch2Weight) . "kg\n";
