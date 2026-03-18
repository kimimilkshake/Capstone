<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Passenger Ticket - #{{ $booking->booking_ref_no }}</title>

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

.passenger-header th{
    border:1px solid #111;
    background:#e8eef7;
}

.passenger-row td{
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
$totalAmount = 0;
$printedBy = optional(auth()->guard('staff')->user())->staff_name
    ?? optional(auth()->guard('admin')->user())->admin_name
    ?? 'System';

// Get tickets with passengers
$allTickets = \App\Models\PassengerTicket::where('booking_ref_no', $booking->booking_ref_no)
    ->with('passenger', 'promo')
    ->get();

// Filter tickets by passenger email if provided
if ($passengerEmail) {
    $tickets = $allTickets->filter(function ($ticket) use ($passengerEmail) {
        return $ticket->passenger->passenger_email === $passengerEmail;
    });
} else {
    $tickets = $allTickets;
}

foreach ($tickets as $ticket) {
    $totalAmount += $ticket->pt_ticket_price;
}

$payment = \App\Models\Payment::where('booking_ref_no', $booking->booking_ref_no)->first();
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


<!-- STATUS BADGE -->

<table class="header-table">
<tr>
<td class="center">
<strong style="font-size:14px">✓ CONFIRMED & PAID</strong>
</td>
</tr>
</table>

<br>

<!-- BOOKING INFORMATION -->

<table class="border booking-info">

<tr>

<td style="width:50%" class="border">

<strong>Booking Information:</strong><br><br>

Booking Reference:
#{{ $booking->booking_ref_no }}

<br>

Booking Status:
{{ $booking->booking_status }}

<br>

Booking Date:
{{ $booking->created_at ? \Carbon\Carbon::parse($booking->created_at)->format('F d, Y') : 'N/A' }}

</td>


<td class="border">

<br>

Voyage Code:
{{ $booking->voyage->voyage_code ?? 'N/A' }}

<br>

Departure Date:
{{ $booking->voyage ? \Carbon\Carbon::parse($booking->voyage->voyage_departure_date)->format('F d, Y') : 'N/A' }}

<br>

Departure Time:
{{ $booking->voyage ? \Carbon\Carbon::parse($booking->voyage->voyage_estimated_TD)->format('g:i A') : 'N/A' }}

</td>

</tr>

</table>



<br>


<!-- VOYAGE INFORMATION -->

<div class="section-title">Voyage Details</div>

<table>

<tr>
<td style="width:50%"><strong>Vessel:</strong> {{ $booking->voyage->vessel->vessel_name ?? 'N/A' }}</td>
<td><strong>Route:</strong> {{ $booking->voyage->routePort->route_origin ?? 'N/A' }} → {{ $booking->voyage->routePort->route_destination ?? 'N/A' }}</td>
</tr>

<tr>
<td><strong>Port of Origin:</strong> {{ $booking->voyage->routePort->port_origin_name ?? 'N/A' }}</td>
<td><strong>Port of Destination:</strong> {{ $booking->voyage->routePort->port_destination_name ?? 'N/A' }}</td>
</tr>

</table>



<br>


<!-- PASSENGERS -->

<table>

<tr>
<th colspan="6" class="border" style="text-align:left;">
Passenger Details
</th>
</tr>

<tr class="passenger-header">

<th style="width:5%">No.</th>
<th style="width:25%">Name</th>
<th style="width:12%">Type</th>
<th style="width:8%">Cot #</th>
<th style="width:15%">Accommodation</th>
<th style="width:18%">Price</th>
<th style="width:17%">Promo</th>

</tr>

@forelse($tickets as $index => $ticket)
@php
    $passenger = $ticket->passenger;
    // Find accommodation by COT number
    $accommodation = null;
    if ($booking->voyage && $booking->voyage->vessel && $booking->voyage->vessel->accommodations) {
        foreach ($booking->voyage->vessel->accommodations as $accom) {
            $ranges = explode(',', $accom->accommodation_cot_range);
            foreach ($ranges as $range) {
                $range = trim($range);
                if (strpos($range, '-') !== false) {
                    list($start, $end) = explode('-', $range);
                    if ($ticket->pt_cot_no >= (int)trim($start) && $ticket->pt_cot_no <= (int)trim($end)) {
                        $accommodation = $accom;
                        break 2;
                    }
                } else {
                    if ($ticket->pt_cot_no == (int)trim($range)) {
                        $accommodation = $accom;
                        break 2;
                    }
                }
            }
        }
    }
@endphp

<tr class="passenger-row">

<td class="center">{{ $index + 1 }}</td>

<td>
{{ $passenger->passenger_firstname }}
@if($passenger->passenger_midinitial)
{{ $passenger->passenger_midinitial }}.
@endif
{{ $passenger->passenger_lastname }}
@if($passenger->passenger_suffix)
{{ $passenger->passenger_suffix }}
@endif
</td>

<td class="center">{{ $passenger->passenger_type }}</td>

<td class="center">{{ $ticket->pt_cot_no }}</td>

<td class="center">{{ $accommodation ? $accommodation->accommodation_name : 'N/A' }}</td>

<td class="right">₱{{ number_format($ticket->pt_ticket_price, 2) }}</td>

<td class="center">
@if($ticket->promo)
{{ $ticket->promo->promo_code }} (-{{ $ticket->promo->promo_discount_rate }}%)
@else
None
@endif
</td>

</tr>

@empty

<tr class="passenger-row">
<td colspan="6" class="center">
No passengers found.
</td>
</tr>

@endforelse

</table>



<br>


<!-- CHARGES -->

<table class="charges-table">

<tr>
<td><strong>Subtotal</strong></td>
<td class="right"><strong>₱ {{ number_format($totalAmount, 2) }}</strong></td>
</tr>

@if($payment)
<tr>
<td><strong>Total Amount Paid:</strong></td>
<td class="right"><strong>₱ {{ number_format($payment->total_amount, 2) }}</strong></td>
</tr>

<tr>
<td>Payment Method:</td>
<td class="right">{{ $payment->mode_of_payment }}</td>
</tr>

<tr>
<td>Payment Status:</td>
<td class="right">{{ $payment->payment_status }}</td>
</tr>
@endif

</table>



<br><br><br>

<!-- QR CODE SECTION -->
@if(!empty($qrCodes))
<div class="qr-section">
    <h4>BOARDING QR CODE &mdash; Present at Terminal Check-in</h4>
    <table style="width:100%;">
        <tr>
            @foreach($tickets as $ticket)
                @if(isset($qrCodes[$ticket->passenger_id]))
                    @php $qrPassenger = $ticket->passenger; @endphp
                    <td style="text-align:center; padding:8px;">
                        <img src="{{ $qrCodes[$ticket->passenger_id] }}" width="130" height="130" style="border:1px solid #111;" /><br>
                        <span class="qr-code-label">
                            {{ $qrPassenger->passenger_firstname }} {{ $qrPassenger->passenger_lastname }}<br>
                            Booking #{{ $booking->booking_ref_no }}
                        </span>
                    </td>
                @endif
            @endforeach
        </tr>
    </table>
</div>
@endif


<br><br>






<div class="footer-note">

This is an automatically generated passenger ticket. Please keep this document for your records.

</div>


<div class="page-number">

Page 1 of 1

</div>


</body>
</html>
