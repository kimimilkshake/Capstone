@extends('layouts.app')
@section('page-title', 'CREATE CARGO ITEM')
@section('content')
  @include('components.authHeader')
  @include('components.staff_nav')
  <div class="staff-body">

    <div class="aci-form_container">

      <form action="{{ route('staff.store_cargo_item') }}" method="POST" class="create-cargoitem-form">
        @csrf

        <div class="form-row"> <!-- First Row -->

          <div class="form-col">
            <div class="form-group">
              <label>Route Category <span class="text-danger">*</span></label>
              <select name="route_category_id" required>
                <option value="">Select Route Category</option>
                @foreach ($route_categories as $routeCategory)
                  <option value="{{ $routeCategory->route_category_id }}">
                    {{ $routeCategory->route_category_name }}
                  </option>
                @endforeach
              </select>
            </div>
          </div>

          <div class="form-col">
            <div class="form-group">
              <label>Cargo Category <span class="text-danger">*</span></label>
              <select name="cargo_category_id" required>
                <option value="">Select Category</option>
                @foreach ($cargo_categories as $category)
                  <option value="{{ $category->cargo_category_id }}">
                    {{ $category->cargo_category_name }}
                  </option>
                @endforeach
              </select>
            </div>
          </div>
          
          <div class="form-col">
            <div class="form-group">
              <label>Description <span class="text-danger">*</span></label>
              <input type="text" name="cargo_item_description" required>
            </div>
          </div>

          <div class="form-col">
            <div class="form-group">
              <label>Freight <span class="text-danger">*</span></label>
              <input type="number" name="cargo_item_freight" step="0.01" min="0" required>
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
                  <input type="radio" name="cargo_item_measure_required" value="Yes" required>
                  <span>Yes</span>
                </label>

                <label class="radio-option">
                  <input type="radio" name="cargo_item_measure_required" value="No">
                  <span>No</span>
                </label>
              </div>
            </div>
          </div>
        </div>

        {{-- Measurement Range Section --}}
        <div id="measurement-section" style="display: none;">

        {{-- MIN AND MAX LWH --}}
          {{-- MIN LWH --}}
          <div class="form-row">
            <div class="form-col">
              <div class="form-group">
                <label>Min Length</label>
                <input type="number" step="0.01" min="0" name="cargo_item_min_length">
              </div>
            </div>

            <div class="form-col">
              <div class="form-group">
                <label>Min Width</label>
                <input type="number" step="0.01" min="0" name="cargo_item_min_width">
              </div>
            </div>

            <div class="form-col">
              <div class="form-group">
                <label>Min Height</label>
                <input type="number" step="0.01" min="0" name="cargo_item_min_height">
              </div>
            </div>

            {{-- MAX LWH --}}
            <div class="form-col">
              <div class="form-group">
                <label>Max Length</label>
                <input type="number" step="0.01" min="0" name="cargo_item_max_length">
              </div>
            </div>

            <div class="form-col">
              <div class="form-group">
                <label>Max Width</label>
                <input type="number" step="0.01" min="0" name="cargo_item_max_width">
              </div>
            </div>

            <div class="form-col">
              <div class="form-group">
                <label>Max Height</label>
                <input type="number" step="0.01" min="0" name="cargo_item_max_height">
              </div>
            </div>

            <div class="form-col">
              <div class="form-group">
                <label>Unit</label>
                <select name="measurement_unit_id">
                  <option value="">Select Unit</option>
                  @foreach ($measurement_units as $unit)
                    <option value="{{ $unit->measurement_unit_id }}">
                      {{ $unit->measurement_unit_name }}
                    </option>
                  @endforeach
                </select>
              </div>
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

<script>
document.addEventListener('DOMContentLoaded', function () {
  const radios = document.querySelectorAll('input[name="cargo_item_measure_required"]');
  const section = document.getElementById('measurement-section');

  radios.forEach(radio => {
    radio.addEventListener('change', function () {
      if (this.value === 'Yes') {
        section.style.display = 'block';

        // make inputs required
        section.querySelectorAll('input, select').forEach(el => {
          el.required = true;
        });
      } else {
        section.style.display = 'none';

        // remove required + clear values
        section.querySelectorAll('input, select').forEach(el => {
          el.required = false;
          el.value = '';
        });
      }
    });
  });
});
</script>
