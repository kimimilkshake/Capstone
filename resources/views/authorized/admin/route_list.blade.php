@extends('layouts.app')
@section('page-title', 'ROUTES')
@section('content')
@include('components.authHeader')
@include('components.admin_nav')

<div class="admin-body">
  <div class="avl-title">
    <h3>ROUTES AND PORTS</h3>
  </div>

  <!--ROUTES AND PORTS TABS-->
  <div class="rp-row">
    <div class="rp-col {{ request()->routeIs('admin.route_list') ? 'active-tab' : '' }}">
      <a href="{{ route('admin.route_list') }}">ROUTES</a>
    </div>
    <div class="rp-col {{ request()->routeIs('admin.port_list') ? 'active-tab' : '' }}">
      <a href="{{ route('admin.port_list') }}">PORTS</a>
    </div>
  </div>

  <!--SEARCH BAR-->
  <div class="search-add-row" style="display: flex; gap: 10px; margin-bottom: 20px;">
    <form class="search-bar" action="{{ route('admin.route_list') }}" method="GET" style="flex: 1;">
      <input type="text" name="search" placeholder="Search by origin or destination" value="{{ request('search') }}">
      <button type="submit">
        <i class="fa-solid fa-magnifying-glass me-2"></i>Search
      </button>
    </form>

    <div class="add-vessel">
      <button type="button" id="addRouteBtn" class="add-link-btn">
        <i class="fa-solid fa-plus me-2"></i>Add Route
      </button>
    </div>
  </div>

  <table class="rp-table">
    <thead>
      <th>Route No.</th>
      <th>Origin</th>
      <th>Destination</th>
      <th>Action</th>
    </thead>
    <tbody>
      @forelse($routes as $route)
        <tr>
          <td>{{ $route->route_id }}</td>
          <td>{{ $route->route_origin }}</td>
          <td>{{ $route->route_destination }}</td>
          <td>
            <button 
              type="button" 
              class="editRouteBtn link-btn"
              data-id="{{ $route->route_id }}" 
              data-origin="{{ $route->route_origin }}" 
              data-destination="{{ $route->route_destination }}">
              <i class="fa fa-pencil"></i>
            </button>
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="4" class="text-center">No routes found.</td>
        </tr>
      @endforelse
    </tbody>
  </table>

  <div class="mt-3">
    {{ $routes->appends(['search' => request('search')])->links('pagination::bootstrap-5') }}
  </div>
</div>

<!-- Add Route Modal -->
<div id="addRouteModal" class="modal-overlay" style="display:none;">
  <div class="modal-content">
    <span class="close-btn" id="closeAddModal">&times;</span>
    <h3>Add Route</h3>
    <form id="addRouteForm">
      @csrf
      <label>Origin:</label>
      <input type="text" name="route_origin" required>
      <label>Destination:</label>
      <input type="text" name="route_destination" required>
      <button type="submit">Add Route</button>
    </form>
  </div>
</div>

<!-- Edit Route Modal -->
<div id="editRouteModal" class="modal-overlay" style="display:none;">
  <div class="modal-content">
    <span class="close-btn" id="closeEditModal">&times;</span>
    <h3>Edit Route</h3>
    <form id="editRouteForm">
      @csrf
      @method('PUT')
      <input type="hidden" name="route_id" id="editRouteId">
      <label>Origin:</label>
      <input type="text" name="route_origin" id="editRouteOrigin" required>
      <label>Destination:</label>
      <input type="text" name="route_destination" id="editRouteDestination" required>
      <button type="submit">Save Changes</button>
    </form>
  </div>
</div>

@endsection

@push('scripts')
<script src="{{ asset('js/route_modal.js') }}"></script>
@endpush
