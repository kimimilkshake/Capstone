<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>E-Ticket / Itinerary Receipt - #{{ $booking->booking_ref_no }}</title>
    <style>
        @page {
            margin: 18px;
            size: A4;
        }

        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 10px;
            color: #1a1a2e;
            margin: 0;
            padding: 0;
            background: #fff;
        }

        table {
            border-collapse: collapse;
        }

        * {
            box-sizing: border-box;
        }

        .company-name {
            font-size: 20px;
            font-weight: bold;
            color: #fff;
            letter-spacing: 1.5px;
        }

        .company-sub {
            font-size: 9.5px;
            color: rgba(255, 255, 255, 0.85);
            margin-top: 5px;
        }
    </style>
</head>

<body>

    @php
        $totalAmount = 0;
        $printedBy =
            optional(auth()->guard('staff')->user())->staff_name ??
            (optional(auth()->guard('admin')->user())->admin_name ?? 'System');

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
        $voyage = $booking->voyage;
        $route = $voyage ? $voyage->routePort : null;
        $vessel = $voyage ? $voyage->vessel : null;
        $firstTicket = $tickets->first();
    @endphp

    {{-- ================================================================ --}}
    {{-- ====================== MAIN TICKET AREA ======================== --}}
    {{-- ================================================================ --}}

    {{-- ---- HEADER ---- --}}
    <table width="100%" cellpadding="0" cellspacing="0"
        style="margin-bottom:18px; padding:26px 16px; border-radius:4px; background:#1a3a6b;">
        <tr>
            {{-- Logo --}}
            <td style="width:160px; vertical-align:middle; text-align:center;">
                <img src="{{ public_path('images/logo_wo_name.png') }}" width="120" alt="Logo" />
            </td>
            {{-- Company Info --}}
            <td style="vertical-align:middle; text-align:center; padding:6px 18px;">
                <div class="company-name">LAPULAPU SHIPPING LINES CORPORATION</div>
                <div class="company-sub">872-876 M.J CUENCO AVENUE, CEBU CITY, PHILIPPINES</div>
                <div class="company-sub">Tel. No. 232-8864 / 232-8865 &nbsp;|&nbsp; TIN: 200-308-788-000-VAT</div>
            </td>
            {{-- Spacer to balance logo width --}}
            <td style="width:160px;"></td>

        </tr>
    </table>

    {{-- ---- TITLE BANNER ---- --}}
    <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:16px;">
        <tr>
            <td
                style="background:#fff; color:#1a3a6b; text-align:center; font-size:17px; font-weight:bold; padding:7px 0; letter-spacing:3px; word-spacing:4px;">
                &#10004;&nbsp; E-TICKET / ITINERARY RECEIPT &nbsp;&mdash;&nbsp; CONFIRMED &amp; PAID
            </td>
        </tr>
    </table>

    {{-- ---- E-TICKET NO / BOOKING REF ---- --}}
    @php
        $bookingYear = $booking->created_at ? $booking->created_at->format('y') : date('y');
        $formattedBookingRef = 'LSLCBK' . $bookingYear . str_pad($booking->booking_ref_no, 6, '0', STR_PAD_LEFT);
        $ticketYear = $firstTicket && $firstTicket->created_at ? $firstTicket->created_at->format('y') : date('y');
        $formattedETicket =
            'LSLCTKT' .
            $ticketYear .
            str_pad($firstTicket->passenger_ticket_id ?? $booking->booking_ref_no, 6, '0', STR_PAD_LEFT);
    @endphp
    <table width="100%" cellpadding="0" cellspacing="0"
        style="background:#f0f4fa; padding:14px 18px; margin-bottom:18px;">
        <tr>
            <td style="font-size:10px; color:#555; vertical-align:top;">
                E-TICKET NO.
                <br><strong style="font-size:18px; color:#1a3a6b;">{{ $formattedETicket }}</strong>
            </td>
            <td style="text-align:right; font-size:10px; color:#555; vertical-align:top;">
                BOOKING REFERENCE NO.
                <br><strong style="font-size:18px; color:#1a3a6b;">{{ $formattedBookingRef }}</strong>
            </td>
        </tr>
    </table>

    {{-- ---- SECTION LABEL ---- --}}
    <div
        style="font-weight:bold; font-size:13px; color:#fff; background:#1a3a6b; padding:10px 12px; margin-bottom:12px; letter-spacing:1px;">
        ELECTRONIC TICKET DETAILS
    </div>

    {{-- ---- PASSENGER + VOYAGE DETAILS (two columns) ---- --}}
    @foreach ($tickets as $ticketItem)
        @php
            $pax = $ticketItem->passenger;
            $accomName = 'N/A';
            if ($vessel && $vessel->accommodations) {
                foreach ($vessel->accommodations as $accom) {
                    $ranges = explode(',', $accom->accommodation_cot_range);
                    foreach ($ranges as $range) {
                        $range = trim($range);
                        if (strpos($range, '-') !== false) {
                            [$start, $end] = explode('-', $range);
                            if (
                                $ticketItem->pt_cot_no >= (int) trim($start) &&
                                $ticketItem->pt_cot_no <= (int) trim($end)
                            ) {
                                $accomName = $accom->accommodation_name;
                                break 2;
                            }
                        } elseif ($ticketItem->pt_cot_no == (int) trim($range)) {
                            $accomName = $accom->accommodation_name;
                            break 2;
                        }
                    }
                }
            }
        @endphp
        <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:12px; border:1px solid #dde6f4;">
            <tr>
                {{-- LEFT: Passenger details --}}
                <td width="48%" style="vertical-align:top; padding:16px 18px; border-right:1px dashed #b0c4de;">
                    <table width="100%" cellpadding="0" cellspacing="0">
                        <tr style="margin-bottom:10px;">
                            <td style="font-size:10px; color:#666; width:130px; padding-bottom:10px;">PASSENGER :</td>
                            <td style="font-size:14px; font-weight:bold; color:#1a1a2e; padding-bottom:10px;">
                                {{ strtoupper($pax->passenger_lastname) }}, {{ strtoupper($pax->passenger_firstname) }}
                                @if ($pax->passenger_midinitial)
                                    {{ strtoupper($pax->passenger_midinitial) }}.
                                @endif
                                @if ($pax->passenger_suffix)
                                    {{ strtoupper($pax->passenger_suffix) }}
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td style="font-size:10px; color:#666; padding-bottom:10px;">AGE :</td>
                            <td style="font-size:13px; font-weight:bold; padding-bottom:10px;">
                                {{ $pax->passenger_age ?? 'N/A' }}
                                &nbsp;&nbsp;|&nbsp;&nbsp;
                                <span style="font-size:10px; color:#666;">TYPE:</span>
                                <strong style="font-size:13px;">{{ $pax->passenger_type }}</strong>
                            </td>
                        </tr>
                        <tr>
                            <td style="font-size:10px; color:#666; padding-bottom:10px;">COT NO. :</td>
                            <td style="font-size:13px; font-weight:bold; padding-bottom:10px;">
                                {{ $ticketItem->pt_cot_no }}</td>
                        </tr>
                        <tr>
                            <td style="font-size:10px; color:#666; padding-bottom:10px;">ACCOMMODATION :</td>
                            <td style="font-size:13px; font-weight:bold; padding-bottom:10px;">
                                {{ strtoupper($accomName) }}</td>
                        </tr>
                        @if ($ticketItem->promo)
                            <tr>
                                <td style="font-size:10px; color:#666; padding-bottom:10px;">PROMO :</td>
                                <td style="font-size:13px; font-weight:bold; color:#c0392b; padding-bottom:10px;">
                                    {{ $ticketItem->promo->promo_code }}
                                    (-{{ $ticketItem->promo->promo_discount_rate }}%)
                                </td>
                            </tr>
                        @endif
                        <tr>
                            <td colspan="2" style="border-top:1px solid #dde6f4; padding-top:10px;">
                                <table width="100%" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td style="font-size:10px; color:#666; width:130px;">TOTAL :</td>
                                        <td style="font-size:18px; font-weight:bold; color:#1a3a6b;">PHP
                                            {{ number_format($ticketItem->pt_ticket_price, 2) }}</td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>
                {{-- RIGHT: Voyage details --}}
                <td width="52%" style="vertical-align:top; padding:16px 18px;">
                    <table width="100%" cellpadding="0" cellspacing="0">
                        <tr>
                            <td style="font-size:10px; color:#666; width:130px; padding-bottom:10px;">ORIGIN :</td>
                            <td style="font-size:16px; font-weight:bold; color:#1a3a6b; padding-bottom:10px;">
                                {{ strtoupper($route->route_origin ?? 'N/A') }}</td>
                        </tr>
                        <tr>
                            <td style="font-size:10px; color:#666; padding-bottom:10px;">DESTINATION :</td>
                            <td style="font-size:16px; font-weight:bold; color:#1a3a6b; padding-bottom:10px;">
                                {{ strtoupper($route->route_destination ?? 'N/A') }}</td>
                        </tr>
                        <tr>
                            <td style="font-size:10px; color:#666; padding-bottom:10px;">VOYAGE NO. :</td>
                            <td style="font-size:13px; font-weight:bold; padding-bottom:10px;">
                                {{ $voyage->voyage_code ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td style="font-size:10px; color:#666; padding-bottom:10px;">DEPARTURE DATE :</td>
                            <td style="font-size:13px; font-weight:bold; padding-bottom:10px;">
                                {{ $voyage ? \Carbon\Carbon::parse($voyage->voyage_departure_date)->format('F d, Y') : 'N/A' }}
                            </td>
                        </tr>
                        <tr>
                            <td style="font-size:10px; color:#666; padding-bottom:10px;">DEPARTURE TIME :</td>
                            <td style="font-size:13px; font-weight:bold; padding-bottom:10px;">
                                {{ $voyage ? \Carbon\Carbon::parse($voyage->voyage_estimated_TD)->format('g:i A') : 'N/A' }}
                            </td>
                        </tr>
                        <tr>
                            <td style="font-size:10px; color:#666;">VESSEL :</td>
                            <td style="font-size:13px; font-weight:bold;">{{ $vessel->vessel_name ?? 'N/A' }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    @endforeach

    {{-- ================================================================ --}}
    {{-- ====================== SCISSOR CUT LINE ======================== --}}
    {{-- ================================================================ --}}
    <table width="100%" cellpadding="0" cellspacing="0" style="margin:18px 0 14px 0;">
        <tr>
            <td style="border-top:2px dashed #aaa;"></td>
        </tr>
    </table>

    {{-- ================================================================ --}}
    {{-- ============ BOTTOM: REMINDERS (left) + QR CODE (right) ======= --}}
    {{-- ================================================================ --}}
    <table width="100%" cellpadding="0" cellspacing="0">
        <tr>

            {{-- ---- LEFT: REMINDERS + T&C ---- --}}
            <td width="60%" style="vertical-align:top; padding-right:14px; border-right:1px dashed #ccc;">

                <div
                    style="font-weight:bold; font-size:12px; background:#1a3a6b; color:#fff; padding:5px 8px; margin-bottom:6px; letter-spacing:1px;">
                    REMINDERS
                </div>

                <table cellpadding="2" cellspacing="0" width="100%">
                    <tr>
                        <td style="vertical-align:top; width:12px; font-size:9px; color:#1a3a6b; font-weight:bold;">•
                        </td>
                        <td style="font-size:9px; line-height:1.55;">
                            Passengers may check-in <strong>2 hours prior</strong> to the indicated departure time.
                            Failure to arrive on time at the check-in counter or boarding gate (even if the passenger
                            has already checked in) may result in the cancellation of the passenger's
                            <strong>cot</strong>. Lapulapu
                            Shipping Lines Corporation shall not be liable to the passengers for any loss or expense as
                            a consequence thereto.
                        </td>
                    </tr>
                    <tr>
                        <td style="vertical-align:top; font-size:9px; color:#1a3a6b; font-weight:bold;">•</td>
                        <td style="font-size:9px; line-height:1.55;">
                            Please take note all passengers must present <strong>valid IDs</strong> upon check-in.
                        </td>
                    </tr>
                    <tr>
                        <td style="vertical-align:top; font-size:9px; color:#1a3a6b; font-weight:bold;">•</td>
                        <td style="font-size:9px; line-height:1.55;">
                            <strong>PWD</strong> must present a valid identification card issued by the National Council
                            on Disability Affairs (NCDA) or local government unit.
                        </td>
                    </tr>
                    <tr>
                        <td style="vertical-align:top; font-size:9px; color:#1a3a6b; font-weight:bold;">•</td>
                        <td style="font-size:9px; line-height:1.55;">
                            <strong>Seniors</strong> must present a valid ID issued by Senior Citizen Affairs or any
                            government ID which reflects their face, name, and birthdate.
                        </td>
                    </tr>
                    <tr>
                        <td style="vertical-align:top; font-size:9px; color:#1a3a6b; font-weight:bold;">•</td>
                        <td style="font-size:9px; line-height:1.55;">
                            <strong>Students</strong> must present their current school ID, report card, or study load.
                        </td>
                    </tr>
                </table>

                <div
                    style="font-weight:bold; font-size:11px; background:#1a3a6b; color:#fff; padding:4px 8px; margin-top:10px; margin-bottom:5px; letter-spacing:1px;">
                    TERMS &amp; CONDITIONS
                </div>
                <div style="font-size:8.5px; line-height:1.55; color:#333;">
                    This e-ticket is likewise subject to the same terms and conditions printed at the back of the
                    regular pre-printed tickets/promo tickets, those posted in the terminal area, at the vessel and
                    those indicated in the website.
                    The company is not liable for any non-compliance of travel requirements and will not issue a full
                    refund of tickets for this reason.
                </div>

            </td>

            {{-- ---- RIGHT: QR CODE ---- --}}
            <td width="40%" style="vertical-align:middle; text-align:center; padding-left:14px;">

                <div style="font-weight:bold; font-size:15px; color:#1a3a6b; margin-bottom:8px; letter-spacing:2px;">
                    BOARDING QR CODE</div>
                <div style="font-size:11px; color:#666; margin-bottom:16px;">Present at Terminal Check-in</div>

                @if (!empty($qrCodes))
                    @foreach ($tickets as $ticket)
                        @if (isset($qrCodes[$ticket->passenger_id]))
                            <img src="{{ $qrCodes[$ticket->passenger_id] }}" width="190" height="190"
                                style="border:3px solid #1a3a6b; padding:4px;" /><br>
                            <div style="font-size:13px; font-weight:bold; margin-top:12px; color:#1a1a2e;">
                                {{ strtoupper($ticket->passenger->passenger_firstname) }}
                                @if ($ticket->passenger->passenger_midinitial)
                                    {{ strtoupper($ticket->passenger->passenger_midinitial) }}.
                                @endif
                                {{ strtoupper($ticket->passenger->passenger_lastname) }}
                                @if ($ticket->passenger->passenger_suffix)
                                    {{ strtoupper($ticket->passenger->passenger_suffix) }}
                                @endif
                            </div>
                            <div style="font-size:11px; color:#555;">{{ $formattedBookingRef }}</div>
                        @endif
                    @endforeach
                @else
                    <table cellpadding="0" cellspacing="0" style="margin:0 auto;">
                        <tr>
                            <td
                                style="width:190px; height:190px; border:3px solid #1a3a6b; text-align:center; vertical-align:middle; color:#aaa; font-size:9px;">
                                QR Code<br>Not Available
                            </td>
                        </tr>
                    </table>
                    <div style="font-size:13px; font-weight:bold; margin-top:12px; color:#1a1a2e;">
                        @if ($firstTicket && $firstTicket->passenger)
                            {{ strtoupper($firstTicket->passenger->passenger_firstname) }}
                            @if ($firstTicket->passenger->passenger_midinitial)
                                {{ strtoupper($firstTicket->passenger->passenger_midinitial) }}.
                            @endif
                            {{ strtoupper($firstTicket->passenger->passenger_lastname) }}
                            @if ($firstTicket->passenger->passenger_suffix)
                                {{ strtoupper($firstTicket->passenger->passenger_suffix) }}
                            @endif
                        @endif
                    </div>
                    <div style="font-size:11px; color:#555;">{{ $formattedBookingRef }}</div>
                @endif

            </td>

        </tr>
    </table>

    {{-- ---- FOOTER NOTE ---- --}}
    <div
        style="margin-top:16px; text-align:center; font-size:8px; color:#999; border-top:1px solid #dde6f4; padding-top:6px;">
        This is an automatically generated e-ticket. Please keep this document for your records. &nbsp;|&nbsp; Page 1 of
        1
        <br>
        Issued by System &nbsp;|&nbsp; {{ now()->format('F d, Y h:i A') }}
    </div>

</body>

</html>
