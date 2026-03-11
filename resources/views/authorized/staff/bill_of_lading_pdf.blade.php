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

.section{
    background:#8fa9cf;
    font-weight:bold;
    padding:6px;
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

.logo{
    width:90px;
}

.cargo-header th{
    background:#8fa9cf;
    border:1px solid #111;
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


<!-- BOOKING INFO -->

<table class="border">

<tr>

<td style="width:50%" class="border">

<strong>Booking Information:</strong><br>

B/L No: {{ $booking->booking_code ?? $booking->booking_ref_no }}<br>

Sailing Date:
{{ $booking->voyage ? \Carbon\Carbon::parse($booking->voyage->voyage_departure_date)->format('F d, Y') : 'N/A' }}

<br>

Vessel:
{{ $booking->voyage->vessel->vessel_name ?? 'N/A' }}

</td>


<td class="border">

<strong>Booking Reference No:</strong>
{{ $booking->booking_ref_no }}

<br>

Voyage No:
{{ $booking->voyage->voyage_code ?? 'N/A' }}

<br>

Confirmed On:
{{ now()->format('F d, Y') }}

<br>

Confirmed By:
{{ optional(auth()->user())->name ?? 'System' }}

</td>

</tr>

</table>



<br>


<!-- SENDER -->

<div class="section">Sender Information</div>

<table>

<tr>
<td>Name: {{ $booking->sender->sender_name ?? 'N/A' }}</td>
</tr>

<tr>
<td>Contact No: {{ $booking->sender->sender_contactno ?? 'N/A' }}</td>
</tr>

</table>



<br>


<!-- CONSIGNEE -->

<div class="section">Consignee Information</div>

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

<td class="border" style="width:50%">
<strong>Loading Port</strong>
</td>

<td class="border">
<strong>Unloading Port</strong>
</td>

</tr>

<tr>

<td class="border">
{{ $booking->voyage->routePort->port_origin_name ?? 'N/A' }}
</td>

<td class="border">
{{ $booking->voyage->routePort->port_destination_name ?? 'N/A' }}
</td>

</tr>

</table>



<br>


<!-- CARGO TABLE -->

<table>

<tr>
<th colspan="7" class="border" style="text-align:left;">Cargo Items Description</th>
</tr>

<tr class="cargo-header">

<th>QTY</th>
<th>Classification</th>
<th>Description</th>
<th>Length</th>
<th>Width</th>
<th>Height</th>
<th>Weight</th>

</tr>

@foreach($booking->cargoBookings as $cargo)

<tr class="cargo-row">

<td>{{ $cargo->quantity }}</td>

<td>
{{ $cargo->cargoClassification->cargo_classification_name ?? '' }}
</td>

<td>
{{ $cargo->cargoItem->cargo_item_description ?? '' }}
</td>

<td>{{ $cargo->length }}</td>

<td>{{ $cargo->width }}</td>

<td>{{ $cargo->height }}</td>

<td>{{ $cargo->weight }}</td>

</tr>

@endforeach

</table>



<br>


<!-- CHARGES -->

<table style="width:300px; float:right">

<tr>
<td>Freight Charges</td>
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



<div style="clear:both"></div>



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


</body>
</html>