<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Cargo Payment Confirmed - Freight Receipt for #<?php echo e($booking->booking_ref_no); ?></title>
    <style>
        body { font-family: Arial, sans-serif; background: #f5f5f5; padding: 20px; }
        .container { max-width: 700px; margin: auto; background: #fff; border-radius: 10px; padding: 30px; }
        h2 { color: #28a745; text-align: center; }
        .booking-ref { font-size: 24px; font-weight: bold; text-align: center; margin: 20px 0; color: #333; }
        .success-badge { background: #28a745; color: white; padding: 10px 20px; border-radius: 5px; text-align: center; font-weight: bold; margin: 15px 0; }
        .payment-info { background: #e8f5e9; padding: 15px; border-radius: 5px; margin: 15px 0; }
        .section { margin-bottom: 20px; }
        .section-title { font-weight: bold; margin-bottom: 10px; color: #333; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #f0f0f0; }
        .footer { text-align: center; margin-top: 30px; font-size: 12px; color: #999; }
    </style>
</head>
<body>
<div class="container">
    <h2>✅ Payment Confirmed!</h2>
    
    <div class="booking-ref">Booking Reference: #<?php echo e($booking->booking_ref_no); ?></div>
    
    <div class="success-badge">
        Your payment of ₱<?php echo e(number_format($payment->total_amount ?? 0, 2)); ?> has been confirmed.
    </div>
    
    <!-- Voyage Information -->
    <?php if($booking->voyage): ?>
    <div class="section">
        <div class="section-title">🚢 Voyage Information</div>
        <p><strong>Voyage Code:</strong> <?php echo e($booking->voyage->voyage_code); ?></p>
        <p><strong>Departure:</strong> <?php echo e(\Carbon\Carbon::parse($booking->voyage->voyage_departure_date)->format('M d, Y (D)')); ?></p>
        <p><strong>Arrival:</strong> <?php echo e(\Carbon\Carbon::parse($booking->voyage->voyage_arrival_date)->format('M d, Y (D)')); ?></p>
        <?php
            $originPort = $booking->voyage->routePort?->portOrigin;
            $originDisplay = $originPort ? ($originPort->terminal_name ?? '') . ' ' . ($originPort->port_name ?? '') . ', ' . ($originPort->city ?? '') : 'N/A';
            $destPort = $booking->voyage->routePort?->portDestination;
            $destDisplay = $destPort ? ($destPort->terminal_name ?? '') . ' ' . ($destPort->port_name ?? '') . ', ' . ($destPort->city ?? '') : 'N/A';
        ?>
        <p><strong>Loading Port:</strong> <?php echo e(trim($originDisplay)); ?></p>
        <p><strong>Unloading Port:</strong> <?php echo e(trim($destDisplay)); ?></p>
    </div>
    <?php endif; ?>
    
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

    <!-- Payment Information -->
    <div class="payment-info">
        <strong>Payment Details:</strong><br>
        <strong>Amount Paid:</strong> ₱<?php echo e(number_format($payment->total_amount ?? 0, 2)); ?><br>
        <strong>Payment Status:</strong> <?php echo e($payment->payment_status ?? 'Initial'); ?><br>
        <strong>Payment Date:</strong> <?php echo e($payment->payment_date ? \Carbon\Carbon::parse($payment->payment_date)->format('M d, Y H:i') : 'N/A'); ?>

    </div>
    
    <p style="font-size: 14px; color: #666; text-align: center;">
        <strong>Important:</strong> <?php if($pdfAttached): ?> The Freight Receipt PDF is attached to this email for your records. <?php else: ?> We apologize, but the Freight Receipt PDF could not be generated at this time. Please contact support for a copy. <?php endif; ?>
    </p>
    
    <div class="footer">
        <p>LAPULAPU SHIPPING LINES</p>
        <p>This is an automated message. Please do not reply to this email.</p>
    </div>
</div>
</body>
</html>
<?php /**PATH C:\Users\kirzt\Documents\GitHub\Capstone\resources\views/emails/cargo_payment_confirmation.blade.php ENDPATH**/ ?>