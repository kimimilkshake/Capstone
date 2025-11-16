document.addEventListener("DOMContentLoaded", function () {
    const cargoForm = document.getElementById("cargoBookingForm");
    const addCargoBtn = document.getElementById("addCargoBtn");
    const cargoSections = document.getElementById("cargoSections");
    const ocrLoader = document.getElementById("ocrLoader");

    let cargoCount = 1;

    /** Show / Hide Loader **/
    function showLoader() {
        ocrLoader.style.display = "flex";
    }
    function hideLoader() {
        ocrLoader.style.display = "none";
    }

    /** Add New Cargo Section **/
    function addCargoSection() {
        cargoCount++;
        const cargoHTML = `
            <div class="cargo-entry bg-white p-3 rounded shadow-sm mb-3" data-cargo="${cargoCount}">
                <h6 class="fw-bold mb-3 text-primary">Cargo ${cargoCount}</h6>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Description</label>
                        <input type="text" class="form-control" name="cargo_description_${cargoCount}" placeholder="e.g. Rice sacks" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Weight (kg)</label>
                        <input type="number" class="form-control" name="cargo_weight_${cargoCount}" min="1" required>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="button" class="btn btn-danger w-100 removeCargoBtn">Remove</button>
                    </div>
                </div>
            </div>
        `;
        cargoSections.insertAdjacentHTML("beforeend", cargoHTML);
        updateRemoveButtons();
    }

    /** Update Remove Buttons **/
    function updateRemoveButtons() {
        document.querySelectorAll(".removeCargoBtn").forEach((btn) => {
            btn.onclick = function () {
                this.closest(".cargo-entry").remove();
                if (document.querySelectorAll(".cargo-entry").length === 0) {
                    cargoSections.innerHTML = `<p class="text-center text-muted fst-italic">Additional cargo will appear here</p>`;
                    cargoCount = 0;
                }
            };
        });
    }

    /** Event Listeners **/
    if (addCargoBtn) addCargoBtn.addEventListener("click", addCargoSection);

    if (cargoForm) {
        cargoForm.addEventListener("submit", async function (e) {
            e.preventDefault();
            showLoader();

            try {
                // Simulate backend process
                await new Promise((resolve) => setTimeout(resolve, 1200));
                window.location.href = "/cargo/confirmbooking";
            } catch (error) {
                console.error(error);
                alert("An error occurred while processing your cargo booking.");
            } finally {
                hideLoader();
            }
        });
    }
});
