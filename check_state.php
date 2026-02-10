<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=lslcis', 'root', '');

echo "=== ALL TABLES IN DATABASE ===\n";
$tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
foreach ($tables as $t) {
    echo "  - $t\n";
}

echo "\n=== CHECKING BOOKING TABLE ===\n";
$stmt = $pdo->query('SHOW COLUMNS FROM booking');
while ($col = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "  {$col['Field']}: {$col['Type']}\n";
}

echo "\n=== BOOKINGS WITH VOYAGE_ID = 1 ===\n";
$bookings = $pdo->query('SELECT * FROM booking WHERE voyage_id = 1')->fetchAll(PDO::FETCH_ASSOC);
echo "Found " . count($bookings) . " bookings\n";
foreach ($bookings as $b) {
    print_r($b);
    echo "\n";
}
