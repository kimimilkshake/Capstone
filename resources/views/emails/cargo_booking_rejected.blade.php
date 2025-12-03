<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Cargo Booking Rejected - #{{ $booking->booking_ref_no }}</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f5f5f5; padding: 20px; }
        .container { max-width: 700px; margin: auto; background: #fff; border-radius: 10px; padding: 20px; }
        h2 { color: #dc3545; }
        .section { margin-bottom: 20px; }
        .section-title { font-weight: bold; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; }
        th { background: #f0f0f0; }
    </style>
</head>
<body>
<div class="container">
    <h2>❌ Cargo Booking Rejected</h2>
    <p>Booking Reference: <strong>#{{ $booking->booking_ref_no }}</strong></p>
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
                </tr>
            </thead>
            <tbody>
                @foreach($cargoItems as $cargo)
                    <tr>
                        <td>{{ $cargo->cargoItem->cargo_item_description }}</td>
                        <td>{{ $cargo->cargoItem->cargo_item_classification }}</td>
                        <td>{{ $cargo->quantity }}</td>
                        <td>{{ $cargo->weight }} kg</td>
                        <td>{{ $cargo->length }} × {{ $cargo->width }} × {{ $cargo->height }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <p>Unfortunately, your cargo booking has been rejected. Please contact the shipping line for further assistance.</p>
</div>
</body>
</html>
