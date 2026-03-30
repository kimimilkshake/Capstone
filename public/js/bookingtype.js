// Grab elements
const routeFromSelect = document.getElementById("routeFrom");
const routeToSelect = document.getElementById("routeTo");
const tripDateInput = document.getElementById("tripDate");
const departureTimeSelect = document.getElementById("departureTime");
const proceedBtn = document.getElementById("proceedBtn");
const bookingTypeRadios = document.getElementsByName("bookingType");

// Parse voyages data from Blade (updated from routes to voyages)
let voyages = JSON.parse(document.getElementById("voyages-data").textContent);

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
        showToast(
            "No voyages found for this route in the next 7 days.",
            "danger",
        );
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
        showToast("You cannot select a past date.", "danger");
        tripDateInput.value = "";
        departureTimeSelect.disabled = true;
        checkProceedButton();
        return;
    }

    if (!availableDates.includes(selectedDate)) {
        showToast(
            "No voyages available on this date for the selected route.",
            "danger",
        );
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
        showToast(
            "Please select origin, destination, date, and departure time before proceeding.",
            "danger",
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
        showToast("Please select a valid departure time.", "danger");
        return;
    }

    const voyageId = selectedTimeOption.dataset.voyageId;
    const departureTime = selectedTimeOption.dataset.departureTime;

    // Find the specific voyage for verification
    const selectedVoyage = availableVoyages.find(
        (v) => v.voyage_id == voyageId,
    );

    if (!selectedVoyage) {
        showToast("Selected voyage not found. Please try again.", "danger");
        return;
    }

    // Validate cargo booking cutoff if cargo type is selected
    if (bookingType === "cargo") {
        const cutoffValidation = validateCargoCutoff(tripDate, departureTime);
        if (!cutoffValidation.valid) {
            showToast(cutoffValidation.message, "danger");
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

// ─── Auto-refresh voyage table every 30 seconds ───────────────────────────────
(function startVoyagePolling() {
    const POLL_INTERVAL = 30000; // 30 seconds

    function formatDate(dateStr) {
        // dateStr: "YYYY-MM-DD"
        const months = [
            "Jan",
            "Feb",
            "Mar",
            "Apr",
            "May",
            "Jun",
            "Jul",
            "Aug",
            "Sep",
            "Oct",
            "Nov",
            "Dec",
        ];
        const [, m, d] = dateStr.split("-");
        return months[parseInt(m, 10) - 1] + " " + d;
    }

    function formatTime(timeStr) {
        // timeStr: "HH:MM:SS"
        const [h, min] = timeStr.split(":");
        return new Date(
            2000,
            0,
            1,
            parseInt(h, 10),
            parseInt(min, 10),
        ).toLocaleTimeString("en-US", { hour: "2-digit", minute: "2-digit" });
    }

    function addVoyageRow(voyage) {
        const tbody = document.querySelector(".voyage-table tbody");

        // Remove "no voyages" placeholder if present
        const placeholder = tbody.querySelector("tr td[colspan]");
        if (placeholder) placeholder.closest("tr").remove();

        const tr = document.createElement("tr");
        tr.className = "voyage-row voyage-row--new";
        tr.style.cursor = "pointer";
        tr.dataset.voyageId = voyage.voyage_id;
        tr.dataset.routeFrom = voyage.route_from;
        tr.dataset.routeTo = voyage.route_to;
        tr.dataset.departureDate = voyage.departure_date;
        tr.dataset.departureTime = voyage.departure_time;

        tr.innerHTML = `
            <td>${formatDate(voyage.departure_date)}</td>
            <td>${voyage.route_from} - ${voyage.route_to}</td>
            <td>${voyage.departure_time ? formatTime(voyage.departure_time) : "-"}</td>
            <td>${voyage.vessel_name}</td>
        `;

        tr.addEventListener("click", function () {
            selectVoyageFromTable(this);
        });

        // Insert in date/time order
        const existingRows = Array.from(tbody.querySelectorAll(".voyage-row"));
        const insertBefore = existingRows.find((row) => {
            const rowDate = row.dataset.departureDate || "";
            const rowTime = row.dataset.departureTime || "";
            return (
                voyage.departure_date + voyage.departure_time <
                rowDate + rowTime
            );
        });
        insertBefore
            ? tbody.insertBefore(tr, insertBefore)
            : tbody.appendChild(tr);

        // Brief highlight to draw attention to new row
        setTimeout(() => tr.classList.remove("voyage-row--new"), 3000);
    }

    function syncOriginDropdown(newVoyages) {
        const origins = [...new Set(newVoyages.map((v) => v.route_from))];
        origins.forEach((origin) => {
            if (
                !routeFromSelect.querySelector(
                    `option[value="${CSS.escape(origin)}"]`,
                )
            ) {
                const opt = document.createElement("option");
                opt.value = opt.textContent = origin;
                routeFromSelect.appendChild(opt);
            }
        });
    }

    async function pollVoyages() {
        try {
            const response = await fetch("/api/voyages", {
                headers: {
                    Accept: "application/json",
                    "X-Requested-With": "XMLHttpRequest",
                },
            });
            if (!response.ok) return;

            const fresh = await response.json();
            const knownIds = new Set(voyages.map((v) => v.voyage_id));
            const added = fresh.filter((v) => !knownIds.has(v.voyage_id));

            if (added.length > 0) {
                added.forEach((v) => {
                    voyages.push(v);
                    addVoyageRow(v);
                });
                syncOriginDropdown(added);
                showToast(
                    added.length === 1
                        ? "1 new voyage is now available."
                        : `${added.length} new voyages are now available.`,
                    "info",
                );
            }

            // Also remove voyages that are no longer in the list (departed / removed by admin)
            const freshIds = new Set(fresh.map((v) => v.voyage_id));
            const removed = voyages.filter((v) => !freshIds.has(v.voyage_id));
            if (removed.length > 0) {
                removed.forEach((v) => {
                    const row = document.querySelector(
                        `.voyage-row[data-voyage-id="${v.voyage_id}"]`,
                    );
                    if (row) row.remove();
                });
                voyages = voyages.filter((v) => freshIds.has(v.voyage_id));

                // If selected voyage was removed, reset the form
                const selectedOption =
                    departureTimeSelect.options[
                        departureTimeSelect.selectedIndex
                    ];
                if (
                    selectedOption &&
                    removed.some(
                        (v) => v.voyage_id == selectedOption.dataset.voyageId,
                    )
                ) {
                    showToast(
                        "Your selected voyage is no longer available. Please choose another.",
                        "danger",
                    );
                    resetSelection();
                }
            }
        } catch (e) {
            // Silent fail — network issues should not interrupt the user
        }
    }

    setInterval(pollVoyages, POLL_INTERVAL);
})();
