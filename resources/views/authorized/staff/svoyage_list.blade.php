@extends('layouts.app')
@section('page-title', 'VOYAGES')
@section('content')
  @include('components.authHeader')
  @include('components.staff_nav')
  <div class="staff-body">

    <!-- Floating Toast Container - Below navbar on the right side -->
        <div class="toast-container position-fixed p-3" style="z-index: 9999; top: 80px; right: 20px;">
            @if (session('success'))
                <div class="toast align-items-center text-white bg-success border-0 show" role="alert" aria-live="assertive"
                    aria-atomic="true" id="successToast">
                    <div class="d-flex">
                        <div class="toast-body">
                            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
                            aria-label="Close"></button>
                    </div>
                </div>
            @endif
            @if ($errors->any())
                <div class="toast align-items-center text-white bg-danger border-0 show" role="alert"
                    aria-live="assertive" aria-atomic="true" id="errorToast">
                    <div class="d-flex">
                        <div class="toast-body">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            @foreach ($errors->all() as $error)
                                {{ $error }}
                            @endforeach
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
                            aria-label="Close"></button>
                    </div>
                </div>
            @endif
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
              <a href="{{ route('staff.voyage_edit', $voyage->voyage_id) }}" title="Edit Voyage" class="editRouteBtn link-btn"><i class="fa fa-pencil me-1"></i></a>
              <a href="{{ route('staff.manifest', $voyage->voyage_id) }}" title="View Manifest" class="editRouteBtn link-btn"><i class="fa-solid fa-file me-1"></i></a>
              <a href="{{ route('staff.semaphore', $voyage->voyage_id) }}" title="Send Message" class="editRouteBtn link-btn"><i class="fa-solid fa-message"></i></a>
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