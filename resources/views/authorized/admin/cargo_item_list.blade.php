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

        <input type="text" name="search" placeholder="Search..." value="{{ request('search') }}">
        <select name="type">
            <option value="">All Types</option>
            <option value="Type A" {{ request('type')=='Type A' ? 'selected' : '' }}>Type A</option>
            <option value="Type B" {{ request('type')=='Type B' ? 'selected' : '' }}>Type B</option>
            <option value="Type C" {{ request('type')=='Type C' ? 'selected' : '' }}>Type C</option>
        </select>
        <button type="submit">Filter</button>
      </form>
    </div>

    <div class="cargo-item-table">
      <thead>
        <tr>
          <th>Item No.</th>
          <th>Calssification</th>
          <th>Description</th>
          <th></th>
        </tr>
      </thead>
    </div>
  </div>

@endsection