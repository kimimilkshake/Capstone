@extends('layouts.app')
@section('page-title', 'CARGO')
@section('content')
  @include('components.authHeader')
  @include('components.staff_nav')
  <div class="staff-body">
    <div class="svl-title">
      <h3>VIEW RATES</h3>
    </div>

    {{-- ERROR MESSAGE --}}
    @if ($errors->any())
      <div class="alert-wrapper">
        <div class="alert alert-danger">
          <strong>All fields are required.</strong><br>
          @foreach ($errors->all() as $error)
            {{ $error }}<br>
          @endforeach
        </div>
      </div>
    @endif

    {{-- SUCCESS MESSAGE --}}
    @if (session('success'))
      <div class="alert-wrapper">
        <div class="alert alert-success">
          {{ session('success') }}
        </div>
      </div>
    @endif

    <div class="search-filter-row" style="display: flex; gap: 10px; margin-bottom: 20px;">

      <form class="search-bar" action="{{ route('staff.cargo_item_list') }}"  method="GET" style="flex: 1;">

        <input type="text" name="search" placeholder="Search..." value="{{ request('search') }}" style="margin-right: 10px;">
        <select name="route_code_id">
          <option value="">All Route Codes</option>
          @foreach ($route_codes as $routeCode)
              <option value="{{ $routeCode->route_code_id }}">
                    {{ $routeCode->route_code_name }}
                  </option>
          @endforeach
        </select>
        <button type="submit">Filter</button>
      </form>

      <div class="add-vessel">
        <button type="button" id="saddCargoClassificationBtn" class="add-link-btn" title="Add Cargo Classification">
          <i class="fa-solid fa-plus me-2"></i><i class="fa-solid fa-boxes-packing"></i>
        </button>
      </div>

    </div>

    <table class="cargo-item-table">
      <thead>
        <tr>
          <th>Route Code</th>
          <th>Description</th>
          <th>Freight</th>
          <th>Arrastre</th>
          <th>With Measurement </th>
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
  <div id="saddCargoClassificationModal" class="modal-overlay" style="display: none">
    <div class="modal-content">
      
      <span class="close-btn" id="scloseCargoClassificationModal">&times;</span>
      <h3>Add Cargo Classification</h3>
      <form id="saddCargoClassificationForm" 
            action="{{ route('staff.cargo_classification_store') }}"
            method="POST">
        @csrf
        <div class="rpmodal-row one-col">
          <div class="rpmodal-col">
            <label>Cargo Classification Name <span class="text-danger">*</span></label>
            <input type="text" name="cargo_classification_name" required>
          </div>
        </div>
        <button type="submit">Add Cargo Classification</button>
      </form>
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function () {

        const openBtn = document.getElementById('saddCargoClassificationBtn');
        const modal = document.getElementById('saddCargoClassificationModal');
        const closeBtn = document.getElementById('scloseCargoClassificationModal');

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