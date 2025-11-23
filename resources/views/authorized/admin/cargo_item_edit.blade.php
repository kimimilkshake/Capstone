@extends('layouts.app')
@section('page-title', 'CARGO')
@section('content')
  @include('components.authHeader')
  @include('components.admin_nav')
  <div class="admin-body">
    <div class="avl-title">
      <h3>EDIT CARGO ITEM</h3>
    </div>

    <div class="aci-form-container">
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
              <label>Volume</label>
              <input type="number" name="cargo_item_volume" value="{{ old('cargo_item_volume', $cargo_item->cargo_item_volume) }}" >
            </div>
          </div>
        </div>

        <div class="form-row">
          <div class="form-col">
            <div class="form-group">
              <label>Description</label>
              <input type="text" name="cargo_item_description" value="{{ old('cargo_item_description', $cargo_item->cargo_item_description) }}"  required>
            </div>
          </div>
          
          <div class="form-col">
            <div class="form-group">
              <label>Weight</label>
              <input type="number" name="cargo_item_weight" value="{{ old('cargo_item_weight', $cargo_item->cargo_item_weight) }}" >
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
              <label>Length</label>
              <input type="number" name="cargo_item_length" value="{{ old('cargo_item_length', $cargo_item->cargo_item_length) }}" >
            </div>
          </div>
        </div>

        <div class="form-row">
          <div class="form-col">
            <div class="form-group">
              <label>Arrastre</label>
              <input type="number" name="cargo_item_arrastre" value="{{ old('cargo_item_arrastre', $cargo_item->cargo_item_arrastre) }}" required>
            </div>
          </div>
          
          <div class="form-col">
            <div class="form-group">
              <label>Height</label>
              <input type="number" name="cargo_item_height" value="{{ old('cargo_item_height',$cargo_item->cargo_item_height) }}" >
            </div>
          </div>
          
        </div>

        <div class="form-row">
          <div class="form-col">
            <div class="form-group">
              <label>Type</label>
              <select name="cargo_item_type" required>
                <option value="">Select Type</option>
                <option value="Type A" {{ $cargo_item->cargo_item_type == 'Type A' ? 'selected' : '' }}>Type A</option>
                <option value="Type B" {{ $cargo_item->cargo_item_type == 'Type B' ? 'selected' : '' }}>Type B</option>
                <option value="Type C" {{ $cargo_item->cargo_item_type == 'Type C' ? 'selected' : '' }}>Type C</option>
              </select>
            </div>
          </div>
          
          <div class="form-col">
            <div class="form-group">
              <label>Width</label>
              <input type="number" name="cargo_item_width" value="{{ old('cargo_item_width',$cargo_item->cargo_item_width) }}" >
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