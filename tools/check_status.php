<?php
require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';

// Bootstrap the application so facades/bindings are available
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$db = $app->make('db');

echo "Jobs remaining in queue: ";
echo $db->table('jobs')->count() . "\n";

echo "\nBookings by status:\n";
$statuses = $db->table('booking')
    ->groupBy('booking_status')
    ->selectRaw('booking_status, COUNT(*) as cnt')
    ->get();

foreach ($statuses as $row) {
    echo "  {$row->booking_status}: {$row->cnt}\n";
}

echo "\nPayments by status:\n";
$payments = $db->table('payment')
    ->groupBy('payment_status')
    ->selectRaw('payment_status, COUNT(*) as cnt')
    ->get();

foreach ($payments as $row) {
    echo "  {$row->payment_status}: {$row->cnt}\n";
}
