import './bootstrap';
import * as bootstrap from 'bootstrap';
import './admin_nav';
import './staff_nav';
import './vessel';
import './route_port_modal';
import './afc';

document.addEventListener("DOMContentLoaded", function () {

    const saveBtn = document.getElementById("saveEditBtn");

    // ❗ If page has no save button → DO NOTHING
    if (!saveBtn) return;

    const form = saveBtn.closest("form");
    if (!form) return;

    const inputs = form.querySelectorAll("input, select, textarea");

    let originalData = {};

    // STORE ORIGINAL VALUES
    inputs.forEach(input => {
        if (input.name) {
            originalData[input.name] = input.value;
        }
    });

    // disable initially
    saveBtn.disabled = true;
    saveBtn.style.backgroundColor = "#ccc";
    saveBtn.style.cursor = "not-allowed";

    // CHECK CHANGES
    inputs.forEach(input => {
        input.addEventListener("input", checkChanges);
        input.addEventListener("change", checkChanges);
    });

    function checkChanges() {
        let changed = false;

        inputs.forEach(input => {
            if (input.name && originalData[input.name] !== input.value) {
                changed = true;
            }
        });

        if (changed) {
            saveBtn.disabled = false;
            saveBtn.style.backgroundColor = "#485B8C";
            saveBtn.style.cursor = "pointer";
        } else {
            saveBtn.disabled = true;
            saveBtn.style.backgroundColor = "#ccc";
            saveBtn.style.cursor = "not-allowed";
        }
    }

});

document.addEventListener("DOMContentLoaded", function() {
    document.querySelectorAll('.disabled-voyage').forEach(el => {
        el.addEventListener('click', function(e) {
            e.preventDefault(); // stop navigation
            showToast('This voyage is currently "At Sea" and cannot be edited.', 'danger');
        });
    });
});
