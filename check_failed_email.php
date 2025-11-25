<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "🔍 Checking Failed Email Jobs\n";
echo "=============================\n\n";

$failedJobs = DB::table('failed_jobs')
    ->where('payload', 'LIKE', '%SendTicketEmail%')
    ->orderBy('failed_at', 'desc')
    ->limit(2)
    ->get();

foreach ($failedJobs as $job) {
    echo "❌ Failed Job ID: {$job->id}\n";
    echo "📅 Failed At: {$job->failed_at}\n";

    $payload = json_decode($job->payload, true);
    echo "🎯 Job: {$payload['displayName']}\n";

    echo "💥 Exception:\n";
    $lines = explode("\n", $job->exception);
    foreach (array_slice($lines, 0, 5) as $line) {
        echo "   " . trim($line) . "\n";
    }
    echo "\n" . str_repeat("-", 50) . "\n\n";
}

?>