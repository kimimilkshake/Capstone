document.addEventListener('DOMContentLoaded', function() {
  // containers
  const hatchContainer = document.getElementById('hatch-container');
  const accContainer = document.getElementById('accommodation-container');

  // helper to make a hatch row element
  function createHatchRow(index) {
    const row = document.createElement('div');
    row.className = 'hatch-row';
    row.innerHTML = `
      <input type="text" name="hatches[${index}][label]" placeholder="Hatch Label" required>
      <input type="number" name="hatches[${index}][capacity]" placeholder="Capacity" required>
      <button type="button" class="dynamic-add hatch-action">+</button>
    `;
    return row;
  }

  // helper to make an accommodation row element
  function createAccRow(index) {
    const row = document.createElement('div');
    row.className = 'accommodation-row';
    row.innerHTML = `
      <input type="text" name="accommodations[${index}][name]" placeholder="Accommodation Name" required>
      <input type="number" name="accommodations[${index}][price]" placeholder="Regular Price" required>
      <button type="button" class="dynamic-add acc-action">+</button>
    `;
    return row;
  }

  // initialize indexes by counting existing rows
  function hatchCount() { return hatchContainer.querySelectorAll('.hatch-row').length; }
  function accCount() { return accContainer.querySelectorAll('.accommodation-row').length; }

  function refreshHatchButtons() {
    const rows = hatchContainer.querySelectorAll('.hatch-row');
    rows.forEach((row, i) => {
      const btn = row.querySelector('button');
      if (!btn) return;
      if (i === rows.length - 1) {
        btn.textContent = '+';
        btn.className = 'dynamic-add hatch-action';
      } else {
        btn.textContent = '−';
        btn.className = 'dynamic-remove remove-hatch';
      }
    });
  }

  function refreshAccButtons() {
    const rows = accContainer.querySelectorAll('.accommodation-row');
    rows.forEach((row, i) => {
      const btn = row.querySelector('button');
      if (!btn) return;
      if (i === rows.length - 1) {
        btn.textContent = '+';
        btn.className = 'dynamic-add acc-action';
      } else {
        btn.textContent = '−';
        btn.className = 'dynamic-remove remove-acc';
      }
    });
  }

  // Attach single delegated click listener for hatch container
  hatchContainer.addEventListener('click', function(e) {
    const target = e.target;

    // Add new hatch
    if (target.classList.contains('hatch-action')) {
      target.textContent = '−';
      target.className = 'dynamic-remove remove-hatch';

      const newIndex = hatchCount(); // next index
      const newRow = createHatchRow(newIndex);
      hatchContainer.appendChild(newRow);

      refreshHatchButtons();
      return;
    }

    // Remove hatch
    if (target.classList.contains('remove-hatch')) {
      const row = target.closest('.hatch-row');
      if (row) row.remove();
      // after removal, refresh buttons so last is + again
      refreshHatchButtons();
      return;
    }
  });

  // Attach delegated listener for accommodation container
  accContainer.addEventListener('click', function(e) {
    const target = e.target;

    // Add new accommodation
    if (target.classList.contains('acc-action')) {
      target.textContent = '−';
      target.className = 'dynamic-remove remove-acc';

      const newIndex = accCount();
      const newRow = createAccRow(newIndex);
      accContainer.appendChild(newRow);

      refreshAccButtons();
      return;
    }

    // Remove accommodation
    if (target.classList.contains('remove-acc')) {
      const row = target.closest('.accommodation-row');
      if (row) row.remove();
      refreshAccButtons();
      return;
    }
  });

  // initial refresh to set button states correctly for pre-existing rows
  refreshHatchButtons();
  refreshAccButtons();
});