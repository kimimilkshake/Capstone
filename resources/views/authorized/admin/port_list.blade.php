@extends('layouts.app')
@section('page-title', 'PORTS')
@section('content')
@include('components.authHeader')
@include('components.admin_nav')

<div class="admin-body">
  <div class="avl-title">
    <h3>ROUTES AND PORTS</h3>
  </div>

  <!-- ROUTES AND PORTS TABS -->
  <div class="rp-row">
    <div class="rp-col {{ request()->routeIs('admin.route_list') ? 'active-tab' : '' }}">
      <a href="{{ route('admin.route_list') }}">ROUTES</a>
    </div>
    <div class="rp-col {{ request()->routeIs('admin.port_list') ? 'active-tab' : '' }}">
      <a href="{{ route('admin.port_list') }}">PORTS</a>
    </div>
  </div>

  <!-- Search + Add -->
  <div class="search-add-row" style="display: flex; gap: 10px; margin-bottom: 20px;">
    <form class="search-bar" action="{{ route('admin.port_list') }}" method="GET" style="flex: 1;">
      <input 
        type="text" 
        name="search" 
        placeholder="Search by name, city, or province" 
        value="{{ request('search') }}">
      <button type="submit">
        <i class="fa-solid fa-magnifying-glass me-2"></i>Search
      </button>
    </form>

    <div class="add-vessel">
      <button type="button" id="addPortBtn" class="add-link-btn">
        <i class="fa-solid fa-plus me-2"></i>Add Port
      </button>
    </div>
  </div>

  <!-- Table -->
  <table class="rp-table">
    <thead>
      <th>Port No.</th>
      <th>Name</th>
      <th>City / Municipality</th>
      <th>Province</th>
      <th>Action</th>
    </thead>
    <tbody>
      @forelse($ports as $port)
      <tr>
        <td>{{ $port->port_id }}</td>
        <td>{{ $port->port_name }}</td>
        <td>{{ $port->port_city }}</td>
        <td>{{ $port->port_province }}</td>
        <td>
          <button 
            type="button" 
            class="editPortBtn link-btn"
            data-id="{{ $port->port_id }}"
            data-name="{{ $port->port_name }}"
            data-city="{{ $port->port_city }}"
            data-province="{{ $port->port_province }}">
            <i class="fa fa-pencil"></i>
          </button>
        </td>
      </tr>
      @empty
      <tr>
        <td colspan="5" class="text-center">No ports found.</td>
      </tr>
      @endforelse
    </tbody>
  </table>

  <div class="mt-3">
    {{ $ports->appends(['search' => request('search')])->links('pagination::bootstrap-5') }}
  </div>
</div>

<!-- Add Port Modal -->
<div id="addPortModal" class="modal-overlay" style="display:none;">
  <div class="modal-content">
    <span class="close-btn" id="closeAddModal">&times;</span>
    <h3>Add Port</h3>
    <form id="addPortForm">
      @csrf
      <label>Name:</label>
      <input type="text" name="port_name" required>
      <label>City:</label>
      <input type="text" name="port_city" required>
      <label>Province:</label>
      <input type="text" name="port_province" required>
      <button type="submit">Add Port</button>
    </form>
  </div>
</div>

<!-- Edit Port Modal -->
<div id="editPortModal" class="modal-overlay" style="display:none;">
  <div class="modal-content">
    <span class="close-btn" id="closeEditModal">&times;</span>
    <h3>Edit Port</h3>
    <form id="editPortForm">
      @csrf
      @method('PUT')
      <input type="hidden" name="port_id" id="editPortId">
      <label>Name:</label>
      <input type="text" name="port_name" id="editPortName" required>
      <label>City:</label>
      <input type="text" name="port_city" id="editPortCity" required>
      <label>Province:</label>
      <input type="text" name="port_province" id="editPortProvince" required>
      <button type="submit">Save Changes</button>
    </form>
  </div>
</div>

@endsection

@push('scripts')
<script src="{{ asset('js/port_modal.js') }}"></script>
@endpush
