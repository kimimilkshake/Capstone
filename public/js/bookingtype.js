// Grab elements
const routeFromSelect = document.getElementById("routeFrom");
const routeToSelect = document.getElementById("routeTo");
const tripDateInput = document.getElementById("tripDate");
const proceedBtn = document.getElementById("proceedBtn");
const bookingTypeRadios = document.getElementsByName("bookingType");

// Parse routes data from Blade
const routes = JSON.parse(document.getElementById("routes-data").textContent);

let allowedDays = [];

// Initially disable proceed button
proceedBtn.disabled = true;

// Event listeners
routeFromSelect.addEventListener("change", updateDestinations);
routeToSelect.addEventListener("change", updateAllowedDays);
tripDateInput.addEventListener("input", validateDate);
bookingTypeRadios.forEach((radio) =>
    radio.addEventListener("change", checkProceedButton)
);

// Check all fields to enable/disable proceed button
function checkProceedButton() {
    const isValid =
        routeFromSelect.value &&
        routeToSelect.value &&
        tripDateInput.value &&
        document.querySelector('input[name="bookingType"]:checked');
    proceedBtn.disabled = !isValid;
}

// Update destinations based on selected origin
function updateDestinations() {
    const origin = routeFromSelect.value;
    routeToSelect.innerHTML = '<option value="">Select Destination</option>';
    tripDateInput.value = "";
    tripDateInput.disabled = true;
    allowedDays = [];

    if (!origin) {
        routeToSelect.disabled = true;
        checkProceedButton();
        return;
    }

    const destinations = routes
        .filter((r) => r.route_from === origin)
        .map((r) => r.route_to)
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

// Update allowed days
function updateAllowedDays() {
    const origin = routeFromSelect.value;
    const destination = routeToSelect.value;
    tripDateInput.value = "";

    if (!origin || !destination) {
        tripDateInput.disabled = true;
        allowedDays = [];
        checkProceedButton();
        return;
    }

    const route = routes.find(
        (r) => r.route_from === origin && r.route_to === destination
    );

    if (!route) {
        alert("No route found. Resetting selection.");
        resetSelection();
        return;
    }

    const dayMap = {
        Sunday: 0,
        Monday: 1,
        Tuesday: 2,
        Wednesday: 3,
        Thursday: 4,
        Friday: 5,
        Saturday: 6,
    };

    allowedDays = JSON.parse(route.operating_days).map((d) => dayMap[d]);
    tripDateInput.disabled = false;
    tripDateInput.min = new Date().toISOString().split("T")[0];
    checkProceedButton();
}

// Validate date
function validateDate() {
    if (!allowedDays.length) return;

    const selectedDate = new Date(this.value);
    const today = new Date();
    today.setHours(0, 0, 0, 0);

    if (selectedDate < today) {
        alert("You cannot select a past date.");
        this.value = "";
        checkProceedButton();
        return;
    }

    if (!allowedDays.includes(selectedDate.getDay())) {
        alert("Selected date is not available for this route.");
        this.value = "";
    }

    checkProceedButton();
}

// Reset
function resetSelection() {
    routeFromSelect.value = "";
    routeToSelect.innerHTML = '<option value="">Select Destination</option>';
    routeToSelect.disabled = true;
    tripDateInput.value = "";
    tripDateInput.disabled = true;
    allowedDays = [];
    checkProceedButton();
}

// Proceed button
proceedBtn.addEventListener("click", function () {
    if (proceedBtn.disabled) {
        alert("Please select origin, destination, and date before proceeding.");
        return;
    }

    const bookingType = document.querySelector(
        'input[name="bookingType"]:checked'
    ).value;
    const routeFrom = routeFromSelect.value;
    const routeTo = routeToSelect.value;
    const tripDate = tripDateInput.value;

    const baseUrl =
        bookingType === "cargo"
            ? proceedBtn.getAttribute("data-cargo-url")
            : proceedBtn.getAttribute("data-passenger-url");

    // ✅ Include type in URL so controller knows which booking page to load
    const url = `${baseUrl}?route_from=${encodeURIComponent(
        routeFrom
    )}&route_to=${encodeURIComponent(
        routeTo
    )}&departure_date=${encodeURIComponent(tripDate)}&type=${encodeURIComponent(
        bookingType
    )}`;

    window.location.href = url;
});
