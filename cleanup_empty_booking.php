<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=lslcis', 'root', '');

echo "Deleting empty canceled booking #1...\n\n";

// Get payment ID
$payment = $pdo->query('SELECT payment_id FROM payment WHERE booking_ref_no = 1')->fetch(PDO::FETCH_OBJ);

// Delete payment
if ($payment) {
    $stmt = $pdo->prepare('DELETE FROM payment WHERE payment_id = ?');
    $stmt->execute([$payment->payment_id]);
    echo "✓ Deleted payment record\n";
}

// Delete booking
$stmt = $pdo->prepare('DELETE FROM booking WHERE booking_ref_no = ?');
$stmt->execute([1]);
echo "✓ Deleted booking record\n";

// Verify
$check = $pdo->query('SELECT COUNT(*) as cnt FROM booking WHERE booking_ref_no = 1')->fetch(PDO::FETCH_OBJ)->cnt;
if ($check == 0) {
    echo "\n✓ Booking #1 successfully removed from system\n";
    echo "\nFinal stats:\n";
    $total = $pdo->query('SELECT COUNT(*) as cnt FROM booking')->fetch(PDO::FETCH_OBJ)->cnt;
    $confirmed = $pdo->query('SELECT COUNT(*) as cnt FROM booking WHERE booking_status = "Confirmed"')->fetch(PDO::FETCH_OBJ)->cnt;
    $canceled = $pdo->query('SELECT COUNT(*) as cnt FROM booking WHERE booking_status = "Canceled"')->fetch(PDO::FETCH_OBJ)->cnt;
    echo "  Total bookings: $total\n";
    echo "  Confirmed: $confirmed\n";
    echo "  Canceled: $canceled\n";
} else {
    echo "\n❌ Failed to delete booking\n";
}
