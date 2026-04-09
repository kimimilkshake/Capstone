<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('components.hero', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="container my-5">
        <div class="card shadow-sm mx-auto" style="max-width:1100px; background-color:#f0f0f0;">
            <div class="card-header bg-dark text-white text-center mb-1">
                <h5 class="mb-0">CARGO BOOKING CONFIRMATION</h5>
            </div>

            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <h6>Booking Reference: <strong><?php echo e($booking->booking_code); ?></strong></h6>
                    </div>
                </div>

                <?php if($booking->voyage): ?>
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Voyage Code:</strong> <?php echo e($booking->voyage->voyage_code); ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Status:</strong> <?php echo e($booking->booking_status); ?></p>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Departure:</strong> <?php echo e(\Carbon\Carbon::parse($booking->voyage->voyage_departure_date)->format('M d, Y')); ?></p>
                        <?php
                            $originPort = $booking->voyage->routePort?->portOrigin;
                            $originDisplay = $originPort ? ($originPort->terminal_name ?? '') . ' ' . ($originPort->port_name ?? '') . ', ' . ($originPort->city ?? '') : 'N/A';
                        ?>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Port of Origin:</strong> <?php echo e(trim($originDisplay)); ?></p>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Arrival:</strong> <?php echo e(\Carbon\Carbon::parse($booking->voyage->voyage_arrival_date)->format('M d, Y')); ?></p>
                        <?php
                            $destPort = $booking->voyage->routePort?->portDestination;
                            $destDisplay = $destPort ? ($destPort->terminal_name ?? '') . ' ' . ($destPort->port_name ?? '') . ', ' . ($destPort->city ?? '') : 'N/A';
                        ?>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Port of Destination:</strong> <?php echo e(trim($destDisplay)); ?></p>
                    </div>
                </div>
                <?php else: ?>
                <div class="row">
                    <div class="col-md-12">
                        <p>Voyage information not available.</p>
                    </div>
                </div>
                <?php endif; ?>

                <hr>

                <div class="row">
                    
                    <div class="col-md-6">
                        <h6>Sender Information</h6>
                        <p><strong>Name:</strong> <?php echo e($sender->sender_name); ?></p>
                        <p><strong>Contact No:</strong> <?php echo e($sender->sender_contactno); ?></p>
                        <p><strong>Email:</strong> <?php echo e($sender->sender_email); ?></p>
                    </div>

                    
                    <div class="col-md-6">
                        <h6>Consignee Information</h6>
                        <p><strong>Name:</strong> <?php echo e($consignee->consignee_name); ?></p>
                        <p><strong>Contact No:</strong> <?php echo e($consignee->consignee_contactno); ?></p>
                    </div>
                </div>

                <hr>

                <h6>Cargo Items</h6>
                <table class="table table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th>Quantity</th>
                            <th>Classification</th>
                            <th>Description</th>
                            <th style="text-align: right;">Length</th>
                            <th style="text-align: right;">Width</th>
                            <th style="text-align: right;">Height</th>
                            <th style="text-align: right;">Freight Rate</th>
                            <th style="text-align: right;">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $totalExpense = 0; ?>

                        <?php $__currentLoopData = $cargoItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php

                                $withMeasurement = strtolower(trim($item->with_measurement ?? 'yes'));
                                $freight = (float) ($item->freight ?? 0);
                                $quantity = (int) ($item->quantity ?? 0);
                                $cbm = (float) ($item->cbm ?? 0);

                                if ($withMeasurement === 'no') {
                                    $subtotal = $freight * $cbm * $quantity;
                                    $rateDisplay = '₱' . number_format($freight, 2) . ' / CBM';
                                } else {
                                    $subtotal = $freight * $quantity;
                                    $rateDisplay = '₱' . number_format($freight, 2) . ' / qty';
                                }

                                $totalExpense += $subtotal;
                            ?>
                            <tr>
                                <td><?php echo e($item->quantity); ?></td>
                                <td><?php echo e($item->cargo_classification_name ?? 'N/A'); ?></td>
                                <td><?php echo e($item->cargo_item_description); ?></td>
                                <td style="text-align: right;"><?php echo e(number_format((float) $item->length, 2)); ?> <?php echo e($item->display_measurement_unit); ?></td>
                                <td style="text-align: right;"><?php echo e(number_format((float) $item->width, 2)); ?> <?php echo e($item->display_measurement_unit); ?></td>
                                <td style="text-align: right;"><?php echo e(number_format((float) $item->height, 2)); ?> <?php echo e($item->display_measurement_unit); ?></td>
                                <td style="text-align: right;">₱<?php echo e(number_format($item->freight, 2)); ?></td>
                                <td style="text-align: right;">₱<?php echo e(number_format($subtotal, 2)); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="7" class="text-end">Total Expense:</th>
                            <th style="text-align: right;">₱<?php echo e(number_format($totalExpense, 2)); ?></th>
                        </tr>
                    </tfoot>
                </table>

                <div class="d-flex justify-content-end gap-2 mt-4">
                    <?php if(strtolower($booking->booking_status) === 'pending'): ?>
                        <form action="<?php echo e(route('cargobooking.finalize', ['booking_ref_no' => $booking->booking_ref_no])); ?>"
                            method="POST">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="btn btn-primary">Proceed</button>
                        </form>

                        <form action="<?php echo e(route('cargobooking.cancel', ['booking_ref_no' => $booking->booking_ref_no])); ?>"
                            method="POST">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="btn btn-outline-secondary">Cancel Booking</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php echo $__env->make('components.footer', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <script>
        // Lock back button: push a duplicate history entry so pressing back fires
        // popstate here instead of actually navigating back to the form.
        (function() {
            history.pushState(null, '', window.location.href);

            window.addEventListener('popstate', function() {
                window.location.replace('<?php echo e(route('bookingtype')); ?>');
            });

            window.addEventListener('pageshow', function(e) {
                if (e.persisted) {
                    window.location.replace('<?php echo e(route('bookingtype')); ?>');
                }
            });
        })();
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\kirzt\Documents\GitHub\Capstone\resources\views/passenger/cargobooking_confirm.blade.php ENDPATH**/ ?>