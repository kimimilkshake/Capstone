<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=lslcis', 'root', '');

echo "=== CARGO_BOOKING COLUMNS ===\n";
$cols = $pdo->query('DESCRIBE cargo_booking')->fetchAll(PDO::FETCH_ASSOC);
foreach ($cols as $col) {
    echo $col['Field'] . "\n";
}

echo "\n=== CARGO_RECEIPT COLUMNS ===\n";
$cols = $pdo->query('DESCRIBE cargo_receipt')->fetchAll(PDO::FETCH_ASSOC);
foreach ($cols as $col) {
    echo $col['Field'] . "\n";
}

echo "\n=== SAMPLE CARGO_BOOKING WITH VOYAGE ===\n";
$sample = $pdo->query("
SELECT cb.*, cr.voyage_id
FROM cargo_booking cb
LEFT JOIN cargo_receipt cr ON (cb.cargo_booking_id = cr.cargo_booking_id OR cb.receipt_id = cr.receipt_id)
LIMIT 3
")->fetchAll(PDO::FETCH_ASSOC);

if (empty($sample)) {
    echo "No results\n";
} else {
    echo "Found " . count($sample) . " bookings\n";
}
