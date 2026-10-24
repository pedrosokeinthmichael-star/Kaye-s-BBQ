// modal.js
// Shared modal open/close behavior + alert helper.
// Used on any page that includes the login modal (and any other .modal-overlay).

(function () {
  function openModal(id) {
    var modal = document.getElementById(id);
    if (!modal) return;
    modal.classList.add('open');
    document.body.style.overflow = 'hidden';
    var focusable = modal.querySelector('input, button, a[href]');
    if (focusable) focusable.focus();
  }

  function closeModal(id) {
    var modal = document.getElementById(id);
    if (!modal) return;
    modal.classList.remove('open');
    document.body.style.overflow = '';
  }

  document.querySelectorAll('[data-open-modal]').forEach(function (el) {
    el.addEventListener('click', function (e) {
      e.preventDefault();
      openModal(el.getAttribute('data-open-modal'));
    });
  });

  document.querySelectorAll('[data-close-modal]').forEach(function (el) {
    el.addEventListener('click', function (e) {
      e.preventDefault();
      closeModal(el.getAttribute('data-close-modal'));
    });
  });

  document.querySelectorAll('.modal-overlay').forEach(function (overlay) {
    overlay.addEventListener('click', function (e) {
      if (e.target === overlay) closeModal(overlay.id);
    });
  });

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
      document.querySelectorAll('.modal-overlay.open').forEach(function (m) {
        closeModal(m.id);
      });
    }
  });

  // Expose helpers globally so login.js / register.js can use them.
  window.KayeModal = {
    open: openModal,
    close: closeModal,
    setAlert: function (alertId, textId, message) {
      var alertEl = document.getElementById(alertId);
      var textEl = document.getElementById(textId);
      if (!alertEl || !textEl) return;
      if (message) {
        textEl.textContent = message;
        alertEl.classList.add('show');
      } else {
        textEl.textContent = '';
        alertEl.classList.remove('show');
      }
    }
  };
})();