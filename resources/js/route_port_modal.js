// ===== Route Code Modal =====
const addRouteCodeBtn = document.getElementById('addRouteCodeBtn');
const addRouteCodeModal = document.getElementById('addRouteCodeModal');
const closeAddRouteCodeModal = document.getElementById('closeAddRouteCodeModal');
const addRouteCodeForm = document.getElementById('addRouteCodeForm');

document.addEventListener("DOMContentLoaded", function () {

    // --- OPEN MODAL ---
    addRouteCodeBtn.addEventListener('click', () => {
        addRouteCodeModal.style.display = 'flex';
    });

    // --- CLOSE MODAL ---
    closeAddRouteCodeModal.addEventListener('click', () => {
        addRouteCodeModal.style.display = 'none';
    });

    // Close modal when clicking outside
    window.addEventListener('click', (e) => {
        if (e.target === addRouteCodeModal) {
            addRouteCodeModal.style.display = 'none';
        }
    });

    // --- ADD ROUTE CODE ---
    addRouteCodeForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        const formData = new FormData(addRouteCodeForm);

        try {
            const response = await fetch("/authorized/admin/route_codes", { // use actual route URL
                method: 'POST',
                headers: {
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: formData
            });

            const data = await response.json().catch(() => ({}));

            if (response.ok && data.status === 'success') {
                showToast('Route Code added successfully!', 'success'); // NEW
                addRouteCodeModal.style.display = 'none';
                addRouteCodeForm.reset();

                // append new route code to dropdown in Add Route & Port modal
                const routeCodeSelect = document.querySelector('#addRoutePortForm select[name="route_code_id"]');
                if (routeCodeSelect && data.routeCode) {
                    const option = document.createElement('option');
                    option.value = data.routeCode.route_code_id;
                    option.text = data.routeCode.route_code_name;
                    routeCodeSelect.appendChild(option);

                    const options = Array.from(routeCodeSelect.options)
                        .slice(1) // skip "Select Route Code"
                        .sort((a, b) => a.text.localeCompare(b.text));

                    routeCodeSelect.innerHTML = '<option value="">Select Route Code</option>';
                    options.forEach(o => routeCodeSelect.appendChild(o));
                }

            } else {
                showToast(data.message || 'Error adding route code', 'danger'); // NEW
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

            const routeCodeSelect = document.getElementById("editRouteCodeId");
            if (routeCodeSelect) {
                routeCodeSelect.value = btn.dataset.route_code_id || "";
            }

            // STORE ORIGINAL VALUES
            originalFormData = {
                route_code_id: document.getElementById("editRouteCodeId").value,
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
            route_code_id: document.getElementById("editRouteCodeId").value,
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
