<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cargo Booking Rejected - #<?php echo e($booking->booking_ref_no); ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background-color: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; border-radius: 12px; overflow: hidden; padding: 20px; }
        h2 { color: #dc3545; }
        .section { margin-bottom: 20px; padding: 15px; border-radius: 8px; background: #f8f9fa; }
        .section-title { font-weight: bold; color: #2a5298; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table th, table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        table th { background: #2a5298; color: white; }
    </style>
</head>

<body>
    <div class="container">
        <h2>❌ Cargo Booking Rejected</h2>
        <p>Booking Reference: <strong>#<?php echo e($booking->booking_code); ?></strong></p>
         <div class="section" style="background:#fff1f2;">
            <div class="section-title">Reason for Rejection</div>
            <p style="color:#7b0b0b; font-weight:600;"><?php echo e($reason ?? 'No reason provided'); ?></p>
        </div>


        <div class="section">
            <div class="section-title">Sender Information</div>
            <p>Name: <?php echo e($sender->sender_name); ?></p>
            <p>Contact: <?php echo e($sender->sender_contactno); ?></p>
            <p>Email: <?php echo e($sender->sender_email ?? 'N/A'); ?></p>
        </div>

        <div class="section">
            <div class="section-title">Consignee Information</div>
            <p>Name: <?php echo e($consignee->consignee_name); ?></p>
            <p>Contact: <?php echo e($consignee->consignee_contactno); ?></p>
        </div>

        <div class="section">
            <div class="section-title">Cargo Items</div>
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
                        $cbm = $cargo->cbm ?? (($cargo->length * $cargo->width * $cargo->height) / 1000000);
                        $subtotal = $freight * $cbm * $cargo->quantity;
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
                        <td><?php echo e($cargo->cargoClassification->cargo_classification_name ?? 'N/A'); ?></td>
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

            <div style="margin-top:15px; text-align:right;">
                <p><strong>Estimated Total:</strong> ₱<?php echo e(number_format($total,2)); ?></p>
            </div>
        </div>

        <div style="text-align:center; margin-top: 20px;">
            <p>Please coordinate with the shipping line for further instructions.</p>
        </div>
    </div>
</body>

</html>
<?php /**PATH C:\Users\kirzt\Documents\GitHub\Capstone\resources\views/emails/cargo_booking_rejected.blade.php ENDPATH**/ ?>