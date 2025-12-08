@extends('layouts.app')
@section('page-title', 'CARGO')
@section('content')
  @include('components.authHeader')
  @include('components.staff_nav')
  <div class="staff-body">
    <div class="svl-title">
      <h3>CREATE CARGO ITEM</h3>
    </div>

    <div class="aci-form_container">

      {{-- ERROR ALERT --}}
      @if ($errors->any())
        <div class="alert alert-danger">
          <strong>All fields are required.</strong><br>
          @foreach ($errors->all() as $error)
            {{ $error }}<br>
          @endforeach
        </div>
      @endif

      {{-- SUCCESS ALERT --}}
      @if (session('success'))
        <div class="alert alert-success">
          {{ session('success') }}
        </div>
      @endif

      <form action="{{ route('staff.store_cargo_item') }}" method="POST" class="create-cargoitem-form">
        @csrf

        <div class="form-row">
          <div class="form-col">
            <div class="form-group">
              <label>Classification <span class="text-danger">*</span></label>
              <input type="text" name="cargo_item_classification" required>
            </div>
          </div>
          
          <div class="form-col">
            <div class="form-group">
              <label>Description <span class="text-danger">*</span></label>
              <input type="text" name="cargo_item_description" required>
            </div>
          </div>
        </div>

        <div class="form-row">
          <div class="form-col">
            <div class="form-group">
              <label>Freight <span class="text-danger">*</span></label>
              <input type="number" name="cargo_item_freight" step="0.01" required>
            </div>
          </div>

          <div class="form-col">
            <div class="form-group">
              <label>Arrastre <span class="text-danger">*</span></label>
              <input type="number" name="cargo_item_arrastre" step="0.01" required>
            </div>
          </div>

          <div class="form-col">
            <div class="form-group">
              <label>Route Destination <span class="text-danger">*</span></label>
              <select name="route_port_id" required>
                <option value="">Select Destination</option>
                @foreach ($routes as $route)
                  <option value="{{ $route->route_port_id }}">
                    {{ $route->route_destination }}
                  </option>
                @endforeach
              </select>
            </div>
          </div>
        </div>

        

        <div class="form-actions">
          <button type="submit" class="acs-add-btn">
            <i class="fa-solid fa-plus me-2"></i> Add Cargo Item
          </button>
        </div>
      </form>
    </div>
  </div>
@endsection