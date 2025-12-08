<?php $__env->startSection('page-title', 'CARGO BOOKING DETAILS'); ?>

<?php $__env->startSection('content'); ?>
<?php echo $__env->make('components.authHeader', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php echo $__env->make('components.staff_nav', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>


    <div class="staff-body">
    <div class="svl-title">
        <h3>REVIEW CARGO BOOKINGS</h3>
    </div>

    
    
    
    <div class="card shadow-sm p-4 mb-4">
        <h5 class="mb-3">Booking Information</h5>
        <div class="row">
            <div class="col-md-6">
                <p><strong>Booking Ref #:</strong> <?php echo e($booking->booking_ref_no); ?></p>
                <p><strong>Status:</strong> <?php echo e($booking->booking_status); ?></p>
                <p><strong>Created:</strong> <?php echo e($booking->created_at->format('M d, Y')); ?></p>
            </div>
            <div class="col-md-6">
                <?php if($booking->voyage): ?>
                    <p><strong>Voyage Code:</strong> <?php echo e($booking->voyage->voyage_code); ?></p>
                    <p><strong>Departure:</strong> <?php echo e($booking->voyage->voyage_departure_date); ?></p>
                    <p><strong>Arrival:</strong> <?php echo e($booking->voyage->voyage_arrival_date); ?></p>
                <?php else: ?>
                    <p><strong>Voyage:</strong> N/A</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    
    
    
    <div class="card shadow-sm p-4 mb-4">
        <h5 class="mb-3">Sender & Consignee Information</h5>
        <div class="row">
            <div class="col-md-6">
                <h6 class="fw-bold">Sender Information</h6>
                <p><strong>Name:</strong> <?php echo e($booking->sender->sender_name); ?></p>
                <p><strong>Contact:</strong> <?php echo e($booking->sender->sender_contactno); ?></p>
                <p><strong>Email:</strong> <?php echo e($booking->sender->sender_email); ?></p>
            </div>
            <div class="col-md-6">
                <h6 class="fw-bold">Consignee Information</h6>
                <p><strong>Name:</strong> <?php echo e($booking->consignee->consignee_name); ?></p>
                <p><strong>Contact:</strong> <?php echo e($booking->consignee->consignee_contactno); ?></p>
            </div>
        </div>
    </div>

    
    
    
    <?php
        $cargoBookings = $booking->cargoBookings;
    ?>
<?php if($cargoBookings->count() > 0): ?>
<div class="card shadow-sm p-4 mb-4">
    <h4 class="fw-bold mb-3">Cargo Photos</h4>

    <div id="cargoCarousel" class="carousel slide" data-bs-ride="carousel">
        
        
        <div class="carousel-indicators">
            <?php $indicatorIndex = 0; ?>
            <?php $__currentLoopData = $cargoBookings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php if($c->cargo_picture): ?>
                    <button type="button"
                            data-bs-target="#cargoCarousel"
                            data-bs-slide-to="<?php echo e($indicatorIndex); ?>"
                            class="<?php echo e($indicatorIndex === 0 ? 'active' : ''); ?>"
                            aria-current="<?php echo e($indicatorIndex === 0 ? 'true' : 'false'); ?>">
                    </button>
                    <?php $indicatorIndex++; ?>
                <?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        
        <div class="carousel-inner">
            <?php $slideIndex = 0; ?>
            <?php $__currentLoopData = $cargoBookings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php if($c->cargo_picture): ?>
                    <?php
                        // Ensure we only use the filename
                        $filename = basename($c->cargo_picture);

                        // Build full asset path
                        $imgPath = file_exists(storage_path('app/public/cargo_pictures/' . $filename))
                                    ? asset('storage/cargo_pictures/' . $filename)
                                    : asset('images/no-image.png'); // fallback
                    ?>
                    <div class="carousel-item <?php echo e($slideIndex === 0 ? 'active' : ''); ?>">
                        <img src="<?php echo e($imgPath); ?>" class="d-block w-100 cargo-carousel-img">
                        <div class="carousel-caption text-start">
                            <?php echo e($c->quantity); ?> <?php echo e($c->cargoItem->cargo_item_classification ?? ''); ?> of <?php echo e($c->cargoItem->cargo_item_description ?? ''); ?>

                        </div>
                    </div>
                    <?php $slideIndex++; ?>
                <?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        
        <button class="carousel-control-prev" type="button" data-bs-target="#cargoCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon"></span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#cargoCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon"></span>
        </button>
    </div>
</div>
<?php endif; ?>


    
    
    
    <div class="card shadow-sm p-3 mb-4">
        <h5>Cargo Items</h5>
        <table class="table table-bordered table-striped mt-3">
            <thead class="table-dark">
                <tr>
                    <th>Description</th>
                    <th>Qty</th>
                    <th>Dimensions (L×W×H cm)</th>
                    <th>CBM</th>
                    <th>Freight</th>
                    <th>Arrastre</th>
                    <th>Subtotal</th>
                </tr>
            </thead>

            <tbody>
                <?php $total = 0; ?>
                <?php $__currentLoopData = $cargoBookings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $freight = $c->cargoItem->cargo_item_freight;
                        $arrastre = $c->cargoItem->cargo_item_arrastre;
                        $cbm = ($c->length * $c->width * $c->height) / 1000000;
                        $subtotal = ($freight + $arrastre) * $cbm * $c->quantity;
                        $total += $subtotal;
                    ?>

                    <tr>
                        <td><?php echo e($c->cargoItem->cargo_item_description); ?> <br>
                            <small class="text-muted">(<?php echo e($c->cargoItem->cargo_item_classification); ?>)</small>
                        </td>
                        <td><?php echo e($c->quantity); ?></td>
                        <td><?php echo e($c->length); ?> × <?php echo e($c->width); ?> × <?php echo e($c->height); ?></td>
                        <td><?php echo e(number_format($cbm, 4)); ?></td>
                        <td>₱<?php echo e(number_format($freight, 2)); ?></td>
                        <td>₱<?php echo e(number_format($arrastre, 2)); ?></td>
                        <td>₱<?php echo e(number_format($subtotal, 2)); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>

            <tfoot>
                <tr>
                    <th colspan="6" class="text-end">TOTAL:</th>
                    <th>₱<?php echo e(number_format($total, 2)); ?></th>
                </tr>
            </tfoot>
        </table>
    </div>

    
    
    
    <?php if($booking->booking_status === 'Pending'): ?>
        <div class="d-flex justify-content-center gap-3 mt-4">
            <form action="<?php echo e(route('cargo.bookings.approve', $booking->booking_ref_no)); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <button class="btn btn-success btn-lg px-4">Accept</button>
            </form>
            <form action="<?php echo e(route('cargo.bookings.reject', $booking->booking_ref_no)); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <button class="btn btn-danger btn-lg px-4">Reject</button>
            </form>
        </div>
    <?php endif; ?>

    <div class="text-center mt-4">
        <a href="<?php echo e(route('cargo.bookings.pending')); ?>" class="btn btn-outline-primary btn-lg px-4">
            Back to Pending Bookings
        </a>
    </div>

</div>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('styles'); ?>
<style>
    /* Carousel image styling (similar to VIEW RATES) */
    .cargo-carousel-img {
        max-height: 320px;
        object-fit: contain;
        background: #f8f9fa;
        border-radius: 10px;
        padding: 10px;
    }

    .carousel-caption {
        background: rgba(0,0,0,0.6);
        padding: 5px 10px;
        border-radius: 5px;
        bottom: 10px;
    }
</style>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Sophia\Documents\Capstone\resources\views/authorized/staff/showcargo.blade.php ENDPATH**/ ?>