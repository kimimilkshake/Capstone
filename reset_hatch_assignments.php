<?php
// Simple script to reset hatch assignments

$host = '127.0.0.1';
$db = 'lslcis';
$user = 'root';
$pass = '';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

// Reset all hatch_id to NULL
$sql = "UPDATE cargo_receipt SET hatch_id = NULL";
$result = $conn->query($sql);

if ($result) {
    $affected = $conn->affected_rows;
    echo "Successfully reset $affected receipt(s) to hatch_id = NULL\n";
    
    // Show current state
    $sql = "SELECT cargo_receipt_id, hatch_id, booking_ref_no FROM cargo_receipt ORDER BY cargo_receipt_id";
    $result = $conn->query($sql);
    
    echo "\nCurrent state:\n";
    while($row = $result->fetch_assoc()) {
        $hatch = $row['hatch_id'] ?? 'NULL';
        echo "Receipt {$row['cargo_receipt_id']}: hatch_id=$hatch, booking={$row['booking_ref_no']}\n";
    }
} else {
    echo 'Error: ' . $conn->error;
}

$conn->close();
?>
