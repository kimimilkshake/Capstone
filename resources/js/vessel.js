document.addEventListener('DOMContentLoaded', function () {
    const hatchContainer = document.getElementById('hatch-container');
    const accContainer = document.getElementById('accommodation-container');

    // ADD HATCH ROW
    function createHatchRow(index) {
        const div = document.createElement('div');
        div.className = "hatch-row";

        div.innerHTML = `
            <div class="form-group hatch-input">
                <label>Label</label>
                <input type="text" name="hatches[${index}][label]" placeholder="Hatch Label" required>
            </div>

            <div class="form-group hatch-input">
                <label>Length (m)</label>
                <input type="number" name="hatches[${index}][length]" step="0.01" placeholder="Length (m)" required>
            </div>

            <div class="form-group hatch-input">
                <label>Width (m)</label>
                <input type="number" name="hatches[${index}][width]" step="0.01" placeholder="Width (m)" required>
            </div>

            <div class="form-group hatch-input">
                <label>Height (m)</label>
                <input type="number" name="hatches[${index}][height]" step="0.01" placeholder="Height (m)" required>
            </div>

            <div class="form-group hatch-input">
                <label>Weight Capacity (%)</label>
                <input type="number" name="hatches[${index}][weight_capacity]" step="0.01" placeholder="Weight Capacity (%)">
            </div>

            <div class="form-group hatch-input">
                <label>Area Capacity (m³)</label>
                <input type="number" name="hatches[${index}][area_capacity]" step="0.01" placeholder="Area Capacity (m³)" required>
            </div>

            <div class="form-group hatch-input">
                <label>Capacity per Hold (Tons)</label>
                <input type="number" name="hatches[${index}][capacity_per_hold]" step="0.01" placeholder="Capacity Per Hold (Tons)" required>
            </div>

            <button type="button" class="hatch-btn add-hatch">+</button>
        `;

        return div;
    }

    // ADD ACCOMMODATION ROW
    function createAccRow(index) {
        const div = document.createElement('div');
        div.className = "accommodation-row";

        div.innerHTML = `
            <div class="form-group acc-input">
                <label>Name</label>
                <input type="text" name="accommodations[${index}][name]" placeholder="Accommodation Name" required>
            </div>

            <div class="form-group acc-input">
                <label>Regular Price</label>
                <input type="number" name="accommodations[${index}][price]" step="0.01" placeholder="Regular Price" required>
            </div>

            <div class="form-group acc-input">
                <label>Cot Range</label>
                <input type="number" name="accommodations[${index}][capacity]" placeholder="Capacity" required>
            </div>

            <button type="button" class="accommodation-btn add-accommodation">+</button>
        `;

        return div;
    }

    // EVENT LISTENER: ADD HATCH
    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('add-hatch')) {
            const index = hatchContainer.querySelectorAll('.hatch-row').length;
            hatchContainer.appendChild(createHatchRow(index));
        }
    });

    // EVENT LISTENER: ADD ACCOMMODATION
    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('add-accommodation')) {
            const index = accContainer.querySelectorAll('.accommodation-row').length;
            accContainer.appendChild(createAccRow(index));
        }
    });
});
