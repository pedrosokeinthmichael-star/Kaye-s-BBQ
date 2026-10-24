/**
 * order_notifications.js — Customer Notification Bell (Kaye's Barbeque)
 * --------------------------------------------------------------
 * Shows the logged-in customer's order updates (preparing, out for
 * delivery, delivered, cancelled, payment requested...). Notifications
 * are stored server-side (cache) and fetched from
 *   GET /customer/notifications
 * every 15s. Add before </body> on every customer page:
 *
 *   <script src="<?=base_url('js/customer/order_notifications.js')?>"></script>
 */
(function () {
  'use strict';

  var POLL_MS = 15000;

  // Works with or without index.php in the URL:
  // /customer/orders  ->  /customer/notifications
  var API = window.location.pathname.replace(/\/customer(\/.*)?$/, '/customer/notifications');

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
    if (type === 'order') {
      return '<svg viewBox="0 0 24 24" fill="none"><path d="M6 3h12v18l-3-2-3 2-3-2-3 2V3z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/></svg>';
    }
    return '<svg viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.7"/><path d="M12 7v5l3.5 2" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>';
  }

  function resolveHref(page) {
    if (!page) return null;
    if (/^https?:\/\//i.test(page) || page.indexOf('/') === 0) return page;
    return window.location.pathname.replace(/\/customer(\/.*)?$/, '/customer/') + page;
  }

  function injectStyles() {
    if (document.getElementById('cust-notif-styles')) return;
    var style = document.createElement('style');
    style.id = 'cust-notif-styles';
    style.textContent = [
      '#cust-notif{position:relative;display:inline-flex;align-items:center;margin-right:10px;}',
      '#cust-notif-btn{position:relative;display:inline-flex;align-items:center;justify-content:center;',
      'width:38px;height:38px;padding:0;border-radius:50%;border:1.5px solid var(--mustard,#d9a441);',
      'background:rgba(217,164,65,.14);cursor:pointer;color:var(--mustard,#d9a441);',
      'transition:background .15s ease,color .15s ease,transform .15s ease;}',
      '#cust-notif-btn:hover{background:var(--mustard,#d9a441);color:#1b1512;transform:scale(1.06);}',
      '#cust-notif-btn:focus-visible{outline:2px solid var(--cream,#f1e6d3);outline-offset:2px;}',
      '#cust-notif-btn svg{width:20px;height:20px;transform-origin:50% 12%;}',
      '#cust-notif-btn.has-unread{background:rgba(198,75,35,.22);border-color:var(--ember,#c64b23);color:#ffd9a0;}',
      '#cust-notif-btn.has-unread svg{animation:cust-notif-ring 3s ease-in-out infinite;}',
      '@keyframes cust-notif-ring{0%,55%,100%{transform:rotate(0)}6%{transform:rotate(16deg)}12%{transform:rotate(-14deg)}',
      '18%{transform:rotate(10deg)}24%{transform:rotate(-8deg)}30%{transform:rotate(4deg)}36%{transform:rotate(0)}}',
      '#cust-notif-badge{position:absolute;top:-7px;right:-7px;min-width:19px;height:19px;padding:0 5px;',
      'box-sizing:border-box;border-radius:999px;background:var(--ember,#c64b23);color:#fff;font-size:11px;line-height:19px;',
      'font-weight:700;text-align:center;box-shadow:0 0 0 2px var(--bg-dark,#1b1512);',
      'animation:cust-notif-pulse 1.8s ease-out infinite;}',
      '@keyframes cust-notif-pulse{0%{box-shadow:0 0 0 2px var(--bg-dark,#1b1512),0 0 0 2px rgba(198,75,35,.7)}',
      '70%{box-shadow:0 0 0 2px var(--bg-dark,#1b1512),0 0 0 9px rgba(198,75,35,0)}',
      '100%{box-shadow:0 0 0 2px var(--bg-dark,#1b1512),0 0 0 2px rgba(198,75,35,0)}}',
      '@media (prefers-reduced-motion:reduce){#cust-notif-btn.has-unread svg,#cust-notif-badge{animation:none;}}',
      '#cust-notif-panel{position:absolute;top:calc(100% + 12px);right:0;width:330px;max-width:88vw;',
      'max-height:420px;overflow:hidden;display:flex;flex-direction:column;background:#fff8ec;',
      'border-radius:14px;box-shadow:0 12px 32px rgba(0,0,0,.3);border:1px solid rgba(0,0,0,.08);',
      'opacity:0;transform:translateY(-6px);pointer-events:none;transition:opacity .15s ease,transform .15s ease;z-index:1000;}',
      '#cust-notif-panel.open{opacity:1;transform:translateY(0);pointer-events:auto;}',
      '#cust-notif-head{display:flex;align-items:center;justify-content:space-between;padding:14px 16px;',
      'border-bottom:1px solid rgba(0,0,0,.08);}',
      '#cust-notif-head h3{margin:0;font-size:15px;font-weight:700;color:#2b241d;}',
      '#cust-notif-mark{border:none;background:none;color:var(--ember,#c64b23);font-size:12.5px;',
      'font-weight:600;cursor:pointer;padding:2px 4px;}',
      '#cust-notif-mark:hover{text-decoration:underline;}',
      '#cust-notif-list{overflow-y:auto;flex:1 1 auto;}',
      '.cust-notif-item{display:flex;gap:10px;align-items:flex-start;padding:12px 16px;cursor:pointer;',
      'border-bottom:1px solid rgba(0,0,0,.06);transition:background .12s ease;}',
      '.cust-notif-item:last-child{border-bottom:none;}',
      '.cust-notif-item:hover{background:rgba(0,0,0,.04);}',
      '.cust-notif-item.unread{background:rgba(217,164,65,.14);}',
      '.cust-notif-item.unread:hover{background:rgba(217,164,65,.22);}',
      '.cust-notif-icon{flex:0 0 auto;width:30px;height:30px;border-radius:50%;display:flex;',
      'align-items:center;justify-content:center;background:rgba(0,0,0,.06);color:var(--ember,#c64b23);}',
      '.cust-notif-icon svg{width:15px;height:15px;}',
      '.cust-notif-body{flex:1 1 auto;min-width:0;}',
      '.cust-notif-title{font-size:13.5px;font-weight:700;color:#2b241d;margin-bottom:2px;}',
      '.cust-notif-msg{font-size:12.5px;color:#6b5c4d;line-height:1.4;}',
      '.cust-notif-time{font-size:11px;color:#9c8f80;margin-top:4px;}',
      '.cust-notif-dot{flex:0 0 auto;width:8px;height:8px;border-radius:50%;background:var(--ember,#c64b23);margin-top:6px;}',
      '#cust-notif-empty{padding:40px 16px;text-align:center;color:#9c8f80;font-size:13px;}',
      '@media (max-width:480px){#cust-notif-panel{position:fixed;top:64px;right:8px;left:8px;width:auto;}}'
    ].join('');
    document.head.appendChild(style);
  }

  function init() {
    var navActions = document.querySelector('.nav-actions');
    if (!navActions || document.getElementById('cust-notif')) return;

    injectStyles();

    // profile.php ships a static placeholder bell — replace it with this one.
    var oldBtn = document.getElementById('notif-btn');
    if (oldBtn && oldBtn.parentNode) oldBtn.parentNode.removeChild(oldBtn);

    var notifications = [];

    var wrap = document.createElement('div');
    wrap.id = 'cust-notif';
    wrap.innerHTML =
      '<button type="button" id="cust-notif-btn" aria-haspopup="true" aria-expanded="false" title="Notifications" aria-label="Notifications">' +
        '<svg viewBox="0 0 24 24" fill="none"><path d="M12 3.5c-3 0-5 2.2-5 5.2v3.1c0 .7-.3 1.4-.8 1.9L5 15v1h14v-1l-1.2-1.3c-.5-.5-.8-1.2-.8-1.9V8.7c0-3-2-5.2-5-5.2Z" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/><path d="M9.5 18.5a2.5 2.5 0 0 0 5 0" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>' +
        '<span id="cust-notif-badge" style="display:none;"></span>' +
      '</button>' +
      '<div id="cust-notif-panel" role="menu" aria-label="Notifications">' +
        '<div id="cust-notif-head"><h3>Notifications</h3>' +
        '<button type="button" id="cust-notif-mark">Mark all read</button></div>' +
        '<div id="cust-notif-list"></div>' +
      '</div>';

    // Sit just before the profile link (after the cart button).
    var profileLink = navActions.querySelector('a[href*="/customer/profile"]');
    if (profileLink) navActions.insertBefore(wrap, profileLink);
    else navActions.insertBefore(wrap, navActions.firstChild);

    var btn = wrap.querySelector('#cust-notif-btn');
    var panel = wrap.querySelector('#cust-notif-panel');
    var badge = wrap.querySelector('#cust-notif-badge');
    var list = wrap.querySelector('#cust-notif-list');
    var markAllBtn = wrap.querySelector('#cust-notif-mark');

    function escapeHtml(str) {
      var div = document.createElement('div');
      div.textContent = String(str == null ? '' : str);
      return div.innerHTML;
    }

    function render() {
      var unread = notifications.filter(function (n) { return !n.read; }).length;
      btn.classList.toggle('has-unread', unread > 0);
      if (unread > 0) {
        badge.style.display = 'block';
        badge.textContent = unread > 9 ? '9+' : String(unread);
      } else {
        badge.style.display = 'none';
      }

      if (!notifications.length) {
        list.innerHTML = '<div id="cust-notif-empty">No notifications yet. We\'ll let you know when your order moves along.</div>';
        return;
      }

      list.innerHTML = notifications.slice()
        .sort(function (a, b) { return b.time - a.time; })
        .map(function (n) {
          return (
            '<div class="cust-notif-item' + (n.read ? '' : ' unread') + '" data-id="' + escapeHtml(n.id) + '" role="menuitem">' +
              '<div class="cust-notif-icon">' + iconFor(n.type) + '</div>' +
              '<div class="cust-notif-body">' +
                '<div class="cust-notif-title">' + escapeHtml(n.title) + '</div>' +
                '<div class="cust-notif-msg">' + escapeHtml(n.message) + '</div>' +
                '<div class="cust-notif-time">' + timeAgo(n.time) + '</div>' +
              '</div>' +
              (n.read ? '' : '<div class="cust-notif-dot"></div>') +
            '</div>'
          );
        }).join('');
    }

    function refresh() {
      if (document.hidden) return;
      api('').then(function (data) {
        if (data && Array.isArray(data.notifications)) {
          notifications = data.notifications;
          render();
        }
      }).catch(function () { /* offline / session expired — retry next tick */ });
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
      var item = e.target.closest('.cust-notif-item');
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
      // Already on that page? Reload so the latest order status shows.
      if (href.split('?')[0] === window.location.pathname) window.location.reload();
      else window.location.href = href;
    });

    refresh();
    setInterval(refresh, POLL_MS);
    document.addEventListener('visibilitychange', function () { if (!document.hidden) refresh(); });

    window.CustomerNotifications = {
      refresh: refresh,
      list: function () { return notifications.slice(); }
    };
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
  else init();
})();