<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Passenger Ticket Confirmed - #{{ $booking->booking_ref_no }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            padding: 20px;
        }

        .container {
            max-width: 700px;
            margin: auto;
            background: #fff;
            border-radius: 10px;
            padding: 20px;
        }

        h2 {
            color: #007bff;
        }

        .section {
            margin-bottom: 20px;
        }

        .section-title {
            font-weight: bold;
            margin-bottom: 10px;
            color: #333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background: #f0f0f0;
            font-weight: bold;
        }

        .highlight {
            background-color: #e7f3ff;
        }

        .footer {
            text-align: center;
            color: #666;
            font-size: 12px;
            margin-top: 20px;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>✅ Passenger Ticket Confirmed</h2>
        <p>Booking Reference: <strong>#{{ $booking->booking_ref_no }}</strong></p>
        <p>Status: <strong>{{ $booking->booking_status }}</strong></p>

        <!-- Voyage Information -->
        <div class="section">
            <div class="section-title">🚢 Voyage Information</div>
            @if ($voyage)
                <p><strong>Voyage Code:</strong> {{ $voyage->voyage_code }}</p>
                <p><strong>Vessel:</strong> {{ $vessel->vessel_name ?? 'N/A' }}</p>
                <p><strong>Route:</strong> {{ $route->route_origin ?? 'N/A' }} →
                    {{ $route->route_destination ?? 'N/A' }}</p>
                <p><strong>Departure:</strong>
                    {{ \Carbon\Carbon::parse($voyage->voyage_departure_date)->format('M d, Y (D)') }} at
                    {{ \Carbon\Carbon::parse($voyage->voyage_estimated_TD)->format('g:i A') }}</p>
                <p><strong>Arrival:</strong>
                    {{ \Carbon\Carbon::parse($voyage->voyage_arrival_date)->format('M d, Y (D)') }} at
                    {{ \Carbon\Carbon::parse($voyage->voyage_estimated_TA)->format('g:i A') }}</p>
            @else
                <p>Voyage information not available.</p>
            @endif
        </div>

        <!-- Loading & Unloading Ports -->
        <div class="section">
            <div class="section-title">🏝️ Ports</div>
            @if ($route)
                <p><strong>Port of Origin:</strong> {{ $route->port_origin_name ?? 'N/A' }}</p>
                <p><strong>Port of Destination:</strong> {{ $route->port_destination_name ?? 'N/A' }}</p>
            @else
                <p>Port information not available.</p>
            @endif
        </div>

        <!-- Passengers -->
        <div class="section">
            <div class="section-title">👥 Passenger Details</div>
            <table>
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Cot #</th>
                        <th>Accommodation</th>
                        <th>Base Price</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($allPassengers as $index => $item)
                        @php
                            $passenger = $item['passenger'] ?? null;
                            $ticket = $item['ticket'] ?? null;
                            $basePrice = $item['accommodation_base_price'] ?? null;
                            $displayPrice = $basePrice !== null ? $basePrice : $ticket->pt_ticket_price ?? 0;
                        @endphp
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                {{ $passenger->passenger_firstname ?? 'N/A' }}
                                @if ($passenger && $passenger->passenger_midinitial)
                                    {{ $passenger->passenger_midinitial }}.
                                @endif
                                {{ $passenger->passenger_lastname ?? '' }}
                                @if ($passenger && $passenger->passenger_suffix)
                                    {{ $passenger->passenger_suffix }}
                                @endif
                            </td>
                            <td>{{ $passenger->passenger_type ?? 'N/A' }}</td>
                            <td>{{ $ticket->pt_cot_no ?? 'N/A' }}</td>
                            <td>{{ $item['accommodation_name'] ?? 'N/A' }}</td>
                            <td>₱{{ number_format($displayPrice, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center;">No passengers found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Payment Information -->
        <div class="section highlight">
            <div class="section-title">💳 Payment Information</div>
            @if ($payment)
                {{-- Per-passenger price breakdown --}}
                @php $hasAnyBreakdown = false; @endphp
                @foreach ($allPassengers as $item)
                    @php
                        $ticket = $item['ticket'] ?? null;
                        $routeRate = $item['route_rate'] ?? 0;
                        $typeDiscountRate = $item['type_discount_rate'] ?? 0;
                        $promo = $item['promo'] ?? null;
                        $hasBreakdown = $ticket && ($routeRate > 0 || $typeDiscountRate > 0 || $promo);
                        if ($hasBreakdown) {
                            $hasAnyBreakdown = true;
                        }
                    @endphp
                @endforeach

                @if ($hasAnyBreakdown)
                    <table style="margin-bottom:12px; font-size:13px;">
                        <thead>
                            <tr>
                                <th>Passenger</th>
                                <th>Base Price</th>
                                <th>Route Rate</th>
                                <th>Type Discount</th>
                                <th>Promo</th>
                                <th>Final Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($allPassengers as $item)
                                @php
                                    $passenger = $item['passenger'] ?? null;
                                    $ticket = $item['ticket'] ?? null;
                                    $basePrice = $item['accommodation_base_price'] ?? null;
                                    $routeRate = (float) ($item['route_rate'] ?? 0);
                                    $typeDiscountRate = (float) ($item['type_discount_rate'] ?? 0);
                                    $promo = $item['promo'] ?? null;
                                    $rateDisplay =
                                        $routeRate > 0
                                            ? '+' . rtrim(rtrim(number_format($routeRate, 2), '0'), '.') . '%'
                                            : '—';
                                    $typeDisplay =
                                        $typeDiscountRate > 0
                                            ? '-' . rtrim(rtrim(number_format($typeDiscountRate, 2), '0'), '.') . '%'
                                            : '—';
                                    $promoDisplay = $promo
                                        ? '-' .
                                            rtrim(rtrim(number_format($promo->promo_discount_rate, 2), '0'), '.') .
                                            '%'
                                        : '—';
                                    $basePriceDisplay = $basePrice !== null ? '₱' . number_format($basePrice, 2) : '—';
                                @endphp
                                <tr>
                                    <td>
                                        {{ $passenger->passenger_firstname ?? '' }}
                                        @if ($passenger && $passenger->passenger_midinitial)
                                            {{ $passenger->passenger_midinitial }}.
                                        @endif
                                        {{ $passenger->passenger_lastname ?? '' }}
                                    </td>
                                    <td>{{ $basePriceDisplay }}</td>
                                    <td>{{ $rateDisplay }}</td>
                                    <td>{{ $typeDisplay }}</td>
                                    <td>{{ $promoDisplay }}</td>
                                    <td><strong>₱{{ number_format($ticket->pt_ticket_price ?? 0, 2) }}</strong></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif

                <p><strong>Total Amount Paid:</strong> ₱{{ number_format($payment->total_amount, 2) }}</p>
                <p><strong>Payment Method:</strong> {{ $payment->mode_of_payment }}</p>
                <p><strong>Payment Status:</strong> {{ $payment->payment_status }}</p>
            @else
                <p>Payment information not available.</p>
            @endif
        </div>

        <!-- Important Reminders -->
        <div class="section">
            <div class="section-title">⚠️ Important Reminders</div>
            <ul>
                <li>Arrive at the terminal <strong>at least 30 minutes</strong> before departure</li>
                <li>Bring a <strong>valid government-issued ID</strong> for verification</li>
                <li>Free hand carry allowance is <strong>7kg per passenger</strong></li>
                <li>Present this ticket and your ID at check-in</li>
                <li>Ticket modifications must be made <strong>at least 2 hours</strong> before departure</li>
                <li>For inquiries, call <strong>(032) 232-8864</strong> or email
                    <strong>lapulapulslc1964@gmail.com</strong>
                </li>
            </ul>
        </div>

        <p>Thank you for booking with <strong>LAPULAPU SHIPPING LINES</strong>. Your passenger tickets are attached to
            this email (one PDF for each passenger).</p>

        <div class="footer">
            <p>This is an automatically generated confirmation email. Please keep this email and all attached PDF
                tickets
                for your records.</p>
        </div>
    </div>
</body>

</html>
