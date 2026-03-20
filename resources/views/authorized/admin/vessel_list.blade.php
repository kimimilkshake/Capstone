@extends('layouts.app')
@section('page-title', 'VESSELS')
@section('content')
  @include('components.authHeader')
  @include('components.admin_nav')
  <div class="admin-body">
        
    <div class="search-add-row" style="display: flex; gap: 10px; margin-bottom: 20px;">
      <form class="search-bar" action="{{ route('admin.vessel_list') }}" method="GET" style="flex: 1;">
          <input type="text" name="search" placeholder="Search by name..." value="{{ request('search') }}">
          <button type="submit"><i class="fa-solid fa-magnifying-glass me-2"></i>Search</button>
      </form>

      <div class="add-vessel">
        <a href="{{ route('admin.create_vessel') }}"><i class="fa-solid fa-plus me-2"></i>Add Vessel</a>
      </div>
    </div>

    <table class="vessel-table">
      <thead>
        <tr>
          <th>Code</th>
          <th>Name</th>
          <th>No. of Hatches</th>
          <th>No. of Accommodations</th>
          <th>Passenger Capacity</th>
          <th>Status</th>
          <th>Action</>
        </tr>
      </thead>
      <tbody>
        @forelse ($vessels as $index => $v)
          <tr>
            <td>{{ $v->vessel_code }}</td>
            <td>{{ $v->vessel_name }}</td>
            <td>{{ $v->hatches->count() }}</td>
            <td>{{ $v->accommodations->count() }}</td>
            <td>{{ $v->vessel_total_passenger_capacity }}</td>
            <td>{{ $v->vessel_status }}</td>
            <td>
              <a href="{{ route('admin.vessel_edit', $v->vessel_id) }}" class="editRouteBtn link-btn" title="Edit Vessel">
                <i class="fa fa-pencil" aria-hidden="true"></i>
              </a>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="6" class="text-center">No vessels found.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
    <div class="pagination-container">
      {{ $vessels->appends(request()->query())->links('pagination::bootstrap-5') }}
    </div>

  </div>

@endsection