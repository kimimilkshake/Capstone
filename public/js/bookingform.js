document.addEventListener("DOMContentLoaded", function () {
    const numPassengersSelect = document.getElementById("numPassengers");
    const passengerSections = document.getElementById("passengerSections");
    const bookingForm = document.getElementById("bookingForm");

    // Generate passenger forms dynamically
    function generatePassengerForms(count) {
        passengerSections.innerHTML = "";
        for (let i = 1; i <= count; i++) {
            const passengerHTML = `
                <div class="passenger-form mb-4 p-3 bg-white rounded shadow-sm" data-passenger="${i}">
                    <h6 class="fw-bold mb-3 text-primary">Personal Information - Person ${i}</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Type</label>
                            <select class="form-select" name="type" required>
                                <option value="">Select Type</option>
                                <option>Adult</option>
                                <option>Child</option>
                                <option>Infant</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Suffix</label>
                            <input type="text" class="form-control" name="suffix">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">First Name</label>
                            <input type="text" class="form-control" name="first_name" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Last Name</label>
                            <input type="text" class="form-control" name="last_name" required>
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
                    </div>

                    <hr>

                    <h6 class="fw-bold mb-3 text-success">Accommodation - Person ${i}</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Accommodation Type</label>
                            <select class="form-select" name="accommodation_type" required>
                                <option value="">Select Accommodation</option>
                                <option>Tourist Class</option>
                                <option>Cabin</option>
                                <option>Economy</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Cot Number</label>
                            <select class="form-select" name="cot_number" required>
                                <option value="">Select Cot</option>
                                <option>1</option>
                                <option>2</option>
                                <option>3</option>
                            </select>
                        </div>
                    </div>
                </div>
            `;
            passengerSections.insertAdjacentHTML("beforeend", passengerHTML);
        }
    }

    // Initialize form with default number of passengers
    if (numPassengersSelect) {
        generatePassengerForms(parseInt(numPassengersSelect.value));
        numPassengersSelect.addEventListener("change", function () {
            generatePassengerForms(parseInt(this.value));
        });
    }

    // Handle booking form submission
    bookingForm.addEventListener("submit", async function (e) {
        e.preventDefault();

        const passengers = [];
        document.querySelectorAll(".passenger-form").forEach((form) => {
            const data = {};
            form.querySelectorAll("input, select").forEach((input) => {
                data[input.name] = input.value;
            });
            passengers.push(data);
        });

        const bookingData = {
            vesselName: bookingForm.dataset.vesselName,
            routeFrom: bookingForm.dataset.routeFrom,
            routeTo: bookingForm.dataset.routeTo,
            departureDate: bookingForm.dataset.departureDate,
            departureTime: bookingForm.dataset.departureTime,
            portOfOrigin: bookingForm.dataset.portOfOrigin,
            passengers: passengers,
        };

        try {
            const response = await fetch(bookingForm.dataset.submitUrl, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": bookingForm.dataset.csrf,
                },
                body: JSON.stringify(bookingData),
            });

            const result = await response.json();

            // Instead of alert — just redirect
            if (result.success) {
                window.location.href =
                    result.payment_url ?? "/passenger/confirmbooking";
            } else {
                window.location.href = "/passenger/confirmbooking";
            }
        } catch (error) {
            console.error("Error submitting booking:", error);
            // Even if there’s an error, still redirect (optional)
            window.location.href = "/passenger/confirmbooking";
        }
    });
});
