@extends('layouts.app')
@section('page-title', 'CARGO')
@section('content')
  @include('components.authHeader')
  @include('components.admin_nav')
  <div class="admin-body">
    <div class="avl-title">
      <h3>EDIT CARGO ITEM</h3>
    </div>

    <div class="aci-form_container">
      @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
      @endif
      <form action="{{ route('admin.cargo_item_update', $cargo_item->cargo_item_id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="form-row">
          <div class="form-col">
            <div class="form-group">
              <label>Classification</label>
              <input type="text" name="cargo_item_classification" value="{{ old('cargo_item_classification', $cargo_item->cargo_item_classification) }}" required>
            </div>
          </div>
          
          <div class="form-col">
            <div class="form-group">
              <label>Description</label>
              <input type="text" name="cargo_item_description" value="{{ old('cargo_item_description', $cargo_item->cargo_item_description) }}"  required>
            </div>
          </div>
        </div>

        <div class="form-row">
          <div class="form-col">
            <div class="form-group">
              <label>Freight</label>
              <input type="number" name="cargo_item_freight" value="{{ old('cargo_item_freight', $cargo_item->cargo_item_freight) }}"  required>
            </div>
          </div>

          <div class="form-col">
            <div class="form-group">
              <label>Arrastre</label>
              <input type="number" name="cargo_item_arrastre" value="{{ old('cargo_item_arrastre', $cargo_item->cargo_item_arrastre) }}" required>
            </div>
          </div>

          <div class="form-col">
              <div class="form-group">
                  <label>Route Destination</label>
                  <select name="route_port_id" required>
                      <option value="">Select Destination</option>
                      @foreach ($routes as $route)
                          <option value="{{ $route->route_port_id }}"
                              {{ $cargo_item->route_port_id == $route->route_port_id ? 'selected' : '' }}>
                              {{ $route->route_destination }}
                          </option>
                      @endforeach
                  </select>
              </div>
          </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Update Cargo Item</button>
            <a href="{{ route('admin.cargo_item_list') }}" class="btn btn-secondary">Cancel</a>
        </div>
      </form>
    </div>


  </div>

@endsection