<?php $__env->startSection('content'); ?>
<?php echo $__env->make('components.hero', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<div class="container my-5">
    <div class="card shadow-sm mx-auto" style="max-width:1100px; background-color:#f0f0f0;">
        <div class="card-header bg-dark text-white text-center mb-1">
            <h5 class="mb-0">CARGO BOOKING CONFIRMATION</h5>
        </div>

        <div class="card-body">
            <h6>Booking Reference: <strong><?php echo e($booking->booking_ref_no); ?></strong></h6>
            <p>Status: <strong><?php echo e($booking->booking_status); ?></strong></p>

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
                        <th>Item</th>
                        <th>Quantity</th>
                        <th>Dimensions (LxWxH cm)</th>
                        <th>CBM</th>
                        <th>Freight Rate</th>
                        <th>Arrastre Rate</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $totalExpense = 0; ?>
                    <?php $__currentLoopData = $cargoItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            // CBM calculation (m³)
                            $cbm = ($item->length * $item->width * $item->height) / 1000000;

                            // Subtotal: (freight + arrastre) * quantity * CBM
                            $subtotal = $cbm * ($item->freight + $item->arrastre) * $item->quantity;
                            $totalExpense += $subtotal;
                        ?>
                        <tr>
                            <td><?php echo e($item->cargo_item_description); ?> (<?php echo e($item->cargo_item_classification); ?>)</td>
                            <td><?php echo e($item->quantity); ?></td>
                            <td><?php echo e($item->length); ?> x <?php echo e($item->width); ?> x <?php echo e($item->height); ?></td>
                            <td><?php echo e(number_format($cbm, 3)); ?></td>
                            <td>PHP <?php echo e(number_format($item->freight, 2)); ?></td>
                            <td>PHP <?php echo e(number_format($item->arrastre, 2)); ?></td>
                            <td>PHP <?php echo e(number_format($subtotal, 2)); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="6" class="text-end">Total Expense:</th>
                        <th>PHP <?php echo e(number_format($totalExpense, 2)); ?></th>
                    </tr>
                </tfoot>
            </table>

<div class="d-flex justify-content-end gap-2 mt-4">
    <?php if(strtolower($booking->booking_status) === 'pending'): ?>
        <form action="<?php echo e(route('cargobooking.finalize', ['booking_ref_no' => $booking->booking_ref_no])); ?>" method="POST">
            <?php echo csrf_field(); ?>
            <button type="submit" class="btn btn-primary">Proceed</button>
        </form>

        <form action="<?php echo e(route('cargobooking.cancel', ['booking_ref_no' => $booking->booking_ref_no])); ?>" method="POST">
            <?php echo csrf_field(); ?>
            <button type="submit" class="btn btn-outline-secondary">Cancel Booking</button>
        </form>
    <?php endif; ?>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\kirzt\Documents\GitHub\Capstone\resources\views/passenger/cargobooking_confirm.blade.php ENDPATH**/ ?>