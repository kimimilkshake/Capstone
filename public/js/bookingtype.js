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
