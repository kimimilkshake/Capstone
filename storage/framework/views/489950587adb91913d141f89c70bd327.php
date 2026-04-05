<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Passenger Ticket Confirmed - #<?php echo e($booking->booking_ref_no); ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            padding: 20px;
        }

        .container {
            max-width: 700px;
            margin: auto;
            background: #fff;
            border-radius: 10px;
            padding: 20px;
        }

        h2 {
            color: #007bff;
        }

        .section {
            margin-bottom: 20px;
        }

        .section-title {
            font-weight: bold;
            margin-bottom: 10px;
            color: #333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background: #f0f0f0;
            font-weight: bold;
        }

        .highlight {
            background-color: #e7f3ff;
        }

        .footer {
            text-align: center;
            color: #666;
            font-size: 12px;
            margin-top: 20px;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>✅ Passenger Ticket Confirmed</h2>
        <p>Booking Reference: <strong>#<?php echo e($booking->booking_ref_no); ?></strong></p>
        <p>Status: <strong><?php echo e($booking->booking_status); ?></strong></p>

        <!-- Voyage Information -->
        <div class="section">
            <div class="section-title">🚢 Voyage Information</div>
            <?php if($voyage): ?>
                <p><strong>Voyage Code:</strong> <?php echo e($voyage->voyage_code); ?></p>
                <p><strong>Vessel:</strong> <?php echo e($vessel->vessel_name ?? 'N/A'); ?></p>
                <p><strong>Route:</strong> <?php echo e($route->route_origin ?? 'N/A'); ?> →
                    <?php echo e($route->route_destination ?? 'N/A'); ?></p>
                <p><strong>Departure:</strong>
                    <?php echo e(\Carbon\Carbon::parse($voyage->voyage_departure_date)->format('M d, Y (D)')); ?> at
                    <?php echo e(\Carbon\Carbon::parse($voyage->voyage_estimated_TD)->format('g:i A')); ?></p>
                <p><strong>Arrival:</strong>
                    <?php echo e(\Carbon\Carbon::parse($voyage->voyage_arrival_date)->format('M d, Y (D)')); ?> at
                    <?php echo e(\Carbon\Carbon::parse($voyage->voyage_estimated_TA)->format('g:i A')); ?></p>
            <?php else: ?>
                <p>Voyage information not available.</p>
            <?php endif; ?>
        </div>

        <!-- Loading & Unloading Ports -->
        <div class="section">
            <div class="section-title">🏝️ Ports</div>
            <?php if($route): ?>
                <p><strong>Port of Origin:</strong> <?php echo e($route->port_origin_name ?? 'N/A'); ?></p>
                <p><strong>Port of Destination:</strong> <?php echo e($route->port_destination_name ?? 'N/A'); ?></p>
            <?php else: ?>
                <p>Port information not available.</p>
            <?php endif; ?>
        </div>

        <!-- Passengers -->
        <div class="section">
            <div class="section-title">👥 Passenger Details</div>
            <table>
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Cot #</th>
                        <th>Accommodation</th>
                        <th>Base Price</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $allPassengers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $passenger = $item['passenger'] ?? null;
                            $ticket = $item['ticket'] ?? null;
                            $basePrice = $item['accommodation_base_price'] ?? null;
                            $displayPrice = $basePrice !== null ? $basePrice : $ticket->pt_ticket_price ?? 0;
                        ?>
                        <tr>
                            <td><?php echo e($index + 1); ?></td>
                            <td>
                                <?php echo e($passenger->passenger_firstname ?? 'N/A'); ?>

                                <?php if($passenger && $passenger->passenger_midinitial): ?>
                                    <?php echo e($passenger->passenger_midinitial); ?>.
                                <?php endif; ?>
                                <?php echo e($passenger->passenger_lastname ?? ''); ?>

                                <?php if($passenger && $passenger->passenger_suffix): ?>
                                    <?php echo e($passenger->passenger_suffix); ?>

                                <?php endif; ?>
                            </td>
                            <td><?php echo e($passenger->passenger_type ?? 'N/A'); ?></td>
                            <td><?php echo e($ticket->pt_cot_no ?? 'N/A'); ?></td>
                            <td><?php echo e($item['accommodation_name'] ?? 'N/A'); ?></td>
                            <td>₱<?php echo e(number_format($displayPrice, 2)); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="6" style="text-align: center;">No passengers found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Payment Information -->
        <div class="section highlight">
            <div class="section-title">💳 Payment Information</div>
            <?php if($payment): ?>
                
                <?php $hasAnyBreakdown = false; ?>
                <?php $__currentLoopData = $allPassengers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $ticket = $item['ticket'] ?? null;
                        $routeRate = $item['route_rate'] ?? 0;
                        $typeDiscountRate = $item['type_discount_rate'] ?? 0;
                        $promo = $item['promo'] ?? null;
                        $hasBreakdown = $ticket && ($routeRate > 0 || $typeDiscountRate > 0 || $promo);
                        if ($hasBreakdown) {
                            $hasAnyBreakdown = true;
                        }
                    ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                <?php if($hasAnyBreakdown): ?>
                    <table style="margin-bottom:12px; font-size:13px;">
                        <thead>
                            <tr>
                                <th>Passenger</th>
                                <th>Base Price</th>
                                <th>Route Rate</th>
                                <th>Type Discount</th>
                                <th>Promo</th>
                                <th>Final Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $allPassengers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $passenger = $item['passenger'] ?? null;
                                    $ticket = $item['ticket'] ?? null;
                                    $basePrice = $item['accommodation_base_price'] ?? null;
                                    $routeRate = (float) ($item['route_rate'] ?? 0);
                                    $typeDiscountRate = (float) ($item['type_discount_rate'] ?? 0);
                                    $promo = $item['promo'] ?? null;
                                    $rateDisplay =
                                        $routeRate > 0
                                            ? '+' . rtrim(rtrim(number_format($routeRate, 2), '0'), '.') . '%'
                                            : '—';
                                    $typeDisplay =
                                        $typeDiscountRate > 0
                                            ? '-' . rtrim(rtrim(number_format($typeDiscountRate, 2), '0'), '.') . '%'
                                            : '—';
                                    $promoDisplay = $promo
                                        ? '-' .
                                            rtrim(rtrim(number_format($promo->promo_discount_rate, 2), '0'), '.') .
                                            '%'
                                        : '—';
                                    $basePriceDisplay = $basePrice !== null ? '₱' . number_format($basePrice, 2) : '—';
                                ?>
                                <tr>
                                    <td>
                                        <?php echo e($passenger->passenger_firstname ?? ''); ?>

                                        <?php if($passenger && $passenger->passenger_midinitial): ?>
                                            <?php echo e($passenger->passenger_midinitial); ?>.
                                        <?php endif; ?>
                                        <?php echo e($passenger->passenger_lastname ?? ''); ?>

                                    </td>
                                    <td><?php echo e($basePriceDisplay); ?></td>
                                    <td><?php echo e($rateDisplay); ?></td>
                                    <td><?php echo e($typeDisplay); ?></td>
                                    <td><?php echo e($promoDisplay); ?></td>
                                    <td><strong>₱<?php echo e(number_format($ticket->pt_ticket_price ?? 0, 2)); ?></strong></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                <?php endif; ?>

                <p><strong>Total Amount Paid:</strong> ₱<?php echo e(number_format($payment->total_amount, 2)); ?></p>
                <p><strong>Payment Method:</strong> <?php echo e($payment->mode_of_payment); ?></p>
                <p><strong>Payment Status:</strong> <?php echo e($payment->payment_status); ?></p>
            <?php else: ?>
                <p>Payment information not available.</p>
            <?php endif; ?>
        </div>

        <!-- Important Reminders -->
        <div class="section">
            <div class="section-title">⚠️ Important Reminders</div>
            <ul>
                <li>Arrive at the terminal <strong>at least 30 minutes</strong> before departure</li>
                <li>Bring a <strong>valid government-issued ID</strong> for verification</li>
                <li>Free hand carry allowance is <strong>7kg per passenger</strong></li>
                <li>Present this ticket and your ID at check-in</li>
                <li>Ticket modifications must be made <strong>at least 2 hours</strong> before departure</li>
                <li>For inquiries, call <strong>(032) 232-8864</strong> or email
                    <strong>lapulapulslc1964@gmail.com</strong>
                </li>
            </ul>
        </div>

        <p>Thank you for booking with <strong>LAPULAPU SHIPPING LINES</strong>. Your passenger tickets are attached to
            this email (one PDF for each passenger).</p>

        <div class="footer">
            <p>This is an automatically generated confirmation email. Please keep this email and all attached PDF
                tickets
                for your records.</p>
        </div>
    </div>
</body>

</html>
<?php /**PATH C:\Users\kirzt\Documents\GitHub\Capstone\resources\views/emails/passenger_ticket_confirmed.blade.php ENDPATH**/ ?>