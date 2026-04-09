<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Cargo Booking Approved - #{{ $booking->booking_code }}</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f5f5f5; padding: 20px; }
        .container { max-width: 700px; margin: auto; background: #fff; border-radius: 10px; padding: 20px; }
        h2 { color: #28a745; }
        .section { margin-bottom: 20px; }
        .section-title { font-weight: bold; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; }
        th { background: #f0f0f0; }
        .payment-section { background: #e8f5e9; border: 2px solid #28a745; border-radius: 10px; padding: 20px; margin: 20px 0; text-align: center; }
        .payment-amount { font-size: 24px; font-weight: bold; color: #28a745; margin: 10px 0; }
        .payment-button { display: inline-block; background: #28a745; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; font-size: 16px; font-weight: bold; margin: 10px 0; }
        .payment-button:hover { background: #218838; }
        .footer { text-align: center; margin-top: 30px; font-size: 12px; color: #999; }
    </style>
</head>
<body>
<div class="container">
    <h2>✅ Cargo Booking Approved</h2>
    <p>Booking Reference: <strong>#{{ $booking->booking_code }}</strong></p>
    <p>Status: <strong>{{ $booking->booking_status }}</strong></p>
    <p><strong>Note:</strong> Arrastre payment and printing will be done in the office.</p>

    <!-- Voyage Information -->
    <div class="section">
        <div class="section-title">🚢 Voyage Information</div>
        @if($booking->voyage)
            <p>Voyage Code: {{ $booking->voyage->voyage_code }}</p>
            <p>Departure: {{ \Carbon\Carbon::parse($booking->voyage->voyage_departure_date)->format('M d, Y (D)') }}</p>
            <p>Arrival: {{ \Carbon\Carbon::parse($booking->voyage->voyage_arrival_date)->format('M d, Y (D)') }}</p>
            @php
                $originPort = $booking->voyage->routePort?->portOrigin;
                $originDisplay = $originPort ? ($originPort->terminal_name ?? '') . ' ' . ($originPort->port_name ?? '') . ', ' . ($originPort->city ?? '') : 'N/A';
                $destPort = $booking->voyage->routePort?->portDestination;
                $destDisplay = $destPort ? ($destPort->terminal_name ?? '') . ' ' . ($destPort->port_name ?? '') . ', ' . ($destPort->city ?? '') : 'N/A';
            @endphp
            <p>Port of Origin: {{ trim($originDisplay) }}</p>
            <p>Port of Destination: {{ trim($destDisplay) }}</p>
        @else
            <p>Voyage information not available.</p>
        @endif
    </div>

    <!-- Sender & Consignee -->
    <div class="section">
        <div class="section-title">📦 Sender & Consignee</div>
        <p><strong>Sender:</strong> {{ $sender->sender_name }} ({{ $sender->sender_contactno }})</p>
        <p><strong>Consignee:</strong> {{ $consignee->consignee_name }} ({{ $consignee->consignee_contactno }})</p>
    </div>

    <!-- Cargo Items -->
    <div class="section">
        <div class="section-title">📋 Cargo Items</div>
        <table>
            <thead>
                <tr>
                    <th>QTY</th>
                    <th>Classification</th>
                    <th>Description</th>
                    <th>Dimensions</th>
                    <th>Weight</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $total = 0;
                    $stampFee = 20;
                @endphp
                @foreach($cargoItems as $cargo)
                @php
                  $freight = $cargo->cargoItem->cargo_item_freight ?? 0;

                 // ✅ match measure_required logic
                 $measureRequired = strtolower($cargo->cargoItem->cargo_item_measure_required ?? 'no');

                 $cbm = (float) ($cargo->cbm ?? 0);

                 // fallback CBM if not stored
                 if (!$cbm) {
                    $cbm = ($cargo->length * $cargo->width * $cargo->height) / 1000000;
                }

                // ✅ UPDATED FORMULA (matches backend)
                if ($measureRequired === 'yes') {
                    $subtotal = $freight * $cargo->quantity;
                 } else {
                    $subtotal = $cbm * $freight * $cargo->quantity;
                }

                $total += $subtotal;

                $unitDisplay = strtolower($cargo->measurementUnit->measurement_unit_abbreviation ?? 'cm');
                $displayLength = (float) $cargo->length;
                $displayWidth = (float) $cargo->width;
                $displayHeight = (float) $cargo->height;
            @endphp
                    <tr>
                        <td>{{ $cargo->quantity }}</td>
                        <td>{{ $cargo->cargoClassification->cargo_classification_name ?? 'N/A' }}</td>
                        <td>{{ $cargo->cargoItem->cargo_item_description }}</td>
                        <td>{{ number_format($displayLength, 2) }} x {{ number_format($displayWidth, 2) }} x {{ number_format($displayHeight, 2) }} {{ $unitDisplay }}</td>
                        <td>{{ $cargo->weight }} kg</td>
                        <td>₱{{ number_format($subtotal,2) }}</td>
                    </tr>
                @endforeach
                <tr style="background-color: #f9f9f9; font-weight: bold;">
                    <td colspan="4"></td>
                    <td>Stamp</td>
                    <td>₱{{ number_format($stampFee, 2) }}</td>
                </tr>
                <tr style="background-color: #f9f9f9; font-weight: bold;">
                    <td colspan="4"></td>
                    <td>Total</td>
                    <td>₱{{ number_format($total + $stampFee, 2) }}</td>
                </tr>
            </tbody>
        </table>
    </div>
    
    <!-- Payment Section -->
    <div class="payment-section">
        <p style="margin: 0; font-size: 14px;">Please complete your payment to proceed.</p>
        
        <div class="payment-amount">₱{{ number_format($payment->total_amount ?? ($total + $stampFee), 2) }}</div>
        
        <a href="{{ $paymentUrl }}" class="payment-button">Pay Now with GCash</a>
        
        <p style="font-size: 12px; color: #666; margin-top: 10px;">
            Once payment is completed, you will receive the Freight Receipt with QR code for verification.
        </p>
    </div>

    <p>Thank you for booking with LAPULAPU SHIPPING LINES. Your cargo booking has been confirmed.</p>
    
    <div class="footer">
        <p>LAPULAPU SHIPPING LINES</p>
        <p>If you did not request this booking, please ignore this email.</p>
    </div>
</div>
</body>
</html>
