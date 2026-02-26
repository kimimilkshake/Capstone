<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=lslcis', 'root', '');

echo "Booking columns:\n";
$cols = $pdo->query('DESCRIBE booking')->fetchAll(PDO::FETCH_ASSOC);
foreach ($cols as $c)
    echo $c['Field'] . ' ';

echo "\n\nCargo_receipt columns:\n";
$cols = $pdo->query('DESCRIBE cargo_receipt')->fetchAll(PDO::FETCH_ASSOC);
foreach ($cols as $c)
    echo $c['Field'] . ' ';

echo "\n\nCurrent bookings for voyage 1:\n";
$bookings = $pdo->query('
    SELECT cb.*, ci.cargo_item_description
    FROM cargo_booking cb
    JOIN cargo_item ci ON cb.cargo_item_id = ci.cargo_item_id
    WHERE cb.booking_ref_no IN (
        SELECT DISTINCT cb2.booking_ref_no
        FROM cargo_booking cb2
        LIMIT 10
    )
    LIMIT 10
')->fetchAll(PDO::FETCH_ASSOC);

echo "\n\nCargo bookings:\n";
foreach ($bookings as $b) {
    echo "{$b['cargo_item_description']}: qty={$b['quantity']}, weight_ea={$b['weight']}kg total_weight=" . (($b['weight'] * $b['quantity'])) . "kg\n";
}
