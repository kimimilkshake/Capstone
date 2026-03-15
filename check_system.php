<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=lslcis', 'root', '');

echo "\n╔════════════════════════════════════════════════════════════╗\n";
echo "║        SYSTEM INTEGRITY CHECK - PASSENGER BOOKINGS          ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

// 1. Total bookings
$result = $pdo->query('SELECT COUNT(*) as cnt FROM booking');
$total = $result->fetch(PDO::FETCH_OBJ)->cnt;
echo "Total bookings: $total\n";

// 2. Booking status breakdown
$result = $pdo->query('SELECT booking_status, COUNT(*) as cnt FROM booking GROUP BY booking_status');
echo "Booking status breakdown:\n";
while ($row = $result->fetch(PDO::FETCH_OBJ)) {
    echo "  $row->booking_status: $row->cnt\n";
}

// 3. Bookings with no passengers
$result = $pdo->query('SELECT COUNT(DISTINCT b.booking_ref_no) as cnt FROM booking b LEFT JOIN passenger_ticket pt ON b.booking_ref_no = pt.booking_ref_no WHERE pt.booking_ref_no IS NULL');
$noPass = $result->fetch(PDO::FETCH_OBJ)->cnt;
echo ($noPass > 0 ? "\n❌ Bookings with NO passengers: $noPass\n" : "\n✓ Bookings with NO passengers: $noPass\n");

// 4. Duplicate passengers in same booking
$result = $pdo->query('SELECT COUNT(*) as cnt FROM (SELECT booking_ref_no, passenger_id, COUNT(*) as c FROM passenger_ticket GROUP BY booking_ref_no, passenger_id HAVING COUNT(*) > 1) as dups');
$dups = $result->fetch(PDO::FETCH_OBJ)->cnt;
echo ($dups > 0 ? "❌ Duplicate passengers found: $dups\n" : "✓ Duplicate passengers found: $dups\n");

// 5. Orphaned passengers
$result = $pdo->query('SELECT COUNT(*) as cnt FROM passenger p LEFT JOIN passenger_ticket pt ON p.passenger_id = pt.passenger_id WHERE pt.passenger_id IS NULL');
$orphan = $result->fetch(PDO::FETCH_OBJ)->cnt;
echo ($orphan > 0 ? "❌ Orphaned passenger records: $orphan\n" : "✓ Orphaned passenger records: $orphan\n");

// 6. Payment-booking mismatches
$result = $pdo->query('SELECT COUNT(*) as cnt FROM payment p LEFT JOIN booking b ON p.booking_ref_no = b.booking_ref_no WHERE b.booking_ref_no IS NULL');
$payMis = $result->fetch(PDO::FETCH_OBJ)->cnt;
echo ($payMis > 0 ? "❌ Payments with NO booking: $payMis\n" : "✓ Payments with NO booking: $payMis\n");

// 7. Recent bookings
echo "\nRecent 5 passenger bookings:\n";
$result = $pdo->query('SELECT b.booking_ref_no, b.booking_status, p.payment_status, b.created_at FROM booking b LEFT JOIN payment p ON b.booking_ref_no = p.booking_ref_no ORDER BY b.created_at DESC LIMIT 5');
while ($row = $result->fetch(PDO::FETCH_OBJ)) {
    $pres = $pdo->prepare('SELECT COUNT(*) as cnt FROM passenger_ticket WHERE booking_ref_no = ?');
    $pres->execute([$row->booking_ref_no]);
    $pc = $pres->fetch(PDO::FETCH_OBJ)->cnt;
    $pstat = $row->payment_status ?: 'N/A';
    echo "  $row->booking_ref_no | Status: $row->booking_status | Payment: $pstat | Passengers: $pc\n";
}

echo "\n╔════════════════════════════════════════════════════════════╗\n";
echo "║        SYSTEM INTEGRITY CHECK - CARGO BOOKINGS              ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

// 8. Total cargo
$result = $pdo->query('SELECT COUNT(*) as cnt FROM cargo_booking');
$totalCargo = $result->fetch(PDO::FETCH_OBJ)->cnt;
echo "Total cargo bookings: $totalCargo\n";

// 9. Cargo without voyage
$result = $pdo->query('SELECT COUNT(DISTINCT cb.cargo_booking_id) as cnt FROM cargo_booking cb LEFT JOIN voyage v ON cb.voyage_id = v.voyage_id WHERE v.voyage_id IS NULL');
$cargoNoVoy = $result->fetch(PDO::FETCH_OBJ)->cnt;
echo ($cargoNoVoy > 0 ? "❌ Cargo with NO voyage: $cargoNoVoy\n" : "✓ Cargo with NO voyage: $cargoNoVoy\n");

// 10. Approved vs unapproved
$result = $pdo->query('SELECT COUNT(*) as cnt FROM cargo_booking WHERE approved_by_staff_id IS NOT NULL');
$approved = $result->fetch(PDO::FETCH_OBJ)->cnt;
$unapproved = $totalCargo - $approved;
echo "✓ Approved cargo: $approved | Pending: $unapproved\n";

// 11. Cargo items check
$result = $pdo->query('SELECT COUNT(DISTINCT cb.cargo_booking_id) as cnt FROM cargo_booking cb LEFT JOIN cargo_item ci ON cb.cargo_booking_id = ci.cargo_booking_id WHERE ci.cargo_booking_id IS NULL');
$cargoNoItems = $result->fetch(PDO::FETCH_OBJ)->cnt;
echo ($cargoNoItems > 0 ? "❌ Cargo with NO items: $cargoNoItems\n" : "✓ Cargo with NO items: $cargoNoItems\n");

echo "\n╔════════════════════════════════════════════════════════════╗\n";
echo "║        SYSTEM INTEGRITY CHECK - BILL OF LADING              ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

$result = $pdo->query('SELECT COUNT(*) as cnt FROM bill_of_lading');
$bolCount = ($result ? $result->fetch(PDO::FETCH_OBJ)->cnt : 0);
echo "Total BOL records: $bolCount\n";

// 13. BOL without cargo
if ($bolCount > 0) {
    $result = $pdo->query('SELECT COUNT(DISTINCT bol.bill_of_lading_id) as cnt FROM bill_of_lading bol LEFT JOIN cargo_booking cb ON bol.cargo_booking_id = cb.cargo_booking_id WHERE cb.cargo_booking_id IS NULL');
    $bolNoCargo = $result->fetch(PDO::FETCH_OBJ)->cnt;
    echo ($bolNoCargo > 0 ? "❌ BOL with NO cargo: $bolNoCargo\n" : "✓ BOL with NO cargo: $bolNoCargo\n");
}

echo "\n╔════════════════════════════════════════════════════════════╗\n";
echo "║        SYSTEM INTEGRITY CHECK - AUTOPLACEMENT               ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

// 14. Voyage checks
$result = $pdo->query('SELECT COUNT(*) as cnt FROM voyage');
$totalVoy = $result->fetch(PDO::FETCH_OBJ)->cnt;
echo "Total voyages: $totalVoy\n";

// 15. Voyages without vessel
$result = $pdo->query('SELECT COUNT(DISTINCT v.voyage_id) as cnt FROM voyage v LEFT JOIN vessel ve ON v.vessel_id = ve.vessel_id WHERE ve.vessel_id IS NULL');
$voyNoVessel = $result->fetch(PDO::FETCH_OBJ)->cnt;
echo ($voyNoVessel > 0 ? "❌ Voyages with NO vessel: $voyNoVessel\n" : "✓ Voyages with NO vessel: $voyNoVessel\n");

// 16. Vessels with hatches
$result = $pdo->query('SELECT COUNT(DISTINCT vessel_id) as cnt FROM hatch');
$vesselsHatch = $result->fetch(PDO::FETCH_OBJ)->cnt;
echo "✓ Vessels with hatches: $vesselsHatch\n";

// 17. Routes
$result = $pdo->query('SELECT COUNT(*) as cnt FROM route');
$totalRoutes = $result->fetch(PDO::FETCH_OBJ)->cnt;
echo "Total routes configured: $totalRoutes\n";

echo "\n" . str_repeat("═", 60) . "\n";
echo "✓ SYSTEM CHECK COMPLETE\n";
echo "Any ❌ marks above indicate data integrity issues.\n";
echo "Check these in your database immediately.\n";
echo str_repeat("═", 60) . "\n\n";
