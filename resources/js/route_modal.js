document.addEventListener("DOMContentLoaded", function () {
    const addModal = document.getElementById("addRouteModal");
    const editModal = document.getElementById("editRouteModal");
    const addBtn = document.getElementById("addRouteBtn");
    const closeAdd = document.getElementById("closeAddModal");
    const closeEdit = document.getElementById("closeEditModal");
    const addForm = document.getElementById("addRouteForm");
    const editForm = document.getElementById("editRouteForm");

    addBtn.addEventListener("click", function (e) {
        e.preventDefault();
        addModal.style.display = "flex";
    });

    closeAdd.addEventListener("click", () => (addModal.style.display = "none"));
    closeEdit.addEventListener("click", () => (editModal.style.display = "none"));


    window.addEventListener("click", function (e) {
        if (e.target === addModal) addModal.style.display = "none";
        if (e.target === editModal) editModal.style.display = "none";
    });

    addForm.addEventListener("submit", async function (e) {
        e.preventDefault();
        const formData = new FormData(this);

        try {
            const response = await fetch("/routes", {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                },
                body: formData
            });

            // Try parsing JSON safely
            let data = {};
            try {
                data = await response.json();
            } catch (err) {
                console.log("Non-JSON response (redirect likely).");
            }

            if (response.ok) {
                alert(data.message || "Route added successfully!");
                location.reload();
            } else {
                alert("Failed to add route.");
            }
        } catch (error) {
            console.error("Error:", error);
            alert("An unexpected error occurred while adding route.");
        }
    });

    document.querySelectorAll(".editRouteBtn").forEach((btn) => {
        btn.addEventListener("click", function (e) {
            e.preventDefault();
            editModal.style.display = "flex";
            document.getElementById("editRouteId").value = this.dataset.id;
            document.getElementById("editRouteOrigin").value = this.dataset.origin;
            document.getElementById("editRouteDestination").value = this.dataset.destination;
        });
    });

    editForm.addEventListener("submit", async function (e) {
        e.preventDefault();
        const routeId = document.getElementById("editRouteId").value;
        const formData = new FormData(this);

        try {
            const response = await fetch(`/routes/${routeId}`, {
                method: "POST", // Laravel method spoofing (PUT in hidden input)
                headers: {
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                },
                body: formData
            });

            let data = {};
            try {
                data = await response.json();
            } catch (err) {
                console.log("Non-JSON response (redirect likely).");
            }

            if (response.ok) {
                alert(data.message || "Route updated successfully!");
                location.reload();
            } else {
                alert("Failed to update route.");
            }
        } catch (error) {
            console.error("Error:", error);
            alert("An unexpected error occurred while updating route.");
        }
    });
});
