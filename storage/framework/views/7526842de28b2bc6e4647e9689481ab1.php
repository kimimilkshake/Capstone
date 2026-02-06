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
                <h5 class="mb-2">Booking Information</h5>
                <p><strong>Booking Ref #:</strong> <?php echo e($booking->booking_code); ?></p>
                <p><strong>Status:</strong> <?php echo e($booking->booking_status); ?></p>
                <p><strong>Created:</strong> <?php echo e($booking->created_at->format('M d, Y')); ?></p>

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
                    <p><strong>Amount Paid:</strong> ₱<?php echo e(number_format($payment->total_amount,2)); ?></p>
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

    
    
    
    <?php
        $cargoBookings = $booking->cargoBookings;
        $cargoWithPhotos = $cargoBookings->filter(fn($c) => $c->cargo_picture)->values();
        $hasPhotos = $cargoWithPhotos->count() > 0;
    ?>

    <div class="card shadow-sm p-4 mb-4">
        <h5 class="fw-bold mb-3">Cargo Photos</h5>

        
        <?php if(!$hasPhotos): ?>
            <p class="text-muted text-center fst-italic">
                No photos were included since the booking was made by the staff
            </p>
        <?php else: ?>
            
            <div id="cargoCarousel" class="carousel slide carousel-fade" data-bs-ride="carousel">
                <div class="carousel-indicators">
                    <?php $__currentLoopData = $cargoWithPhotos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $cargo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <button type="button" 
                                data-bs-target="#cargoCarousel" 
                                data-bs-slide-to="<?php echo e($index); ?>" 
                                class="<?php echo e($index === 0 ? 'active' : ''); ?>"
                                aria-label="Slide <?php echo e($index + 1); ?>"></button>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>

                <div class="carousel-inner">
                    <?php $__currentLoopData = $cargoWithPhotos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $cargo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $filename = basename($cargo->cargo_picture);
                            $imgPath = file_exists(storage_path('app/public/cargo_pictures/' . $filename))
                                        ? asset('storage/cargo_pictures/' . $filename)
                                        : asset('images/no-image.png');
                            $cargoDescription = $cargo->cargoItem->cargo_item_description ?? 'Unknown Cargo';
                            $cargoClassification = $cargo->cargoItem->cargo_item_classification ?? '';
                        ?>

                        <div class="carousel-item <?php echo e($index === 0 ? 'active' : ''); ?>">
                            <div class="carousel-image-container">
                                <img src="<?php echo e($imgPath); ?>" 
                                     class="d-block w-100 carousel-img"
                                     alt="<?php echo e($cargoDescription); ?>"
                                     data-image="<?php echo e($imgPath); ?>" 
                                     data-description="<?php echo e($cargoDescription); ?>"
                                     data-classification="<?php echo e($cargoClassification); ?>"
                                     data-bs-toggle="modal"
                                     data-bs-target="#photoModal"
                                     style="cursor: pointer;">
                                
                                
                                <div class="carousel-zoom-overlay" 
                                     data-image="<?php echo e($imgPath); ?>" 
                                     data-description="<?php echo e($cargoDescription); ?>"
                                     data-classification="<?php echo e($cargoClassification); ?>"
                                     data-bs-toggle="modal"
                                     data-bs-target="#photoModal"
                                     style="cursor: pointer;">
                                    <div class="zoom-content">
                                    </div>
                                </div>

                                
                                <div class="carousel-caption-overlay">
                                    <h5 class="carousel-cargo-title"><?php echo e($cargoDescription); ?></h5>
                                    <p class="carousel-cargo-classification"><?php echo e($cargoClassification); ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>

                
                <?php if($cargoWithPhotos->count() > 1): ?>
                    <button class="carousel-control-prev" type="button" data-bs-target="#cargoCarousel" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Previous</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#cargoCarousel" data-bs-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Next</span>
                    </button>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    
    
    
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
            <form action="<?php echo e(route('cargo.bookings.approve', $booking->booking_ref_no)); ?>" method="POST" class="w-100" style="max-width: 200px;">
                <?php echo csrf_field(); ?>
                <button class="btn btn-success btn-lg px-4 w-100">Accept</button>
            </form>

            <!-- Open modal to collect rejection reason -->
            <button class="btn btn-danger btn-lg px-4" data-bs-toggle="modal" data-bs-target="#rejectModal" style="width: 200px;">Reject</button>
        </div>

        <!-- Reject Modal -->
        <div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form action="<?php echo e(route('cargo.bookings.reject', $booking->booking_ref_no)); ?>" method="POST">
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

    
    <div class="modal fade" id="photoModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content bg-dark">
                <div class="modal-body p-0 position-relative" style="height: 600px;">
                    <img id="modalCargoPhoto" class="w-100 h-100" style="object-fit: contain;" alt="Cargo Photo">
                    
                    
                    <div class="position-absolute bottom-0 start-0 p-3 bg-dark bg-opacity-90 text-white" style="border-radius: 0 8px 0 0;">
                        <h6 id="modalPhotoCaption" class="mb-1">Cargo Item</h6>
                        <small id="modalPhotoClassification" class="text-muted">Classification: --</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="text-center mt-4">
        <a href="<?php echo e(route('cargo.bookings.pending')); ?>" class="btn btn-outline-primary btn-lg px-4">
            Back to Pending Bookings
        </a>

        <button class="btn btn-secondary btn-lg px-4 ms-3" data-bs-toggle="modal" data-bs-target="#billOfLadingModal">
            Bill of Lading
        </button>
    </div>

    <!-- Bill of Lading Modal -->
    <div class="modal fade" id="billOfLadingModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Bill of Lading - <?php echo e($booking->booking_ref_no); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="billOfLadingContent">
                    <div class="p-3">
                        <h5>Shipping Information</h5>
                        <p>Vessel Name: <?php echo e($booking->voyage->vessel_name ?? 'Not specified'); ?></p>
                        <p>Voyage No.: <?php echo e($booking->voyage->voyage_code ?? 'N/A'); ?></p>
                        <p>Bill of Lading (B/L) No.: <?php echo e($booking->booking_ref_no); ?></p>
                        <p>Sailing Date: <?php echo e($booking->voyage ? \Carbon\Carbon::parse($booking->voyage->voyage_departure_date)->format('M d, Y') : 'N/A'); ?></p>
                        <p>Loading Port: <?php echo e($booking->voyage->loading_port ?? 'Not specified'); ?></p>
                        <p>Unloading Port: <?php echo e($booking->voyage->unloading_port ?? 'Not specified'); ?></p>

                        <hr />
                        <h5>Party Details</h5>
                        <p><strong>Shipper:</strong> <?php echo e($booking->sender->sender_name ?? ''); ?></p>
                        <p><strong>Shipper Contact Number :</strong> <?php echo e($booking->sender->sender_contactno ?? ''); ?></p>
                        <p><strong>Shipper Email :</strong> <?php echo e($booking->sender->sender_email ?? ''); ?></p>

                        <p><strong>Consignee:</strong> <?php echo e($booking->consignee->consignee_name ?? ''); ?></p>
                        <p><strong>Consignee Contact Number :</strong> <?php echo e($booking->consignee->consignee_contactno ?? ''); ?></p>

                        <hr />
                        <h5>Cargo Description</h5>
                        <?php $__currentLoopData = $booking->cargoBookings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="mb-2">
                                <div><strong>Qty:</strong> <?php echo e($c->quantity); ?></div>
                                <div><strong>Classification:</strong> <?php echo e($c->cargoItem->cargo_item_classification ?? ''); ?></div>
                                <div><strong>Description:</strong> <?php echo e($c->cargoItem->cargo_item_description ?? ''); ?></div>
                                <div><strong>Dimensions:</strong> <?php echo e($c->length); ?> x <?php echo e($c->width); ?> x <?php echo e($c->height); ?></div>
                                <div><strong>Weight:</strong> <?php echo e($c->weight); ?> kg</div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="<?php echo e(route('cargo.bookings.bol', $booking->booking_ref_no)); ?>" target="_blank" class="btn btn-outline-primary">Open PDF</a>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

</div>
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
</style>

<script>
    // Modal image data handling
    document.querySelectorAll('[data-bs-target="#photoModal"]').forEach(element => {
        element.addEventListener('click', function(e) {
            e.stopPropagation();
            const image = this.getAttribute('data-image');
            const description = this.getAttribute('data-description');
            const classification = this.getAttribute('data-classification');
            
            document.getElementById('modalCargoPhoto').src = image;
            document.getElementById('modalPhotoCaption').textContent = description;
            document.getElementById('modalPhotoClassification').textContent = 'Classification: ' + classification;
        });
    });

    function printBillOfLading() {
        const content = document.getElementById('billOfLadingContent').innerHTML;
        const w = window.open('', '_blank');
        w.document.open();
        w.document.write(`<!doctype html><html><head><title>Bill of Lading</title><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"></head><body>${content}</body></html>`);
        w.document.close();
        w.focus();
        setTimeout(() => { w.print(); }, 300);
    }
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\kirzt\Documents\GitHub\Capstone\resources\views/authorized/staff/showcargo.blade.php ENDPATH**/ ?>