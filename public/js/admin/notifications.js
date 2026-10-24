/**
 * notifications.js — Admin Notification Bell (Kaye's Barbeque Admin Panel)
 * --------------------------------------------------------------
 * Notifications now live in the database (table `notification`) so a
 * customer's action (placing an order) reaches the admin's browser.
 * The bell polls GET /admin/notifications every 15s and marks items
 * read through /admin/notifications/read/(id) and /read-all.
 *
 * Add before </body> on each admin page (unchanged):
 *   <script src="<?= base_url('js/admin/notifications.js') ?>"></script>
 */

(function () {
  'use strict';

  var POLL_MS = 15000;

  // Works with or without index.php in the URL:
  // /admin/orders  ->  /admin/notifications
  var API = window.location.pathname.replace(/\/admin(\/.*)?$/, '/admin/notifications');

  function api(path) {
    return fetch(API + path, {
      credentials: 'same-origin',
      keepalive: true,
      headers: { 'Accept': 'application/json' }
    }).then(function (r) {
      if (!r.ok) throw new Error('HTTP ' + r.status);
      return r.json();
    });
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

    var notifications = [];

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

    navActions.insertBefore(wrap, navActions.firstChild);

    var btn = wrap.querySelector('#admin-notif-btn');
    var panel = wrap.querySelector('#admin-notif-panel');
    var badge = wrap.querySelector('#admin-notif-badge');
    var list = wrap.querySelector('#admin-notif-list');
    var markAllBtn = wrap.querySelector('#admin-notif-mark');

    function escapeHtml(str) {
      var div = document.createElement('div');
      div.textContent = String(str == null ? '' : str);
      return div.innerHTML;
    }

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

    function refresh() {
      if (document.hidden) return;
      api('').then(function (data) {
        if (data && Array.isArray(data.notifications)) {
          notifications = data.notifications;
          render();
        }
      }).catch(function () { /* offline / session expired — try again next tick */ });
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

    function onOutsideClick(e) { if (!wrap.contains(e.target)) closePanel(); }
    function onEscape(e) { if (e.key === 'Escape') closePanel(); }

    btn.addEventListener('click', function (e) {
      e.stopPropagation();
      if (panel.classList.contains('open')) closePanel(); else { openPanel(); refresh(); }
    });

    markAllBtn.addEventListener('click', function (e) {
      e.stopPropagation();
      notifications.forEach(function (n) { n.read = true; });
      render();
      api('/read-all').catch(function () {});
    });

    list.addEventListener('click', function (e) {
      var item = e.target.closest('.admin-notif-item');
      if (!item) return;
      var id = item.getAttribute('data-id');
      var n = notifications.find(function (x) { return String(x.id) === id; });
      if (!n) return;
      if (!n.read) {
        n.read = true;
        render();
        api('/read/' + encodeURIComponent(id)).catch(function () {});
      }
      var href = resolveHref(n.href);
      if (!href) return;
      // Already on that page (e.g. Orders)? Reload so the new order shows up.
      if (href.split('?')[0] === window.location.pathname) window.location.reload();
      else window.location.href = href;
    });

    refresh();
    setInterval(refresh, POLL_MS);
    document.addEventListener('visibilitychange', function () { if (!document.hidden) refresh(); });

    window.AdminNotifications = {
      refresh: refresh,
      markAllRead: function () { markAllBtn.click(); },
      list: function () { return notifications.slice(); }
    };
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();