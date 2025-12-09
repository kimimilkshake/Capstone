
<?php $__env->startSection('page-title', 'CARGO BOOKING DETAILS'); ?>

<?php $__env->startSection('content'); ?>
<?php echo $__env->make('components.authHeader', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php echo $__env->make('components.staff_nav', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<div class="staff-body">
    <div class="svl-title">
        <h3>REVIEW CARGO BOOKINGS</h3>
    </div>

    <div class="row">
        
        <div class="col-lg-6">
            <div class="card shadow-sm p-4 mb-4">
                <h5 class="mb-3">Booking Information</h5>
                <p><strong>Booking Ref #:</strong> <?php echo e($booking->booking_ref_no); ?></p>
                <p><strong>Status:</strong> <?php echo e($booking->booking_status); ?></p>
                <p><strong>Created:</strong> <?php echo e($booking->created_at->format('M d, Y')); ?></p>

                <?php if($booking->voyage): ?>
                    <p><strong>Voyage Code:</strong> <?php echo e($booking->voyage->voyage_code); ?></p>
                    <p><strong>Departure:</strong> <?php echo e($booking->voyage->voyage_departure_date); ?></p>
                    <p><strong>Arrival:</strong> <?php echo e($booking->voyage->voyage_arrival_date); ?></p>
                <?php else: ?>
                    <p><strong>Voyage:</strong> N/A</p>
                <?php endif; ?>
            </div>
        </div>

        
        <div class="col-lg-6">
            <div class="card shadow-sm p-4 mb-4">
                <h5 class="mb-3">Sender & Consignee Information</h5>

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

    
    
    
    <?php
        $cargoBookings = $booking->cargoBookings;
        $cargoWithPhotos = $cargoBookings->filter(fn($c) => $c->cargo_picture)->values();
        $hasPhotos = $cargoWithPhotos->count() > 0;
    ?>

    <div class="card shadow-sm p-4 mb-4">
        <h5 class="fw-bold mb-3">Cargo Photos</h5>

        
        <?php if(!$hasPhotos): ?>
            <p class="text-muted text-center fst-italic">
                No Photos Attached, Booking was made in the Office
            </p>
        <?php endif; ?>

        <div class="d-flex justify-content-center gap-3">

            <?php for($i = 0; $i < 3; $i++): ?>
                <?php
                    $has = isset($cargoWithPhotos[$i]);
                    if($has) {
                        $cargo = $cargoWithPhotos[$i];
                        $filename = basename($cargo->cargo_picture);
                        $imgPath = file_exists(storage_path('app/public/cargo_pictures/' . $filename))
                                    ? asset('storage/cargo_pictures/' . $filename)
                                    : asset('images/no-image.png');
                    }
                ?>

                <div class="cargo-photo-card">
                    <?php if($has): ?>
                        <img src="<?php echo e($imgPath); ?>"
                             class="cargo-photo-thumbnail"
                             data-bs-toggle="modal"
                             data-bs-target="#photoModal"
                             onclick="openPhoto('<?php echo e($imgPath); ?>')">
                    <?php else: ?>
                        <div class="empty-photo">
                            <i class="bi bi-image"></i>
                        </div>
                    <?php endif; ?>
                </div>

            <?php endfor; ?>

        </div>
    </div>

    
    <div class="modal fade" id="photoModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content bg-dark">
                <div class="modal-body text-center p-3">
                    <img id="modalPhoto" class="img-fluid" style="max-height:650px; object-fit:contain;">
                </div>
            </div>
        </div>
    </div>

    <script>
        function openPhoto(src) {
            document.getElementById('modalPhoto').src = src;
        }
    </script>

    
    
    
    <div class="card shadow-sm p-3 mb-4">
        <h5>Cargo Items</h5>

        <table class="table table-bordered table-striped mt-3">
            <thead class="table-dark">
                <tr>
                    <th>Description</th>
                    <th>Qty</th>
                    <th>Dimensions</th>
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
                        <td><?php echo e($c->cargoItem->cargo_item_description); ?>

                            <br><small class="text-muted">(<?php echo e($c->cargoItem->cargo_item_classification); ?>)</small>
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
                    <th>
                        <?php if($payment && $payment->total_amount): ?>
                            ₱<?php echo e(number_format($payment->total_amount, 2)); ?>

                        <?php else: ?>
                            ₱<?php echo e(number_format($total, 2)); ?>

                        <?php endif; ?>
                    </th>
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
    .cargo-photo-card {
        background: #fbf8ed;
        width: 90px;
        height: 90px;
        border-radius: 8px;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .cargo-photo-thumbnail {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform .25s ease;
    }

    .cargo-photo-thumbnail:hover {
        transform: scale(1.15);
    }

    .empty-photo {
        width: 100%;
        height: 100%;
        background: #f3efdf;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #b7b7b7;
        font-size: 1.8rem;
    }
</style>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\kirzt\Documents\GitHub\Capstone\resources\views/authorized/staff/showcargo.blade.php ENDPATH**/ ?>