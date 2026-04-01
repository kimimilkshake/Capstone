@extends('layouts.app')
@section('page-title', 'VOYAGES')
@section('content')
  @include('components.authHeader')
  @include('components.admin_nav')

  <div class="admin-body">

    <div class="search-add-row" style="display: flex; gap: 10px; margin-bottom: 20px; align-items: flex-end;">
      <form class="search-bar" action="{{ route('admin.voyage_list') }}" method="GET"
      style="flex: 1; display: flex; gap: 10px; align-items: flex-end;">

          <!-- SEARCH -->
          <div style="flex: 2; display: flex; flex-direction: column;">
              <label style="font-size: 12px;">Search</label>
              <input 
                  type="text" 
                  name="search" 
                  placeholder="Search by name, code, route, vessel, status..." 
                  value="{{ request('search') }}"
                  style="height: 38px;">
          </div>

          <!-- FROM -->
          <div style="display: flex; flex-direction: column;">
              <label style="font-size: 12px;">From</label>
              <input 
                  type="date" 
                  name="start_date" 
                  value="{{ request('start_date') }}"
                  style="height: 38px;">
          </div>

          <!-- TO -->
          <div style="display: flex; flex-direction: column;">
              <label style="font-size: 12px;">To</label>
              <input 
                  type="date" 
                  name="end_date" 
                  value="{{ request('end_date') }}"
                  style="height: 38px;">
          </div>

          <!-- BUTTON -->
          <div>
              <button type="submit" style="height: 38px; padding: 0 15px;">
                  <i class="fa-solid fa-magnifying-glass me-2"></i>Search
              </button>
          </div>

      </form>

      <div class="add-vessel">
        <a href="{{ route('admin.create_voyage') }}">
          <i class="fa-solid fa-plus me-2"></i>Add Voyage
        </a>
      </div>
    </div>

    <table class="voyage-table">
      <thead>
        <tr>
          <th>Voyage Code</th>
          <th>Route</th>
          <th>Departure Date</th>
          <th>ETD</th>
          <th>Arrival Date</th>
          <th>ETA</th>
          <th>PAX/CAP</th>
          <th>Vessel</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($voyages as $voyage)
          <tr>
            <td>{{ $voyage->voyage_code }}</td>
            <td>
              {{ $voyage->routePort->route_origin }} → 
              {{ $voyage->routePort->route_destination }}
            </td>
            <td>{{ \Carbon\Carbon::parse($voyage->voyage_departure_date)->format('M j, Y, D') }}</td>
            <td>{{ \Carbon\Carbon::parse($voyage->voyage_estimated_TD)->format('g:iA') }}</td>
            <td>{{ \Carbon\Carbon::parse($voyage->voyage_arrival_date)->format('M j, Y, D') }}</td>
            <td>{{ \Carbon\Carbon::parse($voyage->voyage_estimated_TA)->format('g:iA') }}</td>
            <td>{{ $voyage->passenger_tickets_count }}/{{ $voyage->vessel->vessel_total_passenger_capacity }}</td>
            <td>{{ $voyage->vessel->vessel_name}}</td>
            <td>{{ $voyage->voyage_status }}</td>
            <td>
              @if($voyage->voyage_status === 'At Sea')
                  <a href="#" class="link-btn disabled-voyage" style="opacity: 0.5; cursor: not-allowed;" title="Cannot edit while At Sea">
                      <i class="fa fa-pencil me-1"></i>
                  </a>
              @else
                  <a href="{{ route('admin.voyage_edit', $voyage->voyage_id) }}" class="link-btn">
                      <i class="fa fa-pencil me-1"></i>
                  </a>
              @endif
              <a href="{{ route('admin.manifest', $voyage->voyage_id) }}" title="View Manifest" class="editRouteBtn link-btn" style="text-decoration: none"><i class="fa-solid fa-file me-1"></i></a>
              
            </td> 
          </tr>
        @empty
          <tr>
            <td colspan="9" class="text-center">No voyages found.</td>
          </tr>
        @endforelse
      </tbody>
    </table>

    <div class="mt-3">
      {{ $voyages->appends(['search' => request('search')])->links('pagination::bootstrap-5') }}
    </div>
  </div>



@endsection
