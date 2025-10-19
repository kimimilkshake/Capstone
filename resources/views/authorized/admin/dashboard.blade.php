@extends('layouts.app')
@section('page-title', 'DASHBOARD')
@section('content')
    @include('components.authHeader')
    @include('components.admin_nav') {{--NAVBAR--}}
    <div class="admin-body">
        <div class="dashbord-titles">
            <h3 id="dashoard-header">Today's Statistics</h3>
            <h4 id="dashboard-datetoday">{{ \Carbon\Carbon::now()->format('F d, Y') }}</h4>
        </div>
        <div class="astat-boxes-row">
            <div class="astat-boxes-col">
                <p>XX</p>
                <p>Passengers</p>

            </div>
            <div class="astat-boxes-col">
                <p>XX</p>
                <p>Cargo Bookings</p>
            </div>
            <div class="astat-boxes-col">
                <p>PHP XXXX</p>
                <p>Total Sales</p>
            </div>
        </div>
        <div class="astat-voyage">
            <p>Today's Voyages</p>
            <p>Table goes down here, mahina ang kalaban.</p>
        </div>
    </div>
    
    
    
@endsection
