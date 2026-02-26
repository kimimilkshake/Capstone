<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=lslcis', 'root', '');
$hatches = $pdo->query('SELECT hatch_id, hatch_label, hatch_width, hatch_height, hatch_length, hatch_weight_capacity FROM hatch LIMIT 5')->fetchAll(PDO::FETCH_ASSOC);
echo "=== HATCH DIMENSIONS ===\n";
foreach ($hatches as $h) {
    echo "Hatch {$h['hatch_id']}: {$h['hatch_label']} = {$h['hatch_width']}w × {$h['hatch_height']}h × {$h['hatch_length']}d, MaxWeight={$h['hatch_weight_capacity']} tons\n";
}
