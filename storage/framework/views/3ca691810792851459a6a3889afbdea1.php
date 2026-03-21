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
                        <th>Price</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        $totalAmount = 0;
                    ?>
                    <?php $__empty_1 = true; $__currentLoopData = $allPassengers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $passenger = $item['passenger'] ?? null;
                            $ticket = $item['ticket'] ?? null;
                            if ($ticket) {
                                $totalAmount += $ticket->pt_ticket_price;
                            }

                            // Find accommodation by COT number
                            $accommodation = null;
                            if ($voyage && isset($voyage->vessel_id)) {
                                $accommodations = \DB::table('accommodation')
                                    ->where('vessel_id', $voyage->vessel_id)
                                    ->get();

                                foreach ($accommodations as $accom) {
                                    $ranges = explode(',', $accom->accommodation_cot_range);
                                    foreach ($ranges as $range) {
                                        $range = trim($range);
                                        if (strpos($range, '-') !== false) {
                                            [$start, $end] = explode('-', $range);
                                            if (
                                                $ticket->pt_cot_no >= (int) trim($start) &&
                                                $ticket->pt_cot_no <= (int) trim($end)
                                            ) {
                                                $accommodation = $accom;
                                                break 2;
                                            }
                                        } else {
                                            if ($ticket->pt_cot_no == (int) trim($range)) {
                                                $accommodation = $accom;
                                                break 2;
                                            }
                                        }
                                    }
                                }
                            }
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
                            <td><?php echo e($accommodation ? $accommodation->accommodation_name : 'N/A'); ?></td>
                            <td>₱<?php echo e(number_format($ticket->pt_ticket_price ?? 0, 2)); ?></td>
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
                    <strong>lapulapulslc1964@gmail.com</strong></li>
            </ul>
        </div>

        <p>Thank you for booking with <strong>LAPULAPU SHIPPING LINES</strong>. Your passenger tickets are attached to
            this email (one PDF for each passenger).</p>

        <div class="footer">
            <p>This is an automatically generated confirmation email. Please keep this email and all attached PDF tickets
                for your records.</p>
        </div>
    </div>
</body>

</html>
<?php /**PATH C:\Users\Sophia\Documents\Capstone\resources\views/emails/passenger_ticket_confirmed.blade.php ENDPATH**/ ?>