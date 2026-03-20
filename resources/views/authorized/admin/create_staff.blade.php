@extends('layouts.app')
@section('page-title', 'CREATE STAFF MEMBER')
@section('content')
    @include('components.authHeader') {{-- HEADER --}}
    @include('components.admin_nav') {{-- NAVBAR --}}

<div class="admin-body">

  <div class="acs-form_container">
    <!-- Floating Toast Container - Below navbar on the right side -->
        <div class="toast-container position-fixed p-3" style="z-index: 9999; top: 80px; right: 20px;">
            @if (session('success'))
                <div class="toast align-items-center text-white bg-success border-0 show" role="alert" aria-live="assertive"
                    aria-atomic="true" id="successToast">
                    <div class="d-flex">
                        <div class="toast-body">
                            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
                            aria-label="Close"></button>
                    </div>
                </div>
            @endif
            @if ($errors->any())
                <div class="toast align-items-center text-white bg-danger border-0 show" role="alert"
                    aria-live="assertive" aria-atomic="true" id="errorToast">
                    <div class="d-flex">
                        <div class="toast-body">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            @foreach ($errors->all() as $error)
                                {{ $error }}
                            @endforeach
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
                            aria-label="Close"></button>
                    </div>
                </div>
            @endif
        </div>

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