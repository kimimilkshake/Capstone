@extends('layouts.app')
@section('page-title', 'PROMO')
@section('content')
    @include('components.authHeader')
    @include('components.admin_nav')

    <div class="admin-body">
      <div class="apl-title">
        <h3>PROMO LIST</h3>
      </div>
      <div class="search-add-row" style="display: flex; gap: 10px; margin-bottom: 20px;">
        <form class="search-bar" action="{{ route('admin.promo_list') }}" method="GET" style="flex: 1;">
          <input type="text" name="search" placeholder="Search by name..." value="{{ request('search') }}">
          <button type="submit"><i class="fa-solid fa-magnifying-glass me-2"></i>Search</button>
        </form>

        <div class="add-promo">
          <a href="{{ route('admin.create_promo') }}"><i class="fa-solid fa-plus me-2"></i>Add Promo</a>
        </div>
      </div>
      <table class="promo-table">
        <thead>
          <tr>
            <th>Name</th>
            <th>Code</th>
            <th>Type</th>
            <th>Start Date</th>
            <th>End Date</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($promos as $index => $p)
            <tr>
              <td>{{ $p->promo_name }}</td>
              <td>{{ $p->promo_code }}</td>
              <td>{{ $p->promo_type }}</td>
              <td>{{ $p->promo_start_date }}</td>
              <td>{{ $p->promo_end_date }}</td>
              <td>{{ $p->promo_status }}</td>
              <td>
                <a href="{{ route('admin.promo_edit', $p->promo_id) }}" class="editRouteBtn link-btn" title="Edit Promo">
                  <i class="fa fa-pencil" aria-hidden="true"></i>
                </a>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="text-center">No promos fuond.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
      <div class="pagination-container">
        {{ $promos->appends(request()->query())->links('pagination::bootstrap-5') }}
      </div>
    </div>

@endsection