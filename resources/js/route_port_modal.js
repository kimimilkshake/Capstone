// ===== Route Category Modal =====
const addRouteCategoryBtn = document.getElementById('addRouteCategoryBtn');
const addRouteCategoryModal = document.getElementById('addRouteCategoryModal');
const closeAddRouteCategoryModal = document.getElementById('closeAddRouteCategoryModal');
const addRouteCategoryForm = document.getElementById('addRouteCategoryForm');

document.addEventListener("DOMContentLoaded", function () {

    // --- OPEN MODAL ---
    addRouteCategoryBtn.addEventListener('click', () => {
        addRouteCategoryModal.style.display = 'flex';
    });

    // --- CLOSE MODAL ---
    closeAddRouteCategoryModal.addEventListener('click', () => {
        addRouteCategoryModal.style.display = 'none';
    });

    // Close modal when clicking outside
    window.addEventListener('click', (e) => {
        if (e.target === addRouteCategoryModal) {
            addRouteCategoryModal.style.display = 'none';
        }
    });

    // --- ADD ROUTE CATEGORY ---
    addRouteCategoryForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        const formData = new FormData(addRouteCategoryForm);

        try {
            const response = await fetch("/authorized/admin/route_categories", { // use actual route URL
                method: 'POST',
                headers: {
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: formData
            });

            const data = await response.json().catch(() => ({}));

            if (response.ok && data.status === 'success') {
                showToast('Route Category added successfully!', 'success'); // NEW
                addRouteCategoryModal.style.display = 'none';
                addRouteCategoryForm.reset();

                // append new route category to dropdown in Add Route & Port modal
                const routeCategorySelect = document.querySelector('#addRoutePortForm select[name="route_category_id"]');
                if (routeCategorySelect && data.routeCategory) {
                    const option = document.createElement('option');
                    option.value = data.routeCategory.route_category_id;
                    option.text = data.routeCategory.route_category_name;
                    routeCategorySelect.appendChild(option);

                    const options = Array.from(routeCategorySelect.options)
                        .slice(1) // skip "Select Route Category"
                        .sort((a, b) => a.text.localeCompare(b.text));

                    routeCategorySelect.innerHTML = '<option value="">Select Route Category</option>';
                    options.forEach(o => routeCategorySelect.appendChild(o));
                }

            } else {
                showToast(data.message || 'Error adding route category', 'danger'); // NEW
            }

        } catch (err) {
            showToast('Server error', 'danger'); // NEW
            console.error(err);
        }
    });

});



// ===== Route & Port Modal =====
document.addEventListener("DOMContentLoaded", function () {

    const addModal = document.getElementById("addRoutePortModal");
    const editModal = document.getElementById("editRoutPortModal");

    const addBtn = document.getElementById("addRoutePortBtn");
    const closeAdd = document.getElementById("closeAddModal");
    const closeEdit = document.getElementById("closeEditModal");

    const addForm = document.getElementById("addRoutePortForm");
    const editForm = document.getElementById("editRoutePortForm");

    const saveEditBtn = document.getElementById("saveEditBtn");
    let originalFormData = {};

    // --- OPEN MODALS ---
    addBtn.addEventListener("click", () => addModal.style.display = "flex");
    closeAdd.addEventListener("click", () => addModal.style.display = "none");
    closeEdit.addEventListener("click", () => editModal.style.display = "none");
    window.addEventListener("click", (e) => {
        if (e.target === addModal) addModal.style.display = "none";
        if (e.target === editModal) editModal.style.display = "none";
    });

    // --- ADD ROUTE & PORT ---
    addForm.addEventListener("submit", async (e) => {
        e.preventDefault();
        const formData = new FormData(addForm);

        try {
            const response = await fetch("/authorized/admin/route_port", {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
                    "Accept": "application/json"
                },
                body: formData
            });
            const data = await response.json().catch(() => ({}));

            if (response.ok && data.status === "success") {
                showToast(data.message, 'success'); // NEW
                setTimeout(() => location.reload(), 1500);
            } else {
                showToast(data.message || "Failed to add Route & Port.", 'danger'); // NEW
            }
        } catch (err) {
            showToast("Unexpected error: " + err.message, 'danger'); // NEW
        }
    });

    // --- OPEN EDIT MODAL ---
    document.querySelectorAll(".editRouteBtn").forEach((btn) => {
        btn.addEventListener("click", () => {
            editModal.style.display = "flex";

            document.getElementById("editRoutePortId").value = btn.dataset.id;

            document.getElementById("editRouteOrigin").value = btn.dataset.origin;
            document.getElementById("editRouteDestination").value = btn.dataset.destination;

            document.getElementById("editPortOriginName").value = btn.dataset.port_origin_name;
            document.getElementById("editPortOriginCity").value = btn.dataset.port_origin_city;
            document.getElementById("editPortOriginProvince").value = btn.dataset.port_origin_province;

            document.getElementById("editPortDestinationName").value = btn.dataset.port_destination_name;
            document.getElementById("editPortDestinationCity").value = btn.dataset.port_destination_city;
            document.getElementById("editPortDestinationProvince").value = btn.dataset.port_destination_province;

            const routeCategorySelect = document.getElementById("editRouteCategoryId");
            if (routeCategorySelect) {
                routeCategorySelect.value = btn.dataset.route_category_id || "";
            }

            // STORE ORIGINAL VALUES
            originalFormData = {
                route_category_id: document.getElementById("editRouteCategoryId").value,
                route_origin: document.getElementById("editRouteOrigin").value,
                route_destination: document.getElementById("editRouteDestination").value,
                port_origin_name: document.getElementById("editPortOriginName").value,
                port_origin_city: document.getElementById("editPortOriginCity").value,
                port_origin_province: document.getElementById("editPortOriginProvince").value,
                port_destination_name: document.getElementById("editPortDestinationName").value,
                port_destination_city: document.getElementById("editPortDestinationCity").value,
                port_destination_province: document.getElementById("editPortDestinationProvince").value
            };

            // disable button initially
            saveEditBtn.disabled = true;
            saveEditBtn.style.backgroundColor = "#ccc";
            saveEditBtn.style.cursor = "not-allowed";
        });
    });

    // ✅ DETECT CHANGES (STEP 4)
    const editInputs = document.querySelectorAll(
        "#editRoutePortForm input, #editRoutePortForm select"
    );

    editInputs.forEach(input => {
        input.addEventListener("input", checkIfChanged);
        input.addEventListener("change", checkIfChanged);
    });

    // --- UPDATE ROUTE & PORT ---
    editForm.addEventListener("submit", async (e) => {
        e.preventDefault();
        const id = document.getElementById("editRoutePortId").value;
        const formData = new FormData(editForm);

        try {
            const response = await fetch(`/authorized/admin/route_port/${id}`, {
                method: "POST", // method spoofing via @method('PUT')
                headers: {
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                },
                body: formData
            });

            const data = await response.json().catch(() => ({}));

            if (response.ok && data.status === "success") {
                showToast(data.message, 'success'); // NEW
                setTimeout(() => location.reload(), 1500); // optional delay
            } else {
                showToast(data.message || "Failed to update Route & Port.", 'danger'); // NEW       
            }
        } catch (err) {
            showToast("Unexpected error: " + err.message, 'danger'); // NEW
        }
    });

     // ✅ STEP 5 FUNCTION GOES HERE
    function checkIfChanged() {
        const currentData = {
            route_category_id: document.getElementById("editRouteCategoryId").value,
            route_origin: document.getElementById("editRouteOrigin").value,
            route_destination: document.getElementById("editRouteDestination").value,
            port_origin_name: document.getElementById("editPortOriginName").value,
            port_origin_city: document.getElementById("editPortOriginCity").value,
            port_origin_province: document.getElementById("editPortOriginProvince").value,
            port_destination_name: document.getElementById("editPortDestinationName").value,
            port_destination_city: document.getElementById("editPortDestinationCity").value,
            port_destination_province: document.getElementById("editPortDestinationProvince").value
        };

        const isChanged = Object.keys(originalFormData).some(key => {
            return originalFormData[key] !== currentData[key];
        });

        if (isChanged) {
            // ✅ ENABLE BUTTON
            saveEditBtn.disabled = false;
            saveEditBtn.style.backgroundColor = "#485B8C";
            saveEditBtn.style.cursor = "pointer";
        } else {
            // ❌ DISABLE BUTTON
            saveEditBtn.disabled = true;
            saveEditBtn.style.backgroundColor = "#ccc";
            saveEditBtn.style.cursor = "not-allowed";
        }
    }
});
