@extends('layouts.app')
@section('content')
    @include('components.hero')

    <div class="trip-booking-container container">
        <div class="row align-items-stretch">

            <!-- Left Side: Available Voyages -->
            <div class="col-lg-6">
                <div class="table-container">
                    <h4 class="text-center text-primary">Available Voyages (Next 7 Days)</h4>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead class="table-dark text-center">
                                <tr>
                                    <th>Date</th>
                                    <th>Route</th>
                                    <th>Departure</th>
                                    <th>Vessel</th>
                                </tr>
                            </thead>
                            <tbody class="text-center">
                                @forelse ($voyages as $voyage)
                                    <tr>
                                        @php
                                            $depDate = data_get($voyage, 'departure_date');
                                            $depTime = data_get($voyage, 'departure_time');
                                        @endphp
                                        <td>{{ $depDate ? \Carbon\Carbon::parse($depDate)->format('M d') : '-' }}</td>
                                        <td>{{ data_get($voyage, 'route_from', '-') }} -
                                            {{ data_get($voyage, 'route_to', '-') }}</td>
                                        <td>{{ $depTime ? \Carbon\Carbon::parse($depTime)->format('h:i A') : '-' }}</td>
                                        <td>{{ data_get($voyage, 'vessel_name', '-') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center">
                                            <div class="text-muted">
                                                <i class="bi bi-calendar-x fs-1 d-block"></i>
                                                No voyages available in the next 8 days
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>

            <!-- Right Side: Booking Selection -->
            <div class="col-lg-6">
                <div class="booking-selection-content d-flex flex-column">

                    <div class="d-flex justify-content-between align-items-center mb-3 booking-header">
                        <h4 class="text-primary mb-0">Select Route & Schedule</h4>
                        <a href="#" id="requestTicketLink"
                            class="text-decoration-none text-primary fw-semibold d-none d-lg-block"
                            style="font-size: 0.9rem;">
                            <i class="fas fa-ticket-alt me-1"></i>Request Ticket Copy
                        </a>
                    </div>

                    <!-- Booking Type -->
                    <div class="booking-type-section">
                        <h6 class="text-secondary">Booking Type</h6>

                        <div class="booking-type-options">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="bookingType" id="passengerType"
                                    value="passenger" checked>
                                <label class="form-check-label" for="passengerType">Passenger</label>
                            </div>

                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="bookingType" id="cargoType"
                                    value="cargo">
                                <label class="form-check-label" for="cargoType">Cargo</label>
                            </div>
                        </div>
                    </div>

                    <!-- Route Selection -->
                    <div class="route-selection">
                        <div class="route-inputs">
                            <div class="route-input-group">
                                <label class="form-label">From</label>
                                <select id="routeFrom" class="form-select">
                                    <option value="">Select Origin</option>
                                    @foreach (collect($voyages)->pluck('route_from')->unique() as $origin)
                                        <option value="{{ $origin }}">{{ $origin }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="route-arrow">
                                <i class="bi bi-arrow-right fs-4 text-primary"></i>
                            </div>

                            <div class="route-input-group">
                                <label class="form-label">To</label>
                                <select id="routeTo" class="form-select" disabled>
                                    <option value="">Select Destination</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Date and Time Selection -->
                    <div class="date-time-selection">
                        <div class="datetime-inputs">
                            <div class="datetime-input-group">
                                <label class="form-label">Departure Date</label>
                                <div class="calendar-input-wrapper">
                                    <span class="calendar-icon"><i class="bi bi-calendar-event"></i></span>
                                    <input type="date" id="tripDate" class="calendar-input form-control" disabled>
                                </div>
                            </div>

                            <div class="datetime-input-group">
                                <label class="form-label">Departure Time</label>
                                <select id="departureTime" class="form-select" disabled>
                                    <option value="">Select Time</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Spacer to push button to bottom -->
                    <div class="spacer"></div>

                    <!-- Proceed Button -->
                    <button id="proceedBtn" class="proceed-btn btn btn-primary btn-lg" type="button"
                        data-passenger-url="{{ route('passengerbooking') }}" data-cargo-url="{{ route('cargobooking') }}"
                        disabled>
                        <i class="bi bi-arrow-right-circle me-2"></i>PROCEED
                    </button>

                </div>
            </div>

        </div>

        <!-- Mobile-only Ticket Request Section -->
        <div class="row d-lg-none mt-4">
            <div class="col-12">
                <div class="mobile-ticket-request-card">
                    <div class="mobile-ticket-header">
                        <h5><i class="fas fa-ticket-alt me-2"></i>Request Ticket Copy</h5>
                    </div>
                    <div class="mobile-ticket-body">
                        <p class="text-muted mb-3">Lost your ticket? Enter your email and departure date to receive a copy.
                        </p>

                        <form id="requestTicketFormMobile">
                            @csrf
                            <div class="mb-3">
                                <label for="ticketEmailMobile" class="form-label">Email Address <span
                                        class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="ticketEmailMobile" name="email" required
                                    placeholder="your.email@example.com">
                            </div>
                            <div class="mb-3">
                                <label for="departureDateMobile" class="form-label">Departure Date <span
                                        class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="departureDateMobile" name="departure_date"
                                    required>
                            </div>
                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <label for="requestRouteFromMobile" class="form-label">From <span
                                            class="text-danger">*</span></label>
                                    <select id="requestRouteFromMobile" name="route_from" class="form-select" required>
                                        <option value="">Select Origin</option>
                                        @foreach (collect($voyages)->pluck('route_from')->unique() as $origin)
                                            <option value="{{ $origin }}">{{ $origin }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-6">
                                    <label for="requestRouteToMobile" class="form-label">To <span
                                            class="text-danger">*</span></label>
                                    <select id="requestRouteToMobile" name="route_to" class="form-select" required disabled>
                                        <option value="">Select Destination</option>
                                    </select>
                                </div>
                            </div>
                            <div class="text-center">
                                <button type="submit" class="btn btn-primary w-100" id="requestTicketBtnMobile">
                                    <i class="fas fa-paper-plane me-2"></i>Request Ticket Copy
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Overlay for Request Ticket Copy -->
    <div id="ticketRequestModal" class="ticket-modal" style="display: none;">
        <div class="ticket-modal-overlay"></div>
        <div class="ticket-modal-content">
            <div class="ticket-modal-header">
                <h5><i class="fas fa-ticket-alt me-2"></i>Request Ticket Copy</h5>
                <button type="button" class="ticket-modal-close" id="closeTicketModal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="ticket-modal-body">
                <p class="text-muted text-center mb-4">Lost your ticket? Enter your email and departure date to receive a
                    copy.</p>

                <form id="requestTicketForm">
                    @csrf
                    <div class="mb-3">
                        <label for="ticketEmail" class="form-label">Email Address <span
                                class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="ticketEmail" name="email" required
                            placeholder="your.email@example.com">
                    </div>
                    <div class="mb-3">
                        <label for="departureDate" class="form-label">Departure Date <span
                                class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="departureDate" name="departure_date" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label for="requestRouteFrom" class="form-label">From <span
                                    class="text-danger">*</span></label>
                            <select id="requestRouteFrom" name="route_from" class="form-select" required>
                                <option value="">Select Origin</option>
                                @foreach (collect($voyages)->pluck('route_from')->unique() as $origin)
                                    <option value="{{ $origin }}">{{ $origin }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6">
                            <label for="requestRouteTo" class="form-label">To <span
                                    class="text-danger">*</span></label>
                            <select id="requestRouteTo" name="route_to" class="form-select" required disabled>
                                <option value="">Select Destination</option>
                            </select>
                        </div>
                    </div>
                    <div class="text-center">
                        <button type="submit" class="btn btn-primary w-100" id="requestTicketBtn">
                            <i class="fas fa-paper-plane me-2"></i>Request Ticket Copy
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script type="application/json" id="voyages-data">{!! json_encode($voyages) !!}</script>
    <script src="{{ asset('js/bookingtype.js') }}"></script>
    <script>
        // Modal functionality
        const modal = document.getElementById('ticketRequestModal');
        const openLink = document.getElementById('requestTicketLink');
        const closeBtn = document.getElementById('closeTicketModal');
        const overlay = modal.querySelector('.ticket-modal-overlay');

        // Open modal (only on desktop)
        if (openLink) {
            openLink.addEventListener('click', function(e) {
                e.preventDefault();
                modal.style.display = 'block';
                // Force reflow for smooth animation
                modal.offsetHeight;
                modal.classList.add('active');
            });
        }

        // Close modal function
        function closeModal() {
            modal.classList.remove('active');
            setTimeout(() => {
                modal.style.display = 'none';
            }, 200);
        }

        // Close button click
        closeBtn.addEventListener('click', closeModal);

        // Click overlay to close
        overlay.addEventListener('click', closeModal);

        // Close on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && modal.classList.contains('active')) {
                closeModal();
            }
        });

        // Handle route filtering for request ticket form
        const voyagesData = JSON.parse(document.getElementById('voyages-data').textContent);
        const requestRouteFromSelect = document.getElementById('requestRouteFrom');
        const requestRouteToSelect = document.getElementById('requestRouteTo');
        const requestRouteFromMobileSelect = document.getElementById('requestRouteFromMobile');
        const requestRouteToMobileSelect = document.getElementById('requestRouteToMobile');

        // Update destinations based on selected origin for request form
        function updateRequestDestinations(fromSelect, toSelect) {
            const origin = fromSelect.value;
            toSelect.innerHTML = '<option value="">Select Destination</option>';

            if (!origin) {
                toSelect.disabled = true;
                return;
            }

            const destinations = voyagesData
                .filter((v) => v.route_from === origin)
                .map((v) => v.route_to)
                .filter((v, i, a) => a.indexOf(v) === i);

            destinations.forEach((dest) => {
                const option = document.createElement('option');
                option.value = dest;
                option.textContent = dest;
                toSelect.appendChild(option);
            });

            toSelect.disabled = false;
        }

        // Add listeners for desktop form
        if (requestRouteFromSelect) {
            requestRouteFromSelect.addEventListener('change', function() {
                updateRequestDestinations(requestRouteFromSelect, requestRouteToSelect);
            });
        }

        // Add listeners for mobile form
        if (requestRouteFromMobileSelect) {
            requestRouteFromMobileSelect.addEventListener('change', function() {
                updateRequestDestinations(requestRouteFromMobileSelect, requestRouteToMobileSelect);
            });
        }

        // Form submission
        document.getElementById('requestTicketForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const btn = document.getElementById('requestTicketBtn');
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Searching...';

            const formData = new FormData(this);

            try {
                const response = await fetch('{{ route('ticket.request-copy') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: formData
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    // Close modal and show success alert
                    closeModal();
                    alert('Ticket sent to your email!');
                    this.reset();
                } else {
                    alert(data.message || 'No ticket found.');
                }
            } catch (error) {
                alert('An error occurred. Please try again.');
            } finally {
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        });

        // Mobile form submission (same logic)
        const mobileForm = document.getElementById('requestTicketFormMobile');
        if (mobileForm) {
            mobileForm.addEventListener('submit', async function(e) {
                e.preventDefault();

                const btn = document.getElementById('requestTicketBtnMobile');
                const originalText = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Searching...';

                const formData = new FormData(this);

                try {
                    const response = await fetch('{{ route('ticket.request-copy') }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        },
                        body: formData
                    });

                    const data = await response.json();

                    if (response.ok && data.success) {
                        alert('Ticket sent to your email!');
                        this.reset();
                    } else {
                        alert(data.message || 'No ticket found.');
                    }
                } catch (error) {
                    alert('An error occurred. Please try again.');
                } finally {
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                }
            });
        }
    </script>
@endsection
