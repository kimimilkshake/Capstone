@extends('layouts.app')
@section('page-title', 'STAFF')
@section('content')
    @include('components.authHeader')
    @include('components.admin_nav')
    <div class="admin-body">
        <div class="asl-title">
          <h3>EDIT STAFF MEMBER</h3>
        </div>

        <div class="acs-form_container">
            @if(session('success'))
                <div class="alert alert-success text-center mx-auto w-75" role="alert">{{ session('success') }}</div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger text-center">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.staff_update', $staff->staff_id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label for="staff_name">Name <span class="text-danger">*</span></label>
                            <input type="text" name="staff_name" id="staff_name" value="{{ old('staff_name', $staff->staff_name) }}" maxlength="50" required>
                            @error('staff_name')
                                <span class="error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="staff_user">Username <span class="text-danger">*</span></label>
                            <input type="text" name="staff_user" id="staff_user" value="{{ old('staff_user', $staff->staff_user) }}" maxlength="10" required>
                            @error('staff_user')
                                <span class="error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="staff_dob">Date of Birth <span class="text-danger">*</span></label>
                            <input type="date" name="staff_dob" id="staff_dob" value="{{ old('staff_dob', $staff->staff_dob) }}" required>
                            @error('staff_dob')
                                <span class="error">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label for="staff_gender">Gender <span class="text-danger">*</span></label>
                            <select name="staff_gender" id="staff_gender" required>
                                <option value="M" {{ (old('staff_gender', $staff->staff_gender) == 'M') ? 'selected' : '' }}>Male</option>
                                <option value="F" {{ (old('staff_gender', $staff->staff_gender) == 'F') ? 'selected' : '' }}>Female</option>
                            </select>
                            @error('staff_gender')
                                <span class="error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="staff_email">Email Address <span class="text-danger">*</span></label>
                            <input type="email" name="staff_email" id="staff_email" value="{{ old('staff_email', $staff->staff_email) }}" required>
                            @error('staff_email')
                                <span class="error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="staff_status">Status <span class="text-danger">*</span></label>
                            <select name="staff_status" id="staff_status" required>
                                <option value="Active" {{ (old('staff_status', $staff->staff_status) == 'Active') ? 'selected' : '' }}>Active</option>
                                <option value="Inactive" {{ (old('staff_status', $staff->staff_status) == 'Inactive') ? 'selected' : '' }}>Inactive</option>
                            </select>
                            @error('staff_status')
                                <span class="error">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Update Staff</button>
                    <a href="{{ route('admin.staff_list') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>

        </div>

    </div>