document.addEventListener("DOMContentLoaded", function () {

    const addModal = document.getElementById("addRoutePortModal");
    const editModal = document.getElementById("editRoutPortModal");

    const addBtn = document.getElementById("addRoutePortBtn");
    const closeAdd = document.getElementById("closeAddModal");
    const closeEdit = document.getElementById("closeEditModal");

    const addForm = document.getElementById("addRoutePortForm");
    const editForm = document.getElementById("editRoutePortForm");

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
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                },
                body: formData
            });

            const data = await response.json().catch(() => ({}));

            if (response.ok && data.status === "success") {
                alert(data.message);
                location.reload();
            } else {
                alert(data.message || "Failed to add Route & Port.");
            }
        } catch (err) {
            alert("Unexpected error: " + err.message);
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
        });
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
                alert(data.message);
                location.reload();
            } else {
                alert(data.message || "Failed to update Route & Port.");
            }
        } catch (err) {
            alert("Unexpected error: " + err.message);
        }
    });
});
