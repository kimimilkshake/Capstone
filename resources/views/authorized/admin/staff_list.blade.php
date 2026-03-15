@extends('layouts.app')
@section('page-title', 'STAFF')
@section('content')
    @include('components.authHeader')
    @include('components.admin_nav')
    <div class="admin-body">
        <div class="asl-title">
            <h3>STAFF LIST</h3>
        </div>

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
    
        <div class="search-filter-row" style="display: flex; gap: 10px; margin-bottom: 20px;">
            <form class="search-bar" action="{{ route('admin.staff_list') }}" method="GET" style="flex: 1;">
                <input type="text" name="search" placeholder="Search by name..." value="{{ request('search') }}">
                <button type="submit"><i class="fa-solid fa-magnifying-glass me-2"></i>Search</button>
            </form>

            <form method="GET" action="{{ route('admin.staff_list') }}">
                <select name="status" onchange="this.form.submit()"> 
                    <option value="">All Staff</option>
                    <option value="Active" {{ request('status') == 'Active' ? 'selected' : '' }}>Active</option>
                    <option value="Inactive" {{ request('status') == 'Inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </form>
        </div>


        <table class="staff-table">
            <thead>
                <tr>
                    <th>Staff No.</th>
                    <th>Name</th>
                    <th>Date of Birth</th>
                    <th>Age</th>
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
                        <td>{{ $s->staff_dob }}</td>
                        <td>
                            {{ \Carbon\Carbon::parse($s->staff_dob)->age }}
                        </td>
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