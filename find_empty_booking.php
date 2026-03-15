<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=lslcis', 'root', '');
$result = $pdo->query('SELECT b.booking_ref_no, b.booking_status FROM booking b LEFT JOIN passenger_ticket pt ON b.booking_ref_no = pt.booking_ref_no GROUP BY b.booking_ref_no HAVING COUNT(pt.passenger_id) = 0');
echo "Booking(s) with NO passengers:\n";
while ($row = $result->fetch(PDO::FETCH_OBJ)) {
    echo "  - Booking: $row->booking_ref_no | Status: $row->booking_status\n";
}
