@extends('layouts.app')
@section('page-title', 'MANIFEST')
@section('content')
    @include('components.authHeader')
    @include('components.staff_nav') {{--NAVBAR--}}

    <div class="admin-body">
        <div class="manifest-header text-center">
            <h2 class="manifest-title">
                Voyage Number: {{ $voyage->voyage_code ?? '-' }}
            </h2>

            <div class="d-flex justify-content-center flex-wrap gap-5 mt-3">
                <div class="text-start">
                    <p><strong>Schedule: </strong>{{ optional($voyage->voyage_departure_date) ? \Carbon\Carbon::parse($voyage->voyage_departure_date)->format('F j, Y, D') : '-' }}</p>
                    <p><strong>Vessel: </strong> {{ optional($voyage->vessel)->vessel_name ?? '-' }}</p>
                </div>
                <div class="text-start">
                    <p><strong>Voyage Route: </strong>{{ optional($voyage->routePort)->route_origin ?? '-' }} to {{ optional($voyage->routePort)->route_destination ?? '-' }}</p>
                    <p><strong>Status: </strong> {{ $voyage->voyage_status ?? '-' }}</p>
                </div>
            </div>
        </div>

        <div class="manifest-filters text-center mt-4 ">
            <form method="GET" action="{{ url()->current() }}" class="d-flex gap-5 align-items-center" id="manifestFilterForm">
                <input type="hidden" name="show_passenger" value="0">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="show_passenger" id="filterPassenger" value="1" {{ ($showPassenger ?? true) ? 'checked' : '' }} onchange="this.form.submit()">
                    <label class="form-check-label" for="filterPassenger">Show Passenger Manifest</label>
                </div>
                <input type="hidden" name="show_cargo" value="0">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="show_cargo" id="filterCargo" value="1" {{ ($showCargo ?? true) ? 'checked' : '' }} onchange="this.form.submit()">
                    <label class="form-check-label" for="filterCargo">Show Cargo Manifest</label>
                </div>
            </form>
        </div>

        {{-- PASSENGERS --}}
        @if(!empty($showPassenger))
            <div class="passenger-manifest mt-5">
                <div class="d-flex align-items-center mb-3"><h4 class="manifest-section-title">Passenger Manifest</h4>
                <i 
                     class="fas fa-print print-icon-fa" 
                      onclick="printTable('passengerTable')"
                    ></i>
                </div>
                <table id="passengerTable" class="manifest-table table table-striped">
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
                            <tr><td colspan="8" class="text-center">No data available</td></tr>
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
                                    <td>{{ $p->pt_ticket_price ?? '-' }}</td>
                                    <td>{{\Illuminate\Support\Facades\DB::table('booking')->where('booking_ref_no', $p->booking_ref)->value('booking_status') ?? '-'}}</td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
        @endif

        {{-- CARGOS --}}
        @if(!empty($showCargo))
            <div class="cargo-manifest mt-5">
                <div class="d-flex align-items-center mb-3"><h4 class="manifest-section-title">Cargo Manifest</h4>
                     <i 
                     class="fas fa-print print-icon-fa" 
                      onclick="printTable('cargoTable')"
                    ></i>
                </div>
                 <table id="cargoTable" class="manifest-table table table-striped">
                    <thead>
                        <tr>
                            <th>B/L No / Ref</th>
                            <th>Qty</th>
                            <th>Classification / Item</th>
                            <th>Description</th>
                            <th>Sender</th>
                            <th>TIN</th>
                            <th>Consignees</th>
                            <th>Freight</th>
                            <th>VAT</th>
                            <th>Stamp</th>
                            <th>Total</th>
                            <th>Receipt No.</th>
                            <th>Net Arrastre</th>
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
                                    <td>{{ $c->cargoItem->cargo_item_classification ?? 'N/A' }}</td>
                                    <td>{{ $c->cargoItem->cargo_item_description ?? 'N/A'  }}</td>
                                    <td>{{ $c->sender->sender_name ?? 'N/A' }}</td>
                                    <td>{{ $c->tin_number ?? '-' }}</td>
                                    <td>{{ $c->consignee->consignee_name }}</td>
                                    <td>{{ $c->cargoItem->cargo_item_freight ?? '-' }}</td>
                                    <td>{{ $c->vat ?? '-' }}</td>
                                    <td>{{ $c->stamp ?? '-' }}</td>
                                    <td>{{ $c->payment->total_amount ?? 'N/A' }}</td>
                                    <td>{{ $c->receipt_no ?? ($c->cargo_receipt_id ?? '-') }}</td>
                                    <td>{{ $c->cargoItem->cargo_item_arrastre ?? '-' }}</td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
        @endif
    </div>

<script>
    function printTable(tableId) {
        const printContent = document.getElementById(tableId);

        if (!printContent) {
            console.error("Table with ID '" + tableId + "' not found.");
            return;
        }
        
        // Include the Voyage Header for context on the printout
        const manifestHeader = document.querySelector('.manifest-header').innerHTML;
        
        // Start new window content
        const printWindow = window.open('', '', 'height=600,width=800');
        printWindow.document.write('<html><head><title>Manifest Print</title>');
        
        // Add basic print-friendly styling
        printWindow.document.write('<style>');
        printWindow.document.write('body { font-family: Arial, sans-serif; font-size: 10pt; padding: 20px; }');
        printWindow.document.write('table { width: 100%; border-collapse: collapse; margin-top: 20px; }');
        printWindow.document.write('th, td { border: 1px solid #000; padding: 8px; text-align: left; }');
        printWindow.document.write('th { background-color: #f2f2f2; }');
        printWindow.document.write('.manifest-header { text-align: center; margin-bottom: 20px; }');
        printWindow.document.write('.manifest-title { font-size: 14pt; }');
        printWindow.document.write('</style>');
        
        printWindow.document.write('</head><body>');
        
        // Write the header and the table content
        printWindow.document.write('<div class="manifest-header">');
        printWindow.document.write(manifestHeader);
        printWindow.document.write('</div>');
        printWindow.document.write(printContent.outerHTML); // outerHTML includes the table itself

        printWindow.document.write('</body></html>');
        printWindow.document.close();
        
        // Wait a moment for the content to render before calling print
        printWindow.onload = function() {
            printWindow.print();
            printWindow.close();
        }
    }
</script>

@endsection