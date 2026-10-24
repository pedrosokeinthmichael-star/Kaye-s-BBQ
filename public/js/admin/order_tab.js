/**
 * orders-tabs.js
 * Extracted from orders.php so the markup file holds no inline script.
 */
/* Top-level tabs: Active Orders / Order Settings (delivery & payment).
   Open the settings tab directly with  /admin/orders#order-settings .
   After saving something in the settings tab the controller redirects back
   here, so a short-lived flag keeps the settings tab open across that reload. */
(function () {
  var buttons = Array.prototype.slice.call(document.querySelectorAll("[data-os-tab]"));
  var panels  = Array.prototype.slice.call(document.querySelectorAll("[data-os-panel]"));
  var SETTINGS = "tab-order-settings";
  var FLAG = "ordersOpenSettingsAt";
  if (!buttons.length) return;

  function activate(id) {
    buttons.forEach(function (b) {
      var on = b.getAttribute("data-os-tab") === id;
      b.classList.toggle("active", on);
      b.setAttribute("aria-selected", on ? "true" : "false");
    });
    panels.forEach(function (p) { p.hidden = p.id !== id; });
    if (window.history && history.replaceState) {
      history.replaceState(null, "", id === SETTINGS ? "#order-settings" : location.pathname + location.search);
    }
  }

  buttons.forEach(function (b) {
    b.addEventListener("click", function () { activate(b.getAttribute("data-os-tab")); });
  });

  function rememberSettingsTab() {
    try { sessionStorage.setItem(FLAG, String(Date.now())); } catch (e) {}
  }
  var settingsPanel = document.getElementById(SETTINGS);
  if (settingsPanel) {
    settingsPanel.addEventListener("submit", rememberSettingsTab, true);
    settingsPanel.addEventListener("click", function (e) {
      if (e.target.closest("[data-delete-barangay], #delete-barangay-btn")) rememberSettingsTab();
    }, true);
  }

  var initial = "tab-orders";
  if (location.hash === "#order-settings") {
    initial = SETTINGS;
  } else {
    try {
      var at = parseInt(sessionStorage.getItem(FLAG), 10);
      sessionStorage.removeItem(FLAG);
      if (at && Date.now() - at < 10000) initial = SETTINGS;
    } catch (e) {}
  }
  activate(initial);
})();