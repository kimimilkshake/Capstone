<?php $__env->startSection('page-title', 'MANIFEST'); ?>
<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('components.authHeader', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo $__env->make('components.staff_nav', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?> 

    <div class="staff-body">
        <div class="manifest-header text-center">
            <h2 class="manifest-title">
                Voyage Number: <?php echo e($voyage->voyage_code ?? '-'); ?>

            </h2>

            <div class="d-flex justify-content-center flex-wrap gap-5 mt-3">
                <div class="text-start">
                    <p><strong>Schedule: </strong><?php echo e(optional($voyage->voyage_departure_date) ? \Carbon\Carbon::parse($voyage->voyage_departure_date)->format('F j, Y, D') : '-'); ?></p>
                    <p><strong>Vessel: </strong> <?php echo e(optional($voyage->vessel)->vessel_name ?? '-'); ?></p>
                    <p><strong>Time of Departure: </strong> <?php echo e($voyage->voyage_estimated_TD ? \Carbon\Carbon::parse($voyage->voyage_estimated_TD)->format('h:i A') : '-'); ?></p>
                    <p><strong>Actual Time of Departure: </strong> <?php echo e($voyage->voyage_actual_TD ? \Carbon\Carbon::parse($voyage->voyage_actual_TD)->format('h:i A') : '-'); ?></p>
                </div>
                <div class="text-start">
                    <p><strong>Voyage Route: </strong><?php echo e(optional($voyage->routePort)->route_origin ?? '-'); ?> to <?php echo e(optional($voyage->routePort)->route_destination ?? '-'); ?></p>
                    <p><strong>Status: </strong> <?php echo e($voyage->voyage_status ?? '-'); ?></p>
                    <p><strong>Time of Arrival: </strong><?php echo e($voyage->voyage_estimated_TA ? \Carbon\Carbon::parse($voyage->voyage_estimated_TA)->format('h:i A') : '-'); ?></p>
                    <p><strong>Actual Time of Arrival: </strong><?php echo e($voyage->voyage_actual_TA ? \Carbon\Carbon::parse($voyage->voyage_actual_TA)->format('h:i A') : '-'); ?></p>
                </div>
            </div>
        </div>

        <div class="manifest-filters text-center mt-4 ">
            <form method="GET" action="<?php echo e(url()->current()); ?>" class="d-flex gap-5 align-items-center" id="manifestFilterForm">
                <input type="hidden" name="show_passenger" value="0">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="show_passenger" id="filterPassenger" value="1" <?php echo e(($showPassenger ?? true) ? 'checked' : ''); ?> onchange="this.form.submit()">
                    <label class="form-check-label" for="filterPassenger">Show Passenger Manifest</label>
                </div>
                <input type="hidden" name="show_cargo" value="0">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="show_cargo" id="filterCargo" value="1" <?php echo e(($showCargo ?? true) ? 'checked' : ''); ?> onchange="this.form.submit()">
                    <label class="form-check-label" for="filterCargo">Show Cargo Manifest</label>
                </div>
            </form>
        </div>

        
        <?php if(!empty($showPassenger)): ?>
            <div class="passenger-manifest mt-5">
                <div class="d-flex align-items-center mb-3"><h4 class="manifest-section-title">Passenger Manifest</h4>
                <i 
                     class="fas fa-print print-icon-fa" 
                      onclick="printTable('passengerTable')"
                    ></i>
                </div>
                <table id="passengerTable" class="manifest-table table text-center align-middle">
                    <thead>
                        <tr>
                            <th>Ticket / Ref</th>
                            <th>Passenger Name</th>
                            <th>Age / Gender</th>
                            <th>Category</th>
                            <th>Accommodation</th>
                            <th>Cot No</th>
                            <th>Departure</th>
                            <th>Fare</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($passengers->isEmpty()): ?>
                            <tr><td colspan="9" class="text-center">No data available</td></tr>
                        <?php else: ?>
                            <?php $__currentLoopData = $passengers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    
                                    <td>
                                        <?php echo e($p->ticket_no ?? $p->booking_ref ?? ($p->booking_ref_no ?? ($p->booking_ref ?? '-'))); ?>

                                    </td>
                                    <td>
                                        
                                        <?php if(isset($p->passenger_firstname) || isset($p->passenger_lastname)): ?>
                                            <?php echo e(trim(($p->passenger_firstname ?? '') . ' ' . ($p->passenger_midinitial ?? '') . ' ' . ($p->passenger_lastname ?? ''))); ?>

                                        <?php elseif(isset($p->passenger_firstname)): ?>
                                            <?php echo e($p->passenger_firstname); ?>

                                        <?php else: ?>
                                            <?php echo e($p->name ?? ($p->full_name ?? '-')); ?>

                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo e($p->passenger_age ?? ($p->age ?? '-')); ?> / <?php echo e($p->passenger_gender ?? ($p->gender ?? '-')); ?></td>
                                    <td><?php echo e($p->passenger_type ?? ($p->category ?? '-')); ?></td>
                                    <td><?php echo e($p->accommodation_name ?? '-'); ?></td>
                                    <td><?php echo e($p->pt_cot_no ?? '-'); ?></td>
                                    <td><?php echo e($voyage->voyage_departure_date ?? '-'); ?></td>
                                    <td><?php echo e($p->pt_ticket_price ?? '-'); ?></td>
                                    <td><?php echo e(\Illuminate\Support\Facades\DB::table('booking')->where('booking_ref_no', $p->booking_ref)->value('booking_status') ?? '-'); ?></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        
        <?php if(!empty($showCargo)): ?>
            <div class="cargo-manifest mt-5">
                <div class="d-flex align-items-center mb-3"><h4 class="manifest-section-title">Cargo Manifest</h4>
                     <i 
                     class="fas fa-print print-icon-fa" 
                      onclick="printTable('cargoTable')"
                    ></i>
                </div>
                 <table id="cargoTable" class="manifest-table table table-striped text-center align-middle">
                    <thead>
                        <tr>
                            <th>B/L No / Ref</th>
                            <th>Qty</th>
                            <th>Classification / Item</th>
                            <th>Category</th>
                            <th>Description</th>
                            <th>Sender</th>
                            <th>TIN</th>
                            <th>Consignees</th>
                            <th>Freight</th>
                            <th>VAT</th>
                            <th>Stamp</th>
                            <th>Total</th>
                            <th>Receipt No.</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($cargos->isEmpty()): ?>
                            <tr><td colspan="13" class="text-center">No data available</td></tr>
                        <?php else: ?>
                            <?php $__currentLoopData = $cargos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td><?php echo e($c->bl_number ?? $c->booking_ref_no ?? $c->booking_ref ?? ($c->booking_ref_no ?? '-')); ?></td>
                                    <td><?php echo e($c->quantity ?? $c->cargo_item_qty ?? '-'); ?></td>
                                    <td><?php echo e($c->cargo_classification_name ?? 'N/A'); ?></td>
                                    <td><?php echo e($c->cargoItem->cargo_category->cargo_category_name ?? 'N/A'); ?></td>
                                    <td><?php echo e($c->cargoItem->cargo_item_description ?? 'N/A'); ?></td>
                                    <td><?php echo e($c->sender->sender_name ?? 'N/A'); ?></td>
                                    <td><?php echo e($c->sender->sender_tin ?? '-'); ?></td>
                                    <td><?php echo e($c->consignee->consignee_name); ?></td>
                                    <td><?php echo e($c->cargoItem->cargo_item_freight ?? '-'); ?></td>
                                    <td><?php echo e(($c->cargoItem->cargo_item_freight ?? 0) * 0.12); ?></td>
                                    <td>20.00</td>
                                    <td><?php echo e($c->payment->total_amount ?? 'N/A'); ?></td>
                                    <td><?php echo e($c->receipt_no ?? ($c->cargo_receipt_id ?? '-')); ?></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

<script>
function printTable(tableId) {
    const printContent = document.getElementById(tableId);
    if (!printContent) return;

    const tbody = printContent.querySelector('tbody');
    const rows = tbody ? Array.from(tbody.querySelectorAll('tr')) : [];
    const hasRealData = rows.some(row => {
        const text = (row.textContent || '').trim().toLowerCase();
        const noDataRow = row.querySelector('td[colspan]') && text.includes('no data available');
        return !noDataRow && text.length > 0;
    });
    if (!hasRealData) {
        alert('No data available to print for this table.');
        return;
    }

    const manifestHeader = document.querySelector('.manifest-header').innerHTML;
    const printWindow = window.open('', '_blank', 'height=700,width=1000');
    printWindow.document.write('<html><head><title>Manifest Print</title>');
    printWindow.document.write('<style>');
    printWindow.document.write('@page { margin: 14mm; }');
    printWindow.document.write('body { font-family: Arial, sans-serif; font-size: 10pt; padding: 12px; padding-bottom: 70px; }');
    printWindow.document.write('table { width: 100%; border-collapse: collapse; margin-top: 16px; }');
    printWindow.document.write('th, td { border: 1px solid #000; padding: 8px; text-align: center; }');
    printWindow.document.write('th { background-color: #f2f2f2; }');
    printWindow.document.write('.manifest-header { text-align: center; margin-bottom: 16px; }');
    printWindow.document.write('.manifest-title { font-size: 14pt; }');
    printWindow.document.write('.system-note { position: fixed; bottom: 12px; left: 0; right: 0; text-align: center; font-style: italic; }');
    printWindow.document.write('</style>');
    printWindow.document.write('</head><body>');
    printWindow.document.write('<div class="manifest-header">' + manifestHeader + '</div>');
    printWindow.document.write(printContent.outerHTML);
    printWindow.document.write('<div class="system-note">This is a system generated manifest</div>');
    printWindow.document.write('</body></html>');
    printWindow.document.close();

    printWindow.onload = function () {
        printWindow.focus();
        printWindow.print();
        printWindow.close();
    };
}
</script>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Sophia\Documents\Capstone\resources\views/authorized/staff/staffmanifest.blade.php ENDPATH**/ ?>