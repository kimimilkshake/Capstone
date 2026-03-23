@extends('layouts.app')
@section('page-title', 'EDIT VOYAGE')
@section('content')
  @include('components.authHeader')
  @include('components.admin_nav')

  <div class="admin-body">

    <div class="acs-form_container">

      <form action="{{ route('admin.voyage_update', $voyage->voyage_id) }}" method="POST" enctype="multipart/form-data" id="editVoyageForm">
        @csrf
        @method('PUT')

        @php
          $isLocked = in_array($voyage->voyage_status, ['Completed', 'Cancelled']);
          $isCancelled = $voyage->voyage_status === 'Cancelled';
        @endphp

        <div class="voyage_code">
          <h3>VOYAGE CODE: {{ $voyage->voyage_code }}</h3>
        </div>

        @if($isCancelled)
          <p style="color:red; font-weight:600;">
            This voyage is cancelled. Only actual times can be edited.
          </p>
        @endif

        <!-- ROW 1: ROUTE, PORT, VESSEL -->
        <div class="form-row">
          <div class="form-col">
            <div class="form-group">
              <label for="route_port_id">Route <span class="text-danger">*</span></label>
              <select name="route_port_id" id="route_port_id" required @if($isLocked) disabled @endif>
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
              <select name="vessel_id" id="vessel_id" required @if($isLocked) disabled @endif>
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
              <input 
                type="date" 
                id="voyage_departure_date" 
                name="voyage_departure_date" 
                value="{{ $voyage->voyage_departure_date }}"
                @if($isLocked) disabled @endif
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
                @if($isLocked) disabled @endif
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
              <input 
                type="date" 
                id="voyage_arrival_date" 
                name="voyage_arrival_date" 
                value="{{ $voyage->voyage_arrival_date }}" 
                @if($isLocked) disabled @endif
                min="{{ $voyage->voyage_departure_date }}" 
                max="{{ \Carbon\Carbon::today()->addDays(14)->format('Y-m-d') }}"
                required>
            </div>
          </div>

          <div class="form-col">
            <div class="form-group">
              <label for="voyage_estimated_TA">Estimated Time of Arrival (ETA) <span class="text-danger">*</span></label>
              <input 
                type="time" 
                id="voyage_estimated_TA" 
                name="voyage_estimated_TA" 
                value="{{ $voyage->voyage_estimated_TA }}" 
                @if($isLocked) disabled @endif
                required>
            </div>
          </div>

          <div class="form-col">
            <div class="form-group">
              <label for="voyage_actual_TA">Actual Time of Arrival (ATA)</label>
              <input 
                type="time" 
                id="voyage_actual_TA" 
                name="voyage_actual_TA" 
                value="{{ $voyage->voyage_actual_TA }}">
            </div>
          </div>
        </div>

        <!-- ROW 5: DESCRIPTION -->
        <div class="form-row">
          <div class="form-group" style="width: 100%;">
            <label for="voyage_description">Voyage Description</label>
            <textarea id="voyage_description" name="voyage_description" placeholder="Enter voyage description here">{{ $voyage->voyage_description }}</textarea>
          </div>
        </div>

        <!-- ROW 6: STATUS + ACTIONS -->
        <div class="form-row" style="display: flex; justify-content: space-between; align-items: center; margin-top: 1.5rem;">
          <div class="form-group" style="flex: 0 0 250px;">
            <label for="voyage_status">Status <span class="text-danger">*</span></label>
            <select id="voyage_status" name="voyage_status" required @if($isCancelled) disabled @endif>
              <option value="Scheduled" {{ $voyage->voyage_status == 'Scheduled' ? 'selected' : '' }}>Scheduled</option>
              <option value="At Sea" {{ $voyage->voyage_status == 'At Sea' ? 'selected' : '' }}>At Sea</option>
              <option value="Completed" {{ $voyage->voyage_status == 'Completed' ? 'selected' : '' }}>Completed</option>
              <option value="Cancelled" {{ $voyage->voyage_status == 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
              <option value="Archived" {{ $voyage->voyage_status == 'Archived' ? 'selected' : '' }}>Archived</option>
            </select>

            @if($isCancelled)
                <input type="hidden" name="voyage_status" value="Cancelled">
            @endif
          </div>

          <div class="form-actions" style="display: flex; gap: 1rem;">
            <button type="submit" class="acs-add-btn" id="saveEditBtn">
              <i class="fa-solid fa-save me-2"></i>Save Changes
            </button>
            <a href="{{ route('admin.voyage_list') }}" class="acs-add-btn acs-cancel-btn">
              <i class="fa-solid fa-xmark me-2"></i>Cancel
            </a>
          </div>
        </div>

      </form>
    </div>
  </div>
  <script>
    const depDateInput = document.getElementById('voyage_departure_date');
    const arrDateInput = document.getElementById('voyage_arrival_date');
    const etdInput = document.getElementById('voyage_estimated_TD');
    const etaInput = document.getElementById('voyage_estimated_TA');

    // normalize date (remove time)
    function normalizeDate(d) {
        const date = new Date(d);
        date.setHours(0,0,0,0);
        return date;
    }

    // Instant validation for dates
    function validateDatesInstant() {
        if (!depDateInput.value || !arrDateInput.value) return;

        const depDate = normalizeDate(depDateInput.value);
        const arrDate = normalizeDate(arrDateInput.value);

        if (arrDate < depDate) {
            showToast('Arrival date cannot be earlier than departure date.', 'danger');
            arrDateInput.value = depDateInput.value; // set to departure date instead of empty
        }
    }

    // Instant validation for time (if same day)
    function validateTimeInstant() {
        if (!depDateInput.value || !arrDateInput.value) return;
        if (!etdInput.value || !etaInput.value) return;

        const depDate = normalizeDate(depDateInput.value);
        const arrDate = normalizeDate(arrDateInput.value);

        if (depDate.getTime() === arrDate.getTime()) {
            if (etaInput.value <= etdInput.value) {
                showToast('ETA must be later than ETD if same day.', 'danger');
                etaInput.value = '';
            }
        }
    }

    // Modified setArrivalMin for instant prompt
    function setArrivalMin(depDate) {
        const arrival = arrDateInput;
        if(depDate) {
            arrival.min = depDate;

            if(arrival.value && normalizeDate(arrival.value) < normalizeDate(depDate)) {
                showToast('Arrival date must be on or after the departure date.', 'danger');
                arrival.value = depDate; // reset to departure date
            }
        }
    }

    // Event listeners
    depDateInput.addEventListener('change', () => {
        setArrivalMin(depDateInput.value);
        validateDatesInstant();
        validateTimeInstant();
    });

    arrDateInput.addEventListener('change', () => {
        validateDatesInstant();
        validateTimeInstant();
    });

    etdInput.addEventListener('change', validateTimeInstant);
    etaInput.addEventListener('change', validateTimeInstant);
  </script>

@endsection
