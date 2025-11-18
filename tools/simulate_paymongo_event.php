<?php
// Usage:
// php tools/simulate_paymongo_event.php source.chargeable <source_id>
// php tools/simulate_paymongo_event.php payment.paid <source_id>
// Requires local server running (php artisan serve)

if ($argc < 3) {
    echo "USAGE: php tools/simulate_paymongo_event.php <event_type> <source_id>\n";
    exit(1);
}

$eventType = $argv[1];
$sourceId = $argv[2];

$resource = [];
if (str_starts_with($eventType, 'source.')) {
    $resource = [
        'id' => $sourceId,
        'type' => 'source',
        'attributes' => [
            'status' => 'chargeable',
            'type' => 'gcash',
            'billing' => [
                'email' => 'test@example.invalid',
                'name' => 'Test User'
            ]
        ]
    ];
} elseif (str_starts_with($eventType, 'payment.')) {
    $status = str_contains($eventType, 'paid') ? 'paid' : (str_contains($eventType, 'succeeded') ? 'succeeded' : (str_contains($eventType, 'failed') ? 'failed' : 'canceled'));
    $resource = [
        'id' => 'pay_SIMULATED',
        'type' => 'payment',
        'attributes' => [
            'status' => $status,
            'source' => ['id' => $sourceId, 'type' => 'source']
        ]
    ];
} else {
    echo "Unsupported event type\n";
    exit(1);
}

$payload = [
    'data' => [
        'id' => 'evt_SIMULATED_' . uniqid(),
        'type' => 'event',
        'attributes' => [
            'type' => $eventType,
            'data' => $resource,
        ],
    ],
];

$json = json_encode($payload);

$opts = [
    'http' => [
        'method' => 'POST',
        'header' => "Content-Type: application/json\r\n",
        'content' => $json,
        'ignore_errors' => true,
    ],
];
$context = stream_context_create($opts);
$response = file_get_contents('http://127.0.0.1:8000/paymongo/webhook', false, $context);

echo "Sent event: $eventType for source $sourceId\n";
echo "Response: $response\n";
