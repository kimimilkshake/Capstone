<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ferry Ticket - Booking #<?php echo e($bookingRef); ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }

        .ticket-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }

        .ticket-header {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .company-logo {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .company-tagline {
            font-size: 14px;
            opacity: 0.9;
        }

        .ticket-body {
            padding: 30px;
        }

        .ticket-info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }

        .info-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #2a5298;
        }

        .info-title {
            font-size: 16px;
            font-weight: bold;
            color: #2a5298;
            margin-bottom: 15px;
            text-transform: uppercase;
        }

        .info-item {
            margin-bottom: 8px;
            display: flex;
            justify-content: space-between;
            border-bottom: 1px dotted #ddd;
            padding-bottom: 4px;
        }

        .info-label {
            font-weight: bold;
            color: #555;
        }

        .info-value {
            color: #333;
        }

        .passengers-section {
            margin-top: 30px;
        }

        .passenger-card {
            background: #ffffff;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 15px;
            position: relative;
        }

        .passenger-card::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 4px;
            background: #28a745;
            border-radius: 4px 0 0 4px;
        }

        .passenger-name {
            font-size: 18px;
            font-weight: bold;
            color: #2a5298;
            margin-bottom: 10px;
        }

        .passenger-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }

        .qr-section {
            text-align: center;
            padding: 30px;
            background: #f8f9fa;
            border-radius: 8px;
            margin-top: 30px;
        }

        .booking-ref {
            font-size: 36px;
            font-weight: bold;
            color: #2a5298;
            margin-bottom: 10px;
            letter-spacing: 2px;
        }

        .qr-placeholder {
            width: 150px;
            height: 150px;
            background: #ddd;
            margin: 20px auto;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #666;
            font-weight: bold;
        }

        .important-notes {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 8px;
            padding: 20px;
            margin-top: 30px;
        }

        .important-notes h4 {
            color: #856404;
            margin-top: 0;
        }

        .important-notes ul {
            margin: 10px 0;
            padding-left: 20px;
        }

        .important-notes li {
            margin-bottom: 5px;
            color: #856404;
        }

        .footer {
            background: #f8f9fa;
            padding: 20px;
            text-align: center;
            border-top: 1px solid #ddd;
            color: #666;
            font-size: 12px;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-confirmed {
            background: #d4edda;
            color: #155724;
        }

        .total-amount {
            font-size: 24px;
            font-weight: bold;
            color: #28a745;
        }

        @media (max-width: 600px) {
            .ticket-info-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }

            .passenger-details {
                grid-template-columns: 1fr;
            }

            .booking-ref {
                font-size: 24px;
            }
        }
    </style>
</head>

<body>
    <div class="ticket-container">
        <!-- Header -->
        <div class="ticket-header">
            <div class="company-logo">LAPULAPU SHIPPING LINES</div>
            <div class="company-tagline">Your trusted ferry service across the Philippines</div>
        </div>

        <!-- Ticket Body -->
        <div class="ticket-body">
            <!-- Booking Status -->
            <div style="text-align: center; margin-bottom: 30px;">
                <span class="status-badge status-confirmed">✓ CONFIRMED & PAID</span>
            </div>

            <!-- Main Info Grid -->
            <div class="ticket-info-grid">
                <!-- Voyage Information -->
                <div class="info-section">
                    <div class="info-title">🚢 Voyage Details</div>
                    <?php if($route && $voyage && $vessel): ?>
                        <div class="info-item">
                            <span class="info-label">Route:</span>
                            <span class="info-value"><?php echo e($route->route_origin); ?> → <?php echo e($route->route_destination); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Vessel:</span>
                            <span class="info-value"><?php echo e($vessel->vessel_name); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Departure:</span>
                            <span
                                class="info-value"><?php echo e(\Carbon\Carbon::parse($voyage->voyage_departure_date)->format('M d, Y (D)')); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Time:</span>
                            <span
                                class="info-value"><?php echo e(\Carbon\Carbon::parse($voyage->voyage_estimated_TD)->format('g:i A')); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Port:</span>
                            <span class="info-value"><?php echo e($route->port_origin_name ?? 'Main Port'); ?></span>
                        </div>
                    <?php else: ?>
                        <div class="info-item">
                            <span class="info-value">Voyage details not available</span>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Booking Information -->
                <div class="info-section">
                    <div class="info-title">📋 Booking Details</div>
                    <div class="info-item">
                        <span class="info-label">Booking Reference:</span>
                        <span class="info-value"><strong>#<?php echo e($bookingRef); ?></strong></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Booking Date:</span>
                        <span
                            class="info-value"><?php echo e(\Carbon\Carbon::parse($booking->booking_date)->format('M d, Y g:i A')); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Passengers:</span>
                        <span class="info-value"><?php echo e(count($passengers)); ?></span>
                    </div>
                    <?php if($payment): ?>
                        <div class="info-item">
                            <span class="info-label">Total Amount:</span>
                            <span class="info-value total-amount">PHP
                                <?php echo e(number_format($payment->total_amount, 2)); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Payment Method:</span>
                            <span class="info-value"><?php echo e($payment->mode_of_payment); ?></span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Passenger Details -->
            <div class="passengers-section">
                <div class="info-title">👥 Passenger Information</div>
                <?php $__currentLoopData = $passengers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="passenger-card">
                        <div class="passenger-name">
                            <?php echo e($item['passenger']->passenger_firstname); ?>

                            <?php if($item['passenger']->passenger_midinitial): ?>
                                <?php echo e($item['passenger']->passenger_midinitial); ?>.
                            <?php endif; ?>
                            <?php echo e($item['passenger']->passenger_lastname); ?>

                            <?php if($item['passenger']->passenger_suffix): ?>
                                <?php echo e($item['passenger']->passenger_suffix); ?>

                            <?php endif; ?>
                        </div>
                        <div class="passenger-details">
                            <div>
                                <strong>Age:</strong> <?php echo e($item['passenger']->passenger_age); ?> years old
                            </div>
                            <div>
                                <strong>Gender:</strong>
                                <?php echo e($item['passenger']->passenger_gender == 'M' ? 'Male' : 'Female'); ?>

                            </div>
                            <div>
                                <strong>Type:</strong> <?php echo e($item['passenger']->passenger_type); ?>

                            </div>
                            <div>
                                <strong>Cot Number:</strong> <span
                                    style="background: #e3f2fd; padding: 2px 8px; border-radius: 4px; font-weight: bold;"><?php echo e($item['ticket']->pt_cot_no); ?></span>
                            </div>
                            <div>
                                <strong>Ticket Price:</strong> PHP
                                <?php echo e(number_format($item['ticket']->pt_ticket_price, 2)); ?>

                            </div>
                            <?php if($item['passenger']->passenger_email): ?>
                                <div>
                                    <strong>Email:</strong> <?php echo e($item['passenger']->passenger_email); ?>

                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>

            <!-- QR Code Section -->
            <div class="qr-section">
                <div class="booking-ref">#<?php echo e($bookingRef); ?></div>
                <div style="color: #666; margin-bottom: 15px;">Present this reference number when boarding</div>
                <div class="qr-placeholder">
                    QR CODE<br>
                    <small>(<?php echo e($bookingRef); ?>)</small>
                </div>
                <div style="color: #666; font-size: 12px; margin-top: 10px;">
                    Scan this QR code for quick check-in at the terminal
                </div>
            </div>

            <!-- Important Notes -->
            <div class="important-notes">
                <h4>📋 Important Reminders</h4>
                <ul>
                    <li><strong>Arrival Time:</strong> Please arrive at the terminal at least 30 minutes before
                        departure</li>
                    <li><strong>Valid ID:</strong> Bring a valid government-issued ID for verification</li>
                    <li><strong>Baggage:</strong> Free hand carry allowance is 7kg per passenger</li>
                    <li><strong>Check-in:</strong> Present this ticket (digital or printed) and your ID at check-in</li>
                    <li><strong>Contact:</strong> For inquiries, call (032) 232-8864 or email lapulapulslc1964@gmail.com
                    </li>
                    <li><strong>Refunds:</strong> Ticket modifications must be made at least 2 hours before departure
                    </li>
                </ul>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p><strong>Lapulapu Shipping Lines Corporation</strong></p>
            <p>872 M.J. Cuenco Avenue, Barangay Lorega, 6000 Cebu City, Philippines</p>
            <p>Tel: (032) 232-8864, 232-8865 | Email: lapulapulslc1964@gmail.com</p>
            <p style="margin-top: 15px; color: #999;">
                This is an automatically generated ticket. Please keep this email for your records.
            </p>
        </div>
    </div>
</body>

</html>
<?php /**PATH C:\Users\Sophia\Documents\Capstone\resources\views/emails/ticket.blade.php ENDPATH**/ ?>