<?php
// Simulate the packing algorithm to verify it works
echo "=== TESTING PACKING ALGORITHM ===\n\n";

// Hatch dimensions
$binWidth = 8;
$binHeight = 2.5;
$binDepth = 10;
$binMaxWeight = 600; // kg

// Test items
$items = [
    ['name' => 'Glass Bottles', 'w' => 0.6, 'h' => 1.0, 'd' => 0.8, 'weight' => 60, 'breakable' => true],
    ['name' => 'Electronics', 'w' => 0.5, 'h' => 0.8, 'd' => 0.7, 'weight' => 45, 'breakable' => true],
    ['name' => 'Steel Coils', 'w' => 1.2, 'h' => 0.9, 'd' => 1.5, 'weight' => 150, 'breakable' => false],
    ['name' => 'Wooden Pallets', 'w' => 0.8, 'h' => 1.0, 'd' => 1.2, 'weight' => 80, 'breakable' => false],
];

// Sort by weight descending
usort($items, fn($a, $b) => $b['weight'] <=> $a['weight']);

// Zone corner alignment info
$zoneCornerInfo = [
    'frontLeft' => ['baseX' => 0, 'baseZ' => 0, 'alignRight' => false, 'alignBack' => false],
    'frontRight' => ['baseX' => $binWidth, 'baseZ' => 0, 'alignRight' => true, 'alignBack' => false],
    'backLeft' => ['baseX' => 0, 'baseZ' => $binDepth, 'alignRight' => false, 'alignBack' => true],
    'backRight' => ['baseX' => $binWidth, 'baseZ' => $binDepth, 'alignRight' => true, 'alignBack' => true],
];

// Initial corners at bin edges
$corners = [
    ['x' => 0, 'y' => 0, 'z' => 0],
    ['x' => $binWidth, 'y' => 0, 'z' => 0],
    ['x' => 0, 'y' => 0, 'z' => $binDepth],
    ['x' => $binWidth, 'y' => 0, 'z' => $binDepth],
];

// Packed items
$packed = [];
$usedWeight = 0;

echo "Items to pack (sorted by weight):\n";
foreach ($items as $i => $item) {
    $fragility = $item['breakable'] ? '🔴 BREAK' : '🟢 ROBUST';
    echo sprintf(
        "  %d. %s %s (%.1fkg): %.1fw × %.1fh × %.1fd\n",
        $i + 1,
        $fragility,
        $item['name'],
        $item['weight'],
        $item['w'],
        $item['h'],
        $item['d']
    );
}

echo "\n=== PACKING PROCESS ===\n\n";

foreach ($items as $item) {
    // Can it fit by weight?
    if ($usedWeight + $item['weight'] > $binMaxWeight) {
        echo "❌ {$item['name']}: EXCEEDS WEIGHT ({$usedWeight} + {$item['weight']} > {$binMaxWeight})\n";
        continue;
    }

    // Find best corner
    $bestCorner = null;
    $bestZone = null;
    $bestDistance = PHP_FLOAT_MAX;

    foreach ($zoneCornerInfo as $zoneName => $zoneInfo) {
        // Calculate where item would be placed if aligned to this corner
        $itemX = $zoneInfo['alignRight'] ? max(0, $binWidth - $item['w']) : $zoneInfo['baseX'];
        $itemZ = $zoneInfo['alignBack'] ? max(0, $binDepth - $item['d']) : $zoneInfo['baseZ'];

        // Check if it fits
        if ($itemX < 0 || $itemX + $item['w'] > $binWidth)
            continue;
        if ($itemZ < 0 || $itemZ + $item['d'] > $binDepth)
            continue;
        if ($item['h'] > $binHeight)
            continue;

        // Check for collisions with already packed items
        $collision = false;
        foreach ($packed as $p) {
            if (
                !($itemX + $item['w'] <= $p['x'] || $itemX >= $p['x'] + $p['w'] ||
                    $item['h'] <= $p['y'] || 0 >= $p['y'] + $p['h'] ||
                    $itemZ + $item['d'] <= $p['z'] || $itemZ >= $p['z'] + $p['d'])
            ) {
                $collision = true;
                break;
            }
        }
        if ($collision)
            continue;

        // This corner works! Calculate distance
        $distance = abs($itemX - $zoneInfo['baseX']) + abs($itemZ - $zoneInfo['baseZ']);
        if ($distance < $bestDistance) {
            $bestDistance = $distance;
            $bestCorner = ['x' => $itemX, 'y' => 0, 'z' => $itemZ];
            $bestZone = $zoneName;
        }
    }

    if ($bestCorner) {
        $packed[] = array_merge($item, $bestCorner);
        $usedWeight += $item['weight'];
        $zoneAlign = $zoneCornerInfo[$bestZone];
        echo "✅ {$item['name']}: Placed at ({$bestCorner['x']}, {$bestCorner['y']}, {$bestCorner['z']}) in $bestZone\n";
        echo "   → Ends at ({:.1f}, {:.1f}, {:.1f})\n", $bestCorner['x'] + $item['w'], $bestCorner['y'] + $item['h'], $bestCorner['z'] + $item['d'];
    } else {
        echo "❌ {$item['name']}: NO VALID CORNER FOUND\n";
    }
}

echo "\n=== PACKED SUMMARY ===\n";
echo "Total packed: " . count($packed) . " items\n";
echo "Total weight: {$usedWeight}kg / {$binMaxWeight}kg (" . number_format(($usedWeight / $binMaxWeight) * 100, 1) . "%)\n";

echo "\n=== ITEM POSITIONS ===\n";
foreach ($packed as $i => $p) {
    echo sprintf(
        "%d. %s at (%.1f, %.1f, %.1f) → (%.1f, %.1f, %.1f)\n",
        $i + 1,
        $p['name'],
        $p['x'],
        $p['y'],
        $p['z'],
        $p['x'] + $p['w'],
        $p['y'] + $p['h'],
        $p['z'] + $p['d']
    );
}
