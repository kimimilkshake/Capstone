<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=lslcis', 'root', '');

echo "=== CARGO_BOOKING TABLE COLUMNS ===\n";
$stmt = $pdo->query('SHOW COLUMNS FROM cargo_booking');
while ($col = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "  {$col['Field']}: {$col['Type']}\n";
}

echo "\n=== CARGO_BOOKINGS FOR VOYAGE 1 ===\n";
$bookings = $pdo->query('
SELECT cb.*
FROM cargo_booking cb
WHERE cb.booking_ref_no IN (8, 9, 10, 11, 12, 13)
ORDER BY cb.booking_ref_no
')->fetchAll(PDO::FETCH_ASSOC);

foreach ($bookings as $cb) {
    echo "\nBooking Ref {$cb['booking_ref_no']}:\n";
    foreach ($cb as $k => $v) {
        echo "  {$k}: {$v}\n";
    }
}
