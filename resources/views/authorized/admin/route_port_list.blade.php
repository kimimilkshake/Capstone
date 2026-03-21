@extends('layouts.app')
@section('page-title', 'ROUTES AND PORTS')
@section('content')
@include('components.authHeader')
@include('components.admin_nav')

<div class="admin-body">

  <!--SEARCH BAR-->
  <div class="search-add-row" style="display: flex; gap: 10px; margin-bottom: 20px;">
    <form class="search-bar" action="{{ route('admin.route_port_list') }}" method="GET" style="flex: 1;">
      <input type="text" name="search" placeholder="Search by origin, destination, or ports" value="{{ request('search') }}">
      <button type="submit">
        <i class="fa-solid fa-magnifying-glass me-2"></i>Search
      </button>
    </form>

    <div class="add-vessel">
      <button type="button" id="addRouteCodeBtn" class="add-link-btn">
        <i class="fa-solid fa-plus me-2"></i>Add Route Code
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
          <td>{{ $rp->routeCode->route_code_name ?? 'N/A' }}</td>
          <td>{{ $rp->route_origin }}</td>
          <td>{{ $rp->route_destination }}</td>
          <td>
            {{ $rp->port_origin_name }},
            {{ $rp->port_origin_city }},
            {{ $rp->port_origin_province }}
          </td>
          <td>
            {{ $rp->port_destination_name }},
            {{ $rp->port_destination_city }},
            {{ $rp->port_destination_province }}
          </td>
          <td>
            <button 
                type="button" 
                class="editRouteBtn link-btn"
                title="Edit Route and Port"
                data-id="{{ $rp->route_port_id }}" 
                data-origin="{{ $rp->route_origin }}" 
                data-destination="{{ $rp->route_destination }}"
                data-port_origin_name="{{ $rp->port_origin_name }}"
                data-port_origin_city="{{ $rp->port_origin_city }}"
                data-port_origin_province="{{ $rp->port_origin_province }}"
                data-port_destination_name="{{ $rp->port_destination_name }}"
                data-port_destination_city="{{ $rp->port_destination_city }}"
                data-port_destination_province="{{ $rp->port_destination_province }}"
                data-route_code_id="{{ $rp->route_code_id }}"
            >
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

<!-- Add Route Code Modal -->
<div id="addRouteCodeModal" class="modal-overlay" style="display:none;">
  <div class="modal-content">
    <span class="close-btn" id="closeAddRouteCodeModal">&times;</span>
    <h3>Add Route Code</h3>

    <form id="addRouteCodeForm">
      @csrf
      <div class="rpmodal-row one-col">
        <div class="rpmodal-col">
          <label>Route Code Name <span class="text-danger">*</span></label>
          <input type="text" name="route_code_name" required>
        </div>
      </div>

      <button type="submit">Add Route Code</button>
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
          <label>Route Code <span class="text-danger">*</span></label>
          <select name="route_code_id" id="route_code_id">
            <option value="">Select Route Code</option>
            @foreach($route_codes as $code)
                <option value="{{ $code->route_code_id }}">{{ $code->route_code_name }}</option>
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

      <!-- 3 columns for Port Origin -->
      <div class="rpmodal-row three-col">
        <div class="rpmodal-col">
          <label>Port Origin Name <span class="text-danger">*</span></label>
          <input type="text" name="port_origin_name" required>
        </div>
        <div class="rpmodal-col">
          <label>Port Origin City <span class="text-danger">*</span></label>
          <input type="text" name="port_origin_city" required>
        </div>
        <div class="rpmodal-col">
          <label>Port Origin Province <span class="text-danger">*</span></label>
          <input type="text" name="port_origin_province" required>
        </div>
      </div>

      <!-- 3 columns for Port Destination -->
      <div class="rpmodal-row three-col">
        <div class="rpmodal-col">
          <label>Port Destination Name <span class="text-danger">*</span></label>
          <input type="text" name="port_destination_name" required>
        </div>
        <div class="rpmodal-col">
          <label>Port Destination City <span class="text-danger">*</span></label>
          <input type="text" name="port_destination_city" required>
        </div>
        <div class="rpmodal-col">
          <label>Port Destination Province<span class="text-danger">*</span></label>
          <input type="text" name="port_destination_province" required>
        </div>
      </div>

      <button type="submit">Add Route & Port</button>
    </form>
  </div>
</div>


<!-- Edit Route & Port Modal -->
<div id="editRoutPortModal" class="modal-overlay" style="display:none;">
  <div class="modal-content">
    <span class="close-btn" id="closeEditModal">&times;</span>
    <h3>Edit Route and Port</h3>

    <form id="editRoutePortForm">
      @csrf
      @method('PUT')

      <input type="hidden" name="route_port_id" id="editRoutePortId">


      <div class="rpmodal-row three-col">
        <div class="rpmodal-col">
          <label>Route Code <span class="text-danger">*</span></label>
          <select name="route_code_id" id="editRouteCodeId">
            <option value="">Select Route Code</option>
            @foreach($route_codes as $routeCode)
                <option value="{{ $routeCode->route_code_id }}">{{ $routeCode->route_code_name }}</option>
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

      <div class="rpmodal-row three-col">
        <div class="rpmodal-col">
          <label>Port Origin Name <span class="text-danger">*</span></label>
          <input type="text" name="port_origin_name" id="editPortOriginName" required>
        </div>
        <div class="rpmodal-col">
          <label>Port Origin City <span class="text-danger">*</span></label>
          <input type="text" name="port_origin_city" id="editPortOriginCity" required>
        </div>
        <div class="rpmodal-col">
          <label>Port Origin Province <span class="text-danger">*</span></label>
          <input type="text" name="port_origin_province" id="editPortOriginProvince" required>
        </div>
      </div>
      <div class="rpmodal-row three-col">
        <div class="rpmodal-col">
          <label>Port Destination Name <span class="text-danger">*</span></label>
          <input type="text" name="port_destination_name" id="editPortDestinationName" required>
        </div>
        <div class="rpmodal-col">
          <label>Port Destination City <span class="text-danger">*</span></label>
          <input type="text" name="port_destination_city" id="editPortDestinationCity" required>
        </div>
        <div class="rpmodal-col">
          <label>Port Destination Province<span class="text-danger">*</span></label>
          <input type="text" name="port_destination_province" id="editPortDestinationProvince" required>
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
