<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>E-Ticket / Itinerary Receipt - #<?php echo e($booking->booking_ref_no); ?></title>
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
            font-size: 23px;
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

    <?php
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
    ?>

    
    
    

    
    <table width="100%" cellpadding="0" cellspacing="0"
        style="margin-bottom:18px; padding:26px 16px; border-radius:4px; background:#1a3a6b;">
        <tr>
            
            <td style="width:130px; vertical-align:middle; padding-left:10px;">
                <img src="<?php echo e(public_path('images/logo_w_name.png')); ?>" width="100" alt="Logo" />
            </td>
            
            <td style="vertical-align:middle; text-align:center; padding:6px 18px;">
                <div class="company-name">LAPULAPU SHIPPING LINES CORPORATION</div>
                <div class="company-sub">872-876 M.J CUENCO AVENUE, CEBU CITY, PHILIPPINES</div>
                <div class="company-sub">Tel. No. 232-8864 / 232-8865 &nbsp;|&nbsp; TIN: 200-308-788-000-VAT</div>
            </td>
            
            <td style="width:130px; vertical-align:middle; text-align:right; padding-right:10px;">
                <div style="font-size:7.5px; color:rgba(255,255,255,0.70);">ISSUED BY</div>
                <div style="font-size:8.5px; font-weight:bold; color:#fff;"><?php echo e($printedBy); ?></div>
                <div style="font-size:7.5px; color:rgba(255,255,255,0.70);"><?php echo e(now()->format('m/d/Y h:i A')); ?></div>
            </td>
        </tr>
    </table>

    
    <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:16px;">
        <tr>
            <td
                style="background:#fff; color:#1a3a6b; text-align:center; font-size:17px; font-weight:bold; padding:7px 0; letter-spacing:3px; word-spacing:4px;">
                &#10004;&nbsp; E-TICKET / ITINERARY RECEIPT &nbsp;&mdash;&nbsp; CONFIRMED &amp; PAID
            </td>
        </tr>
    </table>

    
    <table width="100%" cellpadding="0" cellspacing="0"
        style="background:#f0f4fa; padding:14px 18px; margin-bottom:18px;">
        <tr>
            <td style="font-size:10px; color:#555; vertical-align:top;">
                E-TICKET NO.
                <br><strong
                    style="font-size:18px; color:#1a3a6b;"><?php echo e($firstTicket->passenger_ticket_id ?? 'TKT-' . $booking->booking_ref_no); ?></strong>
            </td>
            <td style="text-align:right; font-size:10px; color:#555; vertical-align:top;">
                BOOKING REFERENCE NO.
                <br><strong style="font-size:18px; color:#1a3a6b;"><?php echo e($booking->booking_ref_no); ?></strong>
            </td>
        </tr>
    </table>

    
    <div
        style="font-weight:bold; font-size:13px; color:#fff; background:#1a3a6b; padding:10px 12px; margin-bottom:12px; letter-spacing:1px;">
        ELECTRONIC TICKET DETAILS
    </div>

    
    <?php $__currentLoopData = $tickets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ticketItem): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php
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
        ?>
        <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:12px; border:1px solid #dde6f4;">
            <tr>
                
                <td width="48%" style="vertical-align:top; padding:16px 18px; border-right:1px dashed #b0c4de;">
                    <table width="100%" cellpadding="0" cellspacing="0">
                        <tr style="margin-bottom:10px;">
                            <td style="font-size:10px; color:#666; width:130px; padding-bottom:10px;">PASSENGER :</td>
                            <td style="font-size:14px; font-weight:bold; color:#1a1a2e; padding-bottom:10px;">
                                <?php echo e(strtoupper($pax->passenger_lastname)); ?>, <?php echo e(strtoupper($pax->passenger_firstname)); ?>

                                <?php if($pax->passenger_midinitial): ?>
                                    <?php echo e(strtoupper($pax->passenger_midinitial)); ?>.
                                <?php endif; ?>
                                <?php if($pax->passenger_suffix): ?>
                                    <?php echo e(strtoupper($pax->passenger_suffix)); ?>

                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td style="font-size:10px; color:#666; padding-bottom:10px;">AGE :</td>
                            <td style="font-size:13px; font-weight:bold; padding-bottom:10px;">
                                <?php echo e($pax->passenger_age ?? 'N/A'); ?>

                                &nbsp;&nbsp;|&nbsp;&nbsp;
                                <span style="font-size:10px; color:#666;">TYPE:</span>
                                <strong style="font-size:13px;"><?php echo e($pax->passenger_type); ?></strong>
                            </td>
                        </tr>
                        <tr>
                            <td style="font-size:10px; color:#666; padding-bottom:10px;">COT NO. :</td>
                            <td style="font-size:13px; font-weight:bold; padding-bottom:10px;">
                                <?php echo e($ticketItem->pt_cot_no); ?></td>
                        </tr>
                        <tr>
                            <td style="font-size:10px; color:#666; padding-bottom:10px;">ACCOMMODATION :</td>
                            <td style="font-size:13px; font-weight:bold; padding-bottom:10px;">
                                <?php echo e(strtoupper($accomName)); ?></td>
                        </tr>
                        <?php if($ticketItem->promo): ?>
                            <tr>
                                <td style="font-size:10px; color:#666; padding-bottom:10px;">PROMO :</td>
                                <td style="font-size:13px; font-weight:bold; color:#c0392b; padding-bottom:10px;">
                                    <?php echo e($ticketItem->promo->promo_code); ?>

                                    (-<?php echo e($ticketItem->promo->promo_discount_rate); ?>%)
                                </td>
                            </tr>
                        <?php endif; ?>
                        <tr>
                            <td colspan="2" style="border-top:1px solid #dde6f4; padding-top:10px;">
                                <table width="100%" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td style="font-size:10px; color:#666; width:130px;">TOTAL :</td>
                                        <td style="font-size:18px; font-weight:bold; color:#1a3a6b;">PHP
                                            <?php echo e(number_format($ticketItem->pt_ticket_price, 2)); ?></td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>
                
                <td width="52%" style="vertical-align:top; padding:16px 18px;">
                    <table width="100%" cellpadding="0" cellspacing="0">
                        <tr>
                            <td style="font-size:10px; color:#666; width:130px; padding-bottom:10px;">ORIGIN :</td>
                            <td style="font-size:16px; font-weight:bold; color:#1a3a6b; padding-bottom:10px;">
                                <?php echo e(strtoupper($route->route_origin ?? 'N/A')); ?></td>
                        </tr>
                        <tr>
                            <td style="font-size:10px; color:#666; padding-bottom:10px;">DESTINATION :</td>
                            <td style="font-size:16px; font-weight:bold; color:#1a3a6b; padding-bottom:10px;">
                                <?php echo e(strtoupper($route->route_destination ?? 'N/A')); ?></td>
                        </tr>
                        <tr>
                            <td style="font-size:10px; color:#666; padding-bottom:10px;">VOYAGE NO. :</td>
                            <td style="font-size:13px; font-weight:bold; padding-bottom:10px;">
                                <?php echo e($voyage->voyage_code ?? 'N/A'); ?></td>
                        </tr>
                        <tr>
                            <td style="font-size:10px; color:#666; padding-bottom:10px;">DEPARTURE DATE :</td>
                            <td style="font-size:13px; font-weight:bold; padding-bottom:10px;">
                                <?php echo e($voyage ? \Carbon\Carbon::parse($voyage->voyage_departure_date)->format('F d, Y') : 'N/A'); ?>

                            </td>
                        </tr>
                        <tr>
                            <td style="font-size:10px; color:#666; padding-bottom:10px;">DEPARTURE TIME :</td>
                            <td style="font-size:13px; font-weight:bold; padding-bottom:10px;">
                                <?php echo e($voyage ? \Carbon\Carbon::parse($voyage->voyage_estimated_TD)->format('g:i A') : 'N/A'); ?>

                            </td>
                        </tr>
                        <tr>
                            <td style="font-size:10px; color:#666;">VESSEL :</td>
                            <td style="font-size:13px; font-weight:bold;"><?php echo e($vessel->vessel_name ?? 'N/A'); ?></td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

    
    
    
    <table width="100%" cellpadding="0" cellspacing="0" style="margin:18px 0 14px 0;">
        <tr>
            <td style="border-top:2px dashed #aaa;"></td>
            <td style="width:28px; text-align:center; font-size:20px; color:#777; padding:0 4px; white-space:nowrap;">✂
            </td>
            <td style="border-top:2px dashed #aaa;"></td>
        </tr>
    </table>

    
    
    
    <table width="100%" cellpadding="0" cellspacing="0">
        <tr>

            
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
                            Passengers may check-in <strong>1 hour prior</strong> to the indicated departure time.
                            Failure to arrive on time at the check-in counter or boarding gate (even if the passenger
                            has already checked in) may result in the cancellation of the passenger's seat. Lapulapu
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

            
            <td width="40%" style="vertical-align:middle; text-align:center; padding-left:14px;">

                <div style="font-weight:bold; font-size:15px; color:#1a3a6b; margin-bottom:8px; letter-spacing:2px;">
                    BOARDING QR CODE</div>
                <div style="font-size:11px; color:#666; margin-bottom:16px;">Present at Terminal Check-in</div>

                <?php if(!empty($qrCodes)): ?>
                    <?php $__currentLoopData = $tickets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ticket): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php if(isset($qrCodes[$ticket->passenger_id])): ?>
                            <img src="<?php echo e($qrCodes[$ticket->passenger_id]); ?>" width="190" height="190"
                                style="border:3px solid #1a3a6b; padding:4px;" /><br>
                            <div style="font-size:13px; font-weight:bold; margin-top:12px; color:#1a1a2e;">
                                <?php echo e(strtoupper($ticket->passenger->passenger_firstname)); ?>

                                <?php if($ticket->passenger->passenger_midinitial): ?>
                                    <?php echo e(strtoupper($ticket->passenger->passenger_midinitial)); ?>.
                                <?php endif; ?>
                                <?php echo e(strtoupper($ticket->passenger->passenger_lastname)); ?>

                                <?php if($ticket->passenger->passenger_suffix): ?>
                                    <?php echo e(strtoupper($ticket->passenger->passenger_suffix)); ?>

                                <?php endif; ?>
                            </div>
                            <div style="font-size:11px; color:#555;">Booking #<?php echo e($booking->booking_ref_no); ?></div>
                        <?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php else: ?>
                    <table cellpadding="0" cellspacing="0" style="margin:0 auto;">
                        <tr>
                            <td
                                style="width:190px; height:190px; border:3px solid #1a3a6b; text-align:center; vertical-align:middle; color:#aaa; font-size:9px;">
                                QR Code<br>Not Available
                            </td>
                        </tr>
                    </table>
                    <div style="font-size:13px; font-weight:bold; margin-top:12px; color:#1a1a2e;">
                        <?php if($firstTicket && $firstTicket->passenger): ?>
                            <?php echo e(strtoupper($firstTicket->passenger->passenger_firstname)); ?>

                            <?php if($firstTicket->passenger->passenger_midinitial): ?>
                                <?php echo e(strtoupper($firstTicket->passenger->passenger_midinitial)); ?>.
                            <?php endif; ?>
                            <?php echo e(strtoupper($firstTicket->passenger->passenger_lastname)); ?>

                            <?php if($firstTicket->passenger->passenger_suffix): ?>
                                <?php echo e(strtoupper($firstTicket->passenger->passenger_suffix)); ?>

                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                    <div style="font-size:11px; color:#555;">Booking #<?php echo e($booking->booking_ref_no); ?></div>
                <?php endif; ?>

            </td>

        </tr>
    </table>

    
    <div
        style="margin-top:16px; text-align:center; font-size:8px; color:#999; border-top:1px solid #dde6f4; padding-top:6px;">
        This is an automatically generated e-ticket. Please keep this document for your records. &nbsp;|&nbsp; Page 1 of
        1
    </div>

</body>

</html>
<?php /**PATH C:\Users\Sophia\Documents\Capstone\resources\views/passenger/passenger_ticket_pdf.blade.php ENDPATH**/ ?>