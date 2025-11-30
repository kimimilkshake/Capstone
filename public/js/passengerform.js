document.addEventListener("DOMContentLoaded", function () {
    const numPassengersSelect = document.getElementById("numPassengers");
    const passengerSections = document.getElementById("passengerSections");
    const bookingForm = document.getElementById("bookingForm");
    const loader = document.getElementById("ocrLoader");
    let accommodationsWithCots = []; // Will store accommodation data with available cots

    // Get accommodations data from the page (basic info)
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
            // Build accommodation options from vessel data
            const accommodationOptions = accommodations
                .map(
                    (acc) =>
                        `<option value="${acc.accommodation_id}" data-name="${
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
                        <div class="col-md-8">
                            <label class="form-label">Passenger Type</label>
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
                            <label class="form-label">Suffix</label>
                            <input type="text" class="form-control" name="suffix" placeholder="Jr., Sr., III">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">First Name</label>
                            <input type="text" class="form-control first-name" name="first_name" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Last Name</label>
                            <input type="text" class="form-control last-name" name="last_name" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Middle Initial</label>
                            <input type="text" maxlength="1" class="form-control text-center" name="middle_initial">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Age</label>
                            <input type="number" min="0" class="form-control" name="age" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Gender</label>
                            <select class="form-select" name="gender" required>
                                <option value="">Select Gender</option>
                                <option>Male</option>
                                <option>Female</option>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Address</label>
                            <input type="text" class="form-control" name="address" required>
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
                            <select class="form-select cot-select" name="cot_number" required disabled>
                                <option value="">Select Accommodation First</option>
                            </select>
                        </div>
                    </div>
                </div>
            `;
            passengerSections.insertAdjacentHTML("beforeend", passengerHTML);
        }

        // Attach event listeners for accommodation selection
        document.querySelectorAll(".accommodation-select").forEach((select) => {
            select.addEventListener("change", function () {
                const passengerForm = this.closest(".passenger-form");
                const cotSelect = passengerForm.querySelector(".cot-select");
                const accommodationId = this.value;

                if (!accommodationId) {
                    cotSelect.disabled = true;
                    cotSelect.innerHTML =
                        '<option value="">Select Accommodation First</option>';
                    return;
                }

                // Find the accommodation data
                const accommodation = accommodationsWithCots.find(
                    (acc) => acc.accommodation_id == accommodationId
                );

                if (accommodation && accommodation.available_cots) {
                    // Populate cot options with only available cots
                    cotSelect.disabled = false;
                    cotSelect.innerHTML =
                        '<option value="">Select Cot</option>' +
                        accommodation.available_cots
                            .map(
                                (cot) =>
                                    `<option value="${cot}">${cot}</option>`
                            )
                            .join("");

                    // Update cot availability when selection changes
                    updateCotAvailability();
                } else {
                    cotSelect.disabled = true;
                    cotSelect.innerHTML =
                        '<option value="">No available cots</option>';
                }
            });
        });

        // Attach change listeners for cot selects to prevent duplicate selections
        document.querySelectorAll(".cot-select").forEach((s) => {
            s.addEventListener("change", updateCotAvailability);
        });

        // Attach passenger type listeners
        document.querySelectorAll(".passenger-type").forEach((select) => {
            select.addEventListener("change", function () {
                const passengerForm = this.closest(".passenger-form");
                const idFields = passengerForm.querySelector(".id-fields");
                const idNumberInput = passengerForm.querySelector(".id-number");
                const idUploadInput = passengerForm.querySelector(".id-upload");

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
        });
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
                ".accommodation-select"
            );
            const accommodationId = accommodationSelect.value;

            if (!accommodationId) return;

            const accommodation = accommodationsWithCots.find(
                (acc) => acc.accommodation_id == accommodationId
            );

            if (!accommodation || !accommodation.available_cots) return;

            // Rebuild options, hiding already selected cots
            const options = ['<option value="">Select Cot</option>'];

            accommodation.available_cots.forEach((cot) => {
                // Only show cots that aren't selected by other passengers
                // OR this cot is the current selection of this passenger
                if (!selectedCots.has(cot) || cot === currentValue) {
                    options.push(
                        `<option value="${cot}"${
                            cot === currentValue ? " selected" : ""
                        }>${cot}</option>`
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
                    `/voyage/available-cots-by-accommodation?voyage_id=${voyageIdEl.value}`
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
                        accommodationsWithCots
                    );
                }
            } catch (err) {
                console.error(
                    "Failed to fetch available cots by accommodation",
                    err
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

                // Get accommodation name from the selected option
                const accommodationSelect = formEl.querySelector(
                    ".accommodation-select"
                );
                const selectedOption =
                    accommodationSelect.options[
                        accommodationSelect.selectedIndex
                    ];
                const accommodationName = selectedOption
                    ? selectedOption.dataset.name
                    : "";

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
                    accommodation_type: accommodationName,
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
