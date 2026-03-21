@extends('layouts.app')
@section('page-title', 'EDIT VOYAGE')
@section('content')
  @include('components.authHeader')
  @include('components.staff_nav')
  <div class="staff-body">

    <div class="acs-form_container">
      
      <form action="{{ route('staff.voyage_update', $voyage->voyage_id) }}" method="POST" enctype="multipart/form-data" id="editVoyageForm">
        @csrf
        @method('PUT')

        <div class="voyage_code">
          <h3>VOYAGE CODE: {{ $voyage->voyage_code }}</h3>
        </div>

        <!-- ROW 1: ROUTE, PORT, VESSEL -->
        <div class="form-row">
          <div class="form-col">
            <div class="form-group">
              <label for="route_port_id">Route <span class="text-danger">*</span></label>
              <select name="route_port_id" id="route_port_id" required @if($isCompleted) disabled @endif>
                @foreach($route_port as $rp)
                  <option value="{{ $rp->route_port_id }}" 
                    {{ $voyage->route_port_id == $rp->route_port_id ? 'selected' : '' }}>
                    {{ $rp->route_origin }} → {{ $rp->route_destination }}
                  </option>
                @endforeach
              </select>
            </div>
          </div>

          <div class="form-col">
            <div class="form-group">
              <label for="vessel_id">Vessel <span class="text-danger">*</span></label>
              <select name="vessel_id" id="vessel_id" required @if($isCompleted) disabled @endif>
                @foreach($vessels as $vessel)
                  <option value="{{ $vessel->vessel_id }}" 
                    {{ $voyage->vessel_id == $vessel->vessel_id ? 'selected' : '' }}>
                    {{ $vessel->vessel_name }}
                  </option>
                @endforeach
              </select>
            </div>
          </div>
        </div>

        <!--ROW 2: PORT OF ORIGIN AND PORT OF DESTINATION-->
        <div class="form-row">
            <div class="form-col">
                <div class="form-group">
                    <label for="port_origin_info">Port of Origin</label>
                    <input id="port_origin_info" type="text" placeholder="Port Origin Name, City, Province" disabled>
                </div>
            </div>

            <div class="form-col">
                <div class="form-group">
                    <label for="port_destination_info">Port of Destination</label>
                    <input id="port_destination_info" type="text" placeholder="Port Destination Name, City, Province" disabled>
                </div>
            </div>
        </div>

        <!-- ROW 3: DEPARTURE DATE, ETD, ATD -->
        <div class="form-row">
          <div class="form-col">
            <div class="form-group">
              <label for="voyage_departure_date">Departure Date <span class="text-danger">*</span></label>
              <input type="date" id="voyage_departure_date" name="voyage_departure_date" 
                value="{{ $voyage->voyage_departure_date }}" 
                @if($isCompleted) disabled @endif
                min="{{ \Carbon\Carbon::today()->format('Y-m-d') }}"
                max="{{ \Carbon\Carbon::today()->addDays(8)->format('Y-m-d') }}"
                onchange="setArrivalMin(this.value)"
                required>
            </div>
          </div>

          <div class="form-col">
            <div class="form-group">
              <label for="voyage_estimated_TD">Estimated Time of Departure (ETD) <span class="text-danger">*</span></label>
              <input type="time" id="voyage_estimated_TD" name="voyage_estimated_TD" 
                value="{{ $voyage->voyage_estimated_TD }}" 
                @if($isCompleted) disabled @endif
                required>
            </div>
          </div>

          <div class="form-col">
            <div class="form-group">
              <label for="voyage_actual_TD">Actual Time of Departure (ATD)</label>
              <input type="time" id="voyage_actual_TD" name="voyage_actual_TD" 
                value="{{ $voyage->voyage_actual_TD }}">
            </div>
          </div>
        </div>

        <!-- ROW 4: ARRIVAL DATE, ETA, ATA -->
        <div class="form-row">
          <div class="form-col">
            <div class="form-group">
              <label for="voyage_arrival_date">Arrival Date <span class="text-danger">*</span></label>
              <input type="date" id="voyage_arrival_date" name="voyage_arrival_date" 
                value="{{ $voyage->voyage_arrival_date }}" 
                @if($isCompleted) disabled @endif
                min="{{ $voyage->voyage_departure_date }}" 
                max="{{ \Carbon\Carbon::today()->addDays(14)->format('Y-m-d') }}"
                required>
            </div>
          </div>

          <div class="form-col">
            <div class="form-group">
              <label for="voyage_estimated_TA">Estimated Time of Arrival (ETA) <span class="text-danger">*</span></label>
              <input type="time" id="voyage_estimated_TA" name="voyage_estimated_TA" 
                value="{{ $voyage->voyage_estimated_TA }}" 
                @if($isCompleted) disabled @endif
                required>
            </div>
          </div>

          <div class="form-col">
            <div class="form-group">
              <label for="voyage_actual_TA">Actual Time of Arrival (ATA)</label>
              <input type="time" id="voyage_actual_TA" name="voyage_actual_TA" 
                value="{{ $voyage->voyage_actual_TA }}">
            </div>
          </div>
        </div>

        <!-- ROW 5: DESCRIPTION -->
        <div class="form-row">
          <div class="form-group" style="width: 100%;">
            <label for="voyage_description">Voyage Description <span class="text-danger">*</span></label>
            <textarea id="voyage_description" name="voyage_description" placeholder="Enter voyage description here">{{ $voyage->voyage_description }}</textarea>
          </div>
        </div>

        <!-- ROW 6: STATUS + ACTIONS -->
        <div class="form-row" style="display: flex; justify-content: space-between; align-items: center; margin-top: 1.5rem;">
          <div class="form-group" style="flex: 0 0 250px;">
            <label for="voyage_status">Status</label>
            <select id="voyage_status" name="voyage_status" required>
              <option value="Scheduled" {{ $voyage->voyage_status == 'Scheduled' ? 'selected' : '' }}>Scheduled</option>
              <option value="At Sea" {{ $voyage->voyage_status == 'At Sea' ? 'selected' : '' }}>At Sea</option>
              <option value="Completed" {{ $voyage->voyage_status == 'Completed' ? 'selected' : '' }}>Completed</option>
              <option value="Cancelled" {{ $voyage->voyage_status == 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
              <option value="Archived" {{ $voyage->voyage_status == 'Archived' ? 'selected' : '' }}>Archived</option>
            </select>
          </div>

          <div class="form-actions" style="display: flex; gap: 1rem;">
            <button type="submit" class="acs-add-btn update-btn" id="saveEditBtn">
              <i class="fa-solid fa-save me-2"></i>Save Changes
            </button>
            <a href="{{ route('staff.voyage_list') }}" class="acs-add-btn acs-cancel-btn">
              <i class="fa-solid fa-xmark me-2"></i>Cancel
            </a>
          </div>
        </div>

      </form>
    </div>
  </div>

  <script>
    function setArrivalMin(depDate) {
        const arrival = document.getElementById('voyage_arrival_date');
        if(depDate) {
            arrival.min = depDate;
            if(arrival.value < depDate) {
                arrival.value = depDate;
            }
        }
    }
  </script>
@endsection