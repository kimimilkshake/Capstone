<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bill of Lading - <?php echo e($booking->booking_ref_no); ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', 'Arial', sans-serif;
            background: #eceff3;
            padding: 24px;
            margin: 0;
            font-size: 12px;
            line-height: 1.35;
        }

        @page {
            size: A4;
            margin: 8mm;
        }

        .page {
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            background: white;
            border: 1px solid #d9dee5;
            padding: 8mm;
            box-shadow: 0 10px 30px rgba(25, 38, 62, 0.12);
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }

        .header h1 {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .header .company-info {
            font-size: 12px;
            line-height: 1.3;
            color: #333;
        }

        .header .lslc {
            font-size: 10px;
            margin-top: 5px;
            color: #666;
        }

        .section-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 15px;
        }

        .section-row.full {
            grid-template-columns: 1fr;
        }

        .section-box {
            border: 1px solid #333;
            padding: 10px;
        }

        .section-label {
            font-weight: bold;
            font-size: 12px;
            margin-bottom: 5px;
            border-bottom: 1px solid #333;
            padding-bottom: 3px;
        }

        .section-content {
            font-size: 11px;
            line-height: 1.5;
        }

        .section-content p {
            margin: 3px 0;
        }

        .info-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 10px;
        }

        .info-row.full {
            grid-template-columns: 1fr;
        }

        .info-item {
            font-size: 11px;
        }

        .info-item label {
            font-weight: bold;
        }

        .info-item span {
            display: block;
            margin-left: 10px;
            border-bottom: 1px dotted #999;
            min-height: 16px;
        }

        .cargo-table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
            font-size: 11px;
            table-layout: fixed;
        }

        .cargo-table th {
            background: #f0f0f0;
            border: 1px solid #333;
            padding: 8px;
            text-align: left;
            font-weight: bold;
        }

        .cargo-table td {
            border: 1px solid #333;
            padding: 8px;
            word-wrap: break-word;
            overflow-wrap: anywhere;
        }

        .cargo-description {
            width: 100%;
            margin: 15px 0;
            border: 1px solid #333;
        }

        .cargo-description-header {
            background: #f0f0f0;
            padding: 8px;
            font-weight: bold;
            font-size: 11px;
            border-bottom: 1px solid #333;
        }

        .cargo-items {
            padding: 10px;
            min-height: 150px;
        }

        .cargo-item {
            margin-bottom: 8px;
            font-size: 10px;
            line-height: 1.4;
        }

        .cargo-item strong {
            display: block;
        }

        .footer-section {
            margin-top: 20px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            font-size: 10px;
        }

        .signature-block {
            margin-top: 30px;
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 20px;
        }

        .signature-line {
            text-align: center;
            border-top: 1px solid #333;
            padding-top: 5px;
            font-size: 9px;
        }

        .signature-line .label {
            margin-top: 30px;
        }

        .charges-table {
            width: 100%;
            border: 1px solid #333;
            margin-top: 15px;
            table-layout: fixed;
        }

        .charges-table tr {
            border-bottom: 1px solid #333;
        }

        .charges-table td {
            padding: 8px;
            font-size: 11px;
        }

        .charges-table .label {
            font-weight: bold;
            width: auto;
        }

        .charges-table .amount {
            text-align: right;
            width: 130px;
            border-left: 1px solid #333;
            white-space: nowrap;
            padding-right: 10px;
        }

        .notes {
            font-size: 10px;
            margin-top: 10px;
            font-style: italic;
            line-height: 1.3;
        }

        .signature-block,
        .section-row,
        .charges-table,
        .cargo-table {
            page-break-inside: avoid;
        }

        .cargo-table th,
        .cargo-table td {
            page-break-inside: avoid;
        }

        @media print {
            body {
                padding: 0;
                background: white;
            }

            .page {
                margin: 0;
                border: none;
                box-shadow: none;
                padding: 8mm;
                page-break-after: always;
                width: 100%;
                min-height: auto;
            }

            .no-print {
                display: none;
            }
        }

        @media (max-width: 1024px) {
            body {
                padding: 12px;
            }

            .page {
                width: 100%;
                min-height: auto;
            }
        }

        .no-print {
            text-align: center;
            margin-bottom: 20px;
        }

        .no-print button {
            padding: 10px 20px;
            font-size: 14px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin: 0 5px;
        }

        .no-print button:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>



<div class="page">
    <!-- Header -->
    <div class="header">
        <h1>LAPULAPU SHIPPING LINES CORPORATION</h1>
        <div class="company-info">
            TEL. 232-8864, 232-8865 • TIN: 200-008-128-099 • VAT
        </div>
        <div class="lslc">LSLC # 0900189760</div>
    </div>

    <!-- Top Info Section -->
    <div class="info-row full">
        <div class="info-item">
            <label>Vessel:</label>
            <span><?php echo e($booking->voyage && $booking->voyage->vessel ? $booking->voyage->vessel->vessel_name : ($booking->voyage->vessel_name ?? 'Not specified')); ?></span>
        </div>
    </div>

    <div class="info-row">
        <div class="info-item">
            <label>Voyage No.:</label>
            <span><?php echo e($booking->voyage->voyage_code ?? 'N/A'); ?></span>
        </div>
        <div class="info-item">
            <label>Sailing Date:</label>
            <span><?php echo e($booking->voyage ? \Carbon\Carbon::parse($booking->voyage->voyage_departure_date)->format('F d, Y') : 'N/A'); ?></span>
        </div>
    </div>

    <!-- Shipper & Consignee -->
    <div class="section-row">
        <div class="section-box">
            <div class="section-label">SHIPPER</div>
            <div class="section-content">
                <p><strong><?php echo e($booking->sender->sender_name ?? ''); ?></strong></p>
                <p><?php echo e($booking->sender->sender_contactno ?? ''); ?></p>
                <p><?php echo e($booking->sender->sender_email ?? ''); ?></p>
            </div>
        </div>
        <div class="section-box">
            <div class="section-label">CONSIGNEE</div>
            <div class="section-content">
                <p><strong><?php echo e($booking->consignee->consignee_name ?? ''); ?></strong></p>
                <p><?php echo e($booking->consignee->consignee_contactno ?? ''); ?></p>
            </div>
        </div>
    </div>

    <!-- Ports -->
    <div class="section-row">
        <div class="section-box">
            <div class="section-label">Loading Port</div>
            <div class="section-content">
                <p><?php echo e($booking->voyage && $booking->voyage->routePort ? $booking->voyage->routePort->port_origin_name : 'Not specified'); ?></p>
            </div>
        </div>
        <div class="section-box">
            <div class="section-label">Unloading Port</div>
            <div class="section-content">
                <p><?php echo e($booking->voyage && $booking->voyage->routePort ? $booking->voyage->routePort->port_destination_name : 'Not specified'); ?></p>
            </div>
        </div>
    </div>

    <!-- Cargo Description Table -->
    <div style="margin-top: 15px; border: 1px solid #333;">
        <div style="background: #f0f0f0; padding: 8px; font-weight: bold; font-size: 11px; border-bottom: 1px solid #333;">DESCRIPTION OF ARTICLES</div>
        <table class="cargo-table" style="margin: 0; border-top: none;">
            <thead>
                <tr>
                    <th style="width: 8%;">QTY</th>
                    <th style="width: 18%;">Classification</th>
                    <th style="width: 23%;">Description</th>
                    <th style="width: 10%;">Length</th>
                    <th style="width: 10%;">Width</th>
                    <th style="width: 10%;">Height</th>
                    <th style="width: 11%;">Weight (kg)</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $booking->cargoBookings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cargo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php $unit = $cargo->measurementUnit->measurement_unit_abbreviation ?? 'cm'; ?>
                    <tr>
                        <td style="text-align:center;"><?php echo e($cargo->quantity); ?></td>
                        <td><?php echo e($cargo->cargoClassification->cargo_classification_name ?? ''); ?></td>
                        <td><?php echo e($cargo->cargoItem->cargo_item_description ?? ''); ?></td>
                        <td style="text-align:center;"><?php echo e(number_format($cargo->length, 2) . $unit); ?></td>
                        <td style="text-align:center;"><?php echo e(number_format($cargo->width, 2) . $unit); ?></td>
                        <td style="text-align:center;"><?php echo e(number_format($cargo->height, 2) . $unit); ?></td>
                        <td style="text-align:center;"><?php echo e(number_format($cargo->weight, 2)); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>

    <!-- Charges Table -->
    <?php
        $freight = 0;
        $arrastre = 0;
        $stamp = 0;
        foreach($booking->cargoBookings as $cargo) {
            $cbm = (float) ($cargo->cbm ?? (($cargo->length * $cargo->width * $cargo->height) / 1000000));
            $qty = (float) ($cargo->quantity ?? 0);
            $freightRate = (float) ($cargo->cargoItem->cargo_item_freight ?? 0);
            $arrastreRate = (float) ($cargo->cargoItem->cargo_item_arrastre ?? 0);

            $freight += $freightRate * $cbm * $qty;
            $arrastre += $arrastreRate * $cbm * $qty;
        }
        $total = $freight + $arrastre + $stamp;
    ?>
    <table class="charges-table">
        <tr>
            <td class="label">FREIGHT CHARGES</td>
            <td class="amount">₱<?php echo e(number_format($freight, 2)); ?></td>
        </tr>
        <tr>
            <td class="label">ARRASTRE CHARGES</td>
            <td class="amount">₱<?php echo e(number_format($arrastre, 2)); ?></td>
        </tr>
        <tr>
            <td class="label">STAMP</td>
            <td class="amount">₱2<?php echo e(number_format($stamp, 2)); ?></td>
        </tr>
        <tr>
            <td class="label"><strong>TOTAL TRANSACTION</strong></td>
            <td class="amount"><strong>₱<?php echo e(number_format($total, 2)); ?></strong></td>
        </tr>
    </table>

    <!-- NOTE: If the PDF/email version does not match this view, check the BillOfLadingPdf service and the mail template for consistency. -->

    <!-- Signature Section -->
    <div class="signature-block">
        <div class="signature-line">
            <div style="height: 50px;"></div>
            <div class="label">Shipper/Agent</div>
        </div>
        <div class="signature-line">
            <div style="height: 50px;"></div>
            <div class="label">Carrier's Agent</div>
        </div>
        <div class="signature-line">
            <div style="height: 50px;"></div>
            <div class="label">Quartermaster</div>
        </div>
    </div>

    <!-- Footer Notes -->
    <div class="notes">
        <p>Received the merchandise specified herein in good order and condition.</p>
        <p style="margin-top: 10px;">SUBJECT TO CONDITIONS & QUOTATIONS WHETHER PRINTED OR STAMPED OR WRITTEN ON THE ORIGINAL OF THIS BILL OF LADING.</p>
    </div>

</div>

</body>
</html>
<?php /**PATH C:\Users\clint\Desktop\Capstone\resources\views/authorized/staff/bill_of_lading.blade.php ENDPATH**/ ?>