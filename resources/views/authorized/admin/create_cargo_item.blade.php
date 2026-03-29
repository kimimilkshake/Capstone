@extends('layouts.app')
@section('page-title', 'CREATE CARGO ITEM')
@section('content')
  @include('components.authHeader')
  @include('components.admin_nav')

  <div class="admin-body">
    
    <div class="aci-form_container">

      <form action="{{ route('admin.store_cargo_item') }}" method="POST" class="create-cargoitem-form">
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

        </div> <!-- End of First Row -->
          
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
                <input type="number" step="0.01" min="0" name="cargo_item_min_length" required>
              </div>
            </div>

            <div class="form-col">
              <div class="form-group">
                <label>Min Width</label>
                <input type="number" step="0.01" min="0" name="cargo_item_min_width" requried>
              </div>
            </div>

            <div class="form-col">
              <div class="form-group">
                <label>Min Height</label>
                <input type="number" step="0.01" min="0" name="cargo_item_min_height" required>
              </div>
            </div>

            {{-- MAX LWH --}}
              <div class="form-col">
                <div class="form-group">
                  <label>Max Length</label>
                  <input type="number" step="0.01" min="0" name="cargo_item_max_length" required>
                </div>
              </div>

              <div class="form-col">
                <div class="form-group">
                  <label>Max Width</label>
                  <input type="number" step="0.01" min="0" name="cargo_item_max_width" required>
                </div>
              </div>

              <div class="form-col">
                <div class="form-group">
                  <label>Max Height</label>
                  <input type="number" step="0.01" min="0" name="cargo_item_max_height" required>
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

        {{-- BASE CBM SECTION --}}
        <div id="base-cbm-section" style="display: none;">
          <div class="form-row">
            <div class="form-col">
              <div class="form-group">
                <label>Base CBM </label>
                <input type="number" step="0.01" min="0" name="cargo_item_base_cbm" style="width: 150px" required>
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
    const measurementSection = document.getElementById('measurement-section');
    const baseCbmSection = document.getElementById('base-cbm-section');

    radios.forEach(radio => {
      radio.addEventListener('change', function () {

        if (this.value === 'Yes') {
          // SHOW measurement
          measurementSection.style.display = 'block';
          baseCbmSection.style.display = 'none';

          // make measurement required
          measurementSection.querySelectorAll('input, select').forEach(el => {
            el.required = true;
          });

          // remove required + clear base cbm
          baseCbmSection.querySelectorAll('input').forEach(el => {
            el.required = false;
            el.value = '';
          });

        } else {
          // SHOW base CBM
          measurementSection.style.display = 'none';
          baseCbmSection.style.display = 'block';

          // remove required + clear measurement
          measurementSection.querySelectorAll('input, select').forEach(el => {
            el.required = false;
            el.value = '';
          });

          // make base cbm required
          baseCbmSection.querySelectorAll('input').forEach(el => {
            el.required = true;
          });
        }

      });
    });
  });
</script>
