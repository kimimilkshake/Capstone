@extends('layouts.app')
@section('page-title', 'REVIEW CARGO BOOKINGS')
@section('content')
@include('components.authHeader')
@include('components.staff_nav')

<div class="staff-body">
    <div class="svl-title"><h3>REVIEW CARGO BOOKINGS</h3></div>

    <div class="search-add-row mb-3">
        <form class="search-bar" method="GET" action="{{ route('staff.cargo_bookings.review') }}">
            <input type="text" name="search" placeholder="Search by booking ref..." value="{{ request('search') }}">
            <input type="date" name="date" value="{{ request('date') }}">
            <button type="submit"><i class="fa-solid fa-magnifying-glass me-2"></i>Search</button>
        </form>
    </div>

    <table class="voyage-table">
        <thead>
            <tr>
                <th>Booking Ref</th>
                <th>Sender</th>
                <th>Consignee</th>
                <th>Voyage</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($cargoBookings as $booking)
                <tr>
                    <td>{{ $booking->booking_ref_no }}</td>
                    <td>{{ $booking->sender->sender_name ?? 'N/A' }}</td>
                    <td>{{ $booking->consignee->consignee_name ?? 'N/A' }}</td>
                    <td>{{ $booking->voyage->voyage_code ?? 'N/A' }}</td>
                    <td>{{ $booking->booking_status }}</td>
                    <td>
                        @if($booking->booking_status == 'Pending')
                            <form action="{{ route('approveCargoBooking', $booking->booking_ref_no) }}" method="POST" style="display:inline;">
                                @csrf
                                <button class="btn btn-success btn-sm">Confirm</button>
                            </form>
                            <form action="{{ route('rejectCargoBooking', $booking->booking_ref_no) }}" method="POST" style="display:inline;">
                                @csrf
                                <button class="btn btn-danger btn-sm">Cancel</button>
                            </form>
                        @else
                            <span class="text-muted">No actions</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center">No bookings found.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="mt-3">
        {{ $cargoBookings->appends(request()->query())->links('pagination::bootstrap-5') }}
    </div>
</div>
@endsection
