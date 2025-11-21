@extends('layouts.app')
@section('page-title', 'CARGO BOOKING')
@section('content')
@include('components.authHeader')
@include('components.staff_nav')

<div class="staff-body">
    <div class="svl-title">
        <h3>CARGO BOOKING</h3>
    </div>

    <div class="scs-form_container">

        {{-- Success Message --}}
        @if(session('success'))
            <div class="alert alert-success text-center">{{ session('success') }}</div>
        @endif

        {{-- Validation Errors --}}
        @if ($errors->any())
            <div class="alert alert-danger text-center">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('staff.cargo_booking.store') }}" method="POST">
            @csrf

            <div class="form-row">
                <div class="form-col">
                    <label>Sender Name</label>
                    <input type="text" name="sender_name" required>
                </div>
                <div class="form-col">
                    <label>Sender Contact No</label>
                    <input type="text" name="sender_contactno" required>
                </div>
                <div class="form-col">
                    <label>Sender Email</label>
                    <input type="email" name="sender_email">
                </div>
            </div>

            <div class="form-row">
                <div class="form-col">
                    <label>Consignee Name</label>
                    <input type="text" name="consignee_name" required>
                </div>
                <div class="form-col">
                    <label>Consignee Contact No</label>
                    <input type="text" name="consignee_contactno" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-col">
                    <label>Select Voyage</label>
                    <select name="voyage_id" required>
                        <option value="" disabled selected>Select Voyage</option>
                        @foreach($voyages as $voyage)
                            <option value="{{ $voyage->voyage_id }}">
                                {{ $voyage->vessel->vessel_name ?? 'N/A' }} 
                                ({{ $voyage->routePort->route_origin ?? 'N/A' }} → {{ $voyage->routePort->route_destination ?? 'N/A' }})
                                on {{ \Carbon\Carbon::parse($voyage->voyage_departure_date)->format('M d, Y') }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-col">
                    <label>Cargo Item</label>
                    <select name="cargo_item_id" required>
                        <option value="" disabled selected>Select Cargo</option>
                        @foreach($cargoItems as $item)
                            <option value="{{ $item->cargo_item_id }}">{{ $item->cargo_item_description }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-col">
                    <label>Quantity</label>
                    <input type="number" name="cargo_item_qty" min="1" required>
                </div>
            </div>

            <div class="form-actions text-center mt-3">
                <button type="submit" class="acs-add-btn">SUBMIT</button>
                <a href="{{ route('staff.dashboard') }}" class="acs-add-btn acs-cancel-btn">CANCEL</a>
            </div>

        </form>
    </div>
</div>
@endsection
