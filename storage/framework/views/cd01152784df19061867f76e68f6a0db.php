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
        <p>Booking Reference: <strong>#<?php echo e($booking->booking_ref_no); ?></strong></p>
        <p>Status: <strong><?php echo e($booking->booking_status); ?></strong></p>

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
                        <th>Description</th>
                        <th>Classification</th>
                        <th>Quantity</th>
                        <th>Weight</th>
                        <th>Dimensions (L×W×H cm)</th>
                    </tr>
                </thead>
                <tbody>
                <?php $__currentLoopData = $cargoItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cargo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td><?php echo e($cargo->cargoItem->cargo_item_description); ?></td>
                        <td><?php echo e($cargo->cargoItem->cargo_item_classification); ?></td>
                        <td><?php echo e($cargo->quantity); ?></td>
                        <td><?php echo e($cargo->weight); ?> kg</td>
                        <td><?php echo e($cargo->length); ?> × <?php echo e($cargo->width); ?> × <?php echo e($cargo->height); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>

        <div style="text-align:center; margin-top: 20px;">
            <p>Please coordinate with the shipping line for further instructions.</p>
        </div>
    </div>
</body>

</html>
<?php /**PATH C:\Users\kirzt\Documents\GitHub\Capstone\resources\views/emails/cargo_booking_rejected.blade.php ENDPATH**/ ?>