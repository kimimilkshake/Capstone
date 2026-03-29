document.addEventListener("DOMContentLoaded", function () {
    const numPassengersSelect = document.getElementById("numPassengers");
    const passengerSections = document.getElementById("passengerSections");
    const bookingForm = document.getElementById("bookingForm");
    const loader = document.getElementById("ocrLoader");
    let accommodationsWithCots = []; // Will store accommodation data with available cots
    let currentPromo = null; // Store current promo info

    // Get accommodations data from the page (basic info)
    let accommodations = [];
    try {
        const accommodationsData = document.getElementById(
            "accommodations-data",
        );
        if (accommodationsData) {
            accommodations = JSON.parse(accommodationsData.textContent);
        }
    } catch (error) {
        console.error("Failed to load accommodations data:", error);
    }

    function createPassengerHTML(i) {
        const accommodationOptions = accommodations
            .map(
                (acc) =>
                    `<option value="${acc.accommodation_id}" data-name="${
                        acc.accommodation_name
                    }" data-price="${acc.accommodation_regular_price}">${
                        acc.accommodation_name
                    } - ₱${parseFloat(
                        acc.accommodation_regular_price,
                    ).toFixed(2)}</option>`,
            )
            .join("");

        return `
            <div class="passenger-form mb-4 p-3 bg-white rounded shadow-sm" data-passenger="${i}">
                <h6 class="fw-bold mb-3 text-primary">Personal Information - Person ${i}</h6>
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label">Passenger Type <span class="text-danger">*</span></label>
                        <select class="form-select passenger-type" name="type" required>
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
                        <label class="form-label">Promo Code <span class="text-muted">(Optional)</span></label>
                        <input type="text" class="form-control passenger-promo-code" name="promo_code" placeholder="Enter promo code">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">First Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control first-name" name="first_name" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Last Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control last-name" name="last_name" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Middle Initial</label>
                        <input type="text" maxlength="1" class="form-control text-center" name="middle_initial">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Suffix</label>
                        <input type="text" class="form-control" name="suffix" placeholder="Jr., Sr., III">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Age <span class="text-danger">*</span></label>
                        <input type="number" min="0" class="form-control" name="age" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Gender <span class="text-danger">*</span></label>
                        <select class="form-select" name="gender" required>
                            <option value="">Select Gender</option>
                            <option>Male</option>
                            <option>Female</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Province <span class="text-danger">*</span></label>
                        <select class="form-select province-select" name="province" required>
                            <option value="">Select Province</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">City/Municipality <span class="text-danger">*</span></label>
                        <select class="form-select city-select" name="city" required disabled>
                            <option value="">Select City/Municipality</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Barangay <span class="text-danger">*</span></label>
                        <select class="form-select barangay-select" name="barangay" required disabled>
                            <option value="">Select Barangay</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Contact Number <span class="text-danger">*</span></label>
                        <input type="tel" class="form-control" name="contact_number" placeholder="09XXXXXXXXX" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email Address <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" name="email" placeholder="name@email.com" required>
                    </div>

                    <div class="id-fields mt-3" style="display:none;">
                        <div class="col-md-6 mt-2">
                            <label class="form-label">ID Number</label>
                            <input type="text" class="form-control id-number" name="id_number">
                        </div>
                        <div class="col-md-6 mt-2">
                            <label class="form-label">Upload ID Image</label>
                            <input type="file" class="form-control id-upload" accept="image/*">
                        </div>
                    </div>
                </div>

                <hr>

                <h6 class="fw-bold mb-3 text-success">Accommodation - Person ${i}</h6>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Accommodation Type <span class="text-danger">*</span></label>
                        <select class="form-select accommodation-select" name="accommodation_type" required>
                            <option value="">Select Accommodation</option>
                            ${accommodationOptions}
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Cot Number <span class="text-danger">*</span></label>
                        <select class="form-select cot-select" name="cot_number" required disabled>
                            <option value="">Select Accommodation First</option>
                        </select>
                    </div>
                </div>
            </div>
        `;
    }

    function attachListenersToForm(form) {
        // Accommodation select listener
        const accSelect = form.querySelector(".accommodation-select");
        if (accSelect) {
            accSelect.addEventListener("change", function () {
                const cotSelect = form.querySelector(".cot-select");
                const accommodationId = this.value;

                const cotPlanImage = document.getElementById("cotPlanImage");
                const cotPlanPlaceholder =
                    document.getElementById("cotPlanPlaceholder");

                if (!accommodationId) {
                    cotSelect.disabled = true;
                    cotSelect.innerHTML =
                        '<option value="">Select Accommodation First</option>';
                    if (cotPlanImage) {
                        cotPlanImage.style.display = "none";
                        cotPlanPlaceholder.textContent =
                            "No accommodation selected";
                        cotPlanPlaceholder.style.display = "flex";
                    }
                    return;
                }

                const accommodation = accommodationsWithCots.find(
                    (acc) => acc.accommodation_id == accommodationId,
                );

                if (cotPlanImage) {
                    if (accommodation && accommodation.cot_plan_url) {
                        cotPlanImage.src = accommodation.cot_plan_url;
                        cotPlanImage.style.display = "";
                        cotPlanPlaceholder.style.display = "none";
                    } else {
                        cotPlanImage.style.display = "none";
                        cotPlanPlaceholder.textContent = accommodation
                            ? "No image available"
                            : "No accommodation selected";
                        cotPlanPlaceholder.style.display = "flex";
                    }
                }

                if (accommodation && accommodation.available_cots) {
                    cotSelect.disabled = false;

                    const options = accommodation.available_cots
                        .map((cot) => {
                            let cotNumber, bunkType;
                            if (
                                typeof cot === "object" &&
                                cot !== null &&
                                "number" in cot
                            ) {
                                cotNumber = parseInt(cot.number);
                                bunkType = cot.bunk_type || null;
                            } else {
                                cotNumber = parseInt(cot);
                                bunkType = null;
                            }
                            const displayText = bunkType
                                ? `${cotNumber} (${bunkType} bunk)`
                                : `${cotNumber}`;
                            return `<option value="${cotNumber}">${displayText}</option>`;
                        })
                        .join("");

                    cotSelect.innerHTML =
                        '<option value="">Select Cot</option>' + options;
                    updateCotAvailability();
                } else {
                    cotSelect.disabled = true;
                    cotSelect.innerHTML =
                        '<option value="">No available cots</option>';
                }
            });
        }

        // Cot select listener
        const cotSelect = form.querySelector(".cot-select");
        if (cotSelect) {
            cotSelect.addEventListener("change", updateCotAvailability);
        }

        // Passenger type listener
        const typeSelect = form.querySelector(".passenger-type");
        if (typeSelect) {
            typeSelect.addEventListener("change", function () {
                const idFields = form.querySelector(".id-fields");
                const idNumberInput = form.querySelector(".id-number");
                const idUploadInput = form.querySelector(".id-upload");

                if (this.value === "Regular") {
                    idFields.style.display = "none";
                    if (idNumberInput)
                        idNumberInput.removeAttribute("required");
                    if (idUploadInput)
                        idUploadInput.removeAttribute("required");
                } else {
                    idFields.style.display = "block";
                    if (idNumberInput)
                        idNumberInput.setAttribute("required", "required");
                    if (idUploadInput)
                        idUploadInput.setAttribute("required", "required");
                }
            });
        }
    }

    function generatePassengerForms(count) {
        const existingForms = passengerSections.querySelectorAll(".passenger-form");
        const currentCount = existingForms.length;

        if (count > currentCount) {
            // Add new forms only — existing ones stay untouched with their data
            for (let i = currentCount + 1; i <= count; i++) {
                passengerSections.insertAdjacentHTML("beforeend", createPassengerHTML(i));
                const newForm = passengerSections.querySelector(`.passenger-form[data-passenger="${i}"]`);
                attachListenersToForm(newForm);
            }
            // Initialize address dropdowns only for newly added forms
            initializeAddressDropdowns(currentCount);
        } else if (count < currentCount) {
            // Remove only the last form(s) — keep the rest intact with their data
            for (let i = currentCount; i > count; i--) {
                const formToRemove = passengerSections.querySelector(`.passenger-form[data-passenger="${i}"]`);
                if (formToRemove) formToRemove.remove();
            }
            updateCotAvailability();
        }
    }

    // PSGC API integration for Philippine addresses
    const PSGC_API_BASE = "https://psgc.gitlab.io/api";
    let provincesCache = null;
    let citiesCache = {};
    let barangaysCache = {};

    async function fetchPSGC(endpoint) {
        try {
            const response = await fetch(`${PSGC_API_BASE}${endpoint}`);
            if (!response.ok) throw new Error("Failed to fetch");
            return await response.json();
        } catch (error) {
            console.error("PSGC API Error:", error);
            return null;
        }
    }

    async function initializeAddressDropdowns(startFrom) {
        const allForms = document.querySelectorAll(".passenger-form");
        // Only initialize forms that are new (skip already-initialized ones)
        const formsToInit = startFrom !== undefined
            ? Array.from(allForms).slice(startFrom)
            : Array.from(allForms);

        for (const form of formsToInit) {
            const provinceSelect = form.querySelector(".province-select");
            const citySelect = form.querySelector(".city-select");
            const barangaySelect = form.querySelector(".barangay-select");

            // Load all provinces once
            if (!provincesCache) {
                const provinces = await fetchPSGC("/provinces");
                if (provinces) {
                    provincesCache = provinces.sort((a, b) =>
                        a.name.localeCompare(b.name),
                    );
                }
            }

            // Populate province dropdown
            if (provincesCache) {
                provincesCache.forEach((province) => {
                    const option = document.createElement("option");
                    option.value = province.code;
                    option.textContent = province.name;
                    option.dataset.name = province.name;
                    provinceSelect.appendChild(option);
                });
            }

            // Province change handler
            provinceSelect.addEventListener("change", async function () {
                const provinceCode = this.value;

                citySelect.innerHTML =
                    '<option value="">Loading cities...</option>';
                barangaySelect.innerHTML =
                    '<option value="">Select Barangay</option>';
                barangaySelect.disabled = true;
                citySelect.disabled = true;

                if (provinceCode) {
                    // Load cities/municipalities for selected province
                    if (!citiesCache[provinceCode]) {
                        const cities = await fetchPSGC(
                            `/provinces/${provinceCode}/cities-municipalities`,
                        );
                        if (cities) {
                            citiesCache[provinceCode] = cities.sort((a, b) =>
                                a.name.localeCompare(b.name),
                            );
                        }
                    }

                    citySelect.innerHTML =
                        '<option value="">Select City/Municipality</option>';

                    if (citiesCache[provinceCode]) {
                        citiesCache[provinceCode].forEach((city) => {
                            const option = document.createElement("option");
                            option.value = city.code;
                            option.textContent = city.name;
                            option.dataset.name = city.name;
                            citySelect.appendChild(option);
                        });
                        citySelect.disabled = false;
                    }
                } else {
                    citySelect.innerHTML =
                        '<option value="">Select City/Municipality</option>';
                    citySelect.disabled = true;
                }
            });

            // City change handler
            citySelect.addEventListener("change", async function () {
                const cityCode = this.value;

                barangaySelect.innerHTML =
                    '<option value="">Loading barangays...</option>';
                barangaySelect.disabled = true;

                if (cityCode) {
                    // Load barangays for selected city
                    if (!barangaysCache[cityCode]) {
                        // Try both city and municipality endpoints
                        let barangays = await fetchPSGC(
                            `/cities/${cityCode}/barangays`,
                        );
                        if (!barangays) {
                            barangays = await fetchPSGC(
                                `/municipalities/${cityCode}/barangays`,
                            );
                        }
                        if (barangays) {
                            barangaysCache[cityCode] = barangays.sort((a, b) =>
                                a.name.localeCompare(b.name),
                            );
                        }
                    }

                    barangaySelect.innerHTML =
                        '<option value="">Select Barangay</option>';

                    if (barangaysCache[cityCode]) {
                        barangaysCache[cityCode].forEach((barangay) => {
                            const option = document.createElement("option");
                            option.value = barangay.name;
                            option.textContent = barangay.name;
                            barangaySelect.appendChild(option);
                        });
                        barangaySelect.disabled = false;
                    }
                } else {
                    barangaySelect.innerHTML =
                        '<option value="">Select Barangay</option>';
                    barangaySelect.disabled = true;
                }
            });
        }
    }

    // Function to update cot availability across all passenger forms
    function updateCotAvailability() {
        const allCotSelects = document.querySelectorAll(".cot-select");
        const selectedCots = new Set();

        // Collect all selected cots
        allCotSelects.forEach((select) => {
            if (select.value) {
                selectedCots.add(parseInt(select.value));
            }
        });

        // Update each cot select to disable already selected cots
        allCotSelects.forEach((select) => {
            const currentValue = select.value ? parseInt(select.value) : null;
            const passengerForm = select.closest(".passenger-form");
            const accommodationSelect = passengerForm.querySelector(
                ".accommodation-select",
            );
            const accommodationId = accommodationSelect.value;

            if (!accommodationId) return;

            const accommodation = accommodationsWithCots.find(
                (acc) => acc.accommodation_id == accommodationId,
            );

            if (!accommodation || !accommodation.available_cots) return;

            // Rebuild options, hiding already selected cots
            const options = ['<option value="">Select Cot</option>'];

            accommodation.available_cots.forEach((cot) => {
                // Handle both old format (number) and new format (object with number and bunk_type)
                let cotNumber, bunkType;

                if (
                    typeof cot === "object" &&
                    cot !== null &&
                    "number" in cot
                ) {
                    cotNumber = parseInt(cot.number);
                    bunkType = cot.bunk_type || null;
                } else {
                    cotNumber = parseInt(cot);
                    bunkType = null;
                }

                // Only show cots that aren't selected by other passengers
                // OR this cot is the current selection of this passenger
                if (
                    !selectedCots.has(cotNumber) ||
                    cotNumber === currentValue
                ) {
                    const displayText = bunkType
                        ? `${cotNumber} (${bunkType} bunk)`
                        : `${cotNumber}`;
                    options.push(
                        `<option value="${cotNumber}"${
                            cotNumber === currentValue ? " selected" : ""
                        }>${displayText}</option>`,
                    );
                }
            });

            select.innerHTML = options.join("");
        });
    }

    async function init() {
        // Fetch available cots per accommodation for this voyage
        const voyageIdEl = document.getElementById("voyageId");

        if (voyageIdEl && voyageIdEl.value) {
            try {
                const resp = await fetch(
                    `/voyage/available-cots-by-accommodation?voyage_id=${voyageIdEl.value}`,
                );
                const json = await resp.json();

                if (
                    json &&
                    json.success &&
                    Array.isArray(json.accommodations)
                ) {
                    accommodationsWithCots = json.accommodations;
                    console.log(
                        "Loaded accommodation cot data:",
                        accommodationsWithCots,
                    );
                }
            } catch (err) {
                console.error(
                    "Failed to fetch available cots by accommodation",
                    err,
                );
            }
        }

        generatePassengerForms(parseInt(numPassengersSelect.value));
        numPassengersSelect.addEventListener("change", function () {
            generatePassengerForms(parseInt(this.value));
        });
    }

    init();

    bookingForm.addEventListener("submit", async function (e) {
        e.preventDefault();

        loader.style.display = "flex";

        try {
            console.log("Starting form submission...");
            const passengerForms = document.querySelectorAll(".passenger-form");
            console.log("Found", passengerForms.length, "passenger forms");

            // STEP 1: Validate promo codes for each passenger
            const passengerPromos = {}; // Store promo data per passenger

            for (const form of passengerForms) {
                const passengerNumber = form.dataset.passenger || "Unknown";
                const promoCodeInput = form.querySelector(
                    ".passenger-promo-code",
                );
                const promoCode = promoCodeInput
                    ? promoCodeInput.value.trim().toUpperCase()
                    : "";

                if (promoCode) {
                    try {
                        const promoResponse = await fetch(
                            "/api/validate-promo",
                            {
                                method: "POST",
                                headers: {
                                    "Content-Type": "application/json",
                                    Accept: "application/json",
                                    "X-CSRF-TOKEN": bookingForm.dataset.csrf,
                                },
                                body: JSON.stringify({
                                    promo_code: promoCode,
                                }),
                            },
                        );

                        if (
                            !promoResponse.ok &&
                            promoResponse.headers.get("content-type") &&
                            !promoResponse.headers
                                .get("content-type")
                                .includes("application/json")
                        ) {
                            throw new Error(
                                `Promo validation failed with status ${promoResponse.status}. Please try again.`,
                            );
                        }

                        const promoData = await promoResponse.json();

                        if (!promoData.success) {
                            loader.style.display = "none";
                            showToast(
                                `Passenger ${passengerNumber}: Invalid promo code "${promoCode}"`,
                                "danger",
                            );
                            return;
                        }

                        // Store valid promo for this passenger
                        passengerPromos[passengerNumber] = {
                            promo_id: promoData.promo.promo_id,
                            promo_code: promoData.promo.promo_code,
                            promo_discount_rate:
                                promoData.promo.promo_discount_rate,
                        };
                    } catch (error) {
                        loader.style.display = "none";
                        console.error(
                            `Promo validation error for Passenger ${passengerNumber}:`,
                            error,
                        );
                        showToast(
                            `Error validating promo for Passenger ${passengerNumber}`,
                            "danger",
                        );
                        return;
                    }
                }
            }

            // STEP 2: ID verification for non-regular passengers
            for (const form of passengerForms) {
                const type = form.querySelector(".passenger-type").value;
                const passengerNumber = form.dataset.passenger || "Unknown";
                console.log(
                    `Processing Passenger ${passengerNumber}, Type: ${type}`,
                );

                if (type === "Regular") {
                    console.log(
                        `Skipping ID verification for Passenger ${passengerNumber} (Regular)`,
                    );
                    continue;
                }

                console.log(
                    `Starting ID verification for Passenger ${passengerNumber} (${type})`,
                );

                const firstName = form
                    .querySelector(".first-name")
                    .value.trim()
                    .toLowerCase();
                const lastName = form
                    .querySelector(".last-name")
                    .value.trim()
                    .toLowerCase();
                const idNumber = form.querySelector(".id-number").value.trim();
                const idFile = form.querySelector(".id-upload").files[0];

                if (!idFile) {
                    loader.style.display = "none";
                    showToast(
                        `Passenger ${passengerNumber} (${type}): Please upload an ID image for discount verification.`,
                        "danger",
                    );
                    return;
                }

                const formData = new FormData();
                formData.append("file", idFile);

                console.log(
                    `Sending OCR request for Passenger ${passengerNumber}...`,
                );
                const response = await fetch("/ocr/parse", {
                    method: "POST",
                    headers: {
                        Accept: "application/json",
                        "X-CSRF-TOKEN": bookingForm.dataset.csrf,
                    },
                    body: formData,
                });

                if (
                    !response.ok &&
                    response.headers.get("content-type") &&
                    !response.headers
                        .get("content-type")
                        .includes("application/json")
                ) {
                    throw new Error(
                        `OCR service returned ${response.status}. Please try again.`,
                    );
                }

                const data = await response.json();
                if (!data.text)
                    throw new Error(
                        `OCR failed for Passenger ${passengerNumber}`,
                    );

                const scanned = data.text.toLowerCase().replace(/_/g, "");
                console.log(
                    `OCR result for Passenger ${passengerNumber}:`,
                    scanned.substring(0, 100) + "...",
                );

                if (
                    !scanned.includes(firstName) ||
                    !scanned.includes(lastName) ||
                    !scanned.includes(idNumber.toLowerCase())
                ) {
                    loader.style.display = "none";
                    showToast(
                        `Passenger ${passengerNumber} (${firstName} ${lastName}): ID does not match. Please check your input or upload a clearer photo.`,
                        "danger",
                    );
                    return;
                }

                console.log(
                    `✓ ID verification passed for Passenger ${passengerNumber}`,
                );
            }

            console.log(
                "ID verification completed, gathering passenger data...",
            );
            // STEP 3: Gather passenger data and voyage info then submit to server
            const passengers = [];
            for (const formEl of passengerForms) {
                const passengerNumber = formEl.dataset.passenger || "1";
                const passengerType =
                    formEl.querySelector(".passenger-type").value;
                const idNumberInput = formEl.querySelector(
                    'input[name="id_number"]',
                );

                // Get accommodation name from the selected option
                const accommodationSelect = formEl.querySelector(
                    ".accommodation-select",
                );
                const selectedOption =
                    accommodationSelect.options[
                        accommodationSelect.selectedIndex
                    ];
                const accommodationName = selectedOption
                    ? selectedOption.dataset.name
                    : "";

                const passengerData = {
                    type: passengerType,
                    suffix: formEl.querySelector('input[name="suffix"]').value,
                    first_name: formEl
                        .querySelector(".first-name")
                        .value.trim(),
                    last_name: formEl.querySelector(".last-name").value.trim(),
                    middle_initial: formEl
                        .querySelector('input[name="middle_initial"]')
                        .value.trim(),
                    gender: formEl.querySelector('select[name="gender"]').value,
                    province:
                        formEl.querySelector(".province-select")
                            .selectedOptions[0]?.dataset.name || "",
                    city:
                        formEl.querySelector(".city-select").selectedOptions[0]
                            ?.dataset.name || "",
                    barangay: formEl.querySelector(".barangay-select").value,
                    address: [
                        formEl.querySelector(".barangay-select").value,
                        formEl.querySelector(".city-select").selectedOptions[0]
                            ?.dataset.name || "",
                        formEl.querySelector(".province-select")
                            .selectedOptions[0]?.dataset.name || "",
                    ]
                        .filter(Boolean)
                        .join(", "),
                    age: formEl.querySelector('input[name="age"]').value,
                    contact_number: formEl.querySelector(
                        'input[name="contact_number"]',
                    ).value,
                    email: formEl.querySelector('input[name="email"]').value,
                    id_number:
                        passengerType === "Regular"
                            ? null
                            : idNumberInput
                              ? idNumberInput.value
                              : null,
                    accommodation_type: accommodationName,
                    cot_number: formEl.querySelector(
                        'select[name="cot_number"]',
                    ).value,
                };

                // Add per-passenger promo if available
                if (passengerPromos[passengerNumber]) {
                    passengerData.promo_id =
                        passengerPromos[passengerNumber].promo_id;
                    passengerData.promo_code =
                        passengerPromos[passengerNumber].promo_code;
                    passengerData.promo_discount_rate =
                        passengerPromos[passengerNumber].promo_discount_rate;
                }

                passengers.push(passengerData);
            }

            // Voyage info from hidden inputs
            const routeFrom = document.getElementById("routeFrom")
                ? document.getElementById("routeFrom").value
                : null;
            const routeTo = document.getElementById("routeTo")
                ? document.getElementById("routeTo").value
                : null;
            const departureDate = document.getElementById("departureDate")
                ? document.getElementById("departureDate").value
                : null;
            const departureTime = document.getElementById("departureTime")
                ? document.getElementById("departureTime").value
                : null;
            const voyageId = document.getElementById("voyageId")
                ? document.getElementById("voyageId").value
                : null;

            const payload = {
                passengers, // Each passenger has their own promo_id, promo_code, promo_discount_rate
                route_from: routeFrom,
                route_to: routeTo,
                departure_date: departureDate,
                departure_time: departureTime,
                voyage_id: voyageId,
            };

            console.log("Submitting booking with payload:", payload);

            // POST JSON to booking endpoint
            const submitUrl = bookingForm.dataset.submitUrl;
            console.log("Submit URL:", submitUrl);

            const res = await fetch(submitUrl, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    Accept: "application/json",
                    "X-CSRF-TOKEN": bookingForm.dataset.csrf,
                },
                body: JSON.stringify(payload),
            });

            if (
                !res.ok &&
                res.headers.get("content-type") &&
                !res.headers.get("content-type").includes("application/json")
            ) {
                throw new Error(
                    `Server returned ${res.status} ${res.statusText}. Please try again.`,
                );
            }

            const result = await res.json();
            console.log("Booking submission result:", result);

            loader.style.display = "none";
            if (!result.success) {
                showToast(
                    result.message ||
                        "Failed to create booking hold. Please try again.",
                    "danger",
                );
                return;
            }

            // Show persistent success toast before redirecting
            showToast("Booking submitted successfully! Redirecting...", "success", true);

            // Redirect to confirm page for this booking
            setTimeout(() => {
                if (result.redirect_url) {
                    window.location.href = result.redirect_url;
                } else if (result.booking_ref_no) {
                    window.location.href = `/passenger/confirmbooking/${result.booking_ref_no}`;
                } else {
                    window.location.href = "/passenger/confirmbooking";
                }
            }, 1500);
        } catch (err) {
            loader.style.display = "none";
            console.error("Detailed error:", err);
            console.error("Error stack:", err.stack);

            // More specific error messages
            if (err.message && err.message.includes("OCR")) {
                showToast(
                    "Error during ID verification: " +
                        err.message +
                        ". Please try again.",
                    "danger",
                );
            } else if (err.message && err.message.includes("fetch")) {
                showToast(
                    "Network error occurred. Please check your connection and try again.",
                    "danger",
                );
            } else {
                showToast(
                    "Error during form submission: " +
                        (err.message || "Unknown error") +
                        ". Please check the browser console for details.",
                    "danger",
                );
            }
        }
    });
});
