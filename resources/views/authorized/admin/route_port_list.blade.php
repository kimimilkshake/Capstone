@extends('layouts.app')
@section('page-title', 'ROUTES AND PORTS')
@section('content')
    @include('components.authHeader')
    @include('components.admin_nav')

    <div class="admin-body">

        <!--SEARCH BAR-->
        <div class="search-add-row" style="display: flex; gap: 10px; margin-bottom: 20px;">
            <form class="search-bar" action="{{ route('admin.route_port_list') }}" method="GET" style="flex: 1;">
                <input type="text" name="search" placeholder="Search by origin, destination, or ports"
                    value="{{ request('search') }}">
                <button type="submit">
                    <i class="fa-solid fa-magnifying-glass me-2"></i>Search
                </button>
            </form>

            <div class="add-vessel">
                <button type="button" id="addRouteCategoryBtn" class="add-link-btn">
                    <i class="fa-solid fa-plus me-2"></i>Add Route Category
                </button>
            </div>

            <div class="add-vessel">
                <button type="button" id="editRouteCategoryBtn" class="add-link-btn">
                    <i class="fa-solid fa-pen me-2"></i>Edit Route Category
                </button>
            </div>

            <div class="add-vessel">
                <button type="button" id="addRoutePortBtn" class="add-link-btn">
                    <i class="fa-solid fa-plus me-2"></i>Add Route and Port
                </button>
            </div>
        </div>

        <table class="rp-table">
            <thead>
                <th>Route Code</th>
                <th>Route Origin</th>
                <th>Route Destination</th>
                <th>Port Origin</th>
                <th>Port Destination</th>
                <th>Action</th>
            </thead>
            <tbody>
                @forelse($route_port as $rp)
                    <tr>
                        <td>{{ $rp->route_code }}</td>
                        <td>{{ $rp->route_origin }}</td>
                        <td>{{ $rp->route_destination }}</td>
                        <td>
                            {{ $rp->portOrigin->terminal_name ?? '' }} - {{ $rp->portOrigin->port_name ?? '' }}
                            ({{ $rp->portOrigin->city ?? '' }})
                        </td>
                        <td>
                            {{ $rp->portDestination->terminal_name ?? '' }} - {{ $rp->portDestination->port_name ?? '' }}
                            ({{ $rp->portDestination->city ?? '' }})
                        </td>
                        <td>
                            <button type="button" class="editRouteBtn link-btn" title="Edit Route and Port"
                                data-id="{{ $rp->route_port_id }}" data-route_category_id="{{ $rp->route_category_id }}"
                                data-route_origin="{{ $rp->route_origin }}"
                                data-route_destination="{{ $rp->route_destination }}"
                                data-port_origin_id="{{ $rp->port_origin_id }}"
                                data-port_destination_id="{{ $rp->port_destination_id }}">
                                <i class="fa fa-pencil"></i>
                            </button>
                        </td>

                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">No routes and ports found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="mt-3">
            {{-- FIXED pagination variable --}}
            {{ $route_port->appends(['search' => request('search')])->links('pagination::bootstrap-5') }}
        </div>
    </div>

    <!-- Add Route Category Modal -->
    <div id="addRouteCategoryModal" class="modal-overlay" style="display:none;">
        <div class="modal-content">
            <span class="close-btn" id="closeAddRouteCategoryModal">&times;</span>
            <h3>Add Route Category</h3>

            <form id="addRouteCategoryForm">
                @csrf
                <div class="rpmodal-row two-col">
                    <div class="rpmodal-col">
                        <label>Route Category Name <span class="text-danger">*</span></label>
                        <input type="text" name="route_category_name" required style="text-transform: uppercase;">
                    </div>
                    <div class="rpmodal-col">
                        <label>Increase Route Rate (%) <span class="text-muted"
                                style="font-size:0.85em;">(optional)</span></label>
                        <input type="number" name="route_rate" min="0" max="100" step="0.01"
                            placeholder="e.g. 10">
                    </div>
                </div>

                <div style="margin-top:12px;">
                    <div style="display:flex;gap:8px;margin-bottom:2px;">
                        <span style="font-weight:600;flex:1;">Passenger Type</span>
                        <span style="font-weight:600;width:140px;">Discount Rate (%)</span>
                        <span style="width:24px;"></span>
                    </div>
                    <div id="addRCDiscountsContainer"></div>
                    <button type="button" id="addRCDiscountRowBtn"
                        style="margin-top:6px;background:none;border:1px dashed #485B8C;color:#485B8C;padding:4px 12px;border-radius:4px;cursor:pointer;">+
                        Add Discount</button>
                </div>

                <button type="submit">Add Route Category</button>
            </form>
        </div>
    </div>



    <!-- Edit Route Category Modal -->
    <div id="editRouteCategoryModal" class="modal-overlay" style="display:none;">
        <div class="modal-content">
            <span class="close-btn" id="closeEditRouteCategoryModal">&times;</span>
            <h3>Edit Route Category</h3>

            <form id="editRouteCategoryForm">
                @csrf
                <div class="rpmodal-row two-col">
                    <div class="rpmodal-col">
                        <label>Route Category <span class="text-danger">*</span></label>
                        <select name="route_category_id" id="editRCSelect" required>
                            <option value="">Select Route Category</option>
                            @foreach ($route_categories as $rc)
                                <option value="{{ $rc->route_category_id }}" data-name="{{ $rc->route_category_name }}"
                                    data-rate="{{ $rc->route_rate ?? '' }}"
                                    data-discounts="{{ json_encode($rc->passengerDiscounts->map(fn($d) => ['passenger_type' => $d->passenger_type, 'discount_rate' => $d->discount_rate])) }}">
                                    {{ $rc->route_category_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="rpmodal-col">
                        <label>Increase Route Rate (%) <span class="text-muted"
                                style="font-size:0.85em;">(optional)</span></label>
                        <input type="number" id="editRCRate" name="route_rate" min="0" max="100" step="0.01"
                            placeholder="e.g. 10">
                    </div>
                </div>

                <div style="margin-top:12px;">
                    <div style="display:flex;gap:8px;margin-bottom:2px;">
                        <span style="font-weight:600;flex:1;">Passenger Type</span>
                        <span style="font-weight:600;width:140px;">Discount Rate (%)</span>
                        <span style="width:24px;"></span>
                    </div>
                    <div id="editRCDiscountsContainer"></div>
                    <button type="button" id="editRCDiscountRowBtn"
                        style="margin-top:6px;background:none;border:1px dashed #485B8C;color:#485B8C;padding:4px 12px;border-radius:4px;cursor:pointer;">+
                        Add Discount</button>
                </div>

                <button type="submit" id="saveEditRCBtn" disabled style="background-color:#ccc;cursor:not-allowed;">Save
                    Changes</button>
            </form>
        </div>
    </div>


    <!-- Add Route & Port Modal -->
    <div id="addRoutePortModal" class="modal-overlay" style="display:none;">
        <div class="modal-content">
            <span class="close-btn" id="closeAddModal">&times;</span>
            <h3>Add Route and Port</h3>

            <form id="addRoutePortForm" method="POST" action="{{ route('admin.route_port_store') }}">
                @csrf

                <!-- 2 columns for route -->
                <div class="rpmodal-row three-col">
                    <div class="rpmodal-col">
                        <label>Route Category <span class="text-danger">*</span></label>
                        <select name="route_category_id" id="route_category_id">
                            <option value="">Select Route Category</option>
                            @foreach ($route_categories as $category)
                                <option value="{{ $category->route_category_id }}">{{ $category->route_category_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="rpmodal-col">
                        <label>Route Origin <span class="text-danger">*</span></label>
                        <input type="text" name="route_origin" required>
                    </div>
                    <div class="rpmodal-col">
                        <label>Route Destination <span class="text-danger">*</span></label>
                        <input type="text" name="route_destination" required>
                    </div>
                </div>

                <!-- PORT ORIGIN -->
                <div class="rpmodal-row two-col">
                    <div class="rpmodal-col">
                        <label>Port Origin <span class="text-danger">*</span></label>
                        <select name="port_origin_id" id="port_origin_select" required>
                            <option value="">Select Port</option>
                            @foreach ($ports as $port)
                                <option value="{{ $port->port_id }}" data-city="{{ $port->city }}"
                                    data-terminal="{{ $port->terminal_name }}">
                                    {{ $port->terminal_name }} - {{ $port->port_name }} ({{ $port->city }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- PORT DESTINATION -->
                    <div class="rpmodal-col">
                        <label>Port Destination <span class="text-danger">*</span></label>
                        <select name="port_destination_id" id="port_destination_select" required>
                            <option value="">Select Port</option>
                            @foreach ($ports as $port)
                                <option value="{{ $port->port_id }}" data-city="{{ $port->city }}"
                                    data-terminal="{{ $port->terminal_name }}" data-province="{{ $port->province }}">
                                    {{ $port->terminal_name }} - {{ $port->port_name }} ({{ $port->city }},
                                    {{ $port->province }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <button type="submit">Add Route & Port</button>
            </form>
        </div>
    </div>


    <!-- Edit Route & Port Modal -->
    <div id="editRoutePortModal" class="modal-overlay" style="display:none;">
        <div class="modal-content">
            <span class="close-btn" id="closeEditModal">&times;</span>
            <h3>Edit Route and Port</h3>

            <form id="editRoutePortForm">
                @csrf
                @method('PUT')

                <input type="hidden" name="route_port_id" id="editRoutePortId">


                <div class="rpmodal-row three-col">
                    <div class="rpmodal-col">
                        <label>Route Category <span class="text-danger">*</span></label>
                        <select name="route_category_id" id="editRouteCategoryId">
                            <option value="">Select Route Category</option>
                            @foreach ($route_categories as $category)
                                <option value="{{ $category->route_category_id }}">{{ $category->route_category_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="rpmodal-col">
                        <label>Route Origin <span class="text-danger">*</span></label>
                        <input type="text" name="route_origin" id="editRouteOrigin" required>
                    </div>
                    <div class="rpmodal-col">
                        <label>Route Destination <span class="text-danger">*</span></label>
                        <input type="text" name="route_destination" id="editRouteDestination" required>
                    </div>
                </div>

                <!-- PORT ORIGIN -->
                <div class="rpmodal-row two-col">
                    <div class="rpmodal-col">
                        <label>Port Origin <span class="text-danger">*</span></label>
                        <select name="port_origin_id" id="editPortOriginSelect" required>
                            <option value="">Select Port</option>
                            @foreach ($ports as $port)
                                <option value="{{ $port->port_id }}">{{ $port->terminal_name }} - {{ $port->port_name }}
                                    ({{ $port->city }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- PORT DESTINATION -->
                    <div class="rpmodal-col">
                        <label>Port Destination <span class="text-danger">*</span></label>
                        <select name="port_destination_id" id="editPortDestinationSelect" required>
                            <option value="">Select Port</option>
                            @foreach ($ports as $port)
                                <option value="{{ $port->port_id }}">{{ $port->terminal_name }} - {{ $port->port_name }}
                                    ({{ $port->city }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>


                <button type="submit" id="saveEditBtn" disabled style="background-color: #ccc; cursor: not-allowed;">
                    Save Changes
                </button>
            </form>
        </div>
    </div>

@endsection

@push('scripts')
    <script src="{{ asset('js/route_port_modal.js') }}"></script>
@endpush
