@extends('layouts.app')
@section('page-title', 'VESSEL')
@section('content')
  @include('components.authHeader')
  @include('components.admin_nav')

  <div class="admin-body">
    <div class="avl-title">
      <h3>CREATE VESSEL</h3>
    </div>
    <div class="acs-form_container">
      @if ($errors->any())
        <div class="alert alert-danger" style="color: red; text-align: center;">
          <strong>All fields are required.</strong><br>
          @foreach ($errors->all() as $error)
            {{ $error }}<br>
          @endforeach
        </div>
      @endif

      @if (session('success'))
        <div class="alert alert-success" style="color: green; text-align: center; margin-bottom: 1rem;">
          {{ session('success') }}
        </div>
      @endif

      <form action="{{ route('admin.store_vessel') }}" method="POST" enctype="multipart/form-data" id="createVesselForm">
        @csrf

        <!--ROW  1: VESSEL NAME AND PASSENGER CAPACITY-->
        <div class="form-row">

          <div class="form-col">
            <div class="form-group">
              <label for="vessel_code">Vessel Code:</label>
              <input type="text" id="vessel_code" name="vessel_code" placeholder="Enter Vessel Code" required>
            </div>
          </div>

          <div class="form-col">
            <div class="form-group">
              <label for="vessel_name">Vessel Name:</label>
              <input type="text" id="vessel_name" name="vessel_name" placeholder="Enter Vessel Name" required>
            </div>
          </div>

          <div class="form-col">
            <div class="form-group">
              <label for="vessel_total_passenger_capacity">Passenger Capacity:</label>
              <input type="number" id="vessel_total_passenger_capacity" name="vessel_total_passenger_capacity" placeholder="Enter Passenger Capacity" required>
            </div>
          </div>
        </div>

        <!--ROW 2: HATCH AND ACCOMMODATION-->
        <div class="form-row">
          <!-- Hatch Section -->
          <div class="form-col">
            <label class="ha-label">Hatches</label>
            <div id="hatch-container">
              <div class="hatch-row">
                <input type="text" name="hatches[0][label]" placeholder="Hatch Label" required>
                <input type="number" name="hatches[0][capacity]" placeholder="Capacity in Cubic Meters" required>
                <button type="button" class="hatch-btn add-hatch">+</button>
              </div>
            </div>
          </div>

          <!-- Accommodation Section -->
          <div class="form-col">
            <label class="ha-label">Accommodations</label>
            <div id="accommodation-container">
              <div class="accommodation-row">
                <input type="text" name="accommodations[0][name]" placeholder="Accommodation Name" required>
                <input type="number" name="accommodations[0][price]" placeholder="Regular Price" required>
                <button type="button" class="accommodation-btn add-accommodation">+</button>
              </div>
            </div>
          </div>
        </div>


        <div class="form-row">
          <div class="form-group vcot-plan">
            <label for="vessel_cot_plan_url">Cot Plan:</label>
            <input type="file" id="vessel_cot_plan_url" name="vessel_cot_plan_url" accept="image/*">
          </div>
        </div>

        <div class="form-actions" style="display: flex; gap: 1rem; justify-content: center; margin-top: 1.5rem;">
          <button type="submit" class="acs-add-btn">
            <i class="fa-solid fa-plus me-2"></i>ADD
          </button>
          <a href="{{ route('admin.vessel_list') }}" class="acs-add-btn acs-cancel-btn">
              <i class="fa-solid fa-xmark me-2"></i>CANCEL
            </a>
        </div>

      </form>

    </div>
  </div>
  
  
@endsection