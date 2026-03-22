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
                        $cbm = (float) ($cargo->cbm ?? 0);
                        $subtotal = $freight * $cbm * $cargo->quantity;
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


    <p>Thank you for booking with LAPULAPU SHIPPING LINES. Your cargo booking has been confirmed.</p>
</div>
</body>
</html>
