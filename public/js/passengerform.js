document.addEventListener("DOMContentLoaded", function () {
    const numPassengersSelect = document.getElementById("numPassengers");
    const passengerSections = document.getElementById("passengerSections");
    const bookingForm = document.getElementById("bookingForm");
    const loader = document.getElementById("ocrLoader");

    function generatePassengerForms(count) {
        passengerSections.innerHTML = "";
        for (let i = 1; i <= count; i++) {
            const passengerHTML = `
                <div class="passenger-form mb-4 p-3 bg-white rounded shadow-sm" data-passenger="${i}">
                    <h6 class="fw-bold mb-3 text-primary">Personal Information - Person ${i}</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Type</label>
                            <select class="form-select passenger-type" name="type" required>
                                <option value="">Select Type</option>
                                <option value="Adult">Adult</option>
                                <option value="Student">Student</option>
                                <option value="SeniorCitizen">Senior Citizen</option>
                                <option value="PWD">PWD</option>
                                <option value="UniformedPerson">Uniformed Personnel</option>
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

        document.querySelectorAll(".passenger-type").forEach((select) => {
            select.addEventListener("change", function () {
                const idFields =
                    this.closest(".passenger-form").querySelector(".id-fields");
                idFields.style.display =
                    this.value === "Adult" ? "none" : "block";
            });
        });
    }

    generatePassengerForms(parseInt(numPassengersSelect.value));
    numPassengersSelect.addEventListener("change", function () {
        generatePassengerForms(parseInt(this.value));
    });

    bookingForm.addEventListener("submit", async function (e) {
        e.preventDefault();
        loader.style.display = "flex";

        try {
            const passengerForms = document.querySelectorAll(".passenger-form");

            for (const form of passengerForms) {
                const type = form.querySelector(".passenger-type").value;
                if (type === "Adult") continue;

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

            window.location.href = "/passenger/confirmbooking";
        } catch (err) {
            loader.style.display = "none";
            console.error(err);
            alert("Error during ID verification. Please try again.");
        }
    });
});
