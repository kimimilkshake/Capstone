@extends('layouts.app')
@section('page-title', 'CREATE VOYAGE')
@section('content')
  @include('components.authHeader')
  @include('components.staff_nav')
  <div class="staff-body">

    <div class="scs-form_container">

      {{-- FORM START --}}
      <form action="{{ route('staff.store_voyage') }}" method="POST">
        @csrf

        <!--ROW 1: ROUTE, PORT, AND VESSEL-->
        <div class="form-row">
          {{-- Route Dropdown --}}
          <div class="form-col">
            <div class="form-group">
              <label for="route_port_id">Route <span class="text-danger">*</span></label>
              <select id="route_port_id" name="route_port_id" required>
                <option value="" disabled selected>Select Route</option>
                @foreach($route_port as $rp)
                  <option value="{{ $rp->route_port_id }}">
                    {{ $rp->route_origin }} → {{ $rp->route_destination }}
                  </option>
                @endforeach
              </select>
            </div>
          </div>

          {{-- Vessel Dropdown --}}
          <div class="form-col">
            <div class="form-group">
              <label for="vessel_id">Vessel <span class="text-danger">*</span></label>
              <select id="vessel_id" name="vessel_id" required>
                <option value="" disabled selected>Select Vessel</option>
                @foreach($vessels as $vessel)
                  <option value="{{ $vessel->vessel_id }}">{{ $vessel->vessel_name }}</option>
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

        <!--ROW 3: DEPARTURE DATE AND ARRIVAL DATE-->
        <div class="form-row">
          <div class="form-col">
            <div class="form-group">
              <label for="voyage_departure_date">Departure Date <span class="text-danger">*</span></label>
              <input 
                id="voyage_departure_date" 
                type="date" 
                name="voyage_departure_date" 
                required min="{{ \Carbon\Carbon::today()->format('Y-m-d') }}"
                max="{{ \Carbon\Carbon::today()->addDays(8)->format('Y-m-d') }}"
                onchange="setArrivalMin(this.value)"
              >
            </div>
          </div>

          <div class="form-col">
            <div class="form-group">
              <label for="voyage_arrival_date">Arrival Date <span class="text-danger">*</span></label>
              <input 
                id="voyage_arrival_date" 
                type="date" 
                name="voyage_arrival_date" 
                required 
                min="{{ \Carbon\Carbon::today()->addDay()->format('Y-m-d') }}"
                max="{{ \Carbon\Carbon::today()->addDays(14)->format('Y-m-d') }}"
              >
            </div>
          </div>
        </div>

        <!--ROW 4: ESTIMATED TD AND ESTIMATED TA-->
        <div class="form-row">
          <div class="form-col">
            <div class="form-group">
              <label for="voyage_estimated_TD">Estimated Time of Departure (ETD) <span class="text-danger">*</span></label>
              <input id="voyage_estimated_TD" type="time" name="voyage_estimated_TD" required>
            </div>
          </div>

          <div class="form-col">
            <div class="form-group">
              <label for="voyage_estimated_TA">Estimated Time of Arrival (ETA) <span class="text-danger">*</span></label>
              <input id="voyage_estimated_TA" type="time" name="voyage_estimated_TA" required>
            </div>
          </div>
        </div>

        <!--ACTIONS-->
        <div class="form-actions" style="display: flex; gap: 1rem; justify-content: center; margin-top: 1.5rem;">
          <button type="submit" class="acs-add-btn">
            <i class="fa-solid fa-plus me-2"></i>ADD
          </button>
          <a href="{{ route('staff.voyage_list') }}" class="acs-add-btn acs-cancel-btn">
            <i class="fa-solid fa-xmark me-2"></i>CANCEL
          </a>
        </div>
      </form>
      {{-- FORM END --}}
    </div>
  </div>


  <script>
    const routePorts = {
      @foreach($route_port as $rp)
          "{{ $rp->route_port_id }}": {
              origin: "{{ $rp->port_origin_name }}, {{ $rp->port_origin_city }}, {{ $rp->port_origin_province }}",
              destination: "{{ $rp->port_destination_name }}, {{ $rp->port_destination_city }}, {{ $rp->port_destination_province }}"
          },
      @endforeach
    };

    const routeSelect = document.getElementById('route_port_id');
    const portOriginInput = document.getElementById('port_origin_info');
    const portDestinationInput = document.getElementById('port_destination_info');

    routeSelect.addEventListener('change', function() {
        const selectedId = this.value;

        if(routePorts[selectedId]) {
            portOriginInput.value = routePorts[selectedId].origin;
            portDestinationInput.value = routePorts[selectedId].destination;
        } else {
            portOriginInput.value = '';
            portDestinationInput.value = '';
        }
    });

    function setArrivalMin(depDate) {
        const arrival = document.getElementById('voyage_arrival_date');
        if(depDate) {
            // arrival must be >= departure date
            arrival.min = depDate; 
            
            // optionally reset current arrival value if it's now before departure
            if(arrival.value < depDate) {
                arrival.value = depDate;
            }
        }
    }
  </script>
@endsection