@extends('layouts.app')
@section('page-title', 'STAFF MEMBERS')
@section('content')
    @include('components.authHeader')
    @include('components.admin_nav')
    <div class="admin-body">

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
    
        <div class="search-filter-row" style="display: flex; gap: 10px; margin-bottom: 20px;">
            <form class="search-bar" action="{{ route('admin.staff_list') }}" method="GET" style="flex: 1;">
                <input type="text" name="search" placeholder="Search by name..." value="{{ request('search') }}">
                <button type="submit"><i class="fa-solid fa-magnifying-glass me-2"></i>Search</button>
            </form>

            <form method="GET" action="{{ route('admin.staff_list') }}">
                <select name="status" onchange="this.form.submit()"> 
                    <option value="" {{ request('status') == '' ? 'selected' : '' }}>All Staff</option>
                    <option value="Active" {{ request('status', 'Active') == 'Active' ? 'selected' : '' }}>Active</option>
                    <option value="Inactive" {{ request('status') == 'Inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </form>

            <div class="add-vessel">
                <a href="{{ route('admin.create_staff') }}"><i class="fa-solid fa-user-plus me-1"></i></i>Add Staff</a>
            </div>
        </div>


        <table class="staff-table">
            <thead>
                <tr>
                    <th>Staff No.</th>
                    <th>Name</th>
                    <th>Gender</th>
                    <th>Username</th>
                    <th>Email Address</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($staff as $index => $s)
                    <tr>
                        <td>{{ $s->staff_id }}</td>
                        <td>{{ $s->staff_name }}</td>
                        <td>{{ $s->staff_gender }}</td>
                        <td>{{ $s->staff_user }}</td>
                        <td>{{ $s->staff_email }}</td>
                        <td>{{ $s->staff_status }}</td>
                        <td>
                        <a href="{{ route('admin.staff_edit', $s->staff_id) }}" class="editRouteBtn link-btn" title="Edit Staff">
                            <!-- Using Font Awesome pencil icon -->
                            <i class="fa fa-pencil" aria-hidden="true"></i>
                        </a>
                    </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center">No staff accounts found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="pagination-container">
            {{ $staff->appends(request()->query())->links('pagination::bootstrap-5') }}
        </div>
    </div>

@endsection