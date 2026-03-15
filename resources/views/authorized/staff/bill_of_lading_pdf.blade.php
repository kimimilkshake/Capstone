<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Bill of Lading - {{ $booking->booking_ref_no }}</title>

<style>

@page { margin:25px; }

body{
    font-family: DejaVu Sans, Arial, sans-serif;
    font-size:11px;
    color:#111;
}

table{
    width:100%;
    border-collapse:collapse;
}

td,th{
    padding:6px;
}

.border{
    border:1px solid #111;
}

.header-table td{
    border:none;
}

.center{
    text-align:center;
}

.right{
    text-align:right;
}

.section-title{
    font-weight:bold;
    padding:6px 0;
    background:#e8eef7;
    border:1px solid #111;
    padding-left:6px;
}

.booking-info td{
    vertical-align:top;
}

.cargo-header th{
    border:1px solid #111;
    background:#e8eef7;
}

.cargo-row td{
    border:1px solid #111;
}

.signature{
    text-align:center;
    padding-top:40px;
}

.signature-line{
    border-top:1px solid #111;
    width:200px;
    margin:auto;
}

.footer-note{
    margin-top:25px;
    text-align:center;
    font-size:10px;
}

.page-number{
    text-align:right;
    font-size:9px;
    margin-top:10px;
}

.charges-table td{
    border:1px solid #fff;
}

</style>
</head>

<body>

@php
$stamp = 20.00;
$freight = 0.0;
$totalPieces = 0;
$totalWeight = 0.0;

foreach ($booking->cargoBookings as $cargo) {

    $qty = (float)($cargo->quantity ?? 0);
    $weight = (float)($cargo->weight ?? 0);

    $freightRate = (float)($cargo->cargoItem->cargo_item_freight ?? 0);
    $cbm = (float)($cargo->cbm ?? 0);

    $freight += $freightRate * $cbm * $qty;
    $totalPieces += $qty;
    $totalWeight += $weight * $qty;
}

$total = $freight + $stamp;

$printedBy = optional(auth()->guard('staff')->user())->staff_name
    ?? optional(auth()->guard('admin')->user())->admin_name
    ?? 'System';
@endphp


<!-- HEADER -->

<table class="header-table">
<tr>
<td class="center">

<strong style="font-size:16px">
LAPULAPU SHIPPING LINES CORPORATION
</strong>

<br>

872-876 M.J CUENCO AVENUE, CEBU CITY

<br>

TEL NO. 232-8864; 232-8865

<br>

TIN: 200-308-788-000-VAT

</td>
</tr>
</table>


<br>


<!-- BOOKING INFORMATION -->

<table class="border booking-info">

<tr>

<td style="width:50%" class="border">

<strong>Booking Information:</strong><br><br>

Voyage No:
{{ $booking->voyage->voyage_code ?? 'N/A' }}

<br>

Sailing Date:
{{ $booking->voyage ? \Carbon\Carbon::parse($booking->voyage->voyage_departure_date)->format('F d, Y') : 'N/A' }}

<br>

Vessel:
{{ $booking->voyage->vessel->vessel_name ?? 'N/A' }}

</td>


<td class="border">

<br>

Booking Reference No:
{{ $booking->booking_code ?? $booking->booking_ref_no}}

<br>

Confirmed On:
{{ now()->format('F d, Y') }}

<br>

Confirmed By:
{{ $printedBy }}

</td>

</tr>

</table>



<br>


<!-- SENDER INFORMATION -->

<div class="section-title">Sender Information</div>

<table>

<tr>
<td>Name: {{ $booking->sender->sender_name ?? 'N/A' }}</td>
</tr>

<tr>
<td>Contact No: {{ $booking->sender->sender_contactno ?? 'N/A' }}</td>
</tr>

</table>



<br>


<!-- CONSIGNEE INFORMATION -->

<div class="section-title">Consignee Information</div>

<table>

<tr>
<td>Name: {{ $booking->consignee->consignee_name ?? 'N/A' }}</td>
</tr>

<tr>
<td>Contact No: {{ $booking->consignee->consignee_contactno ?? 'N/A' }}</td>
</tr>

</table>



<br>


<!-- PORTS -->

<table class="border">

<tr>

<td class="border left" style="width:50%">
<strong>Loading Port</strong>
</td>

<td class="border left">
<strong>Unloading Port</strong>
</td>

</tr>

<tr>

<td class="border left">
{{ $booking->voyage->routePort->port_origin_name ?? 'N/A' }}
</td>

<td class="border left">
{{ $booking->voyage->routePort->port_destination_name ?? 'N/A' }}
</td>

</tr>

</table>



<br>


<!-- CARGO ITEMS -->

<table>

<tr>
<th colspan="8" class="border" style="text-align:left;">
Cargo Items Description
</th>
</tr>

<tr class="cargo-header">

<th style="width:8%">QTY</th>
<th style="width:18%">Classification</th>
<th style="width:32%">Description</th>
<th style="width:10%">Length</th>
<th style="width:10%">Width</th>
<th style="width:10%">Height</th>
<th style="width:12%">Weight</th>
<th style="width:12%">Subtotal</th>

</tr>

@forelse($booking->cargoBookings as $cargo)
@php
    $unit = $cargo->measurementUnit->measurement_unit_abbreviation ?? 'cm';
    $qty = (float) ($cargo->quantity ?? 0);
    $freightRate = (float) ($cargo->cargoItem->cargo_item_freight ?? 0);
    $cbm = (float) ($cargo->cbm ?? 0);
    $subtotal = $freightRate * $cbm * $qty;
@endphp

<tr class="cargo-row">

<td class="center">{{ $cargo->quantity }}</td>

<td>
{{ $cargo->cargoClassification->cargo_classification_name ?? 'General Cargo' }}
</td>

<td>
{{ $cargo->cargoItem->cargo_item_description ?? 'N/A' }}
</td>

<td class="center">{{ $cargo->length }}{{ $unit }}</td>

<td class="center">{{ $cargo->width }}{{ $unit }}</td>

<td class="center">{{ $cargo->height }}{{ $unit }}</td>

<td class="center">{{ $cargo->weight }}kg</td>

<td class="right">₱{{ number_format($subtotal, 2) }}</td>

</tr>

@empty

<tr class="cargo-row">
<td colspan="8" class="center">
No cargo items found.
</td>
</tr>

@endforelse

</table>



<br>


<!-- CHARGES (NOW SAME WIDTH ALIGNMENT AS CARGO TABLE) -->

<table class="charges-table">

<tr>
<td style="width:80%">Freight Charges</td>
<td class="right">₱ {{ number_format($freight,2) }}</td>
</tr>

<tr>
<td>Stamp</td>
<td class="right">₱ {{ number_format($stamp,2) }}</td>
</tr>

<tr>
<td><strong>Total Transaction</strong></td>
<td class="right"><strong>₱ {{ number_format($total,2) }}</strong></td>
</tr>

</table>



<br><br><br>



<!-- SIGNATURES -->

<table>

<tr>

<td class="signature">
<div class="signature-line"></div>
Arrastre Payment
</td>

<td class="signature">
<div class="signature-line"></div>
Doc Stamp
</td>

<td class="signature">
<div class="signature-line"></div>
Quartermaster
</td>

</tr>

</table>



<div class="footer-note">

Printing of the Bill of Lading, Arrastre Payment and Doc Stamp will be done in the office.

</div>


<div class="page-number">

Page 1 of 1

</div>


</body>
</html>