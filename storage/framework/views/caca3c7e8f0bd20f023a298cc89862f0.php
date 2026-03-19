<?php $__env->startSection('page-title', 'MANIFEST'); ?>
<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('components.authHeader', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo $__env->make('components.staff_nav', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?> 

    <div class="staff-body">
        <div class="manifest-header" style="display: flex; flex-direction: column; align-items: center;">
            <h2 class="manifest-title">
                Voyage Number: <?php echo e($voyage->voyage_code ?? '-'); ?>

            </h2>

            <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-top: 1rem; justify-items: center; text-align: center; width: 100%; max-width: 1100px; margin-left: auto; margin-right: auto;">
                <div>
                    <p><strong>Schedule: </strong><?php echo e(optional($voyage->voyage_departure_date) ? \Carbon\Carbon::parse($voyage->voyage_departure_date)->format('F j, Y, D') : '-'); ?></p>
                </div>
                <div>
                    <p><strong>Time of Departure: </strong> <?php echo e($voyage->voyage_estimated_TD ? \Carbon\Carbon::parse($voyage->voyage_estimated_TD)->format('h:i A') : '-'); ?></p>
                </div>
                <div>
                    <p><strong>Vessel: </strong> <?php echo e(optional($voyage->vessel)->vessel_name ?? '-'); ?></p>
                </div>
                <div>
                    <p><strong>Actual Time of Departure: </strong> <?php echo e($voyage->voyage_actual_TD ? \Carbon\Carbon::parse($voyage->voyage_actual_TD)->format('h:i A') : '-'); ?></p>
                </div>
                <div>
                    <p><strong>Voyage Route: </strong><?php echo e(optional($voyage->routePort)->route_origin ?? '-'); ?> to <?php echo e(optional($voyage->routePort)->route_destination ?? '-'); ?></p>
                </div>
                <div>
                    <p><strong>Time of Arrival: </strong><?php echo e($voyage->voyage_estimated_TA ? \Carbon\Carbon::parse($voyage->voyage_estimated_TA)->format('h:i A') : '-'); ?></p>
                </div>
                <div>
                    <p><strong>Status: </strong> <?php echo e($voyage->voyage_status ?? '-'); ?></p>
                </div>
                <div>
                    <p><strong>Actual Time of Arrival: </strong><?php echo e($voyage->voyage_actual_TA ? \Carbon\Carbon::parse($voyage->voyage_actual_TA)->format('h:i A') : '-'); ?></p>
                </div>
            </div>
        </div>

        <div class="manifest-filters text-start mt-4 ">
            <form method="GET" action="<?php echo e(url()->current()); ?>" class="d-flex gap-5 align-items-center" id="manifestFilterForm">
                <input type="hidden" name="show_passenger" value="0">
                <div class="form-switch">
                    <input class="form-check-input switch-toggle" type="checkbox" name="show_passenger" id="filterPassenger" value="1" <?php echo e(($showPassenger ?? true) ? 'checked' : ''); ?> onchange="handleManifestCheckboxChange(event)">
                    <label class="form-check-label" for="filterPassenger">Show Passenger Manifest</label>
                </div>
                <input type="hidden" name="show_cargo" value="0">
                <div class="form-switch">
                    <input class="form-check-input switch-toggle" type="checkbox" name="show_cargo" id="filterCargo" value="1" <?php echo e(($showCargo ?? true) ? 'checked' : ''); ?> onchange="handleManifestCheckboxChange(event)">
                    <label class="form-check-label" for="filterCargo">Show Cargo Manifest</label>
                </div>
            </form>
            <style>
                .form-switch {
                    display: flex;
                    align-items: center;
                    gap: 0.5em;
                }
                .switch-toggle {
                    width: 2.5em;
                    height: 1.3em;
                    background: #e6e6e6;
                    border-radius: 1em;
                    position: relative;
                    appearance: none;
                    outline: none;
                    cursor: pointer;
                    transition: background 0.3s;
                }
                .switch-toggle:checked {
                    background: #4caf50;
                }
                .switch-toggle:before {
                    content: '';
                    position: absolute;
                    left: 0.2em;
                    top: 0.18em;
                    width: 1em;
                    height: 1em;
                    background: #fff;
                    border-radius: 50%;
                    transition: transform 0.3s;
                }
                .switch-toggle:checked:before {
                    transform: translateX(1.2em);
                }
            </style>
            <script>
            function handleManifestCheckboxChange(e) {
                const form = document.getElementById('manifestFilterForm');
                const passenger = form.querySelector('#filterPassenger');
                const cargo = form.querySelector('#filterCargo');
                // Prevent both from being unchecked
                if (!passenger.checked && !cargo.checked) {
                    // Revert the change
                    e.target.checked = true;
                    alert('At least one manifest must be shown.');
                    return false;
                }
                form.submit();
            }
            </script>
        </div>

        
        <?php if(!empty($showPassenger)): ?>
            <div class="passenger-manifest mt-1">
                <div class="d-flex align-items-center mb-3"><h4 class="manifest-section-title">Passenger Manifest</h4>
                <i 
                     class="fas fa-print print-icon-fa" 
                      onclick="printTable('passengerTable')"
                    ></i>
                </div>
                <table id="passengerTable" class="manifest-table table text-start align-middle">
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
                                    <td class="text-end"><?php echo e($p->pt_ticket_price ?? '-'); ?></td>
                                    <td><?php echo e(\Illuminate\Support\Facades\DB::table('booking')->where('booking_ref_no', $p->booking_ref)->value('booking_status') ?? '-'); ?></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php endif; ?>
                    </tbody>
                </table>
                <div class="pagination-container mt-3" id="passenger-pagination-container">
                    <?php echo e($passengers->links('pagination::bootstrap-5')); ?>

                </div>
            </div>
        <?php endif; ?>

        
        <?php if(!empty($showCargo)): ?>
            <div class="cargo-manifest mt-3">
                                <div class="d-flex align-items-center mb-3"><h4 class="manifest-section-title">Cargo Manifest</h4>
                                         <i 
                                         class="fas fa-print print-icon-fa" 
                                            onclick="printCargoTable()"
                                        ></i>
                </div>
                 <table id="cargoTable" class="manifest-table table table-striped text-start align-middle">
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
                                    <td class="text-end"><?php echo e($c->cargoItem->cargo_item_freight ?? '-'); ?></td>
                                    <td class="text-end"><?php echo e(($c->cargoItem->cargo_item_freight ?? 0) * 0.12); ?></td>
                                    <td>20.00</td>
                                    <td class="text-end"><?php echo e($c->payment->total_amount ?? 'N/A'); ?></td>
                                    <td><?php echo e($c->receipt_no ?? ($c->cargo_receipt_id ?? '-')); ?></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php endif; ?>
                    </tbody>
                </table>
                <div class="pagination-container mt-3" id="cargo-pagination-container">
                    <?php echo e($cargos->links('pagination::bootstrap-5')); ?>

                </div>
            </script>
            <script>
            // AJAX pagination for passenger and cargo manifests with re-attachment after update
            function ajaxifyPagination(containerId, tableId) {
                const container = document.getElementById(containerId);
                if (!container) return;
                container.addEventListener('click', function paginationHandler(e) {
                    const target = e.target.closest('a.page-link');
                    if (target) {
                        e.preventDefault();
                        fetch(target.href, {headers: {'X-Requested-With': 'XMLHttpRequest'}})
                            .then(response => response.text())
                            .then(html => {
                                const parser = new DOMParser();
                                const doc = parser.parseFromString(html, 'text/html');
                                const newTable = doc.getElementById(tableId);
                                const newPagination = doc.getElementById(containerId);
                                if (newTable && newPagination) {
                                    document.getElementById(tableId).outerHTML = newTable.outerHTML;
                                    // Replace the pagination container and re-attach the handler
                                    const oldContainer = document.getElementById(containerId);
                                    oldContainer.outerHTML = newPagination.outerHTML;
                                    setTimeout(() => ajaxifyPagination(containerId, tableId), 0);
                                } else {
                                    window.location.href = target.href;
                                }
                            })
                            .catch(() => window.location.href = target.href);
                    }
                });
            }
            document.addEventListener('DOMContentLoaded', function () {
                ajaxifyPagination('passenger-pagination-container', 'passengerTable');
                ajaxifyPagination('cargo-pagination-container', 'cargoTable');
            });
            </script>
            </div>
        <?php endif; ?>
    </div>

<style>
#passengerTable thead th,
#cargoTable thead th,
#passengerTable tbody td,
#cargoTable tbody td {
    text-align: left !important;
}
</style>

<script>
function printCargoTable() {
    // Get voyageId from URL (expects /staffmanifest/{voyage})
    const match = window.location.pathname.match(/staffmanifest\/(\d+)/);
    const voyageId = match ? match[1] : null;
    if (!voyageId) {
        alert('Cannot determine voyage ID for printing.');
        return;
    }
    // Compose a flex row with logo and manifest title side by side
    const manifestHeaderDiv = document.querySelector('.manifest-header');
    const manifestTitle = manifestHeaderDiv ? manifestHeaderDiv.querySelector('.manifest-title')?.innerHTML : '';
    const logoHtml = '<img src="/images/lslc_logo2.png" alt="Logo" style="height:60px;margin-right:18px;">';
    const headerRowHtml = `<div style="display:flex;align-items:center;justify-content:center;margin-bottom:10px;gap:18px;">${logoHtml}<span class="manifest-title" style="font-size:14pt;font-weight:bold;"><strong>${manifestTitle}</strong></span></div>`;
    // Use the rest of the manifest header (details grid)
    const detailsGrid = manifestHeaderDiv ? manifestHeaderDiv.querySelector('div[style*="grid-template-columns"]')?.outerHTML : '';
    // Get table headers from the DOM
    const table = document.getElementById('cargoTable');
    const thead = table ? table.querySelector('thead').outerHTML : '';
    fetch(`/manifest/${voyageId}/all-cargos-table`)
        .then(response => response.text())
        .then(allRowsHtml => {
            if (!allRowsHtml.trim()) {
                alert('No data available to print for this table.');
                return;
            }
            const printWindow = window.open('', '_blank', 'height=700,width=1000');
            printWindow.document.write('<html><head><title>Manifest Print</title>');
            printWindow.document.write('<style>');
            printWindow.document.write('@page { margin: 14mm; }');
            printWindow.document.write('body { font-family: Arial, sans-serif; font-size: 10pt; padding: 12px; padding-bottom: 70px; }');
            printWindow.document.write('table { width: 100%; border-collapse: collapse; margin-top: 16px; }');
            printWindow.document.write('th, td { border: 1px solid #000; padding: 8px; }');
            printWindow.document.write('th { text-align: left; }');
            printWindow.document.write('td { text-align: left; }');
            printWindow.document.write('th { background-color: #f2f2f2; }');
            printWindow.document.write('.manifest-header { text-align: center; margin-bottom: 16px; }');
            printWindow.document.write('.manifest-title { font-size: 14pt; }');
            printWindow.document.write('.system-note { margin-top: 120px; text-align: center; font-style: italic; }');
            printWindow.document.write('</style>');
            printWindow.document.write('</head><body>');
            printWindow.document.write(headerRowHtml);
            if (detailsGrid) printWindow.document.write('<div class="manifest-header-details">' + detailsGrid + '</div>');
            printWindow.document.write('<table class="manifest-table table text-start align-middle">');
            printWindow.document.write(thead);
            printWindow.document.write('<tbody>');
            printWindow.document.write(allRowsHtml);
            printWindow.document.write('</tbody></table>');
            printWindow.document.write('<div class="system-note">This is a system generated manifest</div>');
            printWindow.document.write('</body></html>');
            printWindow.document.close();
            printWindow.onload = function () {
                printWindow.focus();
                printWindow.print();
                printWindow.close();
            };
        })
        .catch(() => alert('Failed to fetch all cargo data for printing.'));
}

function printTable(tableId) {
    // If printing the passenger table, fetch all rows from the server (no pagination)
    if (tableId === 'passengerTable') {
        // Get voyageId from URL (expects /staffmanifest/{voyage})
        const match = window.location.pathname.match(/staffmanifest\/(\d+)/);
        const voyageId = match ? match[1] : null;
        if (!voyageId) {
            alert('Cannot determine voyage ID for printing.');
            return;
        }
        // Compose a flex row with logo and manifest title side by side
        const manifestHeaderDiv = document.querySelector('.manifest-header');
        const manifestTitle = manifestHeaderDiv ? manifestHeaderDiv.querySelector('.manifest-title')?.innerHTML : '';
        const logoHtml = '<img src="/images/lslc_logo2.png" alt="Logo" style="height:60px;margin-right:18px;">';
        const headerRowHtml = `<div style="display:flex;align-items:center;justify-content:center;margin-bottom:10px;gap:18px;">${logoHtml}<span class="manifest-title" style="font-size:14pt;font-weight:bold;"><strong>${manifestTitle}</strong></span></div>`;
        // Use the rest of the manifest header (details grid)
        const detailsGrid = manifestHeaderDiv ? manifestHeaderDiv.querySelector('div[style*="grid-template-columns"]')?.outerHTML : '';
        // Get table headers from the DOM, but remove 'Departure' and 'Status' columns for print
        const table = document.getElementById('passengerTable');
        let thead = '';
        if (table) {
            const origThead = table.querySelector('thead');
            if (origThead) {
                // Clone and remove 7th (Departure) and 9th (Status) th
                const clonedThead = origThead.cloneNode(true);
                const ths = clonedThead.querySelectorAll('th');
                if (ths[8]) ths[8].remove(); // Status (9th, index 8)
                if (ths[6]) ths[6].remove(); // Departure (7th, index 6)
                thead = '<thead>' + clonedThead.innerHTML + '</thead>';
            }
        }
        fetch(`/manifest/${voyageId}/all-passengers-table`)
            .then(response => response.text())
            .then(allRowsHtml => {
                // Check if there is any data
                if (!allRowsHtml.trim()) {
                    alert('No data available to print for this table.');
                    return;
                }
                const printWindow = window.open('', '_blank', 'height=700,width=1000');
                printWindow.document.write('<html><head><title>Manifest Print</title>');
                printWindow.document.write('<style>');
                printWindow.document.write('@page { margin: 14mm; }');
                printWindow.document.write('body { font-family: Arial, sans-serif; font-size: 10pt; padding: 12px; padding-bottom: 70px; }');
                printWindow.document.write('table { width: 100%; border-collapse: collapse; margin-top: 16px; }');
                printWindow.document.write('th, td { border: 1px solid #000; padding: 8px; }');
                printWindow.document.write('th { text-align: left; }');
                printWindow.document.write('td { text-align: left; }');
                printWindow.document.write('th { background-color: #f2f2f2; }');
                printWindow.document.write('.manifest-header { text-align: center; margin-bottom: 16px; }');
                printWindow.document.write('.manifest-title { font-size: 14pt; }');
                printWindow.document.write('.system-note { position: fixed; bottom: 0px; left: 0; right: 0; text-align: center; font-style: italic; }');
                printWindow.document.write('</style>');
                printWindow.document.write('</head><body>');
                printWindow.document.write(headerRowHtml);
                if (detailsGrid) printWindow.document.write('<div class="manifest-header-details">' + detailsGrid + '</div>');
                printWindow.document.write('<table class="manifest-table table text-start align-middle">');
                printWindow.document.write(thead);
                // Remove 7th and 9th td from each row in allRowsHtml
                let rowsHtml = allRowsHtml;
                // Remove 9th td (Status)
                rowsHtml = rowsHtml.replace(/((<td[^>]*>.*?<\/td>){8})<td[^>]*>.*?<\/td>/g, '$1');
                // Remove 7th td (Departure)
                rowsHtml = rowsHtml.replace(/((<td[^>]*>.*?<\/td>){6})<td[^>]*>.*?<\/td>/g, '$1');
                printWindow.document.write('<tbody>');
                printWindow.document.write(rowsHtml);
                printWindow.document.write('</tbody></table>');
                printWindow.document.write('<div class="system-note">This is a system generated manifest</div>');
                printWindow.document.write('</body></html>');
                printWindow.document.close();
                printWindow.onload = function () {
                    printWindow.focus();
                    printWindow.print();
                    printWindow.close();
                };
            })
            .catch(() => alert('Failed to fetch all passenger data for printing.'));
        return;
    }
    // Otherwise, print the current table as before
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

    const manifestHeaderDiv = document.querySelector('.manifest-header');
    const manifestTitle = manifestHeaderDiv ? manifestHeaderDiv.querySelector('.manifest-title')?.innerHTML : '';
    const logoHtml = '<img src="/images/lslc_logo2.png" alt="Logo" style="height:60px;margin-right:18px;">';
    const headerRowHtml = `<div style="display:flex;align-items:center;justify-content:center;margin-bottom:10px;gap:18px;">${logoHtml}<span class="manifest-title" style="font-size:14pt;font-weight:bold;"><strong>${manifestTitle}</strong></span></div>`;
    const detailsGrid = manifestHeaderDiv ? manifestHeaderDiv.querySelector('div[style*="grid-template-columns"]')?.outerHTML : '';
    const printWindow = window.open('', '_blank', 'height=700,width=1000');
    printWindow.document.write('<html><head><title>Manifest Print</title>');
    printWindow.document.write('<style>');
    printWindow.document.write('@page { margin: 14mm; }');
    printWindow.document.write('body { font-family: Arial, sans-serif; font-size: 10pt; padding: 12px; padding-bottom: 70px; }');
    printWindow.document.write('table { width: 100%; border-collapse: collapse; margin-top: 16px; }');
    printWindow.document.write('th, td { border: 1px solid #000; padding: 8px; }');
    printWindow.document.write('th { text-align: left; }');
    printWindow.document.write('td { text-align: left; }');
    printWindow.document.write('th { background-color: #f2f2f2; }');
    printWindow.document.write('.manifest-header { text-align: center; margin-bottom: 16px; }');
    printWindow.document.write('.manifest-title { font-size: 14pt; }');
    printWindow.document.write('.system-note { margin-top: 120px; text-align: center; font-style: italic; }');
    printWindow.document.write('</style>');
    printWindow.document.write('</head><body>');
    printWindow.document.write(headerRowHtml);
    if (detailsGrid) printWindow.document.write('<div class="manifest-header-details">' + detailsGrid + '</div>');
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
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\clint\Desktop\Capstone\resources\views/authorized/staff/staffmanifest.blade.php ENDPATH**/ ?>