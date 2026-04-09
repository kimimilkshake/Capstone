<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Cargo Booking Approved - #<?php echo e($booking->booking_ref_no); ?></title>
    <style>
        body { font-family: Arial, sans-serif; background: #f5f5f5; padding: 20px; }
        .container { max-width: 700px; margin: auto; background: #fff; border-radius: 10px; padding: 20px; }
        h2 { color: #28a745; }
        .section { margin-bottom: 20px; }
        .section-title { font-weight: bold; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; }
        th { background: #f0f0f0; }
        .footer { text-align: center; margin-top: 30px; font-size: 12px; color: #999; }
    </style>
</head>
<body>
<div class="container">
    <h2>✅ Cargo Booking Approved</h2>
    <p>Booking Reference: <strong>#<?php echo e($booking->booking_ref_no); ?></strong></p>
    <p>Status: <strong><?php echo e($booking->booking_status); ?></strong></p>
    <p><strong>Note:</strong> Your cargo booking has been approved by our staff. Please complete your payment to proceed with the booking.</p>

    <!-- Voyage Information -->
    <div class="section">
        <div class="section-title">🚢 Voyage Information</div>
        <?php if($booking->voyage): ?>
            <p>Voyage Code: <?php echo e($booking->voyage->voyage_code); ?></p>
            <p>Departure: <?php echo e(\Carbon\Carbon::parse($booking->voyage->voyage_departure_date)->format('M d, Y (D)')); ?></p>
            <p>Arrival: <?php echo e(\Carbon\Carbon::parse($booking->voyage->voyage_arrival_date)->format('M d, Y (D)')); ?></p>
            <?php
                $originPort = $booking->voyage->routePort?->portOrigin;
                $originDisplay = $originPort ? ($originPort->terminal_name ?? '') . ' ' . ($originPort->port_name ?? '') . ', ' . ($originPort->city ?? '') : 'N/A';
                $destPort = $booking->voyage->routePort?->portDestination;
                $destDisplay = $destPort ? ($destPort->terminal_name ?? '') . ' ' . ($destPort->port_name ?? '') . ', ' . ($destPort->city ?? '') : 'N/A';
            ?>
            <p>Port of Origin: <?php echo e(trim($originDisplay)); ?></p>
            <p>Port of Destination: <?php echo e(trim($destDisplay)); ?></p>
        <?php else: ?>
            <p>Voyage information not available.</p>
        <?php endif; ?>
    </div>

    <!-- Sender & Consignee -->
    <div class="section">
        <div class="section-title">📦 Sender & Consignee</div>
        <p><strong>Sender:</strong> <?php echo e($sender->sender_name); ?> (<?php echo e($sender->sender_contactno); ?>)</p>
        <p><strong>Consignee:</strong> <?php echo e($consignee->consignee_name); ?> (<?php echo e($consignee->consignee_contactno); ?>)</p>
    </div>

    <!-- Cargo Items -->
    <div class="section">
        <div class="section-title">📋 Cargo Items</div>
        <table>
            <thead>
                <tr>
                    <th>QTY</th>
                    <th>Classification</th>
                    <th>Description</th>
                    <th>Dimensions</th>
                    <th>Weight</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    $total = 0;
                    $stampFee = 20;
                ?>
                <?php $__currentLoopData = $cargoItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cargo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                  $freight = $cargo->cargoItem->cargo_item_freight ?? 0;

                 // ✅ match measure_required logic
                 $measureRequired = strtolower($cargo->cargoItem->cargo_item_measure_required ?? 'no');

                 $cbm = (float) ($cargo->cbm ?? 0);

                 // fallback CBM if not stored
                 if (!$cbm) {
                    $cbm = ($cargo->length * $cargo->width * $cargo->height) / 1000000;
                }

                // ✅ UPDATED FORMULA (matches backend)
                if ($measureRequired === 'yes') {
                    $subtotal = $freight * $cargo->quantity;
                 } else {
                    $subtotal = $cbm * $freight * $cargo->quantity;
                }

                $total += $subtotal;

                $unitDisplay = strtolower($cargo->measurementUnit->measurement_unit_abbreviation ?? 'cm');
                $displayLength = (float) $cargo->length;
                $displayWidth = (float) $cargo->width;
                $displayHeight = (float) $cargo->height;
            ?>
                    <tr>
                        <td><?php echo e($cargo->quantity); ?></td>
                        <td><?php echo e($cargo->cargoClassification->cargo_classification_name ?? 'N/A'); ?></td>
                        <td><?php echo e($cargo->cargoItem->cargo_item_description); ?></td>
                        <td><?php echo e(number_format($displayLength, 2)); ?> x <?php echo e(number_format($displayWidth, 2)); ?> x <?php echo e(number_format($displayHeight, 2)); ?> <?php echo e($unitDisplay); ?></td>
                        <td><?php echo e($cargo->weight); ?> kg</td>
                        <td>₱<?php echo e(number_format($subtotal,2)); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <tr style="background-color: #f9f9f9; font-weight: bold;">
                    <td colspan="4"></td>
                    <td>Stamp</td>
                    <td>₱<?php echo e(number_format($stampFee, 2)); ?></td>
                </tr>
                <tr style="background-color: #f9f9f9; font-weight: bold;">
                    <td colspan="4"></td>
                    <td>Total</td>
                    <td>₱<?php echo e(number_format($total + $stampFee, 2)); ?></td>
                </tr>
            </tbody>
        </table>
    </div>

    <p>Please approach the office for the payment of your cargo booking. The payment of the arrastre and other fees will be required upon arrival.</p>

    <div class="footer">
        <p>LAPULAPU SHIPPING LINES</p>
        <p>If you did not request this booking, please ignore this email.</p>
    </div>
</div>
</body>
</html><?php /**PATH C:\Users\clint\Desktop\Capstone\resources\views/emails/cargo_booking_staff_approved.blade.php ENDPATH**/ ?>