@extends('layouts.app')
@section('page-title', 'MANIFEST')
@section('content')
    @include('components.authHeader')
    @include('components.admin_nav') {{--NAVBAR--}}

    <div class="admin-body">
        <div class="manifest-header" style="display: flex; flex-direction: column; align-items: center;">
            <h2 class="manifest-title">
                Voyage Number: {{ $voyage->voyage_code ?? '-' }}
            </h2>

            <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-top: 1rem; justify-items: center; text-align: center; width: 100%; max-width: 1100px; margin-left: auto; margin-right: auto;">
                <div>
                    <p><strong>Schedule: </strong>{{ optional($voyage->voyage_departure_date) ? \Carbon\Carbon::parse($voyage->voyage_departure_date)->format('F j, Y, D') : '-' }}</p>
                </div>
                <div>
                    <p><strong>Time of Departure: </strong> {{ $voyage->voyage_estimated_TD ? \Carbon\Carbon::parse($voyage->voyage_estimated_TD)->format('h:i A') : '-' }}</p>
                </div>
                <div>
                    <p><strong>Vessel: </strong> {{ optional($voyage->vessel)->vessel_name ?? '-' }}</p>
                </div>
                <div>
                    <p><strong>Actual Time of Departure: </strong> {{ $voyage->voyage_actual_TD ? \Carbon\Carbon::parse($voyage->voyage_actual_TD)->format('h:i A') : '-' }}</p>
                </div>
                <div>
                    <p><strong>Voyage Route: </strong>{{ optional($voyage->routePort)->route_origin ?? '-' }} to {{ optional($voyage->routePort)->route_destination ?? '-' }}</p>
                </div>
                <div>
                    <p><strong>Time of Arrival: </strong>{{ $voyage->voyage_estimated_TA ? \Carbon\Carbon::parse($voyage->voyage_estimated_TA)->format('h:i A') : '-' }}</p>
                </div>
                <div>
                    <p><strong>Status: </strong> {{ $voyage->voyage_status ?? '-' }}</p>
                </div>
                <div>
                    <p><strong>Actual Time of Arrival: </strong>{{ $voyage->voyage_actual_TA ? \Carbon\Carbon::parse($voyage->voyage_actual_TA)->format('h:i A') : '-' }}</p>
                </div>
            </div>
        </div>

        <div class="manifest-filters mt-4">
            <form method="GET" action="{{ url()->current() }}" class="d-flex gap-5 align-items-center" id="manifestFilterForm" onsubmit="return validateManifestFilters();">
                <input type="hidden" name="show_passenger" value="0">
                <div class="form-switch">
                    <input class="form-check-input switch-toggle" type="checkbox" name="show_passenger" id="filterPassenger" value="1" {{ ($showPassenger ?? true) ? 'checked' : '' }} onchange="handleManifestCheckboxChange(event)">
                    <label class="form-check-label" for="filterPassenger">Show Passenger Manifest</label>
                </div>
                <input type="hidden" name="show_cargo" value="0">
                <div class="form-switch">
                    <input class="form-check-input switch-toggle" type="checkbox" name="show_cargo" id="filterCargo" value="1" {{ ($showCargo ?? true) ? 'checked' : '' }} onchange="handleManifestCheckboxChange(event)">
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
                    showManifestError('At least one manifest must be shown.');
                    return false;
                }
                form.submit();
            }
            </script>
        </div>

        {{-- PASSENGERS --}}
        @if(!empty($showPassenger))
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
                        @if($passengers->isEmpty())
                            <tr><td colspan="9" class="text-center">No data available</td></tr>
                        @else
                            @foreach($passengers as $p)
                                <tr>
                                    {{-- Try common fields in order of likelihood --}}
                                    <td>
                                        {{ $p->ticket_no ?? $p->booking_ref ?? ($p->booking_ref_no ?? ($p->booking_ref ?? '-')) }}
                                    </td>
                                    <td>
                                        {{-- If Passenger model --}}
                                        @if(isset($p->passenger_firstname) || isset($p->passenger_lastname))
                                            {{ trim(($p->passenger_firstname ?? '') . ' ' . ($p->passenger_midinitial ?? '') . ' ' . ($p->passenger_lastname ?? '')) }}
                                        @elseif(isset($p->passenger_firstname))
                                            {{ $p->passenger_firstname }}
                                        @else
                                            {{ $p->name ?? ($p->full_name ?? '-') }}
                                        @endif
                                    </td>
                                    <td>{{ $p->passenger_age ?? ($p->age ?? '-') }} / {{ $p->passenger_gender ?? ($p->gender ?? '-') }}</td>
                                    <td>{{ $p->passenger_type ?? ($p->category ?? '-') }}</td>
                                    <td>{{ $p->accommodation_name ?? '-' }}</td>
                                    <td>{{ $p->pt_cot_no ?? '-' }}</td>
                                    <td>{{ $voyage->voyage_departure_date ?? '-' }}</td>
                                    <td class="text-end">{{ $p->pt_ticket_price ?? '-' }}</td>
                                    <td>{{ !empty($p->pt_boarded_at) ? 'Boarded' : (\Illuminate\Support\Facades\DB::table('booking')->where('booking_ref_no', $p->booking_ref)->value('booking_status') ?? '-') }}</td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
                <div class="pagination-container mt-3" id="passenger-pagination-container">
                    {{ $passengers->links('pagination::bootstrap-5') }}
                </div>
            </div>
        @endif

        {{-- CARGOS --}}
        @if(!empty($showCargo))
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
                        @if($cargos->isEmpty())
                            <tr><td colspan="13" class="text-center">No data available</td></tr>
                        @else
                            @foreach($cargos as $c)
                                <tr>
                                     <td>{{ $c->bl_number ?? $c->booking_ref_no ?? $c->booking_ref ?? ($c->booking_ref_no ?? '-') }}</td>
                                    <td>{{ $c->quantity ?? $c->cargo_item_qty ?? '-' }}</td>
                                    <td>{{ $c->cargo_classification_name ?? 'N/A' }}</td>
                                    <td>{{ $c->cargoItem->cargo_category->cargo_category_name ?? 'N/A'  }}</td>
                                    <td>{{ $c->cargoItem->cargo_item_description ?? 'N/A'  }}</td>
                                    <td>{{ $c->sender->sender_name ?? 'N/A' }}</td>
                                    <td>{{ $c->sender->sender_tin ?? '-' }}</td>
                                    <td>{{ $c->consignee->consignee_name }}</td>
                                    <td class="text-end">{{ $c->cargoItem->cargo_item_freight ?? '-' }}</td>
                                    <td class="text-end">{{ ($c->cargoItem->cargo_item_freight ?? 0) * 0.12 }}</td>
                                    <td>20.00</td>
                                    <td class="text-end">{{ $c->payment->total_amount ?? 'N/A' }}</td>
                                    <td>{{ $c->receipt_no ?? ($c->cargo_receipt_id ?? '-') }}</td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
                <div class="pagination-container mt-3" id="cargo-pagination-container">
                    {{ $cargos->links('pagination::bootstrap-5') }}
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
        @endif
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
function showManifestError(message) {
    if (typeof showToast === 'function') {
        showToast(message, 'danger');
        return;
    }

    alert(message);
}

function printCargoTable() {
    // Get voyageId from URL (expects /adminmanifest/{voyage})
    const match = window.location.pathname.match(/adminmanifest\/(\d+)/);
    const voyageId = match ? match[1] : null;
    if (!voyageId) {
        showManifestError('Cannot determine voyage ID for printing.');
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
                showManifestError('No data available to print for this table.');
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
        .catch(() => showManifestError('Failed to fetch all cargo data for printing.'));
}

function printTable(tableId) {
    // If printing the passenger table, fetch all rows from the server (no pagination)
    if (tableId === 'passengerTable') {
        // Get voyageId from URL (expects /adminmanifest/{voyage})
        const match = window.location.pathname.match(/adminmanifest\/(\d+)/);
        const voyageId = match ? match[1] : null;
        if (!voyageId) {
            showManifestError('Cannot determine voyage ID for printing.');
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
                    showManifestError('No data available to print for this table.');
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
                printWindow.document.write('.system-note { position: fixed; bottom: 12px; left: 0; right: 0; text-align: center; font-style: italic; }');
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
            .catch(() => showManifestError('Failed to fetch all passenger data for printing.'));
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
        showManifestError('No data available to print for this table.');
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

@endsection