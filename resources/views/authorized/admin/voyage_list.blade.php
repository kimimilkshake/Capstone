@extends('layouts.app')
@section('page-title', 'VOYAGES')
@section('content')
  @include('components.authHeader')
  @include('components.admin_nav')

  <div class="admin-body">
    <div class="avl-title">
      <h3>VOYAGE LIST</h3>
    </div>

    <div class="search-add-row" style="display: flex; gap: 10px; margin-bottom: 20px;">
      <form class="search-bar" action="{{ route('voyages.index') }}" method="GET" style="flex: 1;">
          <input 
              type="text" 
              name="search" 
              placeholder="Search by name, code, route, vessel, status..." 
              value="{{ request('search') }}">
          <button type="submit">
              <i class="fa-solid fa-magnifying-glass me-2"></i>Search
          </button>
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
          <th>Arrival Date</th>
          <th>ETD</th>
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
              {{ $voyage->route->route_origin }} → 
              {{ $voyage->route->route_destination }}
            </td>
            <td>{{ \Carbon\Carbon::parse($voyage->voyage_departure_date)->format('F j, Y, D') }}</td>
            <td>{{ \Carbon\Carbon::parse($voyage->voyage_arrival_date)->format('F j, Y, D') }}</td>
            <td>{{ \Carbon\Carbon::parse($voyage->voyage_estimated_TD)->format('g:iA') }}</td>
            <td>{{ \Carbon\Carbon::parse($voyage->voyage_estimated_TA)->format('g:iA') }}</td>
            <td>{{ $voyage->vessel->vessel_name}}</td>
            <td>{{ $voyage->voyage_status }}</td>
            <td>
              <a href="{{ route('admin.voyage_edit', $voyage->voyage_id) }}" title="Edit Voyage"><i class="fa fa-pencil me-2" ></i></a>
              <a href="{{ route('manifest', $voyage->voyage_id) }}" title="View Manifest"><i class="fa-solid fa-file"></i></a>
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
