@extends('layouts.app')
@section('page-title', 'VOYAGES')
@section('content')
  @include('components.authHeader')
  @include('components.admin_nav')

  <div class="admin-body">
    <div class="avl-title">
      <h3>VOYAGE LIST</h3>
    </div>

    @if(session('success'))
        <div class="alert alert-success text-center mx-auto w-75" role="alert">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger text-center">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="search-add-row" style="display: flex; gap: 10px; margin-bottom: 20px;">
      <form class="search-bar" action="{{ route('admin.voyage_list') }}" method="GET" style="flex: 1;">
          <input 
              type="text" 
              name="search" 
              placeholder="Search by name, code, route, vessel, status..." 
              value="{{ request('search') }}">
          <input 
              type="date" 
              name="start_date" 
              value="{{ request('start_date') }}"
              style="margin-left:10px;"
              placeholder="Start date">
          <input 
              type="date" 
              name="end_date" 
              value="{{ request('end_date') }}"
              style="margin-left:10px;"
              placeholder="End date">
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
            <td>{{ \Carbon\Carbon::parse($voyage->voyage_departure_date)->format('M j, Y, D') }}</td>
            <td>{{ \Carbon\Carbon::parse($voyage->voyage_estimated_TD)->format('g:iA') }}</td>
            <td>{{ \Carbon\Carbon::parse($voyage->voyage_arrival_date)->format('M j, Y, D') }}</td>
            <td>{{ \Carbon\Carbon::parse($voyage->voyage_estimated_TA)->format('g:iA') }}</td>
            <td>{{ $voyage->vessel->vessel_name}}</td>
            <td>{{ $voyage->voyage_status }}</td>
            <td>
              <a href="{{ route('admin.voyage_edit', $voyage->voyage_id) }}" title="Edit Voyage" class="editRouteBtn link-btn"><i class="fa fa-pencil me-1" ></i></a>
              <a href="{{ route('admin.manifest', $voyage->voyage_id) }}" title="View Manifest" class="editRouteBtn link-btn"><i class="fa-solid fa-file me-1"></i></a>
              
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
