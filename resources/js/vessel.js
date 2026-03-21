document.addEventListener("DOMContentLoaded", function () {
    const hatchContainer = document.getElementById("hatch-container");
    const accContainer = document.getElementById("accommodation-container");

    function createHatchRow(index) {
        const row = document.createElement("div");
        row.className = "hatch-row";
        row.innerHTML = `
        <div class="form-group hatch-input">
            <label>Hatch Label <span class="text-danger">*</span></label>
            <input type="text" name="hatches[${index}][label]" placeholder="Hatch Label" required>
        </div>
        <div class="form-group hatch-input">
            <label>Length (m) <span class="text-danger">*</span></label>
            <input type="number" name="hatches[${index}][length]" placeholder="Length (m)" step="0.01" inputmode="decimal" required>
        </div>
        <div class="form-group hatch-input">
            <label>Width (m) <span class="text-danger">*</span></label>
            <input type="number" name="hatches[${index}][width]" placeholder="Width (m)" step="0.01" inputmode="decimal" required>
        </div>
        <div class="form-group hatch-input">
            <label>Height (m) <span class="text-danger">*</span></label>
            <input type="number" name="hatches[${index}][height]" placeholder="Height (m)" step="0.01" inputmode="decimal" required>
        </div>
        <div class="form-group hatch-input">
            <label>Weight Capacity (%)</label>
            <input type="number" name="hatches[${index}][weight_capacity]" placeholder="Weight Capacity (%)" step="0.01" inputmode="decimal">
        </div>
        <div class="form-group hatch-input">
            <label>Area Capacity (m³) <span class="text-danger">*</span></label>
            <input type="number" name="hatches[${index}][area_capacity]" placeholder="Area Capacity in Cubic Meters" step="0.01" inputmode="decimal" required>
        </div>
        <div class="form-group hatch-input">
            <label>Hold Capacity (Tons) <span class="text-danger">*</span></label>
            <input type="number" name="hatches[${index}][capacity_per_hold]" placeholder="Capacity per Hold (Tons)" step="0.01" inputmode="decimal" required>
        </div>
        <button type="button" class="dynamic-add hatch-action">+</button>
    `;
        return row;
    }

    function createAccRow(index) {
        const row = document.createElement("div");
        row.className = "accommodation-row";
        row.innerHTML = `
        <div class="form-group acc-input">
            <label>Accommodation Name <span class="text-danger">*</span></label>
            <input type="text" name="accommodations[${index}][name]" placeholder="Accommodation Name" required>
        </div>

        <div class="form-group acc-input">
            <label>Regular Price <span class="text-danger">*</span></label>
            <input type="number" name="accommodations[${index}][price]" placeholder="Regular Price" step="0.01" required>
        </div>

        <div class="form-group acc-input">
            <label>Cot Range <span class="text-danger">*</span></label>
            <input type="text" name="accommodations[${index}][cot_range]" placeholder="Cot Range (e.g., 1-5, 7-10)" required>
        </div>

        <div class="form-group acc-input">
            <label>Cot Plan Image <em style="color: #888; font-size: 0.8em;">jpg, jpeg, png only</em></label>
            <input type="file" name="accommodations[${index}][cot_plan]" accept="image/jpeg,image/jpg,image/png">
        </div>

      <button type="button" class="dynamic-add acc-action">+</button>
    `;
        return row;
    }

    function hatchCount() {
        return hatchContainer.querySelectorAll(".hatch-row").length;
    }

    function accCount() {
        return accContainer.querySelectorAll(".accommodation-row").length;
    }

    function refreshHatchButtons() {
        const buttons = hatchContainer.querySelectorAll(".hatch-row button");

        buttons.forEach((btn, index) => {
            if (index === buttons.length - 1) {
                // LAST ROW = PLUS BUTTON
                btn.textContent = "+";
                btn.className = "dynamic-add hatch-action";
                btn.style.backgroundColor = "";
                btn.style.color = "";
            } else {
                // OTHER ROWS = MINUS BUTTON
                btn.textContent = "−";
                btn.className = "dynamic-remove remove-hatch";
                btn.style.backgroundColor = "#d9534f"; // RED
                btn.style.color = "#fff"; // WHITE TEXT
            }
        });
    }

    function refreshAccButtons() {
        const buttons = accContainer.querySelectorAll(
            ".accommodation-row button",
        );

        buttons.forEach((btn, index) => {
            if (index === buttons.length - 1) {
                // LAST ROW = PLUS BUTTON
                btn.textContent = "+";
                btn.className = "dynamic-add acc-action";
                btn.style.backgroundColor = "";
                btn.style.color = "";
            } else {
                // OTHER ROWS = MINUS BUTTON
                btn.textContent = "−";
                btn.className = "dynamic-remove remove-acc";
                btn.style.backgroundColor = "#d9534f"; // RED
                btn.style.color = "#fff"; // WHITE TEXT
            }
        });
    }

    // -------------------------
    // HATCH EVENTS
    // -------------------------
    hatchContainer.addEventListener("click", function (e) {
        const target = e.target;

        // Add hatch
        if (target.classList.contains("hatch-action")) {
            const newRow = createHatchRow(hatchCount());
            hatchContainer.appendChild(newRow);
            refreshHatchButtons();
            return;
        }

        // Remove hatch
        if (target.classList.contains("remove-hatch")) {
            target.closest(".hatch-row").remove();
            refreshHatchButtons();
            return;
        }
    });

    // -------------------------
    // ACC EVENTS
    // -------------------------
    accContainer.addEventListener("click", function (e) {
        const target = e.target;

        // Add accommodation
        if (target.classList.contains("acc-action")) {
            const newRow = createAccRow(accCount());
            accContainer.appendChild(newRow);
            refreshAccButtons();
            return;
        }

        // Remove accommodation
        if (target.classList.contains("remove-acc")) {
            target.closest(".accommodation-row").remove();
            refreshAccButtons();
            return;
        }
    });

    // Initialize buttons on load
    refreshHatchButtons();
    refreshAccButtons();
});
