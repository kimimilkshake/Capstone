@extends('layouts.app')
@section('page-title', 'VOYAGES')
@section('content')
  @include('components.authHeader')
  @include('components.staff_nav')
  <div class="staff-body">
    <div class="svl-title">
      <h3>SEARCH VOYAGE</h3>
    </div>
    <div class="search-add-row" style="display: flex; gap: 10px; margin-bottom: 20px;">
      <form class="search-bar" action="{{ route('staff.voyage_list') }}" method="GET" style="flex: 1;">
          <input 
              type="text" 
              name="search" 
              placeholder="Search by name, code, route, vessel, status..." 
              value="{{ request('search') }}">
          <input 
              type="date" 
              name="date" 
              placeholder="Search by date"
              value="{{ request('date') }}"
              style="margin-left:10px;">
          <button type="submit">
              <i class="fa-solid fa-magnifying-glass me-2"></i>Search
          </button>
      </form>
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
            <td>{{ \Carbon\Carbon::parse($voyage->voyage_departure_date)->format('F j, Y, D') }}</td>
            <td>{{ \Carbon\Carbon::parse($voyage->voyage_estimated_TD)->format('g:iA') }}</td>
            <td>{{ \Carbon\Carbon::parse($voyage->voyage_arrival_date)->format('F j, Y, D') }}</td>
            <td>{{ \Carbon\Carbon::parse($voyage->voyage_estimated_TA)->format('g:iA') }}</td>
            <td>{{ $voyage->vessel->vessel_name}}</td>
            <td>{{ $voyage->voyage_status }}</td>
            <td>
              <a href="{{ route('staff.voyage_edit', $voyage->voyage_id) }}" title="Edit Voyage"><i class="fa fa-pencil me-1" ></i></a>
              <a href="{{ route('manifest', $voyage->voyage_id) }}" title="View Manifest" ><i class="fa-solid fa-file me-1"></i></a>
              <a href="#" title="Cancel Trip" style="color: red; "><i class="fa-solid fa-ban"></i></a>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="9" class="text-center">No voyages found.</td>
          </tr>
        @endforelse
      </tbody>
    </table>

    {{-- ✅ Pagination links --}}
    <div class="mt-3">
      {{ $voyages->appends(['search' => request('search')])->links('pagination::bootstrap-5') }}
    </div>
  </div>

@endsection