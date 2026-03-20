@extends('layouts.app')
@section('page-title', 'REVIEW CARGO BOOKINGS')

@section('content')
@include('components.authHeader')
@include('components.staff_nav')

<div class="staff-body">

<div class="search-filter-row mb-4" style="display:flex; gap:10px;">
    <form class="search-bar d-flex gap-2 align-items-stretch" action="{{ route('cargo.bookings.pending') }}" method="GET" style="flex:1;">
        <input type="text" name="search" class="form-control" style="height: 46px; border-radius: 0;" placeholder="Search by Ref No., Sender, or Consignee..." value="{{ request('search') }}">
        <select name="booking_status" class="form-select" style="max-width: 220px; height: 46px; border-radius: 0;">
            @foreach($allowedStatuses as $status)
                <option value="{{ $status }}" {{ $selectedStatus === $status ? 'selected' : '' }}>
                    {{ in_array($status, ['Canceled', 'Cancelled']) ? 'Rejected' : $status }}
                </option>
            @endforeach
        </select>
        <button type="submit" class="btn btn-primary">Search</button>
        @if(request('search') || request('booking_status'))
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
                <th>Handled By</th>
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
                    <td>{{ in_array($b->booking_status, ['Canceled', 'Cancelled']) ? 'Rejected' : $b->booking_status }}</td>
                    @php
                        $processedBy = optional(optional($b->cargoBookings->first())->approvedByStaff)->staff_name;
                        $processedLabel = 'N/A';
                        if ($processedBy) {
                            if ($b->booking_status === 'Confirmed') {
                                $processedLabel =   $processedBy;
                            } elseif ($b->booking_status === 'Canceled') {
                                $processedLabel = $processedBy;
                            } else {
                                $processedLabel = $processedBy;
                            }
                        }
                    @endphp
                    <td>{{ $processedLabel }}</td>
                    <td>{{ $b->created_at->format('M d, Y') }}</td>
                    <td class="d-flex gap-1">
                        <a href="{{ route('cargo.bookings.show', $b->booking_ref_no) }}" class="btn btn-sm btn-primary">View</a>
                        @if($b->booking_status === 'Pending')
                            <a href="{{ route('cargo.bookings.edit', $b->booking_ref_no) }}" class="btn btn-sm btn-warning">Edit</a>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center">No cargo bookings found for the selected filter.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="pagination-container mt-3">
        {{ $bookings->links('pagination::bootstrap-5') }}
    </div>
</div>
@endsection
