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
              <label>Classification</label>
              <input type="text" name="cargo_item_classification" required>
            </div>
          </div>
          
          <div class="form-col">
            <div class="form-group">
              <label>Volume</label>
              <input type="number" name="cargo_item_volume">
            </div>
          </div>
        </div>

        <div class="form-row">
          <div class="form-col">
            <div class="form-group">
              <label>Description</label>
              <input type="text" name="cargo_item_description" required>
            </div>
          </div>
          
          <div class="form-col">
            <div class="form-group">
              <label>Weight</label>
              <input type="number" name="cargo_item_weight">
            </div>
          </div>
        </div>

        <div class="form-row">
          <div class="form-col">
            <div class="form-group">
              <label>Freight</label>
              <input type="number" name="cargo_item_freight" required>
            </div>
          </div>
          
          <div class="form-col">
            <div class="form-group">
              <label>Length</label>
              <input type="number" name="cargo_item_length">
            </div>
          </div>
        </div>

        <div class="form-row">
          <div class="form-col">
            <div class="form-group">
              <label>Arrastre</label>
              <input type="number" name="cargo_item_arrastre" required>
            </div>
          </div>
          
          <div class="form-col">
            <div class="form-group">
              <label>Height</label>
              <input type="number" name="cargo_item_height">
            </div>
          </div>
          
        </div>

        <div class="form-row">
          <div class="form-col">
            <div class="form-group">
              <label>Type</label>
              <select name="cargo_item_type" required>
                <option value="">Select Type</option>
                <option value="Type A">Type A</option>
                <option value="Type B">Type B</option>
                <option value="Type C">Type C</option>
              </select>
            </div>
          </div>
          
          <div class="form-col">
            <div class="form-group">
              <label>Width</label>
              <input type="number" name="cargo_item_width">
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