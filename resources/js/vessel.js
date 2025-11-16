document.addEventListener('DOMContentLoaded', function() {
  const hatchContainer = document.getElementById('hatch-container');
  const accContainer = document.getElementById('accommodation-container');

  function createHatchRow(index) {
    const row = document.createElement('div');
    row.className = 'hatch-row';
    row.innerHTML = `
      <input type="text" name="hatches[${index}][label]" placeholder="Hatch Label" required>
      <input type="number" name="hatches[${index}][area_capacity]" placeholder="Area Capacity in Cubic Meters" required>
      <input type="number" name="hatches[${index}][weight_capacity]" placeholder="Weight Capacity in Tons" required>
      <button type="button" class="dynamic-add hatch-action">+</button>
    `;
    return row;
  }

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

  hatchContainer.addEventListener('click', function(e) {
    const target = e.target;

    if (target.classList.contains('hatch-action')) {
      target.textContent = '−';
      target.className = 'dynamic-remove remove-hatch';
      const newRow = createHatchRow(hatchCount());
      hatchContainer.appendChild(newRow);
      refreshHatchButtons();
      return;
    }

    if (target.classList.contains('remove-hatch')) {
      target.closest('.hatch-row').remove();
      refreshHatchButtons();
      return;
    }
  });

  accContainer.addEventListener('click', function(e) {
    const target = e.target;

    if (target.classList.contains('acc-action')) {
      target.textContent = '−';
      target.className = 'dynamic-remove remove-acc';
      const newRow = createAccRow(accCount());
      accContainer.appendChild(newRow);
      refreshAccButtons();
      return;
    }

    if (target.classList.contains('remove-acc')) {
      target.closest('.accommodation-row').remove();
      refreshAccButtons();
      return;
    }
  });

  refreshHatchButtons();
  refreshAccButtons();
});
