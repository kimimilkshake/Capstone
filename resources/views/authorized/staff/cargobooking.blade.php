@extends('layouts.app')
@section('page-title', 'Cargo Booking')

@section('content')
    @include('components.authHeader')
    @include('components.staff_nav')

    <div class="container mt-5 pt-5">
        <!-- 🧭 Trip Schedules Section -->
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <h4 class="fw-bold mb-4 text-center">Trip Schedules</h4>
                <table class="table table-bordered table-striped shadow-sm small text-center">
                    <thead class="table-dark">
                        <tr>
                            <th>Route</th>
                            <th>Departure Time</th>
                            <th>Interval</th>
                            <th>Travel Time</th>
                            <th>Port of Origin</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- Placeholder rows --}}
                        <tr>
                            <td>Cebu - Baybay</td>
                            <td>08:00 PM</td>
                            <td>Tue, Thu, Sun</td>
                            <td>6 hrs</td>
                            <td>Port of Cebu, PT2</td>
                        </tr>
                        <tr>
                            <td>Baybay - Cebu</td>
                            <td>08:00 PM</td>
                            <td>Mon, Wed, Fri</td>
                            <td>6 hrs</td>
                            <td>Port of Baybay</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 🧾 Booking Selection Section -->
        <div class="row justify-content-center mt-5">
            <div class="col-lg-6">
                <h4 class="fw-bold mb-4 text-center">Select Route & Schedule</h4>

                <div class="mb-3">
                    <label for="routeFrom" class="form-label">Origin</label>
                    <select id="routeFrom" class="form-select">
                        <option value="">Select Origin</option>
                        <option value="Cebu">Cebu</option>
                        <option value="Baybay">Baybay</option>
                        <option value="Ormoc">Ormoc</option>
                        <option value="Talibon">Talibon</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="routeTo" class="form-label">Destination</label>
                    <select id="routeTo" class="form-select">
                        <option value="">Select Destination</option>
                        <option value="Baybay">Baybay</option>
                        <option value="Cebu">Cebu</option>
                        <option value="Ormoc">Ormoc</option>
                        <option value="Talibon">Talibon</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="tripDate" class="form-label">Departure Date</label>
                    <input type="date" id="tripDate" class="form-control" />
                </div>

                <div class="text-center">
                    <button class="btn btn-primary px-4" type="button">PROCEED</button>
                </div>
            </div>
        </div>
    </div>
@endsection
