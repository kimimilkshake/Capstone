@extends('layouts.app')
@section('page-title', 'CARGO')
@section('content')
  @include('components.authHeader')
  @include('components.staff_nav')
  <div class="staff-body">
    <div class="svl-title">
      <h3>VIEW RATES</h3>
    </div>

    <div class="search-filter-row" style="display: flex; gap: 10px; margin-bottom: 20px;">
      <form class="search-bar" action="{{ route('staff.cargo_item_list') }}"  method="GET" style="flex: 1;">

        <input type="text" name="search" placeholder="Search..." value="{{ request('search') }}" style="margin-right: 10px;">
        <select name="type">
            <option value="">All Types</option>
            <option value="Type A" {{ request('type')=='Type A' ? 'selected' : '' }}>Type A</option>
            <option value="Type B" {{ request('type')=='Type B' ? 'selected' : '' }}>Type B</option>
            <option value="Type C" {{ request('type')=='Type C' ? 'selected' : '' }}>Type C</option>
        </select>
        <button type="submit">Filter</button>
      </form>
    </div>

    <table class="cargo-item-table">
      <thead>
        <tr>
          <th>Item No.</th>
          <th>Classification</th>
          <th>Description</th>
          <th>Freight</th>
          <th>Arrastre</th>
          <th>Volume</th>
          <th>Weight</th>
          <th>Length</th>
          <th>Height</th>
          <th>Width</th>
          <th>Type</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($cargo_items as $index => $c)
          <tr>
            <td>{{ $c->cargo_item_id }}</td>
            <td>{{ $c->cargo_item_classification }}</td>
            <td>{{ $c->cargo_item_description }}</td>
            <td>{{ $c->cargo_item_freight }}</td>
            <td>{{ $c->cargo_item_arrastre }}</td>
            <td>{{ $c->cargo_item_volume }}</td>
            <td>{{ $c->cargo_item_weight }}</td>
            <td>{{ $c->cargo_item_length }}</td>
            <td>{{ $c->cargo_item_height }}</td>
            <td>{{ $c->cargo_item_width }}</td>
            <td>{{ $c->cargo_item_type }}</td>
            <td>
              <a href="{{ route('staff.cargo_item_edit', $c->cargo_item_id) }}" class="edit-icon">
                <i class="fa fa-pencil" aria-hidden="true"></i>
              </a>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
    <div class="pagination-container">
      {{ $cargo_items->appends(request()->query())->links('pagination::bootstrap-5') }}
    </div>

  </div>
@endsection