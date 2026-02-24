<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=lslcis', 'root', '');
    $result = $pdo->query('SELECT hatch_weight_capacity FROM hatch WHERE vessel_id=1 LIMIT 1')->fetch(PDO::FETCH_ASSOC);
    if ($result) {
        $capacity = $result['hatch_weight_capacity'];
        echo "Hatch weight capacity: " . $capacity . " tons\n";
        echo "60% of capacity: " . (0.6 * $capacity) . " tons\n";
        echo "Current items total: 550 kg (0.55 tons)\n";
        echo "Remaining before 60%: " . ((0.6 * $capacity) - 0.55) . " tons\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
