// Grab elements
const routeFromSelect = document.getElementById("routeFrom");
const routeToSelect = document.getElementById("routeTo");
const tripDateInput = document.getElementById("tripDate");
const departureTimeSelect = document.getElementById("departureTime");
const proceedBtn = document.getElementById("proceedBtn");
const bookingTypeRadios = document.getElementsByName("bookingType");

// Parse voyages data from Blade (updated from routes to voyages)
const voyages = JSON.parse(document.getElementById("voyages-data").textContent);

let availableDates = [];
let availableVoyages = []; // Store voyages for selected route

// Validate cargo booking cutoff based on departure date and time
function validateCargoCutoff(departureDate, departureTimeStr) {
    if (!departureDate || !departureTimeStr) {
        return {
            valid: false,
            message: "Invalid voyage departure information.",
        };
    }

    // Parse departure time (format: HH:MM:SS)
    const timeParts = departureTimeStr.split(":");
    const depHour = parseInt(timeParts[0]);

    const now = new Date();
    const depDate = new Date(departureDate);
    const depDateStart = new Date(depDate);
    depDateStart.setHours(0, 0, 0, 0);

    // 12:00 AM to 6:00 PM (00:00 to 18:00) - must book day before
    if (depHour <= 18) {
        if (now >= depDateStart) {
            return {
                valid: false,
                message: "Cargo booking is not allowed anymore at this time",
            };
        }
        return { valid: true };
    }

    // 7:00 PM to 11:59 PM (19:00+) - same day booking with 5:00 PM cutoff
    const depDateEnd = new Date(depDate);
    depDateEnd.setHours(23, 59, 59, 999);

    if (now > depDateEnd) {
        return {
            valid: false,
            message: "Cargo booking is not allowed anymore at this time",
        };
    }

    const isSameDay =
        now.getFullYear() === depDate.getFullYear() &&
        now.getMonth() === depDate.getMonth() &&
        now.getDate() === depDate.getDate();

    if (isSameDay) {
        const cutoff = new Date(depDateStart);
        cutoff.setHours(17, 0, 0, 0); // 5:00 PM
        if (now >= cutoff) {
            return {
                valid: false,
                message: "Cargo booking is not allowed anymore at this time",
            };
        }
    }

    return { valid: true };
}

// Initially disable proceed button
proceedBtn.disabled = true;

// Event listeners
routeFromSelect.addEventListener("change", updateDestinations);
routeToSelect.addEventListener("change", updateAvailableDates);
tripDateInput.addEventListener("input", updateAvailableTimes);
departureTimeSelect.addEventListener("change", checkProceedButton);
bookingTypeRadios.forEach((radio) =>
    radio.addEventListener("change", checkProceedButton),
);

// Check all fields to enable/disable proceed button
function checkProceedButton() {
    const isValid =
        routeFromSelect.value &&
        routeToSelect.value &&
        tripDateInput.value &&
        departureTimeSelect.value &&
        document.querySelector('input[name="bookingType"]:checked');
    proceedBtn.disabled = !isValid;
}

// Update destinations based on selected origin
function updateDestinations() {
    const origin = routeFromSelect.value;
    routeToSelect.innerHTML = '<option value="">Select Destination</option>';
    tripDateInput.value = "";
    tripDateInput.disabled = true;
    departureTimeSelect.innerHTML = '<option value="">Select Time</option>';
    departureTimeSelect.disabled = true;
    availableDates = [];
    availableVoyages = [];

    if (!origin) {
        routeToSelect.disabled = true;
        checkProceedButton();
        return;
    }

    const destinations = voyages
        .filter((v) => v.route_from === origin)
        .map((v) => v.route_to)
        .filter((v, i, a) => a.indexOf(v) === i);

    destinations.forEach((dest) => {
        const option = document.createElement("option");
        option.value = dest;
        option.textContent = dest;
        routeToSelect.appendChild(option);
    });

    routeToSelect.disabled = false;
    checkProceedButton();
}

// Update available dates based on selected route
function updateAvailableDates() {
    const origin = routeFromSelect.value;
    const destination = routeToSelect.value;
    tripDateInput.value = "";
    departureTimeSelect.innerHTML = '<option value="">Select Time</option>';
    departureTimeSelect.disabled = true;

    if (!origin || !destination) {
        tripDateInput.disabled = true;
        availableDates = [];
        availableVoyages = [];
        checkProceedButton();
        return;
    }

    // Find all voyages for this route
    availableVoyages = voyages.filter(
        (v) => v.route_from === origin && v.route_to === destination,
    );

    if (!availableVoyages.length) {
        alert("No voyages found for this route in the next 7 days.");
        resetSelection();
        return;
    }

    // Extract unique available dates from voyages
    availableDates = [
        ...new Set(availableVoyages.map((v) => v.departure_date)),
    ];

    tripDateInput.disabled = false;
    tripDateInput.min = new Date().toISOString().split("T")[0];

    // Set max date to 7 days from now
    const maxDate = new Date();
    maxDate.setDate(maxDate.getDate() + 7);
    tripDateInput.max = maxDate.toISOString().split("T")[0];

    checkProceedButton();
}

// Update available times based on selected date
function updateAvailableTimes() {
    const selectedDate = tripDateInput.value;
    departureTimeSelect.innerHTML = '<option value="">Select Time</option>';

    if (!selectedDate || !availableVoyages.length) {
        departureTimeSelect.disabled = true;
        checkProceedButton();
        return;
    }

    const today = new Date().toISOString().split("T")[0];

    if (selectedDate < today) {
        alert("You cannot select a past date.");
        tripDateInput.value = "";
        departureTimeSelect.disabled = true;
        checkProceedButton();
        return;
    }

    if (!availableDates.includes(selectedDate)) {
        alert("No voyages available on this date for the selected route.");
        tripDateInput.value = "";
        departureTimeSelect.disabled = true;
        checkProceedButton();
        return;
    }

    // Find voyages for the selected date
    const dateVoyages = availableVoyages.filter(
        (v) => v.departure_date === selectedDate,
    );

    // Populate departure times
    dateVoyages.forEach((voyage) => {
        const option = document.createElement("option");
        option.value = voyage.voyage_id;
        option.textContent = new Date(
            "2000-01-01 " + voyage.departure_time,
        ).toLocaleTimeString("en-US", {
            hour: "2-digit",
            minute: "2-digit",
        });
        option.dataset.voyageId = voyage.voyage_id;
        option.dataset.departureTime = voyage.departure_time;
        departureTimeSelect.appendChild(option);
    });

    departureTimeSelect.disabled = false;
    checkProceedButton();
}

// Reset
function resetSelection() {
    routeFromSelect.value = "";
    routeToSelect.innerHTML = '<option value="">Select Destination</option>';
    routeToSelect.disabled = true;
    tripDateInput.value = "";
    tripDateInput.disabled = true;
    departureTimeSelect.innerHTML = '<option value="">Select Time</option>';
    departureTimeSelect.disabled = true;
    availableDates = [];
    availableVoyages = [];
    checkProceedButton();
}

// --- Voyage table row click-to-select ---
document.querySelectorAll(".voyage-row").forEach((row) => {
    row.addEventListener("click", function () {
        selectVoyageFromTable(this);
    });
});

function selectVoyageFromTable(row) {
    // If clicking the already-selected row, deselect and reset
    if (row.classList.contains("table-primary")) {
        row.classList.remove("table-primary");
        resetSelection();
        return;
    }

    const routeFrom = row.dataset.routeFrom;
    const routeTo = row.dataset.routeTo;
    const departureDate = row.dataset.departureDate;
    const voyageId = row.dataset.voyageId;

    // Highlight selected row
    document
        .querySelectorAll(".voyage-row")
        .forEach((r) => r.classList.remove("table-primary"));
    row.classList.add("table-primary");

    // 1. Set origin and trigger destination population
    routeFromSelect.value = routeFrom;
    updateDestinations();

    // 2. Set destination and trigger date population
    routeToSelect.value = routeTo;
    updateAvailableDates();

    // 3. Set date and trigger time population
    tripDateInput.value = departureDate;
    updateAvailableTimes();

    // 4. Set the departure time (option values are voyage IDs)
    departureTimeSelect.value = voyageId;
    checkProceedButton();
}

// Proceed button
proceedBtn.addEventListener("click", function () {
    if (proceedBtn.disabled) {
        alert(
            "Please select origin, destination, date, and departure time before proceeding.",
        );
        return;
    }

    const bookingType = document.querySelector(
        'input[name="bookingType"]:checked',
    ).value;
    const routeFrom = routeFromSelect.value;
    const routeTo = routeToSelect.value;
    const tripDate = tripDateInput.value;
    const selectedTimeOption =
        departureTimeSelect.options[departureTimeSelect.selectedIndex];

    if (!selectedTimeOption || !selectedTimeOption.dataset.voyageId) {
        alert("Please select a valid departure time.");
        return;
    }

    const voyageId = selectedTimeOption.dataset.voyageId;
    const departureTime = selectedTimeOption.dataset.departureTime;

    // Find the specific voyage for verification
    const selectedVoyage = availableVoyages.find(
        (v) => v.voyage_id == voyageId,
    );

    if (!selectedVoyage) {
        alert("Selected voyage not found. Please try again.");
        return;
    }

    // Validate cargo booking cutoff if cargo type is selected
    if (bookingType === "cargo") {
        const cutoffValidation = validateCargoCutoff(tripDate, departureTime);
        if (!cutoffValidation.valid) {
            alert(cutoffValidation.message);
            return;
        }
    }

    const baseUrl =
        bookingType === "cargo"
            ? proceedBtn.getAttribute("data-cargo-url")
            : proceedBtn.getAttribute("data-passenger-url");

    // Include voyage information in URL
    const url = `${baseUrl}?route_from=${encodeURIComponent(
        routeFrom,
    )}&route_to=${encodeURIComponent(
        routeTo,
    )}&departure_date=${encodeURIComponent(
        tripDate,
    )}&departure_time=${encodeURIComponent(
        departureTime,
    )}&voyage_id=${encodeURIComponent(voyageId)}&type=${encodeURIComponent(
        bookingType,
    )}`;

    window.location.href = url;
});
