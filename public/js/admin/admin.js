/**
 * notif.js — Admin Notification Bell (Kaye's Barbeque Admin Panel)
 * --------------------------------------------------------------
 * Drop-in script. Injects a notification bell + dropdown into the
 * `.nav-actions` area of the admin header, on every admin page.
 *
 * Usage: add this before </body> on each admin page (admin.php,
 * orders.php, menus.php, sales.php, profile.php):
 *
 *   <script src="<?= base_url('js/notif.js') ?>"></script>
 *
 * No HTML changes needed — the bell finds `.nav-actions` and
 * inserts itself automatically.
 *
 * Notifications persist in localStorage (same pattern as your
 * cart/orders data), so read/unread state survives page loads
 * and the bell stays in sync across admin.php, orders.php, etc.
 *
 * To push a real notification from elsewhere in your code:
 *
 *   AdminNotifications.add({
 *     type: 'order',        // 'order' | 'stock' | 'system'
 *     title: 'New order #1048',
 *     message: 'Juan Dela Cruz placed an order for ₱630.',
 *     href: '<?= site_url("admin/orders") ?>'
 *   });
 */

(function () {
  'use strict';

  var STORAGE_KEY = 'kbbq_admin_notifications';
  var MAX_NOTIFICATIONS = 30;

  /* ------------------------------------------------------------ *
   * Seed data — shown the first time the bell is ever opened on
   * this browser, so it isn't empty on a fresh install. Mirrors
   * the sample orders/stats already used across the admin pages.
   * ------------------------------------------------------------ */
  function seedNotifications() {
    var now = Date.now();
    return [
      {
        id: 'seed-1',
        type: 'order',
        title: 'New order #1047',
        message: 'Juan Dela Cruz placed an order for ₱1,085 (Delivery).',
        href: 'orders',
        time: now - 6 * 60 * 1000,
        read: false
      },
      {
        id: 'seed-2',
        type: 'order',
        title: 'Order #1046 out for delivery',
        message: "Maria Santos' order is now Delivering.",
        href: 'orders',
        time: now - 45 * 60 * 1000,
        read: false
      },
      {
        id: 'seed-3',
        type: 'stock',
        title: 'Slow-moving item',
        message: 'Tuna Panga has only sold 3 times this month — consider a promo.',
        href: 'menus',
        time: now - 3 * 60 * 60 * 1000,
        read: true
      },
      {
        id: 'seed-4',
        type: 'system',
        title: 'Order #1043 cancelled',
        message: 'Kevin Uy cancelled their order for ₱280.',
        href: 'orders',
        time: now - 26 * 60 * 60 * 1000,
        read: true
      }
    ];
  }

  /* ------------------------------------------------------------ *
   * Storage helpers
   * ------------------------------------------------------------ */
  function loadNotifications() {
    try {
      var raw = localStorage.getItem(STORAGE_KEY);
      if (!raw) {
        var seeded = seedNotifications();
        saveNotifications(seeded);
        return seeded;
      }
      var parsed = JSON.parse(raw);
      return Array.isArray(parsed) ? parsed : [];
    } catch (e) {
      return [];
    }
  }

  function saveNotifications(list) {
    try {
      localStorage.setItem(STORAGE_KEY, JSON.stringify(list.slice(0, MAX_NOTIFICATIONS)));
    } catch (e) {
      /* localStorage unavailable or full — fail silently */
    }
  }

  /* ------------------------------------------------------------ *
   * Formatting helpers
   * ------------------------------------------------------------ */
  function timeAgo(ts) {
    var diff = Math.max(0, Date.now() - ts);
    var mins = Math.floor(diff / 60000);
    if (mins < 1) return 'Just now';
    if (mins < 60) return mins + (mins === 1 ? ' min ago' : ' mins ago');
    var hrs = Math.floor(mins / 60);
    if (hrs < 24) return hrs + (hrs === 1 ? ' hour ago' : ' hours ago');
    var days = Math.floor(hrs / 24);
    if (days < 7) return days + (days === 1 ? ' day ago' : ' days ago');
    return new Date(ts).toLocaleDateString('en-PH', { month: 'short', day: 'numeric' });
  }

  function iconFor(type) {
    switch (type) {
      case 'order':
        return '<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M6 3h12v18l-3-2-3 2-3-2-3 2V3z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/></svg>';
      case 'stock':
        return '<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 3l10 18H2L12 3z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><line x1="12" y1="9" x2="12" y2="14" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/><circle cx="12" cy="17" r="1" fill="currentColor"/></svg>';
      default:
        return '<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.7"/><path d="M12 7v5l3.5 2" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>';
    }
  }

  function resolveHref(page) {
    // If a full URL/path was passed, use it as-is. Otherwise try to
    // build one relative to the current admin.* URL structure.
    if (!page) return null;
    if (/^https?:\/\//i.test(page) || page.indexOf('/') === 0) return page;
    var base = window.location.pathname.replace(/\/(admin)(\/[^/]*)?\/?$/, '/$1/');
    return base + page;
  }

  /* ------------------------------------------------------------ *
   * Styles — scoped under #admin-notif so nothing else is touched.
   * Uses the app's existing CSS variables with sane fallbacks.
   * ------------------------------------------------------------ */
  function injectStyles() {
    if (document.getElementById('admin-notif-styles')) return;
    var style = document.createElement('style');
    style.id = 'admin-notif-styles';
    style.textContent = [
      '#admin-notif{position:relative;display:inline-flex;align-items:center;margin-right:6px;}',
      '#admin-notif-btn{position:relative;display:inline-flex;align-items:center;justify-content:center;',
      'width:38px;height:38px;border-radius:50%;border:1px solid rgba(0,0,0,.08);background:transparent;',
      'cursor:pointer;color:var(--ink-soft,#6b5c4d);transition:background .15s ease,color .15s ease;}',
      '#admin-notif-btn:hover{background:rgba(0,0,0,.05);color:var(--ember,#c9622a);}',
      '#admin-notif-btn svg{width:19px;height:19px;}',
      '#admin-notif-badge{position:absolute;top:2px;right:2px;min-width:16px;height:16px;padding:0 3px;',
      'border-radius:999px;background:var(--brick,#b23b2e);color:#fff;font-size:10px;line-height:16px;',
      'font-weight:700;text-align:center;box-shadow:0 0 0 2px #fff;}',
      '#admin-notif-panel{position:absolute;top:calc(100% + 10px);right:0;width:340px;max-width:88vw;',
      'max-height:420px;overflow:hidden;display:flex;flex-direction:column;background:#fff;',
      'border-radius:14px;box-shadow:0 12px 32px rgba(0,0,0,.18);border:1px solid rgba(0,0,0,.06);',
      'opacity:0;transform:translateY(-6px);pointer-events:none;transition:opacity .15s ease,transform .15s ease;z-index:1000;}',
      '#admin-notif-panel.open{opacity:1;transform:translateY(0);pointer-events:auto;}',
      '#admin-notif-head{display:flex;align-items:center;justify-content:space-between;padding:14px 16px;',
      'border-bottom:1px solid rgba(0,0,0,.06);}',
      '#admin-notif-head h3{margin:0;font-size:15px;font-weight:700;color:#2b241d;}',
      '#admin-notif-mark{border:none;background:none;color:var(--ember,#c9622a);font-size:12.5px;',
      'font-weight:600;cursor:pointer;padding:2px 4px;}',
      '#admin-notif-mark:hover{text-decoration:underline;}',
      '#admin-notif-list{overflow-y:auto;flex:1 1 auto;}',
      '.admin-notif-item{display:flex;gap:10px;align-items:flex-start;padding:12px 16px;cursor:pointer;',
      'border-bottom:1px solid rgba(0,0,0,.045);transition:background .12s ease;}',
      '.admin-notif-item:last-child{border-bottom:none;}',
      '.admin-notif-item:hover{background:rgba(0,0,0,.03);}',
      '.admin-notif-item.unread{background:rgba(217,164,65,.08);}',
      '.admin-notif-item.unread:hover{background:rgba(217,164,65,.14);}',
      '.admin-notif-icon{flex:0 0 auto;width:30px;height:30px;border-radius:50%;display:flex;',
      'align-items:center;justify-content:center;background:rgba(0,0,0,.05);color:var(--ember,#c9622a);}',
      '.admin-notif-icon svg{width:15px;height:15px;}',
      '.admin-notif-body{flex:1 1 auto;min-width:0;}',
      '.admin-notif-title{font-size:13.5px;font-weight:700;color:#2b241d;margin-bottom:2px;}',
      '.admin-notif-msg{font-size:12.5px;color:var(--ink-soft,#6b5c4d);line-height:1.4;}',
      '.admin-notif-time{font-size:11px;color:#9c8f80;margin-top:4px;}',
      '.admin-notif-dot{flex:0 0 auto;width:8px;height:8px;border-radius:50%;background:var(--mustard,#d9a441);margin-top:6px;}',
      '#admin-notif-empty{padding:40px 16px;text-align:center;color:#9c8f80;font-size:13px;}',
      '@media (max-width:480px){#admin-notif-panel{position:fixed;top:64px;right:8px;left:8px;width:auto;}}'
    ].join('');
    document.head.appendChild(style);
  }

  /* ------------------------------------------------------------ *
   * Main widget
   * ------------------------------------------------------------ */
  function init() {
    var navActions = document.querySelector('.nav-actions');
    if (!navActions || document.getElementById('admin-notif')) return;

    injectStyles();

    var notifications = loadNotifications();

    var wrap = document.createElement('div');
    wrap.id = 'admin-notif';
    wrap.innerHTML =
      '<button type="button" id="admin-notif-btn" aria-haspopup="true" aria-expanded="false" title="Notifications">' +
        '<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M18 8a6 6 0 1 0-12 0c0 7-3 9-3 9h18s-3-2-3-9" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/><path d="M13.7 21a2 2 0 0 1-3.4 0" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>' +
        '<span id="admin-notif-badge" style="display:none;"></span>' +
      '</button>' +
      '<div id="admin-notif-panel" role="menu" aria-label="Notifications">' +
        '<div id="admin-notif-head">' +
          '<h3>Notifications</h3>' +
          '<button type="button" id="admin-notif-mark">Mark all read</button>' +
        '</div>' +
        '<div id="admin-notif-list"></div>' +
      '</div>';

    // Insert as the first item in nav-actions (before the profile link).
    navActions.insertBefore(wrap, navActions.firstChild);

    var btn = wrap.querySelector('#admin-notif-btn');
    var panel = wrap.querySelector('#admin-notif-panel');
    var badge = wrap.querySelector('#admin-notif-badge');
    var list = wrap.querySelector('#admin-notif-list');
    var markAllBtn = wrap.querySelector('#admin-notif-mark');

    function render() {
      var unreadCount = notifications.filter(function (n) { return !n.read; }).length;

      if (unreadCount > 0) {
        badge.style.display = 'flex';
        badge.textContent = unreadCount > 9 ? '9+' : String(unreadCount);
      } else {
        badge.style.display = 'none';
      }

      if (!notifications.length) {
        list.innerHTML = '<div id="admin-notif-empty">You\'re all caught up. No notifications yet.</div>';
        return;
      }

      list.innerHTML = notifications
        .slice()
        .sort(function (a, b) { return b.time - a.time; })
        .map(function (n) {
          return (
            '<div class="admin-notif-item' + (n.read ? '' : ' unread') + '" data-id="' + n.id + '" role="menuitem">' +
              '<div class="admin-notif-icon">' + iconFor(n.type) + '</div>' +
              '<div class="admin-notif-body">' +
                '<div class="admin-notif-title">' + escapeHtml(n.title) + '</div>' +
                '<div class="admin-notif-msg">' + escapeHtml(n.message) + '</div>' +
                '<div class="admin-notif-time">' + timeAgo(n.time) + '</div>' +
              '</div>' +
              (n.read ? '' : '<div class="admin-notif-dot"></div>') +
            '</div>'
          );
        })
        .join('');
    }

    function escapeHtml(str) {
      var div = document.createElement('div');
      div.textContent = String(str == null ? '' : str);
      return div.innerHTML;
    }

    function openPanel() {
      panel.classList.add('open');
      btn.setAttribute('aria-expanded', 'true');
      document.addEventListener('click', onOutsideClick, true);
      document.addEventListener('keydown', onEscape, true);
    }

    function closePanel() {
      panel.classList.remove('open');
      btn.setAttribute('aria-expanded', 'false');
      document.removeEventListener('click', onOutsideClick, true);
      document.removeEventListener('keydown', onEscape, true);
    }

    function onOutsideClick(e) {
      if (!wrap.contains(e.target)) closePanel();
    }

    function onEscape(e) {
      if (e.key === 'Escape') closePanel();
    }

    btn.addEventListener('click', function (e) {
      e.stopPropagation();
      if (panel.classList.contains('open')) {
        closePanel();
      } else {
        openPanel();
      }
    });

    markAllBtn.addEventListener('click', function (e) {
      e.stopPropagation();
      notifications.forEach(function (n) { n.read = true; });
      saveNotifications(notifications);
      render();
    });

    list.addEventListener('click', function (e) {
      var item = e.target.closest('.admin-notif-item');
      if (!item) return;
      var id = item.getAttribute('data-id');
      var n = notifications.find(function (x) { return x.id === id; });
      if (!n) return;
      n.read = true;
      saveNotifications(notifications);
      render();
      var href = resolveHref(n.href);
      if (href) window.location.href = href;
    });

    render();

    /* ---------------------------------------------------------- *
     * Public API — call from anywhere to push a live notification,
     * e.g. right after a customer places an order.
     * ---------------------------------------------------------- */
    window.AdminNotifications = {
      add: function (data) {
        var entry = {
          id: 'n-' + Date.now() + '-' + Math.random().toString(36).slice(2, 7),
          type: data.type || 'system',
          title: data.title || 'Notification',
          message: data.message || '',
          href: data.href || null,
          time: Date.now(),
          read: false
        };
        notifications.unshift(entry);
        notifications = notifications.slice(0, MAX_NOTIFICATIONS);
        saveNotifications(notifications);
        render();
        return entry.id;
      },
      markAllRead: function () {
        notifications.forEach(function (n) { n.read = true; });
        saveNotifications(notifications);
        render();
      },
      clear: function () {
        notifications = [];
        saveNotifications(notifications);
        render();
      },
      list: function () {
        return notifications.slice();
      }
    };
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();

/**
 * Sales page — date filter (Kaye's Barbeque Admin Panel)
 * --------------------------------------------------------------
 * Powers the "Filter by Date" panel and the summary stat cards
 * on admin/sales. Guarded so it's a no-op on every other admin
 * page (checks for #daily-table-body before doing anything).
 */
(function () {
  'use strict';

  function init() {
    var tbody = document.getElementById('daily-table-body');
    if (!tbody) return; // Not on the sales page — nothing to do.

    var rows = Array.prototype.slice.call(tbody.querySelectorAll('tr'));

    var modeButtons = Array.prototype.slice.call(document.querySelectorAll('.filter-mode-btn'));
    var fields = Array.prototype.slice.call(document.querySelectorAll('.date-filter-field'));
    var applyBtn = document.getElementById('filter-apply-btn');
    var resetBtn = document.getElementById('filter-reset-btn');
    var errorEl = document.getElementById('filter-error');
    var rangeNote = document.getElementById('filter-range-note');
    var countNote = document.getElementById('daily-count-note');
    var emptyNote = document.getElementById('daily-empty');

    var revenueEl = document.getElementById('s-revenue');
    var revenueSubEl = document.getElementById('s-revenue-sub');
    var ordersEl = document.getElementById('s-orders');
    var ordersSubEl = document.getElementById('s-orders-sub');
    var aovEl = document.getElementById('s-aov');
    var cancelRateEl = document.getElementById('s-cancel-rate');
    var cancelSubEl = document.getElementById('s-cancel-sub');

    var dayInput = document.getElementById('filter-day');
    var weekInput = document.getElementById('filter-week');
    var monthInput = document.getElementById('filter-month');
    var yearInput = document.getElementById('filter-year');
    var rangeFromInput = document.getElementById('filter-range-from');
    var rangeToInput = document.getElementById('filter-range-to');

    var currentMode = 'all';

    function pad(n) { return n < 10 ? '0' + n : '' + n; }
    function toISO(d) { return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate()); }
    function formatDateLabel(iso) {
      var d = new Date(iso + 'T00:00:00');
      return d.toLocaleDateString('en-PH', { month: 'short', day: 'numeric', year: 'numeric' });
    }

    function setMode(mode) {
      currentMode = mode;
      modeButtons.forEach(function (btn) {
        btn.classList.toggle('active', btn.getAttribute('data-mode') === mode);
      });
      fields.forEach(function (f) {
        f.classList.toggle('is-visible', f.getAttribute('data-for') === mode);
      });
      errorEl.textContent = '';
      if (mode === 'all') applyFilter();
    }

    function getRange() {
      if (currentMode === 'all') return null;

      if (currentMode === 'day') {
        var v = dayInput.value;
        if (!v) return { error: 'Pick a date to filter by.' };
        return { start: v, end: v };
      }

      if (currentMode === 'week') {
        var wv = weekInput.value;
        if (!wv) return { error: 'Pick any date within the week you want to see.' };
        var d = new Date(wv + 'T00:00:00');
        var day = d.getDay();
        var diffToMonday = (day === 0 ? -6 : 1 - day);
        var monday = new Date(d);
        monday.setDate(d.getDate() + diffToMonday);
        var sunday = new Date(monday);
        sunday.setDate(monday.getDate() + 6);
        return { start: toISO(monday), end: toISO(sunday) };
      }

      if (currentMode === 'month') {
        var mv = monthInput.value; // "YYYY-MM"
        if (!mv) return { error: 'Pick a month to filter by.' };
        var parts = mv.split('-');
        var year = parseInt(parts[0], 10), month = parseInt(parts[1], 10);
        var lastDay = new Date(year, month, 0).getDate();
        return { start: mv + '-01', end: mv + '-' + pad(lastDay) };
      }

      if (currentMode === 'year') {
        var yv = yearInput.value;
        if (!yv) return { error: 'Enter a year to filter by.' };
        return { start: yv + '-01-01', end: yv + '-12-31' };
      }

      if (currentMode === 'range') {
        var fromV = rangeFromInput.value;
        var toV = rangeToInput.value;
        if (!fromV || !toV) return { error: 'Pick both a start and end date.' };
        if (fromV > toV) return { error: 'The start date must be before the end date.' };
        return { start: fromV, end: toV };
      }

      return null;
    }

    function applyFilter() {
      var range = getRange();
      if (range && range.error) {
        errorEl.textContent = range.error;
        return;
      }
      errorEl.textContent = '';

      var visibleRows = [];
      rows.forEach(function (row) {
        var date = row.getAttribute('data-date');
        var visible = !range || (date >= range.start && date <= range.end);
        row.style.display = visible ? '' : 'none';
        if (visible) visibleRows.push(row);
      });

      var totalCount = visibleRows.length;
      var delivered = visibleRows.filter(function (r) { return r.getAttribute('data-status') === 'delivered'; });
      var cancelled = visibleRows.filter(function (r) { return r.getAttribute('data-status') === 'cancelled'; });
      var revenue = delivered.reduce(function (sum, r) { return sum + parseFloat(r.getAttribute('data-total') || '0'); }, 0);
      var completedCount = delivered.length;
      var aov = completedCount ? revenue / completedCount : 0;
      var cancelRate = totalCount ? (cancelled.length / totalCount) * 100 : 0;

      revenueEl.textContent = '₱' + revenue.toLocaleString('en-PH');
      revenueSubEl.textContent = 'From ' + completedCount + ' delivered order' + (completedCount === 1 ? '' : 's');
      ordersEl.textContent = completedCount;
      ordersSubEl.textContent = 'Out of ' + totalCount + ' order' + (totalCount === 1 ? '' : 's') + ' placed';
      aovEl.textContent = '₱' + Math.round(aov).toLocaleString('en-PH');
      cancelRateEl.textContent = Math.round(cancelRate) + '%';
      cancelSubEl.textContent = cancelled.length + ' of ' + totalCount + ' orders cancelled';

      countNote.textContent = totalCount + ' order' + (totalCount === 1 ? '' : 's') + (range ? ' in selected range' : ' on record');

      if (range) {
        rangeNote.textContent = 'Showing ' + formatDateLabel(range.start) + ' – ' + formatDateLabel(range.end);
      } else {
        rangeNote.textContent = 'Showing all recorded sales';
      }

      emptyNote.style.display = totalCount === 0 ? 'block' : 'none';
      if (totalCount === 0) {
        emptyNote.textContent = range
          ? 'No orders found in that date range. Try a different date or range.'
          : 'No orders recorded in this browser yet. Log in as a customer and place an order to see sales data here.';
      }
    }

    modeButtons.forEach(function (btn) {
      btn.addEventListener('click', function () { setMode(btn.getAttribute('data-mode')); });
    });

    applyBtn.addEventListener('click', applyFilter);
    resetBtn.addEventListener('click', function () { setMode('all'); });

    setMode('all');
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();

/**
 * Orders page — order details modal (Kaye's Barbeque Admin Panel)
 * --------------------------------------------------------------
 * Clicking (or pressing Enter/Space on) an order row opens a modal
 * with the full order breakdown: customer info, delivery/pickup
 * details, itemized list, and totals.
 *
 * Guarded so it's a no-op on every other admin page (checks for
 * #orders-table-body before doing anything). Reads order detail
 * from a `data-order` JSON attribute on each <tr>.
 */
(function () {
  'use strict';

  function init() {
    var tbody = document.getElementById('orders-table-body');
    if (!tbody) return; // Not on the orders page — nothing to do.

    injectStyles();

    var lastFocused = null;

    var backdrop = document.createElement('div');
    backdrop.id = 'order-modal-backdrop';
    backdrop.className = 'admin-modal-backdrop';
    backdrop.innerHTML =
      '<div class="admin-modal" role="dialog" aria-modal="true" aria-labelledby="order-modal-title">' +
        '<div class="admin-modal-head">' +
          '<div>' +
            '<div class="admin-modal-eyebrow">Order Details</div>' +
            '<h3 id="order-modal-title"></h3>' +
          '</div>' +
          '<button type="button" class="admin-modal-close" aria-label="Close">' +
            '<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><line x1="18" y1="6" x2="6" y2="18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><line x1="6" y1="6" x2="18" y2="18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>' +
          '</button>' +
        '</div>' +
        '<div class="admin-modal-body">' +
          '<div class="admin-modal-status-row">' +
            '<span id="order-modal-status" class="status"></span>' +
            '<span id="order-modal-placed" class="admin-modal-placed"></span>' +
          '</div>' +
          '<div class="admin-modal-section">' +
            '<div class="admin-modal-label">Customer</div>' +
            '<div id="order-modal-customer" class="admin-modal-text"></div>' +
            '<div id="order-modal-phone" class="admin-modal-subtext"></div>' +
          '</div>' +
          '<div class="admin-modal-section">' +
            '<div class="admin-modal-label" id="order-modal-fulfillment-label">Delivery Address</div>' +
            '<div id="order-modal-fulfillment" class="admin-modal-text"></div>' +
          '</div>' +
          '<div class="admin-modal-section">' +
            '<div class="admin-modal-label">Payment Method</div>' +
            '<div id="order-modal-payment" class="admin-modal-text"></div>' +
          '</div>' +
          '<div class="admin-modal-section">' +
            '<div class="admin-modal-label">Items</div>' +
            '<table class="admin-modal-items"><tbody id="order-modal-items"></tbody></table>' +
          '</div>' +
          '<div class="admin-modal-totals" id="order-modal-totals"></div>' +
          '<div class="admin-modal-section" id="order-modal-notes-wrap" style="display:none;">' +
            '<div class="admin-modal-label">Special Instructions</div>' +
            '<div id="order-modal-notes" class="admin-modal-text"></div>' +
          '</div>' +
        '</div>' +
      '</div>';
    document.body.appendChild(backdrop);

    var modal = backdrop.querySelector('.admin-modal');
    var closeBtn = backdrop.querySelector('.admin-modal-close');
    var titleEl = backdrop.querySelector('#order-modal-title');
    var statusEl = backdrop.querySelector('#order-modal-status');
    var placedEl = backdrop.querySelector('#order-modal-placed');
    var customerEl = backdrop.querySelector('#order-modal-customer');
    var phoneEl = backdrop.querySelector('#order-modal-phone');
    var fulfillmentLabelEl = backdrop.querySelector('#order-modal-fulfillment-label');
    var fulfillmentEl = backdrop.querySelector('#order-modal-fulfillment');
    var paymentEl = backdrop.querySelector('#order-modal-payment');
    var itemsEl = backdrop.querySelector('#order-modal-items');
    var totalsEl = backdrop.querySelector('#order-modal-totals');
    var notesWrapEl = backdrop.querySelector('#order-modal-notes-wrap');
    var notesEl = backdrop.querySelector('#order-modal-notes');

    function escapeHtml(str) {
      var div = document.createElement('div');
      div.textContent = str == null ? '' : String(str);
      return div.innerHTML;
    }

    function formatPeso(n) {
      return '₱' + Number(n || 0).toLocaleString('en-PH');
    }

    function statusClass(status) {
      return 'status status-' + String(status || '').toLowerCase().replace(/[^a-z]/g, '');
    }

    function openModal(order) {
      lastFocused = document.activeElement;

      titleEl.textContent = order.id || '';
      statusEl.textContent = order.status || '';
      statusEl.className = statusClass(order.status);
      placedEl.textContent = order.placedAt ? 'Placed ' + order.placedAt : '';

      customerEl.textContent = order.customer || '—';
      phoneEl.textContent = order.phone || '';
      phoneEl.style.display = order.phone ? '' : 'none';

      if (order.type === 'Pickup') {
        fulfillmentLabelEl.textContent = 'Pickup';
        fulfillmentEl.textContent = order.pickupNote || 'Pickup at the shop';
      } else {
        fulfillmentLabelEl.textContent = 'Delivery Address';
        fulfillmentEl.textContent = order.address || '—';
      }

      paymentEl.textContent = order.payment || '—';

      var items = Array.isArray(order.items) ? order.items : [];
      itemsEl.innerHTML = items.map(function (item) {
        var lineTotal = (item.qty || 1) * (item.price || 0);
        return (
          '<tr>' +
            '<td class="admin-modal-item-name">' + escapeHtml(item.name) + '</td>' +
            '<td class="admin-modal-item-qty">×' + (item.qty || 1) + '</td>' +
            '<td class="admin-modal-item-price num">' + formatPeso(lineTotal) + '</td>' +
          '</tr>'
        );
      }).join('');

      var totalsHtml = '<div class="admin-modal-totals-row"><span>Subtotal</span><span>' + formatPeso(order.subtotal) + '</span></div>';
      if (order.deliveryFee) {
        totalsHtml += '<div class="admin-modal-totals-row"><span>Delivery Fee</span><span>' + formatPeso(order.deliveryFee) + '</span></div>';
      }
      totalsHtml += '<div class="admin-modal-totals-row admin-modal-totals-grand"><span>Total</span><span>' + formatPeso(order.total) + '</span></div>';
      totalsEl.innerHTML = totalsHtml;

      if (order.notes) {
        notesWrapEl.style.display = '';
        notesEl.textContent = order.notes;
      } else {
        notesWrapEl.style.display = 'none';
      }

      backdrop.classList.add('open');
      document.body.style.overflow = 'hidden';
      document.addEventListener('keydown', onKeydown, true);
      closeBtn.focus();
    }

    function closeModal() {
      backdrop.classList.remove('open');
      document.body.style.overflow = '';
      document.removeEventListener('keydown', onKeydown, true);
      if (lastFocused && typeof lastFocused.focus === 'function') lastFocused.focus();
    }

    function onKeydown(e) {
      if (e.key === 'Escape') closeModal();
    }

    backdrop.addEventListener('click', function (e) {
      if (e.target === backdrop) closeModal();
    });
    closeBtn.addEventListener('click', closeModal);

    function handleRowActivate(row) {
      var raw = row.getAttribute('data-order');
      if (!raw) return;
      var order;
      try {
        order = JSON.parse(raw);
      } catch (e) {
        return;
      }
      openModal(order);
    }

    tbody.addEventListener('click', function (e) {
      if (e.target.closest('.row-actions')) return; // Let action buttons work as normal.
      var row = e.target.closest('tr[data-order]');
      if (!row) return;
      handleRowActivate(row);
    });

    tbody.addEventListener('keydown', function (e) {
      if (e.key !== 'Enter' && e.key !== ' ') return;
      var row = e.target.closest('tr[data-order]');
      if (!row) return;
      e.preventDefault();
      handleRowActivate(row);
    });

    initRowActions(tbody);
  }

  /* ------------------------------------------------------------ *
   * Row actions — Mark Delivering / Mark Complete / Cancel.
   * Preparing -> Delivering (row stays, buttons swap to Mark
   * Complete). Delivering -> Delivered, or Cancel from either
   * state, removes the row (it now belongs on the Sales page).
   * ------------------------------------------------------------ */
  function initRowActions(tbody) {
    var totalEl = document.getElementById('o-total');
    var pendingEl = document.getElementById('o-pending');
    var deliveringEl = document.getElementById('o-delivering');
    var countNote = document.getElementById('orders-count-note');
    var emptyNote = document.getElementById('orders-empty');
    var notice = document.getElementById('orders-notice');
    var cancelModal = createCancelModal();

    function bumpStat(el, delta) {
      if (!el) return;
      var current = parseInt(el.textContent, 10) || 0;
      el.textContent = Math.max(0, current + delta);
    }

    function showNotice(message) {
      if (!notice) return;
      notice.textContent = message;
      notice.style.display = 'block';
      window.scrollTo({ top: 0, behavior: 'smooth' });
      setTimeout(function () { notice.style.display = 'none'; }, 4000);
    }

    function updateCountNote() {
      if (!countNote) return;
      var remaining = tbody.querySelectorAll('tr[data-order]').length;
      countNote.textContent = 'Showing ' + remaining + ' of ' + remaining + ' orders';
      if (emptyNote) emptyNote.style.display = remaining === 0 ? 'block' : 'none';
    }

    function updateRowOrderStatus(row, status) {
      var raw = row.getAttribute('data-order');
      if (!raw) return;
      try {
        var order = JSON.parse(raw);
        order.status = status;
        row.setAttribute('data-order', JSON.stringify(order));
      } catch (e) { /* leave as-is if it can't be parsed */ }
    }

    function markDelivering(row) {
      var statusCell = row.querySelector('td:nth-last-child(2) .status');
      if (statusCell) {
        statusCell.textContent = 'Delivering';
        statusCell.className = 'status status-delivering';
      }
      updateRowOrderStatus(row, 'Delivering');

      var actionBtn = row.querySelector('[data-action="mark-delivering"]');
      if (actionBtn) {
        actionBtn.textContent = 'Mark Complete';
        actionBtn.setAttribute('data-action', 'mark-complete');
      }

      bumpStat(pendingEl, -1);
      bumpStat(deliveringEl, 1);
      showNotice(row.querySelector('.order-id').textContent + ' marked as Delivering.');
    }

    function markComplete(row) {
      var orderId = row.querySelector('.order-id').textContent;
      bumpStat(deliveringEl, -1);
      bumpStat(totalEl, -1);
      row.remove();
      updateCountNote();
      showNotice(orderId + ' marked as Delivered and moved to Sales.');
    }

    function cancelOrder(row) {
      var orderId = row.querySelector('.order-id').textContent;
      var wasDelivering = !!row.querySelector('.status-delivering');
      bumpStat(wasDelivering ? deliveringEl : pendingEl, -1);
      bumpStat(totalEl, -1);
      row.remove();
      updateCountNote();
      showNotice(orderId + ' has been cancelled.');
    }

    function isOnlinePayment(payment) {
      // "Cash on Delivery" / "Cash on Pickup" are not online payments;
      // anything else (GCash, cards, e-wallets, etc.) is treated as online.
      return !!payment && payment.toLowerCase().indexOf('cash') === -1;
    }

    function requestCancel(row) {
      var orderId = row.querySelector('.order-id').textContent;
      var customer = row.children[1] ? row.children[1].textContent : '';
      var total = row.querySelector('.order-total') ? row.querySelector('.order-total').textContent : '';

      var status = '';
      var payment = '';
      var raw = row.getAttribute('data-order');
      if (raw) {
        try {
          var order = JSON.parse(raw);
          status = order.status || '';
          payment = order.payment || '';
        } catch (e) { /* no order data — skip the payment-based note */ }
      }

      var note = null;
      if (status === 'Delivering') {
        if (isOnlinePayment(payment)) {
          note = {
            type: 'warning',
            text: 'This order is already out for delivery and was paid via ' + payment +
              '. Cancelling now is non-refundable.'
          };
        } else {
          note = {
            type: 'info',
            text: 'This order is already out for delivery. Since it\u2019s ' + (payment || 'Cash on Delivery') +
              ', no payment has been collected yet, so there\u2019s nothing to refund.'
          };
        }
      }

      cancelModal.open({ orderId: orderId, customer: customer, total: total, note: note }, function () {
        cancelOrder(row);
      });
    }

    tbody.addEventListener('click', function (e) {
      var btn = e.target.closest('[data-action]');
      if (!btn) return;
      e.stopPropagation(); // Don't also trigger the row's detail-modal click.

      var row = btn.closest('tr[data-order]');
      if (!row) return;

      var action = btn.getAttribute('data-action');
      if (action === 'mark-delivering') markDelivering(row);
      else if (action === 'mark-complete') markComplete(row);
      else if (action === 'cancel') requestCancel(row);
    });
  }

  /* ------------------------------------------------------------ *
   * Cancel-order confirmation modal. A single instance is built
   * and reused for every cancel click on the Orders page — call
   * .open(details, onConfirm) to show it for a given row.
   * ------------------------------------------------------------ */
  function createCancelModal() {
    var backdrop = document.createElement('div');
    backdrop.id = 'cancel-modal-backdrop';
    backdrop.className = 'admin-modal-backdrop';
    backdrop.innerHTML =
      '<div class="admin-modal admin-modal-sm" role="dialog" aria-modal="true" aria-labelledby="cancel-modal-title">' +
        '<div class="admin-modal-head">' +
          '<div>' +
            '<div class="admin-modal-eyebrow">Cancel Order</div>' +
            '<h3 id="cancel-modal-title"></h3>' +
          '</div>' +
          '<button type="button" class="admin-modal-close" aria-label="Close">' +
            '<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><line x1="18" y1="6" x2="6" y2="18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><line x1="6" y1="6" x2="18" y2="18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>' +
          '</button>' +
        '</div>' +
        '<div class="admin-modal-body">' +
          '<p class="admin-modal-text" id="cancel-modal-message"></p>' +
          '<p class="admin-modal-note" id="cancel-modal-warning" style="display:none;"></p>' +
          '<div class="admin-modal-confirm-actions">' +
            '<button type="button" class="btn btn-outline" id="cancel-modal-keep">Keep Order</button>' +
            '<button type="button" class="btn btn-danger-solid" id="cancel-modal-confirm">Cancel Order</button>' +
          '</div>' +
        '</div>' +
      '</div>';
    document.body.appendChild(backdrop);

    var titleEl = backdrop.querySelector('#cancel-modal-title');
    var messageEl = backdrop.querySelector('#cancel-modal-message');
    var warningEl = backdrop.querySelector('#cancel-modal-warning');
    var closeBtn = backdrop.querySelector('.admin-modal-close');
    var keepBtn = backdrop.querySelector('#cancel-modal-keep');
    var confirmBtn = backdrop.querySelector('#cancel-modal-confirm');

    var lastFocused = null;
    var onConfirm = null;

    function close() {
      backdrop.classList.remove('open');
      document.body.style.overflow = '';
      document.removeEventListener('keydown', onKeydown, true);
      if (lastFocused && typeof lastFocused.focus === 'function') lastFocused.focus();
      onConfirm = null;
    }

    function onKeydown(e) {
      if (e.key === 'Escape') close();
    }

    function open(details, onConfirmCallback) {
      titleEl.textContent = details.orderId || '';
      messageEl.textContent = 'Are you sure you want to cancel ' + (details.orderId || 'this order') +
        (details.customer ? ' for ' + details.customer : '') +
        (details.total ? ' (' + details.total + ')' : '') +
        '? This can\u2019t be undone.';

      if (details.note && details.note.text) {
        warningEl.textContent = details.note.text;
        warningEl.className = 'admin-modal-note ' +
          (details.note.type === 'warning' ? 'admin-modal-note-warning' : 'admin-modal-note-info');
        warningEl.style.display = 'block';
      } else {
        warningEl.style.display = 'none';
      }

      onConfirm = onConfirmCallback;

      lastFocused = document.activeElement;
      backdrop.classList.add('open');
      document.body.style.overflow = 'hidden';
      document.addEventListener('keydown', onKeydown, true);
      keepBtn.focus();
    }

    backdrop.addEventListener('click', function (e) {
      if (e.target === backdrop) close();
    });
    closeBtn.addEventListener('click', close);
    keepBtn.addEventListener('click', close);
    confirmBtn.addEventListener('click', function () {
      var callback = onConfirm;
      close();
      if (callback) callback();
    });

    return { open: open };
  }

  function injectStyles() {
    if (document.getElementById('order-modal-styles')) return;
    var style = document.createElement('style');
    style.id = 'order-modal-styles';
    style.textContent = [
      '#orders-table-body tr[data-order]{cursor:pointer;}',
      '#orders-table-body tr[data-order]:hover{background:rgba(0,0,0,.03);}',
      '#orders-table-body tr[data-order]:focus-visible{outline:2px solid var(--ember,#c9622a);outline-offset:-2px;}',
      '.admin-modal-backdrop{position:fixed;inset:0;background:rgba(24,18,12,.55);display:flex;',
      'align-items:center;justify-content:center;padding:20px;z-index:2000;opacity:0;pointer-events:none;',
      'transition:opacity .15s ease;}',
      '.admin-modal-backdrop.open{opacity:1;pointer-events:auto;}',
      '.admin-modal{background:#fff;border-radius:16px;max-width:540px;width:100%;max-height:86vh;',
      'overflow-y:auto;box-shadow:0 24px 60px rgba(0,0,0,.28);transform:translateY(10px) scale(.98);',
      'transition:transform .15s ease;}',
      '.admin-modal-backdrop.open .admin-modal{transform:translateY(0) scale(1);}',
      '.admin-modal-head{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;',
      'padding:20px 22px 14px;border-bottom:1px solid rgba(0,0,0,.06);position:sticky;top:0;background:#fff;}',
      '.admin-modal-eyebrow{font-family:var(--font-display,inherit);font-size:11px;letter-spacing:.08em;',
      'text-transform:uppercase;color:var(--ember,#c9622a);margin-bottom:2px;}',
      '.admin-modal-head h3{margin:0;font-size:22px;color:#2b241d;}',
      '.admin-modal-close{flex:0 0 auto;width:32px;height:32px;border-radius:50%;border:none;',
      'background:rgba(0,0,0,.05);color:#2b241d;display:flex;align-items:center;justify-content:center;',
      'cursor:pointer;transition:background .15s ease;}',
      '.admin-modal-close:hover{background:rgba(0,0,0,.1);}',
      '.admin-modal-close svg{width:15px;height:15px;}',
      '.admin-modal-body{padding:18px 22px 22px;}',
      '.admin-modal-status-row{display:flex;align-items:center;justify-content:space-between;',
      'flex-wrap:wrap;gap:8px;margin-bottom:18px;}',
      '.admin-modal-placed{font-size:12.5px;color:var(--ink-soft,#6b5c4d);}',
      '.admin-modal-section{margin-bottom:16px;}',
      '.admin-modal-label{font-family:var(--font-display,inherit);font-size:11px;letter-spacing:.06em;',
      'text-transform:uppercase;color:var(--ink-soft,#6b5c4d);margin-bottom:4px;}',
      '.admin-modal-text{font-size:14.5px;color:#2b241d;line-height:1.5;}',
      '.admin-modal-subtext{font-size:13px;color:var(--ink-soft,#6b5c4d);margin-top:2px;}',
      '.admin-modal-items{width:100%;border-collapse:collapse;margin-top:4px;}',
      '.admin-modal-items td{padding:7px 0;font-size:14px;border-bottom:1px solid rgba(0,0,0,.05);}',
      '.admin-modal-items tr:last-child td{border-bottom:none;}',
      '.admin-modal-item-qty{color:var(--ink-soft,#6b5c4d);padding:0 10px;white-space:nowrap;}',
      '.admin-modal-item-price{text-align:right;white-space:nowrap;}',
      '.admin-modal-totals{border-top:1px solid rgba(0,0,0,.08);margin-top:6px;padding-top:10px;}',
      '.admin-modal-totals-row{display:flex;justify-content:space-between;font-size:14px;',
      'color:var(--ink-soft,#6b5c4d);padding:3px 0;}',
      '.admin-modal-totals-grand{font-size:16px;font-weight:700;color:#2b241d;margin-top:4px;}',
      '@media (max-width:480px){.admin-modal-head{padding:16px 16px 12px;}.admin-modal-body{padding:14px 16px 18px;}}',
      '.admin-modal-sm{max-width:400px;}',
      '.admin-modal-confirm-actions{display:flex;justify-content:flex-end;gap:10px;margin-top:20px;}',
      '.admin-modal-note{margin-top:12px;padding:10px 12px;border-radius:10px;font-size:13px;line-height:1.5;}',
      '.admin-modal-note-warning{background:rgba(198, 75, 35, 0.1);border:1px solid rgba(198, 75, 35, 0.25);color:var(--brick,#b23b2e);}',
      '.admin-modal-note-info{background:rgba(0, 0, 0, 0.04);border:1px solid rgba(0, 0, 0, 0.08);color:var(--ink-soft,#6b5c4d);}',
      '.btn-danger-solid{background:var(--brick,#b23b2e);color:#fff8ec;border-color:var(--brick,#b23b2e);}',
      '.btn-danger-solid:hover{background:#9c3226;border-color:#9c3226;}'
    ].join('');
    document.head.appendChild(style);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();

/**
 * --------------------------------------------------------------
 * Menus page -- "Add New Item" modal (Kaye's Barbeque Admin Panel)
 * --------------------------------------------------------------
 * The "Add Item" button in the All Menu Items panel opens a modal
 * containing the add-item form (markup lives inside
 * #add-item-modal-backdrop in menus.php). On submit, a new row is
 * appended to the menu table and the summary stats update.
 *
 * Guarded so it's a no-op on every other admin page (checks for
 * #add-product-form before doing anything). Reuses the shared
 * .admin-modal-* styles injected by the orders-page modal script,
 * injecting its own copy if that script hasn't run on this page.
 */
(function () {
  'use strict';

  function init() {
    var form = document.getElementById('add-product-form');
    if (!form) return; // Not on the menus page -- nothing to do.

    injectStyles();

    var backdrop = document.getElementById('add-item-modal-backdrop');
    var openBtn = document.getElementById('open-add-item-btn');
    var closeBtn = document.getElementById('add-item-modal-close');
    var cancelBtn = document.getElementById('add-item-cancel-btn');
    var errorEl = document.getElementById('add-error');

    var nameInput = document.getElementById('p-name');
    var categoryInput = document.getElementById('p-category');
    var priceInput = document.getElementById('p-price');
    var costInput = document.getElementById('stock');
    var descriptionInput = document.getElementById('p-description');
    var availableInput = document.getElementById('p-available');
    var imageInput = document.getElementById('p-image');
    var imagePreviewWrap = document.getElementById('p-image-preview-wrap');
    var imagePreview = document.getElementById('p-image-preview');
    var imageRemoveBtn = document.getElementById('p-image-remove');
    var currentImageData = null;

    var lastFocused = null;

    function openModal() {
      if (!backdrop) return;
      lastFocused = document.activeElement;
      errorEl.textContent = '';
      backdrop.classList.add('open');
      document.body.style.overflow = 'hidden';
      setTimeout(function () { nameInput.focus(); }, 0);
    }

    function clearImage() {
      currentImageData = null;
      if (imageInput) imageInput.value = '';
      if (imagePreviewWrap) imagePreviewWrap.style.display = 'none';
      if (imagePreview) imagePreview.src = '';
    }

    function closeModal() {
      if (!backdrop) return;
      backdrop.classList.remove('open');
      document.body.style.overflow = '';
      form.reset();
      availableInput.checked = true;
      clearImage();
      errorEl.textContent = '';
      if (lastFocused && typeof lastFocused.focus === 'function') lastFocused.focus();
    }

    function onKeydown(e) {
      if (e.key === 'Escape' && backdrop.classList.contains('open')) closeModal();
    }

    if (imageInput) {
      imageInput.addEventListener('change', function () {
        var file = imageInput.files && imageInput.files[0];
        if (!file) { clearImage(); return; }

        if (!/^image\//.test(file.type)) {
          errorEl.textContent = 'Please choose an image file (JPG, PNG, etc).';
          imageInput.value = '';
          return;
        }
        var MAX_BYTES = 2 * 1024 * 1024; // 2MB — data URLs get large fast
        if (file.size > MAX_BYTES) {
          errorEl.textContent = 'Image is too large — please use one under 2MB.';
          imageInput.value = '';
          return;
        }

        errorEl.textContent = '';
        var reader = new FileReader();
        reader.onload = function (e) {
          currentImageData = e.target.result;
          imagePreview.src = currentImageData;
          imagePreviewWrap.style.display = 'block';
        };
        reader.onerror = function () {
          errorEl.textContent = 'Could not read that image — please try another file.';
          clearImage();
        };
        reader.readAsDataURL(file);
      });
    }

    if (imageRemoveBtn) {
      imageRemoveBtn.addEventListener('click', clearImage);
    }

    if (openBtn) openBtn.addEventListener('click', openModal);
    if (closeBtn) closeBtn.addEventListener('click', closeModal);
    if (cancelBtn) cancelBtn.addEventListener('click', closeModal);
    if (backdrop) {
      backdrop.addEventListener('click', function (e) {
        if (e.target === backdrop) closeModal();
      });
    }
    document.addEventListener('keydown', onKeydown);

    form.addEventListener('submit', function (e) {
      errorEl.textContent = '';

      var name = nameInput.value.trim();
      var category = categoryInput.value.trim();
      var price = parseFloat(priceInput.value);
      var stock = costInput.value.trim() === '' ? NaN : parseInt(costInput.value, 10);
      var description = descriptionInput.value.trim();
      var hasImage = !!(imageInput && imageInput.files && imageInput.files.length);

      if (!name || !category) {
        e.preventDefault();
        errorEl.textContent = 'Item name and category are required.';
        return;
      }
      if (isNaN(price) || price < 0) {
        e.preventDefault();
        errorEl.textContent = 'Enter a valid price.';
        return;
      }
      if (isNaN(stock) || stock < 0) {
        e.preventDefault();
        errorEl.textContent = 'Enter a valid stock amount.';
        return;
      }
      if (!description) {
        e.preventDefault();
        errorEl.textContent = 'Description is required.';
        return;
      }
      if (!hasImage) {
        e.preventDefault();
        errorEl.textContent = 'Please choose a photo.';
        return;
      }

      // Validation passed — let the form submit for real to
      // BarbequeController::store(), which redirects back to
      // /admin/menus with the newly saved item included.
    });
  }

  function injectStyles() {
    if (document.getElementById('order-modal-styles') || document.getElementById('menu-modal-styles')) return;
    var style = document.createElement('style');
    style.id = 'menu-modal-styles';
    style.textContent = [
      '.admin-modal-backdrop{position:fixed;inset:0;background:rgba(24,18,12,.55);display:flex;',
      'align-items:center;justify-content:center;padding:20px;z-index:2000;opacity:0;pointer-events:none;',
      'transition:opacity .15s ease;}',
      '.admin-modal-backdrop.open{opacity:1;pointer-events:auto;}',
      '.admin-modal{background:#fff;border-radius:16px;max-width:540px;width:100%;max-height:86vh;',
      'overflow-y:auto;box-shadow:0 24px 60px rgba(0,0,0,.28);transform:translateY(10px) scale(.98);',
      'transition:transform .15s ease;}',
      '.admin-modal-backdrop.open .admin-modal{transform:translateY(0) scale(1);}',
      '.admin-modal-head{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;',
      'padding:20px 22px 14px;border-bottom:1px solid rgba(0,0,0,.06);position:sticky;top:0;background:#fff;}',
      '.admin-modal-eyebrow{font-family:var(--font-display,inherit);font-size:11px;letter-spacing:.08em;',
      'text-transform:uppercase;color:var(--ember,#c9622a);margin-bottom:2px;}',
      '.admin-modal-head h3{margin:0;font-size:22px;color:#2b241d;}',
      '.admin-modal-close{flex:0 0 auto;width:32px;height:32px;border-radius:50%;border:none;',
      'background:rgba(0,0,0,.05);color:#2b241d;display:flex;align-items:center;justify-content:center;',
      'cursor:pointer;transition:background .15s ease;}',
      '.admin-modal-close:hover{background:rgba(0,0,0,.1);}',
      '.admin-modal-close svg{width:15px;height:15px;}',
      '.admin-modal-body{padding:18px 22px 22px;}',
      '@media (max-width:480px){.admin-modal-head{padding:16px 16px 12px;}.admin-modal-body{padding:14px 16px 18px;}}',
      '.menu-item-cell{display:flex;align-items:center;gap:10px;}',
      '.menu-item-thumb{width:36px;height:36px;border-radius:8px;object-fit:cover;flex:0 0 auto;',
      'border:1px solid rgba(0,0,0,.08);}',
      '.menu-item-thumb-empty{background:rgba(0,0,0,.05);}',
      '.btn-danger-link{color:var(--brick,#b23b2e);}',
      '.btn-danger-link:hover{background:rgba(178,59,46,.08);}'
    ].join('');
    document.head.appendChild(style);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
/**
 * --------------------------------------------------------------
 * Menus page -- "All Menu Items" / "Price History" tabs
 * --------------------------------------------------------------
 * Guarded so it's a no-op on every other admin page (checks for
 * .tab-btn[data-tab-target] before doing anything).
 */
(function () {
  'use strict';

  function init() {
    var tabButtons = document.querySelectorAll('.tab-btn[data-tab-target]');
    if (!tabButtons.length) return;

    var panels = document.querySelectorAll('.tab-content');

    function activate(targetId) {
      tabButtons.forEach(function (btn) {
        var isActive = btn.getAttribute('data-tab-target') === targetId;
        btn.classList.toggle('active', isActive);
        btn.setAttribute('aria-selected', isActive ? 'true' : 'false');
      });
      panels.forEach(function (panel) {
        panel.hidden = panel.id !== targetId;
      });
    }

    tabButtons.forEach(function (btn) {
      btn.addEventListener('click', function () {
        activate(btn.getAttribute('data-tab-target'));
      });
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();