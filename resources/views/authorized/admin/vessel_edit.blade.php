@extends('layouts.app')
@section('page-title', 'VESSEL')
@section('content')
  @include('components.authHeader')
  @include('components.admin_nav')

  <div class="admin-body">
    <div class="avl-title">
      <h3>EDIT VESSEL</h3>
    </div>

    <div class="acs-form_container">
      @if (session('success'))
        <div class="alert alert-success" style="color: green; text-align: center; margin-bottom: 1rem;">
          {{ session('success') }}
        </div>
      @endif

      <form action="{{ route('admin.vessel_update', $vessel->vessel_id) }}" method="POST" enctype="multipart/form-data" id="editVesselForm">
        @csrf

        <!-- ROW 1: CODE + NAME + CAPACITY -->
        <div class="form-row">

          <div class="form-col">
            <div class="form-group">
              <label for="vessel_code">Vessel Code:</label>
              <input type="text" id="vessel_code" name="vessel_code" value="{{ $vessel->vessel_code }}" required>
            </div>
          </div>

          <div class="form-col">
            <div class="form-group">
              <label for="vessel_name">Vessel Name:</label>
              <input type="text" id="vessel_name" name="vessel_name" value="{{ $vessel->vessel_name }}" required>
            </div>
          </div>

          <div class="form-col">
            <div class="form-group">
              <label for="vessel_total_passenger_capacity">Total Passenger Capacity:</label>
             <input type="number" value="{{ $vessel->vessel_total_passenger_capacity }}" disabled> 
            </div>
          </div>
        </div>

        <!-- ROW 2: HATCHES -->
        <div class="form-row">
          <div class="form-col">
            <div class="form-group">
              <label class="ha-label">Hatches</label>
              <div id="hatch-container">
                @foreach ($vessel->hatches as $index => $hatch)
                  <div class="hatch-row">
                    
                    <div class="form-group hatch-input">
                      <label>Label</label>
                      <input type="text" 
                            name="hatches[{{ $index }}][label]" 
                            value="{{ $hatch->hatch_label }}" 
                            placeholder="Hatch Label" required>
                    </div>
                    
                    <div class="form-group hatch-input">
                      <label>Length (m)</label>
                      <input type="number" 
                        name="hatches[{{ $index }}][length]" 
                        value="{{ $hatch->hatch_length }}" 
                        placeholder="Length (m)" step="0.01" inputmode="decimal" required>
                    </div>

                    <div class="form-group hatch-input">
                      <label>Width (m)</label>
                      <input type="number" 
                        name="hatches[{{ $index }}][width]" 
                        value="{{ $hatch->hatch_width }}" 
                        placeholder="Width (m)" step="0.01" inputmode="decimal" required>
                    </div>

                    <div class="form-group hatch-input">
                      <label>Height (m)</label>
                      <input type="number" 
                        name="hatches[{{ $index }}][height]" 
                        value="{{ $hatch->hatch_height }}" 
                        placeholder="Height (m)" step="0.01" inputmode="decimal" required>
                    </div>

                    <div class="form-group hatch-input">
                      <label>Weight Capacity (%)</label>
                      <input type="number" 
                          name="hatches[{{ $index }}][weight_capacity]" 
                          value="{{ $hatch->hatch_weight_capacity }}" 
                          placeholder="Weight Capacity (%)" step="0.01" inputmode="decimal">
                    </div>

                    <div class="form-group hatch-input">
                      <label>Area Capacity (m³)</label>
                      <input type="number" 
                          name="hatches[{{ $index }}][area_capacity]" 
                          value="{{ $hatch->hatch_area_capacity }}" 
                          placeholder="Area Capacity (m³)" step="0.01" inputmode="decimal" required>
                    </div>

                    <div class="form-group hatch-input">
                      <label>Capacity per Hold (Tons)</label>
                      <input type="number" 
                          name="hatches[{{ $index }}][capacity_per_hold]" 
                          value="{{ $hatch->hatch_capacity_per_hold }}" 
                          placeholder="Capacity Per Hold (Tons)" step="0.01" inputmode="decimal" required>
                    </div>

                    <button type="button" class="hatch-btn add-hatch">+</button>
                  </div>
                @endforeach

                @if ($vessel->hatches->isEmpty())
                  <div class="hatch-row">

                    <div class="form-group hatch-input">
                      <label>Label</label>
                      <input type="text" name="hatches[0][label]" placeholder="Hatch Label" required>
                    </div>

                    <div class="form-group hatch-input">
                      <label>Length (m)</label>
                      <input type="number" name="hatches[0][length]" placeholder="Length (m)" step="0.01" inputmode="decimal" required>
                    </div>

                    <div class="form-group hatch-input">
                      <label>Width (m)</label>
                      <input type="number" name="hatches[0][width]" placeholder="Width (m)" step="0.01" inputmode="decimal" required>
                    </div>

                    <div class="form-group hatch-input">
                      <label>Height (m)</label>
                      <input type="number" name="hatches[0][height]" placeholder="Height (m)" step="0.01" inputmode="decimal" required>
                    </div>

                    <div class="form-group hatch-input">
                      <label> Capacity (%)</label>
                      <input type="number" name="hatches[0][weight_capacity]" placeholder="Weight Capacity (%)" step="0.01" inputmode="decimal">
                    </div>

                    <div class="form-group hatch-input">
                      <label>Area Capacity (m³)</label>
                      <input type="number" name="hatches[0][area_capacity]" placeholder="Area Capacity (m³)" step="0.01" inputmode="decimal" required>
                    </div>

                    <div class="form-group hatch-input">
                      <label>Capacity per Hold (Tons)</label>
                      <input type="number" name="hatches[0][capacity_per_hold]" placeholder="Capacity Per Hold (Tons)" step="0.01" inputmode="decimal" required>
                    </div>


                    
                    
                    
                    
                    
                    
                    

                    <button type="button" class="hatch-btn add-hatch">+</button>
                  </div>
                @endif

              </div>

            </div>
          </div>
        </div>


        <!--ROW 3: ACCOMMODATIONS-->
        <div class="form-row">
          <div class="form-col">
            <label class="ha-label">Accommodations</label>
            <div id="accommodation-container">
              @foreach ($vessel->accommodations as $index => $acc)
                <div class="accommodation-row">
                  <div class="form-group acc-input">
                    <label>Name</label>
                    <input type="text" name="accommodations[{{ $index }}][name]" value="{{ $acc->accommodation_name }}" placeholder="Accommodation Name" required>
                  </div>
                  <div class="form-group acc-input">
                    <label>Regular Price</label>
                    <input type="number" name="accommodations[{{ $index }}][price]" value="{{ $acc->accommodation_regular_price }}" placeholder="Regular Price" step="0.01" inputmode="decimal" required>
                  </div>
                  <div class="form-group acc-input">
                    <label>Cot Range</label>
                    <input type="number" name="accommodations[{{ $index }}][capacity]" value="{{ $acc->accommodation_capacity }}" placeholder="Capacity" required>
                  </div>
                  
                  <button type="button" class="accommodation-btn add-accommodation">+</button>
                </div>
              @endforeach
              @if ($vessel->accommodations->isEmpty())
                <div class="accommodation-row">
                  <input type="text" name="accommodations[0][name]" placeholder="Accommodation Name" required>
                  <input type="number" name="accommodations[0][price]" placeholder="Regular Price" step="0.01" inputmode="decimal" required>
                  <input type="number" name="accommodations[0][capacity]" placeholder="Accommodation Capacity" required>
                  <button type="button" class="accommodation-btn add-accommodation">+</button>
                </div>
              @endif
            </div>
          </div>
        </div>

        <!-- ROW 4: COT PLAN + STATUS -->
        <div class="form-row" style="display: flex; gap: 2rem; align-items: flex-start; width: 100%;">
          <!-- Cot Plan -->
          <div class="form-col" style="flex: 1;">
            <div class="form-group vcot-plan" style="display: flex; flex-direction: column; align-items: flex-start; width: 100%;">
              <label for="vessel_cot_plan_url" style="margin-bottom: 8px;">Cot Plan:</label>

              @if ($vessel->vessel_cot_plan_url)
                <img 
                  src="{{ asset('storage/'.$vessel->vessel_cot_plan_url) }}" 
                  alt="Cot Plan" 
                  style="width: 100%; height: auto; max-height: 250px; object-fit: contain; border: 1px solid #ccc; border-radius: 8px; margin-bottom: 10px; background-color: #f9f9f9;">
              @else
                <p style="color: #888; font-style: italic; margin-bottom: 10px;">No cot plan uploaded yet.</p>
              @endif

              <input 
                type="file" 
                id="vessel_cot_plan_url" 
                name="vessel_cot_plan_url" 
                accept="image/*"
                style="width: 100%; margin-top: 5px;">
            </div>
          </div>

          <!-- Status -->
          <div class="form-col" style="flex: 1;">
            <div class="form-group" style="width: 100%;">
              <label for="vessel_status" style="margin-bottom: 8px;">Status:</label>
              <select 
                name="vessel_status" 
                id="vessel_status" 
                required 
                style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 8px;">
                <option value="Active" {{ $vessel->vessel_status === 'Active' ? 'selected' : '' }}>Active</option>
                <option value="Inactive" {{ $vessel->vessel_status === 'Inactive' ? 'selected' : '' }}>Inactive</option>
              </select>
            </div>
          </div>

        </div>

        <!-- ACTION BUTTONS -->
        <div class="form-actions" style="display: flex; gap: 1rem; justify-content: center; margin-top: 1.5rem;">
          <button type="submit" class="acs-add-btn">
            <i class="fa-solid fa-save me-2"></i>Update Vessel
          </button>
          <a href="{{ route('admin.vessel_list') }}" class="acs-add-btn acs-cancel-btn">
            <i class="fa-solid fa-xmark me-2"></i>Cancel
          </a>
        </div>

      </form>
    </div>
  </div>

  <script src="{{ asset('js/vessel.js') }}"></script>
@endsection
