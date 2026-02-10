@extends('layouts.app')
@section('page-title', 'CARGO')
@section('content')
  @include('components.authHeader')
  @include('components.admin_nav')
  <div class="admin-body">
    <div class="avl-title">
      <h3>VIEW RATES</h3>
    </div>

    <div class="search-filter-row" style="display: flex; gap: 10px; margin-bottom: 20px;">
      <form class="search-bar" action="{{ route('admin.cargo_item_list') }}"  method="GET" style="flex: 1;">

        <input type="text" name="search" placeholder="Search..." value="{{ request('search') }}" style="margin-right: 10px;">
        <select name="route_code_id">
          <option value="">Select Route Code</option>
          @foreach ($route_codes as $routeCode)
              <option value="{{ $routeCode->route_code_id }}">
                    {{ $routeCode->route_code_name }}
                  </option>
          @endforeach
        </select>
        <button type="submit">Filter</button>
      </form>
    </div>

    <table class="cargo-item-table">
      <thead>
        <tr>
          <th>Route Code</th>
          <th>Description</th>
          <th>Freight</th>
          <th>Arrastre</th>
          <th>With Measurement Range?</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($cargo_items as $index => $c)
          <tr>
            <td>{{ $c->routeCode->route_code_name ?? 'N/A' }}</td>
            <td>{{ $c->cargo_item_description }}</td>
            <td>{{ $c->cargo_item_freight }}</td>
            <td>{{ $c->cargo_item_arrastre }}</td>
            <td>{{ $c->cargo_item_measure_required }}</td>
            <td>
              <a href="{{ route('admin.cargo_item_edit', $c->cargo_item_id) }}" class="edit-icon">
                <i class="fa fa-pencil" aria-hidden="true"></i>
              </a>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="6" class="text-center">No cargo items found.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
    <div class="pagination-container">
      {{ $cargo_items->appends(request()->query())->links('pagination::bootstrap-5') }}
    </div>
  </div>

@endsection