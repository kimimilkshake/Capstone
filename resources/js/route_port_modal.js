// ===== Route Category Modal =====
const addRouteCategoryBtn = document.getElementById("addRouteCategoryBtn");
const addRouteCategoryModal = document.getElementById("addRouteCategoryModal");
const closeAddRouteCategoryModal = document.getElementById(
    "closeAddRouteCategoryModal",
);
const addRouteCategoryForm = document.getElementById("addRouteCategoryForm");

document.addEventListener("DOMContentLoaded", function () {
    // --- OPEN MODAL ---
    addRouteCategoryBtn.addEventListener("click", () => {
        addRouteCategoryModal.style.display = "flex";
    });

    // --- CLOSE MODAL ---
    function closeRouteCategoryModal() {
        addRouteCategoryModal.style.display = "none";
        addRouteCategoryForm.reset();
    }
    closeAddRouteCategoryModal.addEventListener(
        "click",
        closeRouteCategoryModal,
    );

    // Close modal when clicking outside
    window.addEventListener("click", (e) => {
        if (e.target === addRouteCategoryModal) closeRouteCategoryModal();
    });

    // --- ADD ROUTE CATEGORY ---
    addRouteCategoryForm.addEventListener("submit", async (e) => {
        e.preventDefault();

        const formData = new FormData(addRouteCategoryForm);

        try {
            const response = await fetch("/authorized/admin/route_categories", {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": document.querySelector(
                        'meta[name="csrf-token"]',
                    ).content,
                    Accept: "application/json",
                },
                body: formData,
            });

            const data = await response.json().catch(() => ({}));

            if (response.ok && data.status === "success") {
                showToast("Route Category added successfully!", "success");
                addRouteCategoryModal.style.display = "none";
                addRouteCategoryForm.reset();

                // append new route category to dropdown in Add Route & Port modal
                const routeCategorySelect =
                    document.getElementById("route_category_id");
                if (routeCategorySelect && data.routeCategory) {
                    const option = document.createElement("option");
                    option.value = data.routeCategory.route_category_id;
                    option.text = data.routeCategory.route_category_name;
                    routeCategorySelect.appendChild(option);

                    // sort options alphabetically (skip first "Select Route Category")
                    const options = Array.from(routeCategorySelect.options)
                        .slice(1)
                        .sort((a, b) => a.text.localeCompare(b.text));
                    routeCategorySelect.innerHTML =
                        '<option value="">Select Route Category</option>';
                    options.forEach((o) => routeCategorySelect.appendChild(o));
                }
            } else {
                showToast(
                    data.message || "Error adding route category",
                    "danger",
                );
            }
        } catch (err) {
            showToast("Server error", "danger");
            console.error(err);
        }
    });
});

// ===== Route & Port Modal =====
document.addEventListener("DOMContentLoaded", function () {
    const addModal = document.getElementById("addRoutePortModal");
    const editModal = document.getElementById("editRoutePortModal");

    const addBtn = document.getElementById("addRoutePortBtn");
    const closeAdd = document.getElementById("closeAddModal");
    const closeEdit = document.getElementById("closeEditModal");

    const addForm = document.getElementById("addRoutePortForm");
    const editForm = document.getElementById("editRoutePortForm");

    const saveEditBtn = document.getElementById("saveEditBtn");
    let originalFormData = {};

    // Add Port selects
    const portOriginSelect = document.getElementById("port_origin_select");
    const portDestinationSelect = document.getElementById(
        "port_destination_select",
    );

    const routeOriginInput = addForm.querySelector(
        'input[name="route_origin"]',
    );
    const routeDestinationInput = addForm.querySelector(
        'input[name="route_destination"]',
    );

    // Store original options for filtering later
    const originalPortOriginOptions = Array.from(portOriginSelect.options);
    const originalPortDestinationOptions = Array.from(
        portDestinationSelect.options,
    );

    // --- EDIT MODAL PORT FILTERING ---
    const editRouteOriginInput = document.getElementById("editRouteOrigin");
    const editRouteDestinationInput = document.getElementById(
        "editRouteDestination",
    );
    const editPortOriginSelect = document.getElementById(
        "editPortOriginSelect",
    );
    const editPortDestinationSelect = document.getElementById(
        "editPortDestinationSelect",
    );

    // Store original options for filtering later
    const originalEditPortOriginOptions = Array.from(
        editPortOriginSelect.options,
    );
    const originalEditPortDestinationOptions = Array.from(
        editPortDestinationSelect.options,
    );

    // --- FILTER PORTS FUNCTION (shared by Add and Edit modals) ---
    function filterPorts(inputValue, selectElement, originalOptions) {
        const search = inputValue.toLowerCase().trim();
        selectElement.innerHTML = '<option value="">Select Port</option>';

        if (search === "") {
            originalOptions.forEach((opt) =>
                selectElement.appendChild(opt.cloneNode(true)),
            );
            return;
        }

        originalOptions.forEach((opt) => {
            const city = opt.dataset.city?.toLowerCase() || "";
            const terminal = opt.dataset.terminal?.toLowerCase() || "";
            const text = opt.text.toLowerCase();
            if (
                city.includes(search) ||
                terminal.includes(search) ||
                text.includes(search)
            ) {
                selectElement.appendChild(opt.cloneNode(true));
            }
        });
    }

    // --- EVENT LISTENERS ---
    editRouteOriginInput.addEventListener("input", () => {
        filterPorts(
            editRouteOriginInput.value,
            editPortOriginSelect,
            originalEditPortOriginOptions,
        );
    });

    editRouteDestinationInput.addEventListener("input", () => {
        filterPorts(
            editRouteDestinationInput.value,
            editPortDestinationSelect,
            originalEditPortDestinationOptions,
        );
    });

    // --- FILTER PORTS EVENT LISTENERS ---
    routeOriginInput.addEventListener("input", () => {
        filterPorts(
            routeOriginInput.value,
            portOriginSelect,
            originalPortOriginOptions,
        );
    });

    routeDestinationInput.addEventListener("input", () => {
        filterPorts(
            routeDestinationInput.value,
            portDestinationSelect,
            originalPortDestinationOptions,
        );
    });

    // --- OPEN/CLOSE MODALS ---
    function closeAddModal() {
        addModal.style.display = "none";
        addForm.reset();
        // Restore full port lists after clearing route text
        filterPorts("", portOriginSelect, originalPortOriginOptions);
        filterPorts("", portDestinationSelect, originalPortDestinationOptions);
    }
    function closeEditModal() {
        editModal.style.display = "none";
        editForm.reset();
        // Restore full port lists
        filterPorts("", editPortOriginSelect, originalEditPortOriginOptions);
        filterPorts(
            "",
            editPortDestinationSelect,
            originalEditPortDestinationOptions,
        );
    }

    addBtn.addEventListener("click", () => (addModal.style.display = "flex"));
    closeAdd.addEventListener("click", closeAddModal);
    closeEdit.addEventListener("click", closeEditModal);
    window.addEventListener("click", (e) => {
        if (e.target === addModal) closeAddModal();
        if (e.target === editModal) closeEditModal();
    });

    // --- ADD ROUTE & PORT ---
    addForm.addEventListener("submit", async (e) => {
        e.preventDefault();
        const formData = new FormData(addForm);

        try {
            const response = await fetch(addForm.action, {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": document.querySelector(
                        'meta[name="csrf-token"]',
                    ).content,
                    Accept: "application/json",
                },
                body: formData,
            });

            const data = await response.json().catch(() => ({}));

            if (response.ok && data.status === "success") {
                showToast(data.message, "success");
                addForm.reset();
                addModal.style.display = "none";
                setTimeout(() => location.reload(), 1500);
            } else {
                showToast(
                    data.message || "Failed to add Route & Port.",
                    "danger",
                );
            }
        } catch (err) {
            showToast("Unexpected error: " + err.message, "danger");
        }
    });

    // --- OPEN EDIT MODAL FUNCTION ---
    function openEditModal(data) {
        editModal.style.display = "flex";

        document.getElementById("editRoutePortId").value = data.route_port_id;
        document.getElementById("editRouteOrigin").value = data.route_origin;
        document.getElementById("editRouteDestination").value =
            data.route_destination;
        document.getElementById("editPortOriginSelect").value =
            data.port_origin_id || "";
        document.getElementById("editPortDestinationSelect").value =
            data.port_destination_id || "";
        document.getElementById("editRouteCategoryId").value =
            data.route_category_id || "";

        originalFormData = {
            route_category_id: data.route_category_id,
            route_origin: data.route_origin,
            route_destination: data.route_destination,
            port_origin_id: data.port_origin_id,
            port_destination_id: data.port_destination_id,
        };

        saveEditBtn.disabled = true;
        saveEditBtn.style.backgroundColor = "#ccc";
        saveEditBtn.style.cursor = "not-allowed";
    }

    // --- OPEN EDIT MODAL FROM EXISTING BUTTONS ---
    document.querySelector(".rp-table tbody").addEventListener("click", (e) => {
        if (e.target.closest(".editRouteBtn")) {
            const btn = e.target.closest(".editRouteBtn");
            openEditModal({
                route_port_id: btn.dataset.id,
                route_category_id: btn.dataset.route_category_id,
                route_origin: btn.dataset.route_origin,
                route_destination: btn.dataset.route_destination,
                port_origin_id: btn.dataset.port_origin_id,
                port_destination_id: btn.dataset.port_destination_id,
            });
        }
    });

    // --- DETECT CHANGES ---
    const editInputs = document.querySelectorAll(
        "#editRoutePortForm input, #editRoutePortForm select",
    );
    editInputs.forEach((input) => {
        input.addEventListener("input", checkIfChanged);
        input.addEventListener("change", checkIfChanged);
    });

    function checkIfChanged() {
        const currentData = {
            route_category_id: document.getElementById("editRouteCategoryId")
                .value,
            route_origin: document.getElementById("editRouteOrigin").value,
            route_destination: document.getElementById("editRouteDestination")
                .value,
            port_origin_id: document.getElementById("editPortOriginSelect")
                .value,
            port_destination_id: document.getElementById(
                "editPortDestinationSelect",
            ).value,
        };

        const isChanged = Object.keys(originalFormData).some(
            (key) => originalFormData[key] !== currentData[key],
        );

        saveEditBtn.disabled = !isChanged;
        saveEditBtn.style.backgroundColor = isChanged ? "#485B8C" : "#ccc";
        saveEditBtn.style.cursor = isChanged ? "pointer" : "not-allowed";
    }

    // --- UPDATE ROUTE & PORT ---
    editForm.addEventListener("submit", async (e) => {
        e.preventDefault();
        const id = document.getElementById("editRoutePortId").value;
        const formData = new FormData(editForm);

        try {
            const response = await fetch(`/authorized/admin/route_port/${id}`, {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": document.querySelector(
                        'meta[name="csrf-token"]',
                    ).content,
                },
                body: formData,
            });

            const data = await response.json().catch(() => ({}));

            if (response.ok && data.status === "success") {
                showToast(data.message, "success");
                setTimeout(() => location.reload(), 1500);
            } else {
                showToast(
                    data.message || "Failed to update Route & Port.",
                    "danger",
                );
            }
        } catch (err) {
            showToast("Unexpected error: " + err.message, "danger");
        }
    });
});
