<?php $__env->startSection('page-title', 'PASSENGER BOOKING'); ?>
<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('components.authHeader', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo $__env->make('components.staff_nav', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="staff-body">
        <div class="svl-title text-center">
            <h3>PASSENGER BOOKING</h3>
        </div>

        <!-- Floating Toast Container - Below navbar on the right side -->
        <div class="toast-container position-fixed p-3" style="z-index: 9999; top: 80px; right: 20px;">
            <?php if(session('success')): ?>
                <div class="toast align-items-center text-white bg-success border-0 show" role="alert" aria-live="assertive"
                    aria-atomic="true" id="successToast">
                    <div class="d-flex">
                        <div class="toast-body">
                            <i class="fas fa-check-circle me-2"></i><?php echo e(session('success')); ?>

                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
                            aria-label="Close"></button>
                    </div>
                </div>
            <?php endif; ?>
            <?php if($errors->any()): ?>
                <div class="toast align-items-center text-white bg-danger border-0 show" role="alert"
                    aria-live="assertive" aria-atomic="true" id="errorToast">
                    <div class="d-flex">
                        <div class="toast-body">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php echo e($error); ?>

                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
                            aria-label="Close"></button>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <form action="<?php echo e(route('staff.passenger_booking.store')); ?>" method="POST" id="passengerBookingForm">
            <?php echo csrf_field(); ?>

            <!-- Voyage and Number Selection -->
            <div class="row mb-4">
                <div class="col-lg-9">
                    <label class="form-label fw-bold">Select Voyage <span class="text-danger">*</span></label>
                    <select name="voyage_id" id="voyageSelect" class="form-select" required>
                        <option value="">-- Choose Voyage --</option>
                        <?php $__currentLoopData = $voyages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $voyage): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($voyage->voyage_id); ?>" data-vessel-id="<?php echo e($voyage->vessel_id); ?>"
                                data-cot-plan="<?php echo e($voyage->vessel && $voyage->vessel->vessel_cot_plan_url ? asset('storage/' . $voyage->vessel->vessel_cot_plan_url) : asset('images/sample-cot-plan.jpg')); ?>">
                                <?php echo e($voyage->voyage_code); ?> |
                                <?php echo e($voyage->routePort->route_origin ?? 'N/A'); ?> →
                                <?php echo e($voyage->routePort->route_destination ?? 'N/A'); ?> |
                                Departure: <?php echo e(\Carbon\Carbon::parse($voyage->voyage_departure_date)->format('M d, Y')); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="col-lg-3">
                    <label class="form-label fw-bold">No. of Passengers</label>
                    <select id="numPassengers" class="form-select">
                        <?php for($i = 1; $i <= 20; $i++): ?>
                            <option value="<?php echo e($i); ?>"><?php echo e($i); ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
            </div>

            <hr class="my-4">

            <div class="row">
                <!-- LEFT: Cot Plan Image -->
                <div class="col-lg-6 mb-4 text-center">
                    <h5 class="fw-bold mb-3">Cot Plan Layout</h5>
                    <img id="cotPlanImage" src="<?php echo e($cotPlanUrl); ?>" alt="Cot Plan" class="img-fluid rounded shadow-sm"
                        style="max-height: 500px; object-fit: contain;">
                    <p class="text-muted mt-2">Vessel cot plan layout</p>
                </div>

                <!-- RIGHT: Passenger Details Container -->
                <div class="col-lg-6">
                    <div id="passengersContainer"></div>
                </div>
            </div>

            <!-- Hidden payment mode field -->
            <input type="hidden" name="payment_mode" id="paymentModeField" value="">

            <div class="text-center mt-4 mb-5">
                <button type="button" id="bookButton" class="btn btn-primary btn-lg" disabled>
                    <i class="fas fa-arrow-right"></i> PROCEED TO PAYMENT
                </button>
                <a href="<?php echo e(route('staff.dashboard')); ?>" class="btn btn-outline-danger btn-lg">CANCEL</a>
            </div>
        </form>

    </div>

    <script>
        const voyageSelect = document.getElementById('voyageSelect');
        const numPassengersSelect = document.getElementById('numPassengers');
        const passengersContainer = document.getElementById('passengersContainer');
        let accommodationsData = [];

        // PSGC API for Philippine addresses
        const PSGC_API_BASE = 'https://psgc.gitlab.io/api';
        let provincesCache = null;
        let citiesCache = {};
        let barangaysCache = {};

        async function fetchPSGC(endpoint) {
            try {
                const response = await fetch(`${PSGC_API_BASE}${endpoint}`);
                if (!response.ok) throw new Error('Failed to fetch');
                return await response.json();
            } catch (error) {
                console.error('PSGC API Error:', error);
                return null;
            }
        }

        // Load provinces once
        async function loadProvinces() {
            if (!provincesCache) {
                const provinces = await fetchPSGC('/provinces');
                if (provinces) {
                    provincesCache = provinces.sort((a, b) => a.name.localeCompare(b.name));
                }
            }
            return provincesCache;
        }

        async function loadAccommodations(voyageId) {
            if (!voyageId) {
                accommodationsData = [];
                return;
            }

            try {
                const url = `/authorized/staff/passenger-booking/available-cots?voyage_id=${voyageId}`;
                console.log('Fetching accommodations from:', url); // Debug log

                const response = await fetch(url);
                const data = await response.json();

                console.log('Accommodations loaded:', data); // Debug log

                if (data.success) {
                    accommodationsData = data.accommodations;
                    console.log('Accommodations data:', accommodationsData); // Debug log
                } else {
                    accommodationsData = [];
                    alert('Failed to load accommodations: ' + (data.message || 'Unknown error'));
                }
            } catch (error) {
                console.error('Error loading accommodations:', error);
                accommodationsData = [];
                alert('Error loading accommodations. Check console for details.');
            }
        }

        async function generatePassengerForms(count) {
            passengersContainer.innerHTML = '';
            const provinces = await loadProvinces();

            for (let i = 0; i < count; i++) {
                const passengerDiv = document.createElement('div');
                passengerDiv.className = 'card p-3 shadow-sm mb-3 passenger-card';
                passengerDiv.innerHTML = `
            <h6 class="fw-bold mb-3 text-primary">Personal Information - Person ${i + 1}</h6>
            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label">Passenger Type <span class="text-danger">*</span></label>
                    <select name="passengers[${i}][type]" class="form-select passenger-type" required>
                        <option value="">Select Type</option>
                        <option value="Regular">Regular/Adult</option>
                        <option value="Senior Citizen">Senior Citizen</option>
                        <option value="PWD">PWD</option>
                        <option value="Student">Student</option>
                        <option value="Uniformed Personnel">Uniformed Personnel</option>
                        <option value="3 to 11 years old">3 to 11 years old</option>
                        <option value="Below 3 years old">Below 3 years old</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Suffix</label>
                    <input type="text" name="passengers[${i}][suffix]" class="form-control" placeholder="Jr., Sr., III">
                </div>
                <div class="col-md-6">
                    <label class="form-label">First Name <span class="text-danger">*</span></label>
                    <input type="text" name="passengers[${i}][first_name]" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Last Name <span class="text-danger">*</span></label>
                    <input type="text" name="passengers[${i}][last_name]" class="form-control" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Middle Initial</label>
                    <input type="text" name="passengers[${i}][middle_initial]" class="form-control text-center" maxlength="1">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Age <span class="text-danger">*</span></label>
                    <input type="number" name="passengers[${i}][age]" class="form-control" min="0" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Gender <span class="text-danger">*</span></label>
                    <select name="passengers[${i}][gender]" class="form-select" required>
                        <option value="">Select Gender</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Province <span class="text-danger">*</span></label>
                    <select name="passengers[${i}][province]" class="form-select province-select" data-index="${i}" required>
                        <option value="">Select Province</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">City/Municipality <span class="text-danger">*</span></label>
                    <select name="passengers[${i}][city]" class="form-select city-select" data-index="${i}" required disabled>
                        <option value="">Select City/Municipality</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Barangay <span class="text-danger">*</span></label>
                    <select name="passengers[${i}][barangay]" class="form-select barangay-select" data-index="${i}" required disabled>
                        <option value="">Select Barangay</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Contact Number <span class="text-danger">*</span></label>
                    <input type="tel" name="passengers[${i}][contact_number]" class="form-control" placeholder="09XXXXXXXXX" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="passengers[${i}][email]" class="form-control" placeholder="name@email.com">
                </div>
            </div>

            <hr>

            <h6 class="fw-bold mb-3 text-success">Accommodation - Person ${i + 1}</h6>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Accommodation Type <span class="text-danger">*</span></label>
                    <select name="passengers[${i}][accommodation_id]" class="form-select accommodation-select" data-index="${i}" required disabled>
                        <option value="">Select Accommodation</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Cot Number <span class="text-danger">*</span></label>
                    <select name="passengers[${i}][cot_number]" class="form-select cot-select" data-index="${i}" required disabled>
                        <option value="">Select Accommodation First</option>
                    </select>
                </div>
            </div>
        `;
                passengersContainer.appendChild(passengerDiv);

                // Populate province dropdown
                const provinceSelect = passengerDiv.querySelector('.province-select');
                if (provinces) {
                    provinces.forEach(province => {
                        const option = document.createElement('option');
                        option.value = province.name;
                        option.textContent = province.name;
                        option.dataset.code = province.code;
                        provinceSelect.appendChild(option);
                    });
                }

                // Setup address cascading
                setupAddressCascading(passengerDiv, i);

                // Setup accommodation/cot cascading
                setupAccommodationCascading(passengerDiv, i);
            }

            // If voyage is already selected, populate accommodations for new forms
            if (voyageSelect.value && accommodationsData.length > 0) {
                populateAccommodations();
            }
        }

        function setupAddressCascading(passengerDiv, index) {
            const provinceSelect = passengerDiv.querySelector('.province-select');
            const citySelect = passengerDiv.querySelector('.city-select');
            const barangaySelect = passengerDiv.querySelector('.barangay-select');

            provinceSelect.addEventListener('change', async function() {
                const provinceCode = this.selectedOptions[0]?.dataset.code;

                citySelect.innerHTML = '<option value="">Loading...</option>';
                barangaySelect.innerHTML = '<option value="">Select Barangay</option>';
                barangaySelect.disabled = true;
                citySelect.disabled = true;

                if (provinceCode) {
                    if (!citiesCache[provinceCode]) {
                        const cities = await fetchPSGC(`/provinces/${provinceCode}/cities-municipalities`);
                        if (cities) {
                            citiesCache[provinceCode] = cities.sort((a, b) => a.name.localeCompare(b.name));
                        }
                    }

                    citySelect.innerHTML = '<option value="">Select City</option>';
                    if (citiesCache[provinceCode]) {
                        citiesCache[provinceCode].forEach(city => {
                            const option = document.createElement('option');
                            option.value = city.name;
                            option.textContent = city.name;
                            option.dataset.code = city.code;
                            citySelect.appendChild(option);
                        });
                        citySelect.disabled = false;
                    }
                }
            });

            citySelect.addEventListener('change', async function() {
                const cityCode = this.selectedOptions[0]?.dataset.code;

                barangaySelect.innerHTML = '<option value="">Loading...</option>';
                barangaySelect.disabled = true;

                if (cityCode) {
                    if (!barangaysCache[cityCode]) {
                        let barangays = await fetchPSGC(`/cities/${cityCode}/barangays`);
                        if (!barangays) {
                            barangays = await fetchPSGC(`/municipalities/${cityCode}/barangays`);
                        }
                        if (barangays) {
                            barangaysCache[cityCode] = barangays.sort((a, b) => a.name.localeCompare(b.name));
                        }
                    }

                    barangaySelect.innerHTML = '<option value="">Select Barangay</option>';
                    if (barangaysCache[cityCode]) {
                        barangaysCache[cityCode].forEach(barangay => {
                            const option = document.createElement('option');
                            option.value = barangay.name;
                            option.textContent = barangay.name;
                            barangaySelect.appendChild(option);
                        });
                        barangaySelect.disabled = false;
                    }
                }
            });
        }

        function setupAccommodationCascading(passengerDiv, index) {
            const accommodationSelect = passengerDiv.querySelector('.accommodation-select');
            const cotSelect = passengerDiv.querySelector('.cot-select');

            accommodationSelect.addEventListener('change', function() {
                const accommodationId = this.value;
                cotSelect.innerHTML = '<option value="">Select Cot</option>';
                cotSelect.disabled = true;

                if (accommodationId) {
                    const accommodation = accommodationsData.find(a => a.accommodation_id == accommodationId);
                    if (accommodation && accommodation.available_cots) {
                        const selectedCots = getSelectedCots();
                        accommodation.available_cots.forEach(cot => {
                            if (!selectedCots.includes(cot)) {
                                const option = document.createElement('option');
                                option.value = cot;
                                option.textContent = cot;
                                cotSelect.appendChild(option);
                            }
                        });
                        cotSelect.disabled = false;
                    }
                }
            });

            cotSelect.addEventListener('change', updateCotAvailability);
        }

        function getSelectedCots() {
            const allCotSelects = document.querySelectorAll('.cot-select');
            const selected = [];
            allCotSelects.forEach(select => {
                if (select.value) {
                    selected.push(parseInt(select.value));
                }
            });
            return selected;
        }

        function updateCotAvailability() {
            const selectedCots = getSelectedCots();
            const allCotSelects = document.querySelectorAll('.cot-select');

            allCotSelects.forEach(cotSelect => {
                const currentValue = cotSelect.value;
                const passengerCard = cotSelect.closest('.passenger-card');
                const accommodationSelect = passengerCard.querySelector('.accommodation-select');
                const accommodationId = accommodationSelect.value;

                if (accommodationId) {
                    const accommodation = accommodationsData.find(a => a.accommodation_id == accommodationId);
                    if (accommodation && accommodation.available_cots) {
                        cotSelect.innerHTML = '<option value="">Select Cot</option>';
                        accommodation.available_cots.forEach(cot => {
                            if (cot == currentValue || !selectedCots.includes(cot)) {
                                const option = document.createElement('option');
                                option.value = cot;
                                option.textContent = cot;
                                if (cot == currentValue) option.selected = true;
                                cotSelect.appendChild(option);
                            }
                        });
                    }
                }
            });
        }

        function populateAccommodations() {
            const accommodationSelects = document.querySelectorAll('.accommodation-select');
            console.log('Populating accommodations for', accommodationSelects.length, 'selects'); // Debug log
            console.log('Available accommodations:', accommodationsData); // Debug log

            accommodationSelects.forEach(select => {
                select.innerHTML = '<option value="">Select Accommodation</option>';

                if (accommodationsData.length > 0) {
                    accommodationsData.forEach(acc => {
                        const option = document.createElement('option');
                        option.value = acc.accommodation_id;
                        option.textContent = `${acc.accommodation_name} - ₱${acc.accommodation_price}`;
                        select.appendChild(option);
                    });
                    select.disabled = false;
                    console.log('Accommodation select enabled:', select); // Debug log
                } else {
                    select.disabled = true;
                    console.log('No accommodations available, select disabled'); // Debug log
                }
            });
        }

        // Event Listeners
        voyageSelect.addEventListener('change', async function() {
            // Update cot plan image
            const selectedOption = this.options[this.selectedIndex];
            const cotPlanUrl = selectedOption.dataset.cotPlan;
            if (cotPlanUrl) {
                document.getElementById('cotPlanImage').src = cotPlanUrl;
            }

            await loadAccommodations(this.value);

            // Populate accommodations for existing passenger forms
            populateAccommodations();

            checkFormCompletion();
        });

        numPassengersSelect.addEventListener('change', function() {
            generatePassengerForms(parseInt(this.value));
            if (voyageSelect.value) {
                populateAccommodations();
            }
            checkFormCompletion();
        });

        // Check if all required fields are filled
        function checkFormCompletion() {
            const voyageSelected = voyageSelect.value !== '';
            const passengerCards = document.querySelectorAll('.passenger-card');
            let allFieldsFilled = voyageSelected && passengerCards.length > 0;

            if (allFieldsFilled) {
                passengerCards.forEach(card => {
                    const requiredFields = card.querySelectorAll('[required]');
                    requiredFields.forEach(field => {
                        if (!field.value || field.value === '') {
                            allFieldsFilled = false;
                        }
                    });
                });
            }

            document.getElementById('bookButton').disabled = !allFieldsFilled;
        }

        // Listen to input changes to enable proceed button
        document.addEventListener('input', function(e) {
            if (e.target.closest('#passengerBookingForm')) {
                checkFormCompletion();
            }
        });

        document.addEventListener('change', function(e) {
            if (e.target.closest('#passengerBookingForm')) {
                checkFormCompletion();
            }
        });

        // Proceed to Payment button - Submit form with Pending payment mode
        document.getElementById('bookButton').addEventListener('click', function() {
            // Set payment mode to Pending for reservation
            document.getElementById('paymentModeField').value = 'Pending';
            // Submit the form
            document.getElementById('passengerBookingForm').submit();
        });

        // Initialize
        generatePassengerForms(1);

        // Auto-hide toast notifications after 5 seconds
        setTimeout(function() {
            const successToast = document.getElementById('successToast');
            const errorToast = document.getElementById('errorToast');

            if (successToast) {
                const bsToast = new bootstrap.Toast(successToast);
                bsToast.hide();
            }
            if (errorToast) {
                const bsToast = new bootstrap.Toast(errorToast);
                bsToast.hide();
            }
        }, 5000);
    </script>

    <style>
        .passenger-card {
            border-left: 4px solid #0d6efd;
        }

        .staff-body {
            padding: 2rem;
            background: #f8f9fa;
        }

        .svl-title {
            margin-bottom: 2rem;
        }

        .section-header {
            font-size: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #e9ecef;
            color: #495057;
        }

        .section-header i {
            margin-right: 0.5rem;
        }

        /* Toast styling */
        .toast {
            min-width: 300px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            font-size: 1rem;
        }

        .toast-body {
            padding: 1rem;
        }
    </style>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\clint\Desktop\Capstone\resources\views/authorized/staff/passengerbooking.blade.php ENDPATH**/ ?>