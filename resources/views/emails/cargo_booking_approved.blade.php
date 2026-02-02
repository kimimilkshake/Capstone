<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Cargo Booking Approved - #{{ $booking->booking_ref_no }}</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f5f5f5; padding: 20px; }
        .container { max-width: 700px; margin: auto; background: #fff; border-radius: 10px; padding: 20px; }
        h2 { color: #28a745; }
        .section { margin-bottom: 20px; }
        .section-title { font-weight: bold; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; }
        th { background: #f0f0f0; }
    </style>
</head>
<body>
<div class="container">
    <h2>✅ Cargo Booking Approved</h2>
    <p>Booking Reference: <strong>#{{ $booking->booking_code }}</strong></p>
    <p>Status: <strong>{{ $booking->booking_status }}</strong></p>

    <!-- Voyage Information -->
    <div class="section">
        <div class="section-title">🚢 Voyage Information</div>
        @if($booking->voyage)
            <p>Voyage Code: {{ $booking->voyage->voyage_code }}</p>
            <p>Departure: {{ \Carbon\Carbon::parse($booking->voyage->voyage_departure_date)->format('M d, Y (D)') }}</p>
            <p>Arrival: {{ \Carbon\Carbon::parse($booking->voyage->voyage_arrival_date)->format('M d, Y (D)') }}</p>
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
                    $totalQuantity = 0;
                @endphp
                @foreach($cargoItems as $cargo)
                    @php
                        $freight = $cargo->cargoItem->cargo_item_freight ?? 0;
                        $arrastre = $cargo->cargoItem->cargo_item_arrastre ?? 0;
                        $cbm = ($cargo->length * $cargo->width * $cargo->height) / 1000000;
                        $subtotal = ($freight + $arrastre) * $cbm * $cargo->quantity;
                        $total += $subtotal;
                        $totalQuantity += $cargo->quantity;
                        
                        // Determine unit of measurement
                        $unit = $cargo->measurement_unit ?? 'cm';
                        $unitDisplay = ($unit === 'in') ? 'inches' : 'cm';
                        
                        // For display, show dimensions in the unit chosen by customer
                        if ($unit === 'in') {
                            $displayLength = round($cargo->length / 2.54, 2);
                            $displayWidth = round($cargo->width / 2.54, 2);
                            $displayHeight = round($cargo->height / 2.54, 2);
                        } else {
                            $displayLength = $cargo->length;
                            $displayWidth = $cargo->width;
                            $displayHeight = $cargo->height;
                        }
                    @endphp
                    <tr>
                        <td>{{ $cargo->quantity }}</td>
                        <td>{{ $cargo->cargoItem->cargo_item_classification }}</td>
                        <td>{{ $cargo->cargoItem->cargo_item_description }}</td>
                        <td>{{ $displayLength }} × {{ $displayWidth }} × {{ $displayHeight }} {{ $unitDisplay }}</td>
                        <td>{{ $cargo->weight }} kg</td>
                        <td>₱{{ number_format($subtotal,2) }}</td>
                    </tr>
                @endforeach
                <tr style="background-color: #f9f9f9; font-weight: bold;">
                    <td colspan="2">Total Items</td>
                    <td colspan="2">{{ $totalQuantity }}</td>
                    <td colspan="2"></td>
                </tr>
            </tbody>
        </table>

        <div style="margin-top:15px; text-align:left;">
            <p><strong>Mode of Payment:</strong> {{ $payment->mode_of_payment ?? 'N/A' }}</p>
            <p><strong>Payment Status:</strong> {{ $payment->payment_status ?? 'N/A' }}</p>
            <p style="font-size: 16px; color: #28a745;"><strong>Total Overall: ₱{{ number_format($payment->total_amount ?? $total,2) }}</strong></p>
        </div>
    </div>

    <p>Thank you for booking with LAPULAPU SHIPPING LINES. Your cargo booking has been confirmed.</p>
</div>
</body>
</html>
