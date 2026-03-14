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
        <div class="form-row"> <!-- First Row -->

          <div class="form-col">
            <div class="form-group">
              <label>Route Code <span class="text-danger">*</span></label>
              <select name="route_code_id" required>
                @foreach ($route_codes as $routeCode)
                  <option value="{{ $routeCode->route_code_id }}" {{ $cargo_item->route_code_id == $routeCode->route_code_id ? 'selected' : '' }}>
                    {{ $routeCode->route_code_name }}
                  </option>
                @endforeach
              </select>
            </div>
          </div>

          <div class="form-col">
            <div class="form-group">
              <label>Category <span class="text-danger">*</span></label>
              <select name="cargo_category_id" required>
                @foreach ($cargo_categories as $category)
                  <option value="{{ $category->cargo_category_id }}" {{ $cargo_item->cargo_category_id == $category->cargo_category_id ? 'selected' : '' }}>
                    {{ $category->cargo_category_name }}
                  </option>
                @endforeach
              </select>
            </div>
          </div>
          
          <div class="form-col">
            <div class="form-group">
              <label>Description <span class="text-danger">*</span></label>
              <input type="text" name="cargo_item_description" value="{{ old('cargo_item_description', $cargo_item->cargo_item_description) }}"  required>
            </div>
          </div>

          <div class="form-col">
            <div class="form-group">
              <label>Freight <span class="text-danger">*</span></label>
              <input type="number" name="cargo_item_freight" value="{{ old('cargo_item_freight', $cargo_item->cargo_item_freight) }}"  required>
            </div>
          </div>

        </div>

        <div class="form-row"> <!-- Second Row -->
          <div class="form-col">
            <div class="form-group inline-radio">
              <label class="inline-label">
                With Measurement Range? <span class="text-danger">*</span>
              </label>

              <div class="radio-group">
                <label class="radio-option">
                  <input type="radio" name="cargo_item_measure_required" value="Yes"
                    {{ $cargo_item->cargo_item_measure_required == 'Yes' ? 'checked' : '' }} required>
                  <span>Yes</span>
                </label>

                <label class="radio-option">
                  <input type="radio" name="cargo_item_measure_required" value="No"
                    {{ $cargo_item->cargo_item_measure_required == 'No' ? 'checked' : '' }}>
                  <span>No</span>
                </label>
              </div>
            </div>
          </div>
        </div>

        {{-- Measurement Range Section --}}
        <div id="measurement-section" style="{{ $cargo_item->cargo_item_measure_required == 'Yes' ? 'display:block;' : 'display:none;' }}">
          <div class="form-row">
            <div class="form-col">
              <div class="form-group">
                <label>Min Length</label>
                <input type="number" step="0.01" name="cargo_item_min_length" 
                       value="{{ old('cargo_item_min_length', $cargo_item->cargo_item_min_length) }}">
              </div>
            </div>

            <div class="form-col">
              <div class="form-group">
                <label>Min Width</label>
                <input type="number" step="0.01" name="cargo_item_min_width" 
                       value="{{ old('cargo_item_min_width', $cargo_item->cargo_item_min_width) }}">
              </div>
            </div>

            <div class="form-col">
              <div class="form-group">
                <label>Min Height</label>
                <input type="number" step="0.01" name="cargo_item_min_height" 
                       value="{{ old('cargo_item_min_height', $cargo_item->cargo_item_min_height) }}">
              </div>
            </div>

          </div>

          {{-- Max LWH --}}
          <div class="form-row">
            <div class="form-col">
              <div class="form-group">
                <label>Max Length</label>
                <input type="number" step="0.01" name="cargo_item_max_length" 
                       value="{{ old('cargo_item_max_length', $cargo_item->cargo_item_max_length) }}">
              </div>
            </div>

            <div class="form-col">
              <div class="form-group">
                <label>Max Width</label>
                <input type="number" step="0.01" name="cargo_item_max_width" 
                       value="{{ old('cargo_item_max_width', $cargo_item->cargo_item_max_width) }}">
              </div>
            </div>

            <div class="form-col">
              <div class="form-group">
                <label>Max Height</label>
                <input type="number" step="0.01" name="cargo_item_max_height" 
                       value="{{ old('cargo_item_max_height', $cargo_item->cargo_item_max_height) }}">
              </div>
            </div>

            <div class="form-col">
              <div class="form-group">
                <label>Unit</label>
                <select name="measurement_unit_id">
                  <option value="">Select Unit</option>
                  @foreach ($measurement_units as $unit)
                    <option value="{{ $unit->measurement_unit_id }}"
                      {{ $cargo_item->measurement_unit_id == $unit->measurement_unit_id ? 'selected' : '' }}>
                      {{ $unit->measurement_unit_name }}
                    </option>
                  @endforeach
                </select>
              </div>
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

<script>
document.addEventListener('DOMContentLoaded', function () {
  const radios = document.querySelectorAll('input[name="cargo_item_measure_required"]');
  const section = document.getElementById('measurement-section');

  radios.forEach(radio => {
    radio.addEventListener('change', function () {
      if (this.value === 'Yes') {
        section.style.display = 'block';
        section.querySelectorAll('input, select').forEach(el => el.required = true);
      } else {
        section.style.display = 'none';
        section.querySelectorAll('input, select').forEach(el => {
          el.required = false;
          el.value = '';
        });
      }
    });
  });
});
</script>