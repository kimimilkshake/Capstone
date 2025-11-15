//ABOUT PAGE - SIDE BAR
function showFragment(fragment) {
  fetch(`/passenger/about_partials/${fragment}`)
    .then(res => res.text())
    .then(html => document.getElementById('about-content').innerHTML = html);
}

const sidebarItems = document.querySelectorAll('.about-sidebar li');

  sidebarItems.forEach(item => {
    item.addEventListener('click', function() {
      // remove active class from all
      sidebarItems.forEach(li => li.classList.remove('active'));
      // add active to clicked one
      this.classList.add('active');

      // load fragment
      showFragment(this.dataset.section);
    });
  });

//ABOUT PAGE - VESSELS PAGE
(() => {
  // open modal when clicking any .ava-card (event delegation)
  document.addEventListener('click', (e) => {
    const card = e.target.closest('.ava-card');
    if (!card) return;

    const img = card.querySelector('.ava-pic');
    if (!img) return;

    const modal = document.getElementById('avaModal');
    const modalImg = document.getElementById('avaModalImg');
    if (!modal || !modalImg) return;

    modalImg.src = img.src;
    modal.style.display = 'flex';
  });

  // close modal when clicking the X, or clicking outside the image (on the overlay)
  document.addEventListener('click', (e) => {
    // close button (supports fontawesome inside span too)
    if (e.target.closest('.ava-close')) {
      const modal = document.getElementById('avaModal');
      if (modal) modal.style.display = 'none';
      return;
    }

    // clicking overlay (but not the image) closes modal
    const modal = document.getElementById('avaModal');
    if (!modal) return;
    if (e.target === modal) {
      modal.style.display = 'none';
    }
  });

  // close with Escape key
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
      const modal = document.getElementById('avaModal');
      if (modal && modal.style.display === 'flex') modal.style.display = 'none';
    }
  });

  // optional: prevent image clicks from bubbling to the overlay handler
  const stopPropagationIfModalImg = (e) => {
    if (e.target.classList && e.target.classList.contains('ava-modal-content')) {
      e.stopPropagation();
    }
  };
  document.addEventListener('click', stopPropagationIfModalImg);
})();

//FAQs PAGE for every FAQ Item
document.querySelectorAll('.faq-question').forEach(button => {
    button.addEventListener('click', () => {
        const answer = button.nextElementSibling;
        const toggle = button.querySelector('.faq-toggle');

        // Toggle display
        if (answer.style.display === 'block') {
            answer.style.display = 'none';
            toggle.textContent = '+';
        } else {
            // Close all other open answers
            document.querySelectorAll('.faq-answer').forEach(a => a.style.display = 'none');
            document.querySelectorAll('.faq-toggle').forEach(t => t.textContent = '+');

            answer.style.display = 'block';
            toggle.textContent = '–';
        }
    });
});
