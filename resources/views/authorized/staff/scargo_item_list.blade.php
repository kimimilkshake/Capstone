@extends('layouts.app')
@section('page-title', 'CARGO ITEMS')
@section('content')
  @include('components.authHeader')
  @include('components.staff_nav')
  <div class="staff-body">

    <div class="search-filter-row" style="display: flex; gap: 10px; margin-bottom: 20px;">

      <form class="search-bar" action="{{ route('staff.cargo_item_list') }}"  method="GET" style="flex: 1;">

        <input type="text" name="search" placeholder="Search..." value="{{ request('search') }}" style="margin-right: 10px;">
        <select name="route_code_id">
            <option value="">All Route Codes</option>
            @foreach ($route_codes as $routeCode)
                <option value="{{ $routeCode->route_code_id }}" 
                    {{ request('route_code_id') == $routeCode->route_code_id ? 'selected' : '' }}>
                    {{ $routeCode->route_code_name }}
                </option>
            @endforeach
        </select>

        <select name="cargo_category_id">
            <option value="">All Categories</option>
            @foreach($cargo_categories as $category)
                <option value="{{ $category->cargo_category_id }}" 
                    {{ request('cargo_category_id') == $category->cargo_category_id ? 'selected' : '' }}>
                    {{ $category->cargo_category_name }}
                </option>
            @endforeach
        </select>
        <button type="submit">Filter</button>
      </form>

      <div class="add-vessel">
        <button type="button" id="saddCargoCategoryBtn" class="add-link-btn" title="Add Cargo Category">
          <i class="fa-solid fa-plus me-2"></i><i class="fa-solid fa-boxes-packing"></i>
        </button>
      </div>

    </div>

    <table class="cargo-item-table">
      <thead>
        <tr>
          <th>Route Code</th>
          <th>Category</th>
          <th>Description</th>
          <th>Freight</th>
          <th>With Measurement </th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($cargo_items as $index => $c)
          <tr>
            <td>{{ $c->routeCode->route_code_name ?? 'N/A' }}</td>
            <td>{{ $c->cargo_category->cargo_category_name ?? 'N/A' }}</td>
            <td>{{ $c->cargo_item_description }}</td>
            <td>{{ $c->cargo_item_freight }}</td>
            <td>{{ $c->cargo_item_measure_required }}</td>
            <td>
              <a href="{{ route('staff.cargo_item_edit', $c->cargo_item_id) }}" class="editRouteBtn link-btn" title="Edit Cargo Item">
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

  <!-- Add Cargo Classification Modal -->
  <div id="saddCargoCategoryModal" class="modal-overlay" style="display: none">
    <div class="modal-content">
      
      <span class="close-btn" id="scloseCargoCategoryModal">&times;</span>
      <h3>Add Cargo Category</h3>
      <form id="saddCargoCategoryForm" 
            action="{{ route('staff.cargo_category_store') }}"
            method="POST">
        @csrf
        <div class="rpmodal-row one-col">
          <div class="rpmodal-col">
            <label>Cargo Category Name <span class="text-danger">*</span></label>
            <input type="text" name="cargo_category_name" required>
          </div>
        </div>
        <button type="submit">Add Cargo Category</button>
      </form>
    </div>
  </div>

  <script>

    document.addEventListener('DOMContentLoaded', function () {

        const openBtn = document.getElementById('saddCargoCategoryBtn');
        const modal = document.getElementById('saddCargoCategoryModal');
        const closeBtn = document.getElementById('scloseCargoCategoryModal');

        // Open modal
        openBtn.addEventListener('click', function () {
            modal.style.display = 'flex';
        });

        // Close modal (X button)
        closeBtn.addEventListener('click', function () {
            modal.style.display = 'none';
        });

        // Close modal when clicking outside content
        window.addEventListener('click', function (e) {
            if (e.target === modal) {
                modal.style.display = 'none';
            }
        });

    });
    </script>
@endsection