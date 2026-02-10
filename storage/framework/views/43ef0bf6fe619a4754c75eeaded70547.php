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
    </style>
</head>
<body>
<div class="container">
    <h2>✅ Cargo Booking Approved</h2>
    <p>Booking Reference: <strong>#<?php echo e($booking->booking_code); ?></strong></p>
    <p>Status: <strong><?php echo e($booking->booking_status); ?></strong></p>
    <p>The Bill of Lading (B/L) for this booking is attached as a PDF for your records and printing.</p>

    <!-- Voyage Information -->
    <div class="section">
        <div class="section-title">🚢 Voyage Information</div>
        <?php if($booking->voyage): ?>
            <p>Voyage Code: <?php echo e($booking->voyage->voyage_code); ?></p>
            <p>Departure: <?php echo e(\Carbon\Carbon::parse($booking->voyage->voyage_departure_date)->format('M d, Y (D)')); ?></p>
            <p>Arrival: <?php echo e(\Carbon\Carbon::parse($booking->voyage->voyage_arrival_date)->format('M d, Y (D)')); ?></p>
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
                    $totalQuantity = 0;
                ?>
                <?php $__currentLoopData = $cargoItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cargo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $freight = $cargo->cargoItem->cargo_item_freight ?? 0;
                        $arrastre = $cargo->cargoItem->cargo_item_arrastre ?? 0;
                        $cbm = ($cargo->length * $cargo->width * $cargo->height) / 1000000;
                        $subtotal = ($freight + $arrastre) * $cbm * $cargo->quantity;
                        $total += $subtotal;
                        $totalQuantity += $cargo->quantity;
                        
                        // Determine unit of measurement
                        $unit = $cargo->measurement_unit ?? 'cm';
                        $unitDisplay = ($unit === 'in') ? 'inches' : 'cm';
                        
                        // For display, show dimensions in the unit chosen by customer
                        if ($unit === 'in') {
                            $displayLength = round($cargo->length / 2.54, 2);
                            $displayWidth = round($cargo->width / 2.54, 2);
                            $displayHeight = round($cargo->height / 2.54, 2);
                        } else {
                            $displayLength = $cargo->length;
                            $displayWidth = $cargo->width;
                            $displayHeight = $cargo->height;
                        }
                    ?>
                    <tr>
                        <td><?php echo e($cargo->quantity); ?></td>
                        <td><?php echo e($cargo->cargoItem->cargo_item_classification); ?></td>
                        <td><?php echo e($cargo->cargoItem->cargo_item_description); ?></td>
                        <td><?php echo e($displayLength); ?> × <?php echo e($displayWidth); ?> × <?php echo e($displayHeight); ?> <?php echo e($unitDisplay); ?></td>
                        <td><?php echo e($cargo->weight); ?> kg</td>
                        <td>₱<?php echo e(number_format($subtotal,2)); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <tr style="background-color: #f9f9f9; font-weight: bold;">
                    <td colspan="2">Total Items</td>
                    <td colspan="2"><?php echo e($totalQuantity); ?></td>
                    <td colspan="2"></td>
                </tr>
            </tbody>
        </table>

        <div style="margin-top:15px; text-align:left;">
            <p><strong>Mode of Payment:</strong> <?php echo e($payment->mode_of_payment ?? 'N/A'); ?></p>
            <p><strong>Payment Status:</strong> <?php echo e($payment->payment_status ?? 'N/A'); ?></p>
            <p style="font-size: 16px; color: #28a745;"><strong>Total Overall: ₱<?php echo e(number_format($payment->total_amount ?? $total,2)); ?></strong></p>
        </div>
    </div>

    <p>Thank you for booking with LAPULAPU SHIPPING LINES. Your cargo booking has been confirmed.</p>
</div>
</body>
</html>
<?php /**PATH C:\Users\clint\Desktop\Capstone\resources\views/emails/cargo_booking_approved.blade.php ENDPATH**/ ?>