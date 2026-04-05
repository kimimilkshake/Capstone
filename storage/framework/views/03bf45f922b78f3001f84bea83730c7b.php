<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Freight Receipt for <?php echo e($booking->booking_code); ?></title>
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
            padding: 20px;
            background: #f5f5f5;
        }

        .document-container {
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
            padding: 30px 35px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border: 1px solid #ddd;
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
    <div class="document-container">

    <?php
        $printedBy =
            optional(auth()->guard('staff')->user())->staff_name ??
            (optional(auth()->guard('admin')->user())->admin_name ?? 'System');

        // Get cargo bookings
        $cargoBookings = $booking->cargoBookings;

        // Calculate totals
        $totalAmount = 0;
        $totalCBM = 0;
        $totalWeight = 0;

        // Unit conversion helper
        function toMeters($value, $unit) {
            $unit = strtolower($unit);
            return match ($unit) {
                'm'   => $value,
                'cm'  => $value / 100,
                'in'  => $value * 0.0254,
                'ft'  => $value * 0.3048,
                default => $value
            };
        }

        // CBM calculation
        function computeCBM($cargo) {
            $unit = $cargo->measurementUnit->measurement_unit_abbreviation ?? 'cm';
            $length = toMeters((float)$cargo->length, $unit);
            $width  = toMeters((float)$cargo->width, $unit);
            $height = toMeters((float)$cargo->height, $unit);
            return $length * $width * $height;
        }

        // Subtotal calculation
        function computeSubtotal($cargo) {
            $freight = $cargo->cargoItem->cargo_item_freight ?? 0;
            $qty = (float) ($cargo->quantity ?? 0);
            $measureRequired = strtolower($cargo->cargoItem->cargo_item_measure_required ?? 'no');

            if ($measureRequired === 'yes') {
                return $freight * $qty;
            }
            return $freight * computeCBM($cargo) * $qty;
        }

        $cargoData = [];
        foreach ($cargoBookings as $c) {
            $cbm = computeCBM($c);
            $subtotal = computeSubtotal($c);
            $totalAmount += $subtotal;
            $totalCBM += $cbm * $c->quantity;
            $totalWeight += $c->weight * $c->quantity;
            $cargoData[] = [
                'cargo' => $c,
                'cbm' => $cbm,
                'subtotal' => $subtotal
            ];
        }

        $stamp = 20.00;
        $grandTotal = $totalAmount + $stamp;

        $payment = \App\Models\Payment::where('booking_ref_no', $booking->booking_ref_no)->first();
        $voyage = $booking->voyage;
        $route = $voyage ? $voyage->routePort : null;
        $vessel = $voyage ? $voyage->vessel : null;
        $sender = $booking->sender;
        $consignee = $booking->consignee;

        $processedBy = optional(optional($cargoBookings->first())->approvedByStaff)->staff_name;
        $processedLabel = 'N/A';
        if ($processedBy) {
            if ($booking->booking_status === 'Confirmed') {
                $processedLabel = 'Approved by ' . $processedBy;
            } elseif ($booking->booking_status === 'Canceled') {
                $processedLabel = 'Canceled by ' . $processedBy;
            } else {
                $processedLabel = $processedBy;
            }
        }

        $bookingYear = $booking->created_at ? $booking->created_at->format('y') : date('y');
        $formattedBookingRef = 'LSLCBK' . $bookingYear . str_pad($booking->booking_ref_no, 6, '0', STR_PAD_LEFT);
    ?>

    
    
    

    
    <table width="100%" cellpadding="0" cellspacing="0"
        style="margin-bottom:18px; padding:26px 16px; border-radius:4px; background:#1a3a6b;">
        <tr>
            
            <td style="width:160px; vertical-align:middle; text-align:center;">
                <img src="<?php echo e(asset('images/logo_wo_name.png')); ?>" width="90" alt="Logo" />
            </td>
            
            <td style="vertical-align:middle; text-align:center; padding:6px 18px;">
                <div class="company-name">LAPULAPU SHIPPING LINES CORPORATION</div>
                <div class="company-sub">872-876 M.J CUENCO AVENUE, CEBU CITY, PHILIPPINES</div>
                <div class="company-sub">Tel. No. 232-8864 / 232-8865 &nbsp;|&nbsp; TIN: 200-308-788-000-VAT</div>
            </td>
            
            <td style="width:160px;"></td>

        </tr>
    </table>

    
    <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:16px;">
        <tr>
            <td
                style="background:#fff; color:#1a3a6b; text-align:center; font-size:17px; font-weight:bold; padding:7px 0; letter-spacing:3px; word-spacing:4px;">
                &#10004;&nbsp; CARGO &nbsp;&mdash;&nbsp; FREIGHT RECEIPT
            </td>
        </tr>
    </table>

    
    <table width="100%" cellpadding="0" cellspacing="0"
        style="background:#f0f4fa; padding:14px 18px; margin-bottom:18px;">
        <tr>
            <td style="font-size:10px; color:#555; vertical-align:top;">
                BOOKING REFERENCE NO.
                <br><strong style="font-size:18px; color:#1a3a6b;"><?php echo e($formattedBookingRef); ?></strong>
            </td>
            <td style="text-align:right; font-size:10px; color:#555; vertical-align:top;">
                STATUS
                <br><strong style="font-size:18px; color:#1a3a6b;"><?php echo e($booking->booking_status ?? 'N/A'); ?></strong>
            </td>
        </tr>
    </table>

    
    <div
        style="font-weight:bold; font-size:13px; color:#fff; background:#1a3a6b; padding:10px 12px; margin-bottom:12px; letter-spacing:1px;">
        FREIGHT & CARGO DETAILS
    </div>

    
    <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:12px; border:1px solid #dde6f4;">
        <tr>
            
            <td width="48%" style="vertical-align:top; padding:16px 18px; border-right:1px dashed #b0c4de;">
                <table width="100%" cellpadding="0" cellspacing="0">
                    
                    <tr>
                        <td colspan="2" style="font-size:11px; font-weight:bold; color:#1a3a6b; padding-bottom:8px; border-bottom:1px solid #dde6f4; margin-bottom:8px;">
                            SHIPPER / SENDER
                        </td>
                    </tr>
                    <tr>
                        <td style="font-size:10px; color:#666; width:100px; padding-bottom:6px;">NAME :</td>
                        <td style="font-size:11px; font-weight:bold; color:#1a1a2e; padding-bottom:6px;">
                            <?php echo e(strtoupper($sender->sender_name ?? 'N/A')); ?>

                        </td>
                    </tr>
                    <tr>
                        <td style="font-size:10px; color:#666; padding-bottom:6px;">CONTACT :</td>
                        <td style="font-size:11px; padding-bottom:6px;">
                            <?php echo e($sender->sender_contactno ?? 'N/A'); ?>

                        </td>
                    </tr>
                    <tr>
                        <td style="font-size:10px; color:#666; padding-bottom:12px;">EMAIL :</td>
                        <td style="font-size:11px; padding-bottom:12px;">
                            <?php echo e($sender->sender_email ?? 'N/A'); ?>

                        </td>
                    </tr>

                    
                    <tr>
                        <td colspan="2" style="font-size:11px; font-weight:bold; color:#1a3a6b; padding-bottom:8px; border-bottom:1px solid #dde6f4; margin-bottom:8px;">
                            CONSIGNEE
                        </td>
                    </tr>
                    <tr>
                        <td style="font-size:10px; color:#666; width:100px; padding-bottom:6px;">NAME :</td>
                        <td style="font: size 11px;px; font-weight:bold; color:#1a1a2e; padding-bottom:6px;">
                            <?php echo e(strtoupper($consignee->consignee_name ?? 'N/A')); ?>

                        </td>
                    </tr>
                    <tr>
                        <td style="font-size:10px; color:#666; padding-bottom:6px;">CONTACT :</td>
                        <td style="font-size:11px; padding-bottom:6px;">
                            <?php echo e($consignee->consignee_contactno ?? 'N/A'); ?>

                        </td>
                    </tr>
                </table>
            </td>
            
            <td width="52%" style="vertical-align:top; padding:16px 18px;">
                <table width="100%" cellpadding="0" cellspacing="0">
                        <tr>
                            <td style="font-size:10px; color:#666; width:150px; padding-bottom:10px;">PORT OF ORIGIN :</td>
                            <td style="font-size:14px; font-weight:bold; color:#1a3a6b; padding-bottom:10px;">
                                <?php echo e(strtoupper($route->route_origin ?? 'N/A')); ?></td>
                        </tr>
                        <tr>
                            <td style="font-size:10px; color:#666; padding-bottom:10px;">PORT OF DESTINATION :</td>
                            <td style="font-size:14px; font-weight:bold; color:#1a3a6b; padding-bottom:10px;">
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

    
    
    
    <div
        style="font-weight:bold; font-size:12px; color:#fff; background:#1a3a6b; padding:8px 12px; margin-bottom:8px; letter-spacing:1px;">
        CARGO ITEMS
    </div>

    <table width="100%" cellpadding="0" cellspacing="0"
        style="margin-bottom:12px; border:1px solid #dde6f4; font-size:9px;">
        <thead>
            <tr style="background:#f0f4fa;">
                <th style="padding:8px 6px; text-align:center; border-bottom:1px solid #dde6f4; font-weight:bold; color:#1a3a6b;">QTY</th>
                <th style="padding:8px 6px; text-align:left; border-bottom:1px solid #dde6f4; font-weight:bold; color:#1a3a6b;">CLASSIFICATION</th>
                <th style="padding:8px 6px; text-align:left; border-bottom:1px solid #dde6f4; font-weight:bold; color:#1a3a6b;">DESCRIPTION</th>
                <th style="padding:8px 6px; text-align:center; border-bottom:1px solid #dde6f4; font-weight:bold; color:#1a3a6b;">L (cm)</th>
                <th style="padding:8px 6px; text-align:center; border-bottom:1px solid #dde6f4; font-weight:bold; color:#1a3a6b;">W (cm)</th>
                <th style="padding:8px 6px; text-align:center; border-bottom:1px solid #dde6f4; font-weight:bold; color:#1a3a6b;">H (cm)</th>
                <th style="padding:8px 6px; text-align:center; border-bottom:1px solid #dde6f4; font-weight:bold; color:#1a3a6b;">CBM</th>
                <th style="padding:8px 6px; text-align:center; border-bottom:1px solid #dde6f4; font-weight:bold; color:#1a3a6b;">WEIGHT (kg)</th>
                <th style="padding:8px 6px; text-align:right; border-bottom:1px solid #dde6f4; font-weight:bold; color:#1a3a6b;">FREIGHT</th>
                <th style="padding:8px 6px; text-align:right; border-bottom:1px solid #dde6f4; font-weight:bold; color:#1a3a6b;">SUBTOTAL</th>
            </tr>
        </thead>
        <tbody>
            <?php $__currentLoopData = $cargoData; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $c = $item['cargo'];
                ?>
                <tr>
                    <td style="padding:8px 6px; text-align:center; border-bottom:1px solid #dde6f4;"><?php echo e($c->quantity); ?></td>
                    <td style="padding:8px 6px; text-align:left; border-bottom:1px solid #dde6f4;">
                        <?php echo e($c->cargoClassification->cargo_classification_name ?? 'N/A'); ?>

                    </td>
                    <td style="padding:8px 6px; text-align:left; border-bottom:1px solid #dde6f4;">
                        <?php echo e($c->cargoItem->cargo_item_description ?? 'N/A'); ?>

                    </td>
                    <td style="padding:8px 6px; text-align:center; border-bottom:1px solid #dde6f4;"><?php echo e(number_format($c->length, 2)); ?></td>
                    <td style="padding:8px 6px; text-align:center; border-bottom:1px solid #dde6f4;"><?php echo e(number_format($c->width, 2)); ?></td>
                    <td style="padding:8px 6px; text-align:center; border-bottom:1px solid #dde6f4;"><?php echo e(number_format($c->height, 2)); ?></td>
                    <td style="padding:8px 6px; text-align:center; border-bottom:1px solid #dde6f4;"><?php echo e(number_format($item['cbm'], 4)); ?></td>
                    <td style="padding:8px 6px; text-align:center; border-bottom:1px solid #dde6f4;"><?php echo e(number_format($c->weight, 2)); ?></td>
                    <td style="padding:8px 6px; text-align:right; border-bottom:1px solid #dde6f4;">₱<?php echo e(number_format($c->cargoItem->cargo_item_freight ?? 0, 2)); ?></td>
                    <td style="padding:8px 6px; text-align:right; border-bottom:1px solid #dde6f4;">₱<?php echo e(number_format($item['subtotal'], 2)); ?></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
        <tfoot>
            <tr style="background:#f0f4fa;">
                <td colspan="6" style="padding:10px 6px; text-align:right; font-weight:bold; color:#1a3a6b;"></td>
                <td style="padding:10px 6px; text-align:center; font-weight:bold; color:#1a3a6b;"></td>
                <td style="padding:10px 6px; text-align:center; font-weight:bold; color:#1a3a6b;"></td>
                <td style="padding:10px 6px; text-align:right; font-weight:bold; color:#1a3a6b;">SUBTOTAL:</td>
                <td style="padding:10px 6px; text-align:right; font-weight:bold; color:#1a3a6b;">₱<?php echo e(number_format($totalAmount, 2)); ?></td>
            </tr>
            <tr>
                <td colspan="9" style="padding:6px 6px; text-align:right; font-size:10px;">STAMP FEE:</td>
                <td style="padding:6px 6px; text-align:right; font-size:10px;">₱<?php echo e(number_format($stamp, 2)); ?></td>
            </tr>
            <tr style="background:#1a3a6b; color:#fff;">
                <td colspan="9" style="padding:10px 6px; text-align:right; font-size:14px; font-weight:bold;">OVERALL TOTAL:</td>
                <td style="padding:10px 6px; text-align:right; font-size:14px; font-weight:bold;">₱<?php echo e(number_format($grandTotal, 2)); ?></td>
            </tr>
        </tfoot>
    </table>

    
    
    
    <table width="100%" cellpadding="0" cellspacing="0" style="margin:18px 0 14px 0;">
        <tr>
            <td style="border-top:2px dashed #aaa;"></td>
        </tr>
    </table>

    
    
    
    <table width="100%" cellpadding="0" cellspacing="0">
        <tr>

            
            <td width="60%" style="vertical-align:top; padding-right:14px; border-right:1px dashed #ccc;">

                <div
                    style="font-weight:bold; font-size:12px; background:#1a3a6b; color:#fff; padding:5px 8px; margin-bottom:6px; letter-spacing:1px;">
                    PAYMENT & PROCESSING
                </div>

                <table cellpadding="2" cellspacing="0" width="100%">
                    <tr>
                        <td style="font-size:10px; color:#666; width:130px;">MODE OF PAYMENT:</td>
                        <td style="font-size:11px; font-weight:bold;"><?php echo e($payment->mode_of_payment ?? 'N/A'); ?></td>
                    </tr>
                    <tr>
                        <td style="font-size:10px; color:#666;">PAYMENT STATUS:</td>
                        <td style="font-size:11px; font-weight:bold;"><?php echo e($payment->payment_status ?? 'N/A'); ?></td>
                    </tr>
                    <tr>
                        <td style="font-size:10px; color:#666;">AMOUNT PAID:</td>
                        <td style="font-size:14px; font-weight:bold; color:#1a3a6b;">
                            ₱<?php echo e(number_format($payment->total_amount ?? $grandTotal, 2)); ?>

                        </td>
                    </tr>
                </table>

                <div
                    style="font-weight:bold; font-size:11px; background:#1a3a6b; color:#fff; padding:4px 8px; margin-top:10px; margin-bottom:5px; letter-spacing:1px;">
                    TERMS & CONDITIONS
                </div>
                <div style="font-size:8.5px; line-height:1.55; color:#333;">
                    <strong>1.</strong> Cargo must be claimed at the destination port upon presentation of this Bill of Lading.
                    <strong>2.</strong> The company is not liable for loss or damage to cargo not claimed within 30 days of arrival.
                    <strong>3.</strong> This document serves as proof of contract for freight services and is subject to the company's
                    general terms and conditions.
                    <strong>4.</strong> The company reserves the right to open and inspect any cargo for safety and customs compliance.
                </div>

            </td>

            
            <td width="40%" style="vertical-align:top; padding-left:14px;">

                <div style="font-weight:bold; font-size:15px; color:#1a3a6b; margin-bottom:8px; letter-spacing:2px;">
                    BOOKING SUMMARY</div>
                <div style="font-size:11px; color:#666; margin-bottom:16px;"><?php echo e($formattedBookingRef); ?></div>

                <table width="100%" cellpadding="0" cellspacing="0">
                    <tr>
                        <td style="font-size:10px; color:#666; padding-bottom:8px;">TOTAL ITEMS:</td>
                        <td style="font-size:12px; font-weight:bold; padding-bottom:8px; text-align:right;">
                            <?php echo e($cargoBookings->sum('quantity')); ?>

                        </td>
                    </tr>
                    <tr>
                        <td style="font-size:10px; color:#666; padding-bottom:8px;">AMOUNT DUE:</td>
                        <td style="font-size:16px; font-weight:bold; color:#1a3a6b; padding-bottom:8px; text-align:right;">
                            ₱<?php echo e(number_format($grandTotal, 2)); ?>

                        </td>
                    </tr>
                    <tr>
                        <td style="font-size:10px; color:#666;">AMOUNT PAID:</td>
                        <td style="font-size:16px; font-weight:bold; color:#27ae60; text-align:right;">
                            ₱<?php echo e(number_format($payment->total_amount ?? $grandTotal, 2)); ?>

                        </td>
                    </tr>
                </table>

            </td>

        </tr>
    </table>

    
    <div
        style="margin-top:16px; text-align:center; font-size:8px; color:#999; border-top:1px solid #dde6f4; padding-top:6px;">
        This is an automatically generated Freight Receipt. Please keep this document for your records. &nbsp;|&nbsp; Page 1 of 1
        <br>
        Issued by <?php echo e($printedBy); ?> &nbsp;|&nbsp; <?php echo e(now()->format('F d, Y h:i A')); ?>

    </div>

    </div>

</body>

</html>
<?php /**PATH C:\Users\kirzt\Documents\GitHub\Capstone\resources\views/authorized/staff/bill_of_lading.blade.php ENDPATH**/ ?>