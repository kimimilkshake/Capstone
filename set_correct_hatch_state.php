<?php
$host = '127.0.0.1';
$db = 'lslcis';
$user = 'root';
$pass = '';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

// Set receipts 1, 2, 3 to hatch_id = 5 (Hatch 1)
$sql = "UPDATE cargo_receipt SET hatch_id = 5 WHERE cargo_receipt_id IN (1, 2, 3)";
$result = $conn->query($sql);
echo "Set receipts 1, 2, 3 to hatch_id=5: " . $conn->affected_rows . " updated\n";

// Set receipt 4 to hatch_id = NULL (unassigned)
$sql = "UPDATE cargo_receipt SET hatch_id = NULL WHERE cargo_receipt_id = 4";
$result = $conn->query($sql);
echo "Set receipt 4 to hatch_id=NULL: " . $conn->affected_rows . " updated\n";

// Show current state
$sql = "SELECT cargo_receipt_id, hatch_id, booking_ref_no FROM cargo_receipt ORDER BY cargo_receipt_id";
$result = $conn->query($sql);

echo "\nCurrent state:\n";
while($row = $result->fetch_assoc()) {
    $hatch = $row['hatch_id'] ?? 'NULL';
    echo "Receipt {$row['cargo_receipt_id']}: hatch_id=$hatch, booking={$row['booking_ref_no']}\n";
}

$conn->close();
?>
