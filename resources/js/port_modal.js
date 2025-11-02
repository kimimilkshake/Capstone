document.addEventListener('DOMContentLoaded', function () {
  const addPortBtn = document.getElementById('addPortBtn');
  const addPortModal = document.getElementById('addPortModal');
  const closeAddModal = document.getElementById('closeAddModal');
  const addPortForm = document.getElementById('addPortForm');

  const editPortBtns = document.querySelectorAll('.editPortBtn');
  const editPortModal = document.getElementById('editPortModal');
  const closeEditModal = document.getElementById('closeEditModal');
  const editPortForm = document.getElementById('editPortForm');

  // Open/close add modal
  addPortBtn.onclick = () => (addPortModal.style.display = 'flex');
  closeAddModal.onclick = () => (addPortModal.style.display = 'none');

  // Open edit modal and populate fields
  editPortBtns.forEach((btn) => {
    btn.addEventListener('click', () => {
      document.getElementById('editPortId').value = btn.dataset.id;
      document.getElementById('editPortName').value = btn.dataset.name;
      document.getElementById('editPortCity').value = btn.dataset.city;
      document.getElementById('editPortProvince').value = btn.dataset.province;
      editPortModal.style.display = 'flex';
    });
  });

  closeEditModal.onclick = () => (editPortModal.style.display = 'none');

  // Add Port (AJAX)
  addPortForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(addPortForm);

    try {
      const response = await fetch('/authorized/admin/ports', {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': formData.get('_token'),
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData,
      });

      const data = await response.json().catch(() => null);
      if (data && data.success) {
        alert('Port added successfully!');
        location.reload();
      } else {
        alert('Error adding port.');
      }
    } catch (err) {
      console.error(err);
      alert('An error occurred.');
    }
  });

  // Edit Port (AJAX)
  editPortForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    const id = document.getElementById('editPortId').value;
    const formData = new FormData(editPortForm);

    try {
      const response = await fetch(`/authorized/admin/ports/${id}`, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': formData.get('_token'),
          'X-HTTP-Method-Override': 'PUT',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData,
      });

      const data = await response.json().catch(() => null);
      if (data && data.success) {
        alert('Port updated successfully!');
        location.reload();
      } else {
        alert('Error updating port.');
      }
    } catch (err) {
      console.error(err);
      alert('An error occurred.');
    }
  });
});
