<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Bill of Lading - {{ $booking->booking_ref_no }}</title>
    <style>
        @page { margin: 14mm; }
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 10pt; color: #111; }
        .header { text-align: center; margin-bottom: 12px; }
        .header h1 { margin: 0; font-size: 15pt; }
        .header .sub { margin-top: 4px; font-size: 9pt; }
        .meta, .parties, .ports, .charges { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .meta td, .parties td, .ports td, .charges td, .cargo th, .cargo td { border: 1px solid #000; padding: 6px; vertical-align: top; }
        .label { width: 30%; font-weight: bold; background: #f2f2f2; }
        .cargo { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .cargo th { background: #f2f2f2; font-weight: bold; }
        .right { text-align: right; }
        .center { text-align: center; }
        .sig { width: 100%; border-collapse: collapse; margin-top: 24px; }
        .sig td { text-align: center; padding-top: 32px; font-size: 9pt; }
        .line { border-top: 1px solid #000; padding-top: 5px; display: inline-block; min-width: 170px; }
        .note { margin-top: 10px; font-size: 8.5pt; font-style: italic; }
    </style>
</head>
<body>
@php
    $stamp = 20.00;
    $freight = 0;
    foreach($booking->cargoBookings as $cargo) {
        $cbm = (float) ($cargo->cbm ?? (($cargo->length * $cargo->width * $cargo->height) / 1000000));
        $qty = (float) ($cargo->quantity ?? 0);
        $freightRate = (float) ($cargo->cargoItem->cargo_item_freight ?? 0);
        $freight += $freightRate * $cbm * $qty;
    }
    $total = $freight + $stamp;
@endphp

<div class="header">
    <h1>LAPULAPU SHIPPING LINES CORPORATION</h1>
    <div class="sub">BILL OF LADING</div>
</div>

<table class="meta">
    <tr>
        <td class="label">B/L No.</td>
        <td>{{ $booking->booking_code ?? $booking->booking_ref_no }}</td>
        <td class="label">Voyage No.</td>
        <td>{{ $booking->voyage->voyage_code ?? 'N/A' }}</td>
    </tr>
    <tr>
        <td class="label">Vessel</td>
        <td>{{ $booking->voyage && $booking->voyage->vessel ? $booking->voyage->vessel->vessel_name : ($booking->voyage->vessel_name ?? 'N/A') }}</td>
        <td class="label">Sailing Date</td>
        <td>{{ $booking->voyage ? \Carbon\Carbon::parse($booking->voyage->voyage_departure_date)->format('F d, Y') : 'N/A' }}</td>
    </tr>
</table>

<table class="parties">
    <tr>
        <td class="label">Shipper</td>
        <td>
            <div><strong>{{ $booking->sender->sender_name ?? '' }}</strong></div>
            <div>{{ $booking->sender->sender_contactno ?? '' }}</div>
            <div>{{ $booking->sender->sender_email ?? '' }}</div>
        </td>
        <td class="label">Consignee</td>
        <td>
            <div><strong>{{ $booking->consignee->consignee_name ?? '' }}</strong></div>
            <div>{{ $booking->consignee->consignee_contactno ?? '' }}</div>
        </td>
    </tr>
</table>

<table class="ports">
    <tr>
        <td class="label">Loading Port</td>
        <td>{{ $booking->voyage && $booking->voyage->routePort ? $booking->voyage->routePort->port_origin_name : 'N/A' }}</td>
        <td class="label">Unloading Port</td>
        <td>{{ $booking->voyage && $booking->voyage->routePort ? $booking->voyage->routePort->port_destination_name : 'N/A' }}</td>
    </tr>
</table>

<table class="cargo">
    <thead>
        <tr>
            <th style="width:8%">QTY</th>
            <th style="width:17%">Classification</th>
            <th style="width:23%">Description</th>
            <th style="width:10%">Length</th>
            <th style="width:10%">Width</th>
            <th style="width:10%">Height</th>
            <th style="width:12%">Weight (kg)</th>
        </tr>
    </thead>
    <tbody>
        @foreach($booking->cargoBookings as $cargo)
            @php $unit = $cargo->measurementUnit->measurement_unit_abbreviation ?? 'cm'; @endphp
            <tr>
                <td class="center">{{ $cargo->quantity }}</td>
                <td>{{ $cargo->cargoClassification->cargo_classification_name ?? '' }}</td>
                <td>{{ $cargo->cargoItem->cargo_item_description ?? '' }}</td>
                <td class="center">{{ number_format($cargo->length, 2) . $unit }}</td>
                <td class="center">{{ number_format($cargo->width, 2) . $unit }}</td>
                <td class="center">{{ number_format($cargo->height, 2) . $unit }}</td>
                <td class="center">{{ number_format($cargo->weight, 2) }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

<table class="charges">
    <tr>
        <td class="label">FREIGHT CHARGES</td>
        <td class="right">PHP {{ number_format($freight, 2) }}</td>
    </tr>
    <tr>
        <td class="label">STAMP</td>
        <td class="right">PHP {{ number_format($stamp, 2) }}</td>
    </tr>
    <tr>
        <td class="label"><strong>TOTAL TRANSACTION</strong></td>
        <td class="right"><strong>PHP {{ number_format($total, 2) }}</strong></td>
    </tr>
</table>

<table class="sig">
    <tr>
        <td><span class="line">Shipper/Agent</span></td>
        <td><span class="line">Carrier's Agent</span></td>
        <td><span class="line">Quartermaster</span></td>
    </tr>
</table>

<div class="note">Received the merchandise specified herein in good order and condition. Subject to conditions and quotations printed or written on the original Bill of Lading.</div>
</body>
</html>
