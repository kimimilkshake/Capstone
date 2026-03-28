<?php $__env->startSection('page-title', 'CARGO BOOKING DETAILS'); ?>

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('components.authHeader', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo $__env->make('components.staff_nav', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="staff-body">

        <div class="row">
            
            <div class="col-lg-6">
                <div class="card shadow-sm p-4 mb-4">
                    <h5 class="mb-2">Booking Information</h5>

                    <?php
                        // ✅ CENTRALIZED CALCULATION FUNCTION (ADDED ONLY)
                        function computeSubtotal($cargo) {
                            $freight = $cargo->cargoItem->cargo_item_freight ?? 0;

                            // Always compute CBM consistently (do not rely on stored cbm)
                            $cbm = (float)(
                                ($cargo->length * $cargo->width * $cargo->height) / 1000000
                            );

                            $qty = (float) ($cargo->quantity ?? 0);

                            $measureRequired = strtolower($cargo->cargoItem->cargo_item_measure_required ?? 'no');

                            if ($measureRequired === 'yes') {
                                return $freight * $qty;
                            }

                            return $freight * $cbm * $qty;
                        }

                        $processedBy = optional(optional($booking->cargoBookings->first())->approvedByStaff)
                            ->staff_name;
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
                    ?>

                    <p><strong>Booking Ref #:</strong> <?php echo e($booking->booking_code); ?></p>
                    <p><strong>Status:</strong> <?php echo e($booking->booking_status); ?></p>
                    <p><strong>Created:</strong> <?php echo e($booking->created_at->format('M d, Y')); ?></p>
                    <p><strong>Approved By:</strong> <?php echo e($processedLabel); ?></p>

                    <?php if($booking->voyage): ?>
                        <p><strong>Voyage Code:</strong> <?php echo e($booking->voyage->voyage_code); ?></p>
                        <p><strong>Departure:</strong> <?php echo e($booking->voyage->voyage_departure_date); ?></p>
                        <p><strong>Arrival:</strong> <?php echo e($booking->voyage->voyage_arrival_date); ?></p>
                    <?php else: ?>
                        <p><strong>Voyage:</strong> N/A</p>
                    <?php endif; ?>

                    <?php if($payment): ?>
                        <p><strong>Mode of Payment:</strong> <?php echo e($payment->mode_of_payment); ?></p>
                        <p><strong>Payment Status:</strong> <?php echo e($payment->payment_status); ?></p>

                        
                        <?php
                            $calculatedTotal = 0;

                            foreach ($booking->cargoBookings as $c) {
                                $calculatedTotal += computeSubtotal($c);
                            }

                            $stamp = 20.00;
                            $calculatedTotal += $stamp;
                        ?>

                        <p><strong>Amount Paid:</strong> ₱<?php echo e(number_format($calculatedTotal, 2)); ?></p>
                    <?php endif; ?>
                </div>
            </div>

            
            <div class="col-lg-6">
                <div class="card shadow-sm p-4 mb-4">

                    <h6 class="fw-bold">Sender Information</h6>
                    <p><strong>Name:</strong> <?php echo e($booking->sender->sender_name); ?></p>
                    <p><strong>Contact:</strong> <?php echo e($booking->sender->sender_contactno); ?></p>
                    <p><strong>Email:</strong> <?php echo e($booking->sender->sender_email); ?></p>

                    <h6 class="fw-bold mt-3">Consignee Information</h6>
                    <p><strong>Name:</strong> <?php echo e($booking->consignee->consignee_name); ?></p>
                    <p><strong>Contact:</strong> <?php echo e($booking->consignee->consignee_contactno); ?></p>
                </div>
            </div>
        </div>

        
        
        
        <div class="card shadow-sm p-3 mb-4">
            <div class="table-responsive">
                <table class="table table-bordered table-striped mt-3 align-middle cargo-items-table">
                    <thead class="table-dark">
                        <tr>
                            <th>Qty</th>
                            <th>Classification</th>
                            <th>Description</th>
                            <th>Length</th>
                            <th>Width</th>
                            <th>Height</th>
                            <th>CBM</th>
                            <th>Total Weight (kg)</th>
                            <th>Freight</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php $total = 0; ?>

                        <?php $__currentLoopData = $booking->cargoBookings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                // ✅ FIXED CBM CALCULATION (CONSISTENT)
                                $cbm = (float)(
                                    ($c->length * $c->width * $c->height) / 1000000
                                );

                                $freight = $c->cargoItem->cargo_item_freight ?? 0;

                                // ✅ USE CENTRALIZED FUNCTION
                                $subtotal = computeSubtotal($c);
                                $total += $subtotal;
                            ?>

                            <tr>
                                <td class="text-center"><?php echo e($c->quantity); ?></td>
                                <td><?php echo e($c->cargoClassification->cargo_classification_name ?? 'N/A'); ?></td>
                                <td><?php echo e($c->cargoItem->cargo_item_description); ?></td>
                                <td class="text-end"><?php echo e(number_format($c->length, 2)); ?></td>
                                <td class="text-end"><?php echo e(number_format($c->width, 2)); ?></td>
                                <td class="text-end"><?php echo e(number_format($c->height, 2)); ?></td>
                                <td class="text-end"><?php echo e(number_format($cbm, 4)); ?></td>
                                <td class="text-end"><?php echo e(number_format($c->weight, 2)); ?></td>
                                <td class="text-end">₱<?php echo e(number_format($freight, 2)); ?></td>
                                <td class="text-end">₱<?php echo e(number_format($subtotal, 2)); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>

                    <tfoot>
                        <tr>
                            <th colspan="9" class="text-end">TOTAL:</th>
                            <th class="text-end">
                                <?php
                                    // ✅ FINAL TOTAL RECALCULATION (NO TRUSTING OLD VALUES)
                                    $total = 0;

                                    foreach ($booking->cargoBookings as $c) {
                                        $total += computeSubtotal($c);
                                    }

                                    $stamp = 20.00;
                                    $totalTransaction = $total + $stamp;
                                ?>

                                ₱<?php echo e(number_format($totalTransaction, 2)); ?>

                            </th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <?php if($booking->booking_status === 'Pending'): ?>
            <div class="d-flex justify-content-center gap-3 mt-4">
                <form action="<?php echo e(route('cargo.bookings.approve', $booking->booking_ref_no)); ?>" method="POST"
                    class="w-100" style="max-width: 200px;" id="acceptForm">
                    <?php echo csrf_field(); ?>
                    <button type="button" class="btn btn-success btn-lg px-4 w-100" id="acceptBtn"
                        onclick="validateAndAccept(event)">Accept</button>
                </form>

                <button type="button" class="btn btn-danger btn-lg px-4" id="rejectBtn"
                    onclick="showRejectModal(event)" style="width: 200px;">Reject</button>
            </div>
        <?php endif; ?>

        <div class="text-center mt-4">
            <a href="<?php echo e(route('cargo.bookings.pending')); ?>" class="btn btn-outline-primary btn-lg px-4">
                Back to Pending Bookings
            </a>

            <a href="<?php echo e(route('cargo.bookings.bol', $booking->booking_ref_no)); ?>" target="_blank"
                class="btn btn-secondary btn-lg px-4 ms-3">
                Bill of Lading
            </a>
        </div>
    </div>

    <?php if($booking->booking_status === 'Pending'): ?>
        <div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form action="<?php echo e(route('cargo.bookings.reject', $booking->booking_ref_no)); ?>" method="POST"
                        onsubmit="showLoadingModal(event)">
                        <?php echo csrf_field(); ?>
                        <div class="modal-header">
                            <h5 class="modal-title">Reason for Rejection</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Please provide the reason for rejecting this booking</label>
                                <textarea name="reason" class="form-control" rows="4" required></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-danger">Submit Rejection</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endif; ?>

    
    <div class="modal fade" id="placementValidationModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title" id="placementModalTitle">⚠️ Cargo Placement Validation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="placementMessage"></div>
                    <div id="unpackedItemsList" class="mt-3"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" id="confirmAcceptBtn"
                        onclick="proceedWithAcceptance()">Proceed with Acceptance</button>
                </div>
            </div>
        </div>
    </div>

    
    <div class="modal fade" id="loadingModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0">
                <div class="modal-body text-center p-5">
                    <div class="spinner-border text-primary mb-3" role="status" style="width: 60px; height: 60px;">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <h5 class="mt-3">Loading... Please Wait</h5>
                    <p class="text-muted mt-2">Processing your request</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        /**
         * Show reject modal
         */
        function showRejectModal(event) {
            event.preventDefault();
            const modal = new bootstrap.Modal(document.getElementById('rejectModal'));
            modal.show();
        }

        /**
         * Show loading modal when form is submitted
         */
        function showLoadingModal(event) {
            event.preventDefault();
            const modal = new bootstrap.Modal(document.getElementById('loadingModal'));
            modal.show();
            
            // Submit the form after showing the modal
            setTimeout(() => {
                event.target.submit();
            }, 500);
        }

        /**
         * Validate cargo placement before accepting booking
         */
        function validateAndAccept(event) {
            event.preventDefault();

            const voyageId = <?php echo e($booking->voyage_id); ?>;
            const cargoBookingIds = [
                <?php $__currentLoopData = $booking->cargoBookings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cargo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php echo e($cargo->cargo_booking_id); ?>,
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            ];

            if (cargoBookingIds.length === 0) {
                // If no cargo items, just show loading and submit
                showLoadingAndSubmit();
                return;
            }

            // Show loading indicator
            const btn = event.target;
            const originalText = btn.innerHTML;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Validating placement...';
            btn.disabled = true;

            // Call API to validate placement
            fetch('<?php echo e(route('cargo.placement.validate')); ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ||
                            document.querySelector('input[name="_token"]')?.value
                    },
                    body: JSON.stringify({
                        voyage_id: voyageId,
                        cargo_booking_ids: cargoBookingIds
                    })
                })
                .then(response => response.json())
                .then(data => {
                    btn.innerHTML = originalText;
                    btn.disabled = false;

                    if (data.success || data.skipValidation) {
                        // Cargo can fit, proceed with acceptance
                        showLoadingAndSubmit();
                    } else {
                        // Show warning modal
                        showPlacementWarning(data);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    btn.innerHTML = originalText;
                    btn.disabled = false;

                    // If error, still allow to proceed (fail-open policy)
                    showLoadingAndSubmit();
                });
        }

        /**
         * Show loading modal and submit form
         */
        function showLoadingAndSubmit() {
            const modal = new bootstrap.Modal(document.getElementById('loadingModal'));
            modal.show();
            
            setTimeout(() => {
                document.getElementById('acceptForm').submit();
            }, 500);
        }

        /**
         * Show placement validation warning as browser alert
         */
        function showPlacementWarning(data) {
            alert(data.message);
        }

        /**
         * Proceed with acceptance after validation
         */
        function proceedWithAcceptance() {
            const form = document.getElementById('acceptForm');
            if (form) {
                const modal = bootstrap.Modal.getInstance(document.getElementById('placementValidationModal'));
                if (modal) {
                    modal.hide();
                }
                
                showLoadingAndSubmit();
            }
        }
    </script>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('styles'); ?>
    <style>
        /* Bootstrap Carousel Customization */
        #cargoCarousel {
            background: #f8f9fa;
            border-radius: 12px;
            overflow: hidden;
            padding: 20px;
        }

        .carousel-inner {
            border-radius: 8px;
            overflow: hidden;
            background: white;
        }

        .carousel-item {
            height: 500px;
        }

        .carousel-image-container {
            position: relative;
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #e9ecef;
            overflow: hidden;
        }

        .carousel-img {
            object-fit: contain;
            padding: 30px;
            transition: transform 0.3s ease;
            cursor: pointer;
        }

        .carousel-item:hover .carousel-img {
            transform: scale(1.05);
        }

        /* Zoom Overlay */
        .carousel-zoom-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .carousel-item:hover .carousel-zoom-overlay {
            opacity: 1;
        }

        .zoom-content {
            text-align: center;
            color: white;
        }

        .zoom-content i {
            font-size: 3.5rem;
            display: block;
            margin-bottom: 12px;
            text-shadow: 0 2px 6px rgba(0, 0, 0, 0.4);
        }

        .zoom-content p {
            font-size: 1.1rem;
            font-weight: 600;
            margin: 0;
            text-shadow: 0 1px 3px rgba(0, 0, 0, 0.4);
        }

        /* Caption Overlay at Bottom */
        .carousel-caption-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(to top, rgba(0, 0, 0, 0.85), rgba(0, 0, 0, 0.6) 70%, rgba(0, 0, 0, 0));
            color: white;
            padding: 30px 20px 20px;
            text-align: center;
        }

        .carousel-cargo-title {
            font-size: 1.6rem;
            font-weight: 700;
            margin: 0 0 10px 0;
            line-height: 1.3;
        }

        .carousel-cargo-classification {
            font-size: 1rem;
            color: #e0e0e0;
            margin: 0;
            font-weight: 500;
        }

        /* Indicators */
        .carousel-indicators {
            bottom: -50px;
            padding: 20px 0 0;
        }

        .carousel-indicators button {
            width: 14px;
            height: 14px;
            border-radius: 50%;
            background-color: #dee2e6;
            border: 2px solid #dee2e6;
            transition: all 0.3s ease;
        }

        .carousel-indicators button:hover {
            background-color: #0d6efd;
            border-color: #0d6efd;
        }

        .carousel-indicators button.active {
            background-color: #0d6efd;
            border-color: #0d6efd;
            width: 16px;
            height: 16px;
        }

        /* Navigation Controls */
        .carousel-control-prev,
        .carousel-control-next {
            width: 50px;
            height: 50px;
            background: rgba(13, 110, 253, 0.9);
            border-radius: 50%;
            top: 50%;
            transform: translateY(-50%);
            transition: all 0.3s ease;
            opacity: 1;
        }

        .carousel-control-prev:hover,
        .carousel-control-next:hover {
            background: rgba(13, 110, 253, 1);
            box-shadow: 0 4px 12px rgba(13, 110, 253, 0.4);
        }

        .carousel-control-prev-icon,
        .carousel-control-next-icon {
            filter: brightness(1.3);
            font-size: 1.5rem;
        }

        .carousel-control-prev {
            left: 20px;
        }

        .carousel-control-next {
            right: 20px;
        }

        /* Fade Animation */
        .carousel-fade .carousel-item {
            opacity: 0;
            transition-property: opacity;
            transition-duration: 0.6s;
        }

        .carousel-fade .carousel-item.active {
            opacity: 1;
        }

        /* Responsive Design */
        @media (max-width: 992px) {
            #cargoCarousel {
                padding: 15px;
            }

            .carousel-item {
                height: 400px;
            }

            .carousel-img {
                padding: 25px;
            }

            .carousel-cargo-title {
                font-size: 1.4rem;
            }

            .carousel-cargo-classification {
                font-size: 0.95rem;
            }

            .carousel-control-prev {
                left: 10px;
            }

            .carousel-control-next {
                right: 10px;
            }
        }

        @media (max-width: 768px) {
            #cargoCarousel {
                padding: 12px;
            }

            .carousel-item {
                height: 320px;
            }

            .carousel-img {
                padding: 20px;
            }

            .zoom-content i {
                font-size: 2.5rem;
                margin-bottom: 8px;
            }

            .zoom-content p {
                font-size: 0.95rem;
            }

            .carousel-cargo-title {
                font-size: 1.2rem;
                padding: 0 10px;
            }

            .carousel-cargo-classification {
                font-size: 0.85rem;
            }

            .carousel-control-prev,
            .carousel-control-next {
                width: 44px;
                height: 44px;
            }

            .carousel-control-prev-icon,
            .carousel-control-next-icon {
                font-size: 1.25rem;
            }
        }

        @media (max-width: 576px) {
            #cargoCarousel {
                padding: 10px;
            }

            .carousel-item {
                height: 250px;
            }

            .carousel-img {
                padding: 15px;
            }

            .carousel-caption-overlay {
                padding: 20px 15px 15px;
            }

            .zoom-content i {
                font-size: 2rem;
                margin-bottom: 6px;
            }

            .zoom-content p {
                font-size: 0.85rem;
            }

            .carousel-cargo-title {
                font-size: 1rem;
                padding: 0 8px;
            }

            .carousel-cargo-classification {
                font-size: 0.75rem;
            }

            .carousel-control-prev,
            .carousel-control-next {
                width: 40px;
                height: 40px;
            }

            .carousel-control-prev {
                left: 5px;
            }

            .carousel-control-next {
                right: 5px;
            }

            .carousel-indicators {
                bottom: -40px;
            }

            .carousel-indicators button {
                width: 12px;
                height: 12px;
            }

            .carousel-indicators button.active {
                width: 14px;
                height: 14px;
            }
        }

        .cargo-items-table th,
        .cargo-items-table td {
            vertical-align: middle;
        }
    </style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.addEventListener('click', function(event) {
                const trigger = event.target.closest('[data-bs-target="#photoModal"]');
                if (!trigger) return;

                const image = trigger.getAttribute('data-image');
                const description = trigger.getAttribute('data-description') || 'Cargo Item';
                const classification = trigger.getAttribute('data-classification') || '--';

                const modalImage = document.getElementById('modalCargoPhoto');
                const modalCaption = document.getElementById('modalPhotoCaption');
                const modalClassification = document.getElementById('modalPhotoClassification');

                if (modalImage) modalImage.src = image || '';
                if (modalCaption) modalCaption.textContent = description;
                if (modalClassification) modalClassification.textContent = 'Classification: ' +
                    classification;
            });

            const photoModal = document.getElementById('photoModal');
            if (photoModal) {
                photoModal.addEventListener('hidden.bs.modal', function() {
                    const modalImage = document.getElementById('modalCargoPhoto');
                    if (modalImage) {
                        modalImage.removeAttribute('src');
                    }
                });
            }
        });
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\kirzt\Documents\GitHub\Capstone\resources\views/authorized/staff/showcargo.blade.php ENDPATH**/ ?>