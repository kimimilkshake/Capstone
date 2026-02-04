@extends('layouts.app')
@section('page-title', 'REVIEW CARGO BOOKINGS')

@section('content')
@include('components.authHeader')
@include('components.staff_nav')

<div class="staff-body">
    <div class="svl-title">
        <h3>REVIEW CARGO BOOKINGS</h3>
    </div>

<div class="search-filter-row mb-4" style="display:flex; gap:10px;">
    <form class="search-bar d-flex gap-2" action="{{ route('cargo.bookings.pending') }}" method="GET" style="flex:1;">
        <input type="text" name="search" class="form-control" placeholder="Search by Ref No., Sender, or Consignee..." value="{{ request('search') }}">
        <button type="submit" class="btn btn-primary">Search</button>
        @if(request('search'))
            <a href="{{ route('cargo.bookings.pending') }}" class="btn btn-outline-secondary">Clear</a>
        @endif
    </form>
</div>


    <table class="cargo-item-table">
        <thead>
            <tr>
                <th>Booking Ref #</th>
                <th>Sender</th>
                <th>Consignee</th>
                <th>Status</th>
                <th>Created</th>
                <th>Action</th>
            </tr>
        </thead>

        <tbody>
            @forelse($bookings as $b)
                <tr>
                    <td>{{ $b->booking_code }}</td>
                    <td>{{ optional($b->sender)->sender_name ?? 'N/A' }}</td>
                    <td>{{ optional($b->consignee)->consignee_name ?? 'N/A' }}</td>
                    <td>{{ $b->booking_status }}</td>
                    <td>{{ $b->created_at->format('M d, Y') }}</td>
                    <td class="d-flex gap-1">
                        <a href="{{ route('cargo.bookings.show', $b->booking_ref_no) }}" class="btn btn-sm btn-primary">View</a>
                        <a href="{{ route('cargo.bookings.edit', $b->booking_ref_no) }}" class="btn btn-sm btn-warning">Edit</a>
                        @if($b->booking_status === 'Pending')
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">No pending bookings.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="pagination-container mt-3">
        {{ $bookings->links('pagination::bootstrap-5') }}
    </div>
</div>
@endsection
