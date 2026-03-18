<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\QrCodeGenerator;

echo "🧪 Testing QR Code Generator\n\n";

// Test 1: Generate as base64
echo "Test 1: Generate as base64\n";
$qrBase64 = QrCodeGenerator::generate("Hello World");
if ($qrBase64) {
    echo "✅ Base64 generated\n";
    echo "   Length: " . strlen($qrBase64) . " characters\n";
    echo "   First 50 chars: " . substr($qrBase64, 0, 50) . "...\n";
} else {
    echo "❌ Failed to generate base64\n";
}

// Test 2: Generate and save to disk
echo "\nTest 2: Generate and save to disk\n";
$filepath = QrCodeGenerator::generateAndSave("Booking:001:Passenger:123", "test_booking_passenger");
if ($filepath) {
    echo "✅ QR code saved to disk\n";
    echo "   Path: " . $filepath . "\n";
    if (file_exists($filepath)) {
        echo "   File size: " . filesize($filepath) . " bytes\n";
        echo "   File exists: YES\n";
    } else {
        echo "   ⚠️ File not found at path\n";
    }
} else {
    echo "❌ Failed to save QR code\n";
}

// Test 3: Generate as data URL
echo "\nTest 3: Generate as data URL\n";
$dataUrl = QrCodeGenerator::generateAsDataUrl("Test QR Code Data");
if ($dataUrl) {
    echo "✅ Data URL generated\n";
    echo "   Length: " . strlen($dataUrl) . " characters\n";
    echo "   Starts with: " . substr($dataUrl, 0, 30) . "...\n";
} else {
    echo "❌ Failed to generate data URL\n";
}

// Test 4: Generate with different sizes
echo "\nTest 4: Generate with different sizes\n";
$sizes = [200, 300, 500];
foreach ($sizes as $size) {
    $qr = QrCodeGenerator::generate("Size test: $size", $size);
    if ($qr) {
        echo "✅ Size {$size}x{$size}: OK (base64 length: " . strlen($qr) . ")\n";
    } else {
        echo "❌ Size {$size}x{$size}: Failed\n";
    }
}

echo "\n✅ All tests completed!\n";
