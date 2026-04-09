// ===== Route Category Modal =====

const PASSENGER_TYPES = [
    "3 to 11 years old",
    "Below 3 years old",
    "PWD",
    "Senior Citizen",
    "Student",
    "Uniformed Personnel",
];

const DEFAULT_DISCOUNT_RATES = {
    "Senior Citizen": 20,
    PWD: 20,
    Student: 20,
    "Uniformed Personnel": 20,
    "3 to 11 years old": 50,
    "Below 3 years old": 75,
};

function getUsedTypes(containerEl) {
    return Array.from(
        containerEl.querySelectorAll(
            'select[name="discounts[][passenger_type]"]',
        ),
    )
        .map((s) => s.value)
        .filter(Boolean);
}

function refreshDiscountSelects(containerEl, addBtn) {
    const usedTypes = getUsedTypes(containerEl);
    containerEl
        .querySelectorAll('select[name="discounts[][passenger_type]"]')
        .forEach((sel) => {
            const own = sel.value;
            sel.innerHTML =
                '<option value="">Passenger Type</option>' +
                PASSENGER_TYPES.filter(
                    (t) => t === own || !usedTypes.includes(t),
                )
                    .map(
                        (t) =>
                            `<option value="${t}"${t === own ? " selected" : ""}>${t}</option>`,
                    )
                    .join("");
        });
    if (addBtn) {
        const full = usedTypes.length >= PASSENGER_TYPES.length;
        addBtn.disabled = full;
        addBtn.style.opacity = full ? "0.4" : "1";
        addBtn.style.cursor = full ? "not-allowed" : "pointer";
    }
}

function buildDiscountRow(containerEl, type = "", rate = null, addBtn = null) {
    const defaultRate = type !== "" ? (DEFAULT_DISCOUNT_RATES[type] ?? 20) : 20;
    const actualRate = rate !== null ? rate : defaultRate;

    const row = document.createElement("div");
    row.style.cssText =
        "display:flex;gap:8px;align-items:center;margin-top:6px;";

    const usedTypes = getUsedTypes(containerEl);
    const sel = document.createElement("select");
    sel.name = "discounts[][passenger_type]";
    sel.style.cssText =
        "flex:1;padding:4px 6px;border:1px solid #ccc;border-radius:4px;";
    sel.innerHTML =
        '<option value="">Passenger Type</option>' +
        PASSENGER_TYPES.filter((t) => t === type || !usedTypes.includes(t))
            .map(
                (t) =>
                    `<option value="${t}"${t === type ? " selected" : ""}>${t}</option>`,
            )
            .join("");

    const inp = document.createElement("input");
    inp.type = "number";
    inp.name = "discounts[][discount_rate]";
    inp.placeholder = "Discount %";
    inp.min = 0;
    inp.max = 100;
    inp.step = 0.01;
    inp.value = actualRate;
    inp.style.cssText =
        "width:140px;padding:4px 6px;border:1px solid #ccc;border-radius:4px;";

    sel.addEventListener("change", function () {
        inp.value = DEFAULT_DISCOUNT_RATES[this.value] ?? 20;
        refreshDiscountSelects(containerEl, addBtn);
    });

    const rm = document.createElement("button");
    rm.type = "button";
    rm.textContent = "×";
    rm.style.cssText =
        "background:none;border:none;color:#dc3545;font-size:1.2em;cursor:pointer;line-height:1;padding:0 4px;";
    rm.addEventListener("click", () => {
        row.remove();
        refreshDiscountSelects(containerEl, addBtn);
    });

    row.appendChild(sel);
    row.appendChild(inp);
    row.appendChild(rm);
    containerEl.appendChild(row);

    refreshDiscountSelects(containerEl, addBtn);
}

const addRouteCategoryBtn = document.getElementById("addRouteCategoryBtn");
const addRouteCategoryModal = document.getElementById("addRouteCategoryModal");
const closeAddRouteCategoryModal = document.getElementById(
    "closeAddRouteCategoryModal",
);
const addRouteCategoryForm = document.getElementById("addRouteCategoryForm");

document.addEventListener("DOMContentLoaded", function () {
    // --- DISCOUNT ROWS (Add) ---
    const addRCDiscountsContainer = document.getElementById(
        "addRCDiscountsContainer",
    );
    const addRCDiscountRowBtn = document.getElementById("addRCDiscountRowBtn");

    function populateDefaultDiscountRows() {
        addRCDiscountsContainer.innerHTML = "";
        PASSENGER_TYPES.forEach((type) => {
            buildDiscountRow(
                addRCDiscountsContainer,
                type,
                null,
                addRCDiscountRowBtn,
            );
        });
    }

    addRCDiscountRowBtn.addEventListener("click", () => {
        const used = getUsedTypes(addRCDiscountsContainer);
        const nextType = PASSENGER_TYPES.find((t) => !used.includes(t)) || "";
        buildDiscountRow(
            addRCDiscountsContainer,
            nextType,
            null,
            addRCDiscountRowBtn,
        );
    });

    // --- OPEN MODAL --- auto-fill defaults
    addRouteCategoryBtn.addEventListener("click", () => {
        populateDefaultDiscountRows();
        addRouteCategoryModal.style.display = "flex";
    });

    // Detach the original open listener reference (handled above now)
    addRouteCategoryBtn._rcOpenHandled = true;

    // --- CLOSE MODAL ---
    function closeRouteCategoryModal() {
        addRouteCategoryModal.style.display = "none";
        addRouteCategoryForm.reset();
        populateDefaultDiscountRows();
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

        // Collect discount rows
        const rows = addRCDiscountsContainer.querySelectorAll("div");
        rows.forEach((row, i) => {
            const sel = row.querySelector("select");
            const inp = row.querySelector('input[type="number"]');
            if (sel && inp && sel.value && inp.value !== "") {
                formData.append(`discounts[${i}][passenger_type]`, sel.value);
                formData.append(`discounts[${i}][discount_rate]`, inp.value);
            }
        });

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
                populateDefaultDiscountRows();

                if (data.routeCategory) {
                    const rc = data.routeCategory;
                    const discounts = rc.passenger_discounts ?? [];
                    const discountsJson = JSON.stringify(
                        discounts.map((d) => ({
                            passenger_type: d.passenger_type,
                            discount_rate: d.discount_rate,
                        })),
                    );

                    // Helper: build a sorted option and append to a select
                    function addSortedOption(
                        selectEl,
                        value,
                        text,
                        extraDataset,
                    ) {
                        const option = document.createElement("option");
                        option.value = value;
                        option.text = text;
                        if (extraDataset)
                            Object.assign(option.dataset, extraDataset);
                        selectEl.appendChild(option);
                        const opts = Array.from(selectEl.options)
                            .slice(1)
                            .sort((a, b) => a.text.localeCompare(b.text));
                        selectEl.innerHTML = selectEl.options[0].outerHTML;
                        opts.forEach((o) => selectEl.appendChild(o));
                    }

                    // Update Add Route & Port dropdown
                    const routeCategorySelect =
                        document.getElementById("route_category_id");
                    if (routeCategorySelect) {
                        addSortedOption(
                            routeCategorySelect,
                            rc.route_category_id,
                            rc.route_category_name,
                            {},
                        );
                    }

                    // Update Edit Route Category dropdown
                    const editRCSelect =
                        document.getElementById("editRCSelect");
                    if (editRCSelect) {
                        addSortedOption(
                            editRCSelect,
                            rc.route_category_id,
                            rc.route_category_name,
                            {
                                name: rc.route_category_name,
                                rate: rc.route_rate ?? "",
                                discounts: discountsJson,
                            },
                        );
                    }
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

// ===== Edit Route Category Modal =====
document.addEventListener("DOMContentLoaded", function () {
    const editRCBtn = document.getElementById("editRouteCategoryBtn");
    const editRCModal = document.getElementById("editRouteCategoryModal");
    const closeEditRCModal = document.getElementById(
        "closeEditRouteCategoryModal",
    );
    const editRCForm = document.getElementById("editRouteCategoryForm");
    const editRCSelect = document.getElementById("editRCSelect");
    const editRCRate = document.getElementById("editRCRate");
    const saveEditRCBtn = document.getElementById("saveEditRCBtn");
    const editRCDiscountsContainer = document.getElementById(
        "editRCDiscountsContainer",
    );

    let originalRCRate = null;

    function closeEditRouteCategory() {
        editRCModal.style.display = "none";
        editRCForm.reset();
        saveEditRCBtn.disabled = true;
        saveEditRCBtn.style.backgroundColor = "#ccc";
        saveEditRCBtn.style.cursor = "not-allowed";
        originalRCRate = null;
        editRCDiscountsContainer.innerHTML = "";
    }

    editRCBtn.addEventListener("click", () => {
        editRCModal.style.display = "flex";
    });

    closeEditRCModal.addEventListener("click", closeEditRouteCategory);

    window.addEventListener("click", (e) => {
        if (e.target === editRCModal) closeEditRouteCategory();
    });

    // When a category is selected, populate the rate and discount fields
    editRCSelect.addEventListener("change", function () {
        const selected = this.options[this.selectedIndex];
        if (selected && selected.value !== "") {
            editRCRate.value = selected.dataset.rate ?? "";
            originalRCRate = selected.dataset.rate ?? "";

            // Load existing discounts
            editRCDiscountsContainer.innerHTML = "";
            try {
                const discounts = JSON.parse(
                    selected.dataset.discounts || "[]",
                );
                discounts.sort((a, b) =>
                    a.passenger_type.localeCompare(b.passenger_type),
                );
                discounts
                    .filter((d) => PASSENGER_TYPES.includes(d.passenger_type))
                    .forEach((d) =>
                        buildDiscountRow(
                            editRCDiscountsContainer,
                            d.passenger_type,
                            d.discount_rate,
                            editRCDiscountRowBtn,
                        ),
                    );
                refreshDiscountSelects(
                    editRCDiscountsContainer,
                    editRCDiscountRowBtn,
                );
            } catch (e) {
                /* ignore */
            }
        } else {
            editRCRate.value = "";
            originalRCRate = null;
            editRCDiscountsContainer.innerHTML = "";
        }
        checkRCChanged();
    });

    // Wire + Add Discount button for edit modal
    const editRCDiscountRowBtn = document.getElementById(
        "editRCDiscountRowBtn",
    );
    editRCDiscountRowBtn.addEventListener("click", () => {
        const used = getUsedTypes(editRCDiscountsContainer);
        const nextType = PASSENGER_TYPES.find((t) => !used.includes(t)) || "";
        buildDiscountRow(
            editRCDiscountsContainer,
            nextType,
            null,
            editRCDiscountRowBtn,
        );
        checkRCChanged();
    });

    // Enable save button only when something changed
    editRCRate.addEventListener("input", checkRCChanged);
    editRCDiscountsContainer.addEventListener("input", checkRCChanged);
    editRCDiscountsContainer.addEventListener("change", checkRCChanged);

    function checkRCChanged() {
        const hasCategory = editRCSelect.value !== "";
        const rateChanged = editRCRate.value !== (originalRCRate ?? "");
        // Always allow save if a category is selected (discounts may have changed)
        const canSave = hasCategory;

        saveEditRCBtn.disabled = !canSave;
        saveEditRCBtn.style.backgroundColor = canSave ? "#485B8C" : "#ccc";
        saveEditRCBtn.style.cursor = canSave ? "pointer" : "not-allowed";
    }

    editRCForm.addEventListener("submit", async (e) => {
        e.preventDefault();

        const id = editRCSelect.value;
        const selectedOption = editRCSelect.options[editRCSelect.selectedIndex];
        const formData = new FormData(editRCForm);
        // Include the current name so validation passes
        formData.set("route_category_name", selectedOption.dataset.name);
        // Laravel method spoofing
        formData.set("_method", "PUT");

        // Collect discount rows
        const rows = editRCDiscountsContainer.querySelectorAll("div");
        rows.forEach((row, i) => {
            const sel = row.querySelector("select");
            const inp = row.querySelector('input[type="number"]');
            if (sel && inp && sel.value && inp.value !== "") {
                formData.append(`discounts[${i}][passenger_type]`, sel.value);
                formData.append(`discounts[${i}][discount_rate]`, inp.value);
            }
        });

        try {
            const response = await fetch(
                `/authorized/admin/route_categories/${id}`,
                {
                    method: "POST",
                    headers: {
                        "X-CSRF-TOKEN": document.querySelector(
                            'meta[name="csrf-token"]',
                        ).content,
                        Accept: "application/json",
                    },
                    body: formData,
                },
            );

            const data = await response.json().catch(() => ({}));

            if (response.ok && data.status === "success") {
                showToast("Route Category updated successfully!", "success");

                // Update the stored data-rate and data-discounts on the option
                selectedOption.dataset.rate = editRCRate.value;
                if (
                    data.routeCategory &&
                    data.routeCategory.passenger_discounts
                ) {
                    selectedOption.dataset.discounts = JSON.stringify(
                        data.routeCategory.passenger_discounts.map((d) => ({
                            passenger_type: d.passenger_type,
                            discount_rate: d.discount_rate,
                        })),
                    );
                }

                closeEditRouteCategory();
            } else {
                showToast(
                    data.message || "Error updating route category",
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
            const province = opt.dataset.province?.toLowerCase() || "";
            const city = opt.dataset.city?.toLowerCase() || "";
            const terminal = opt.dataset.terminal?.toLowerCase() || "";
            const text = opt.text.toLowerCase();
            if (
                province.includes(search) ||
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
