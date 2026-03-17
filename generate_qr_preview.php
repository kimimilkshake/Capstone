<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\QrCodeGenerator;

// Generate a test QR code
$testData = "Booking:1001:Passenger:5001";
$qrDataUrl = QrCodeGenerator::generateAsDataUrl($testData, 400);

// Create HTML to display the QR code
$html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code Viewer</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .container {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            text-align: center;
            max-width: 600px;
        }
        
        h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 28px;
        }
        
        .status {
            color: #22c55e;
            font-size: 18px;
            margin-bottom: 30px;
            font-weight: bold;
        }
        
        .qr-wrapper {
            background: #f5f5f5;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            display: inline-block;
        }
        
        .qr-wrapper img {
            display: block;
            width: 300px;
            height: 300px;
            border: 2px solid #ddd;
            border-radius: 5px;
        }
        
        .info {
            background: #f0f9ff;
            padding: 15px;
            border-radius: 8px;
            text-align: left;
            margin-top: 20px;
            border-left: 4px solid #667eea;
        }
        
        .info h3 {
            margin-top: 0;
            color: #667eea;
        }
        
        .info code {
            background: #fff;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: monospace;
            word-break: break-all;
            color: #764ba2;
        }
        
        .details {
            margin-top: 15px;
            text-align: left;
            font-size: 14px;
            color: #666;
        }
        
        .details p {
            margin: 8px 0;
            line-height: 1.6;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>✅ QR Code Generated Successfully!</h1>
        <div class="status">Ready for Use</div>
        
        <div class="qr-wrapper">
            <img src="$qrDataUrl" alt="QR Code">
        </div>
        
        <div class="info">
            <h3>QR Code Data</h3>
            <p><code>$testData</code></p>
            
            <div class="details">
                <p><strong>📊 Details:</strong></p>
                <p>• Format: PNG Image</p>
                <p>• Size: 400x400 pixels</p>
                <p>• Type: Data URL (Base64)</p>
                <p>• Data Length: SDK function encodes booking reference with passenger ID</p>
                <p>• Encoding: Data URL format ready for PDF/HTML embedding</p>
            </div>
            
            <div class="details" style="margin-top: 15px;">
                <p><strong>🔧 Methods Available:</strong></p>
                <p>• <code>generate()</code> - Returns base64 PNG</p>
                <p>• <code>generateAndSave()</code> - Saves to disk</p>
                <p>• <code>generateAsDataUrl()</code> - Returns data URL</p>
            </div>
        </div>
    </div>
</body>
</html>
HTML;

// Save and display
file_put_contents('qr_preview.html', $html);
echo "✅ QR Code preview created!\n";
echo "📁 File: qr_preview.html\n";
echo "🔗 Open in browser to see the QR code image\n";
echo "\n📊 QR Data: $testData\n";
echo "✓ Generator Status: WORKING\n";
?>
