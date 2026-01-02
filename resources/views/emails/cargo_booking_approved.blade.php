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
                    <th>Description</th>
                    <th>Classification</th>
                    <th>Quantity</th>
                    <th>Weight</th>
                    <th>Dimensions (L×W×H cm)</th>
                    <th>CBM</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @php $total = 0; @endphp
                @foreach($cargoItems as $cargo)
                    @php
                        $freight = $cargo->cargoItem->cargo_item_freight ?? 0;
                        $arrastre = $cargo->cargoItem->cargo_item_arrastre ?? 0;
                        $cbm = ($cargo->length * $cargo->width * $cargo->height) / 1000000;
                        $subtotal = ($freight + $arrastre) * $cbm * $cargo->quantity;
                        $total += $subtotal;
                    @endphp
                    <tr>
                        <td>{{ $cargo->cargoItem->cargo_item_description }}</td>
                        <td>{{ $cargo->cargoItem->cargo_item_classification }}</td>
                        <td>{{ $cargo->quantity }}</td>
                        <td>{{ $cargo->weight }} kg</td>
                        <td>{{ $cargo->length }} × {{ $cargo->width }} × {{ $cargo->height }}</td>
                        <td>{{ number_format($cbm,4) }}</td>
                        <td>₱{{ number_format($subtotal,2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div style="margin-top:15px; text-align:left;">
            <p><strong>Mode of Payment:</strong> {{ $payment->mode_of_payment ?? 'N/A' }}</p>
            <p><strong>Payment Status:</strong> {{ $payment->payment_status ?? 'N/A' }}</p>
            <p><strong>Total Amount:</strong> ₱{{ number_format($payment->total_amount ?? $total,2) }}</p>
        </div>
    </div>

    <p>Thank you for booking with LAPULAPU SHIPPING LINES. Your cargo booking has been confirmed.</p>
</div>
</body>
</html>
