@extends('layouts.app')
@section('page-title', 'CREATE STAFF MEMBER')
@section('content')
    @include('components.authHeader') {{-- HEADER --}}
    @include('components.admin_nav') {{-- NAVBAR --}}

<div class="admin-body">

  <div class="acs-form_container">
  
    <form action="{{ route('admin.storeStaff') }}" method="POST" class="create-staff-form">
      @csrf
      <div class="form-row">
        <div class="form-col">
          <div class="form-group">
            <label for="staff_name">Full Name <span class="text-danger">*</span></label>
            <input type="text" id="staff_name" name="staff_name" required>
          </div>

          <div class="form-group">
            <label for="staff_gender">Gender <span class="text-danger">*</span></label>
            <select id="staff_gender" name="staff_gender" required>
              <option value="">Select</option>
              <option value="M">Male</option>
              <option value="F">Female</option>
            </select>
          </div>

          <div class="form-group">
            <label for="staff_dob">Date of Birth <span class="text-danger">*</span></label>
            <input type="date" id="staff_dob" name="staff_dob" required>
          </div>
        </div>

        <!-- Column 2 -->
        <div class="form-col">
          <div class="form-group">
            <label for="staff_email">Email <span class="text-danger">*</span></label>
            <input type="email" id="staff_email" name="staff_email" required>
          </div>

          <div class="form-group">
            <label for="staff_user">Username <span class="text-danger">*</span></label>
            <input type="text" id="staff_user" name="staff_user" required>
          </div>

          <div class="form-group">
            <label for="staff_password">Password <span class="text-danger">*</span></label>
            <input type="password" id="staff_password" name="staff_password" required>
          </div>
        </div>
      </div>

      <div class="form-actions">
        <button type="submit" class="acs-add-btn"><i class="fa-solid fa-plus me-2"></i>Add Staff</button>
      </div>
    </form>
  </div>
  
</div>

@endsection