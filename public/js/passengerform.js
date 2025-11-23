document.addEventListener("DOMContentLoaded", function () {
    const numPassengersSelect = document.getElementById("numPassengers");
    const passengerSections = document.getElementById("passengerSections");
    const bookingForm = document.getElementById("bookingForm");
    const loader = document.getElementById("ocrLoader");
    let unavailableCots = [];

    // Get accommodations data from the page
    let accommodations = [];
    try {
        const accommodationsData = document.getElementById(
            "accommodations-data"
        );
        if (accommodationsData) {
            accommodations = JSON.parse(accommodationsData.textContent);
        }
    } catch (error) {
        console.error("Failed to load accommodations data:", error);
    }

    function generatePassengerForms(count) {
        passengerSections.innerHTML = "";
        for (let i = 1; i <= count; i++) {
            // Build cot options dynamically (1..50)
            const cotOptions = Array.from(
                { length: 50 },
                (_, idx) => `<option>${idx + 1}</option>`
            ).join("");

            // Build accommodation options from vessel data
            const accommodationOptions = accommodations
                .map(
                    (acc) =>
                        `<option value="${
                            acc.accommodation_name
                        }" data-price="${acc.accommodation_regular_price}">${
                            acc.accommodation_name
                        } - ₱${parseFloat(
                            acc.accommodation_regular_price
                        ).toFixed(2)}</option>`
                )
                .join("");
            const passengerHTML = `
                <div class="passenger-form mb-4 p-3 bg-white rounded shadow-sm" data-passenger="${i}">
                    <h6 class="fw-bold mb-3 text-primary">Personal Information - Person ${i}</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Type</label>
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
                        <div class="col-md-6">
                            <label class="form-label">Suffix</label>
                            <input type="text" class="form-control" name="suffix">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">First Name</label>
                            <input type="text" class="form-control first-name" name="first_name" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Last Name</label>
                            <input type="text" class="form-control last-name" name="last_name" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Middle Initial</label>
                            <input type="text" maxlength="1" class="form-control text-center" name="middle_initial">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Gender</label>
                            <select class="form-select" name="gender" required>
                                <option value="">Select Gender</option>
                                <option>Male</option>
                                <option>Female</option>
                            </select>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Address</label>
                            <input type="text" class="form-control" name="address" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Age</label>
                            <input type="number" min="0" class="form-control" name="age" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Contact Number</label>
                            <input type="tel" class="form-control" name="contact_number" placeholder="09XXXXXXXXX" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email Address</label>
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
                            <label class="form-label">Accommodation Type</label>
                            <select class="form-select accommodation-select" name="accommodation_type" required>
                                <option value="">Select Accommodation</option>
                                ${accommodationOptions}
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Cot Number</label>
                            <select class="form-select" name="cot_number" required>
                                <option value="">Select Cot</option>
                                ${cotOptions}
                            </select>
                        </div>
                    </div>
                </div>
            `;
            passengerSections.insertAdjacentHTML("beforeend", passengerHTML);
        }

        // After rendering, wire up cot-selection uniqueness among current selects
        function updateCotOptions() {
            const selects = document.querySelectorAll(
                'select[name="cot_number"]'
            );
            // collect selected values
            const chosen = [];
            selects.forEach((s) => {
                if (s.value) chosen.push(s.value);
            });
            // for each select, disable options that are chosen by other selects
            selects.forEach((s) => {
                const val = s.value;
                Array.from(s.options).forEach((opt) => {
                    if (!opt.value) return; // skip placeholder
                    // enable by default unless globally unavailable
                    if (unavailableCots.includes(opt.value)) {
                        opt.disabled = true;
                    } else {
                        opt.disabled = false;
                    }
                });
                selects.forEach((other) => {
                    if (other === s) return;
                    const otherVal = other.value;
                    if (otherVal) {
                        const optionToDisable = s.querySelector(
                            'option[value="' + otherVal + '"]'
                        );
                        if (optionToDisable) optionToDisable.disabled = true;
                    }
                });
                // keep currently selected value enabled so the select shows it
                if (val) {
                    const cur = s.querySelector('option[value="' + val + '"]');
                    if (cur) cur.disabled = false;
                }
            });
        }

        // Attach change listeners
        document.querySelectorAll('select[name="cot_number"]').forEach((s) => {
            s.addEventListener("change", updateCotOptions);
        });

        // initial update
        updateCotOptions();

        document.querySelectorAll(".passenger-type").forEach((select) => {
            select.addEventListener("change", function () {
                const passengerForm = this.closest(".passenger-form");
                const idFields = passengerForm.querySelector(".id-fields");
                const idNumberInput = passengerForm.querySelector(".id-number");
                const idUploadInput = passengerForm.querySelector(".id-upload");

                if (this.value === "Regular") {
                    idFields.style.display = "none";
                    // Remove required attribute for regular passengers
                    if (idNumberInput)
                        idNumberInput.removeAttribute("required");
                    if (idUploadInput)
                        idUploadInput.removeAttribute("required");
                } else {
                    idFields.style.display = "block";
                    // Add required attribute for non-regular passengers
                    if (idNumberInput)
                        idNumberInput.setAttribute("required", "required");
                    if (idUploadInput)
                        idUploadInput.setAttribute("required", "required");
                }
            });
        });
    }

    async function init() {
        // try to fetch unavailable cots for this voyage (route_from, route_to, departure_date)
        const routeFromEl = document.getElementById("routeFrom");
        const routeToEl = document.getElementById("routeTo");
        const departureDateEl = document.getElementById("departureDate");
        if (routeFromEl && routeToEl && departureDateEl) {
            try {
                const q = new URLSearchParams({
                    route_from: routeFromEl.value,
                    route_to: routeToEl.value,
                    departure_date: departureDateEl.value,
                });
                const resp = await fetch(
                    "/voyage/unavailable-cots?" + q.toString()
                );
                const json = await resp.json();
                if (json && json.success && Array.isArray(json.unavailable)) {
                    unavailableCots = json.unavailable.map(String);
                }
            } catch (err) {
                console.error("Failed to fetch unavailable cots", err);
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

            for (const form of passengerForms) {
                const type = form.querySelector(".passenger-type").value;
                console.log("Processing passenger type:", type);

                if (type === "Regular") {
                    console.log(
                        "Skipping ID verification for Regular passenger"
                    );
                    continue;
                }

                console.log("Starting ID verification for", type);

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
                    alert(
                        "Please upload an ID image for discount verification."
                    );
                    return;
                }

                const formData = new FormData();
                formData.append("file", idFile);

                const response = await fetch("/ocr/parse", {
                    method: "POST",
                    headers: {
                        "X-CSRF-TOKEN": bookingForm.dataset.csrf,
                    },
                    body: formData,
                });

                const data = await response.json();
                if (!data.text) throw new Error("OCR failed");

                const scanned = data.text.toLowerCase().replace(/_/g, "");
                if (
                    !scanned.includes(firstName) ||
                    !scanned.includes(lastName) ||
                    !scanned.includes(idNumber.toLowerCase())
                ) {
                    loader.style.display = "none";
                    alert(
                        "ID does not match. Please check your input or upload a clearer photo."
                    );
                    return;
                }
            }

            console.log(
                "ID verification completed, gathering passenger data..."
            );
            // Gather passenger data and voyage info then submit to server to create a hold
            const passengers = [];
            for (const formEl of passengerForms) {
                const passengerType =
                    formEl.querySelector(".passenger-type").value;
                const idNumberInput = formEl.querySelector(
                    'input[name="id_number"]'
                );

                passengers.push({
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
                    address: formEl.querySelector('input[name="address"]')
                        .value,
                    age: formEl.querySelector('input[name="age"]').value,
                    contact_number: formEl.querySelector(
                        'input[name="contact_number"]'
                    ).value,
                    email: formEl.querySelector('input[name="email"]').value,
                    id_number:
                        passengerType === "Regular"
                            ? null
                            : idNumberInput
                            ? idNumberInput.value
                            : null,
                    accommodation_type: formEl.querySelector(
                        'select[name="accommodation_type"]'
                    ).value,
                    cot_number: formEl.querySelector(
                        'select[name="cot_number"]'
                    ).value,
                });
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
                passengers,
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
                    "X-CSRF-TOKEN": bookingForm.dataset.csrf,
                },
                body: JSON.stringify(payload),
            });

            const result = await res.json();
            console.log("Booking submission result:", result);

            loader.style.display = "none";
            if (!result.success) {
                alert(
                    result.message ||
                        "Failed to create booking hold. Please try again."
                );
                return;
            }

            // Redirect to confirm page for this booking
            if (result.redirect_url) {
                window.location.href = result.redirect_url;
            } else if (result.booking_ref_no) {
                window.location.href = `/passenger/confirmbooking/${result.booking_ref_no}`;
            } else {
                window.location.href = "/passenger/confirmbooking";
            }
        } catch (err) {
            loader.style.display = "none";
            console.error("Detailed error:", err);
            console.error("Error stack:", err.stack);

            // More specific error messages
            if (err.message && err.message.includes("OCR")) {
                alert(
                    "Error during ID verification: " +
                        err.message +
                        ". Please try again."
                );
            } else if (err.message && err.message.includes("fetch")) {
                alert(
                    "Network error occurred. Please check your connection and try again."
                );
            } else {
                alert(
                    "Error during form submission: " +
                        (err.message || "Unknown error") +
                        ". Please check the browser console for details."
                );
            }
        }
    });
});
