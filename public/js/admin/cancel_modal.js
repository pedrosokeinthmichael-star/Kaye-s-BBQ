/**
 * cancel-modal.js
 * Extracted from modals/cancel_modal.php so the markup file holds no inline script.
 */
/**
 * Cancellation note.
 *  - Clears the note every time the modal opens.
 *  - Shows a character counter.
 *  - When "Cancel Order" is clicked, attaches the note as `cancel_note`
 *    to the cancel request that js/admin/orders.js sends (works for
 *    fetch() with a JSON, FormData or URL-encoded body), so orders.js
 *    itself doesn't need to change.
 */
(function () {
  var backdrop = document.getElementById('cancel-modal-backdrop');
  var note = document.getElementById('cancel-modal-note');
  var count = document.getElementById('cancel-modal-note-count');
  var confirmBtn = document.getElementById('cancel-modal-confirm');
  if (!backdrop || !note || !confirmBtn) return;

  function updateCount() { count.textContent = note.value.length + '/' + note.maxLength; }
  note.addEventListener('input', updateCount);

  // Reset whenever the modal goes from hidden to visible.
  var wasOpen = false;
  new MutationObserver(function () {
    var isOpen = window.getComputedStyle(backdrop).display !== 'none';
    if (isOpen && !wasOpen) { note.value = ''; updateCount(); }
    wasOpen = isOpen;
  }).observe(backdrop, { attributes: true, attributeFilter: ['style', 'class'] });

  // Capture phase: runs before orders.js's own click handler.
  var pending = false, timer = null;
  confirmBtn.addEventListener('click', function () {
    pending = true;
    clearTimeout(timer);
    timer = setTimeout(function () { pending = false; }, 2000);
  }, true);

  var nativeFetch = window.fetch;
  window.fetch = function (input, init) {
    if (pending && init && init.body != null) {
      var text = note.value.trim();
      var body = init.body;
      init = Object.assign({}, init);

      if (typeof FormData !== 'undefined' && body instanceof FormData) {
        body.append('cancel_note', text);
      } else if (typeof URLSearchParams !== 'undefined' && body instanceof URLSearchParams) {
        body.append('cancel_note', text);
      } else if (typeof body === 'string') {
        try {
          var obj = JSON.parse(body);
          obj.cancel_note = text;
          init.body = JSON.stringify(obj);
        } catch (e) {
          init.body = body + (body ? '&' : '') + 'cancel_note=' + encodeURIComponent(text);
        }
      }
      pending = false;
    }
    return nativeFetch.call(this, input, init);
  };
})();