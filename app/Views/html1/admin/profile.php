<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Profile — Kaye's Barbeque Admin</title>
<link rel="stylesheet" href="<?= base_url('css/admin_css/base.css') ?>">
<link rel="stylesheet" href="<?= base_url('css/admin_css/header.css') ?>">
<link rel="stylesheet" href="<?= base_url('css/admin_css/footer.css') ?>">
<link rel="stylesheet" href="<?= base_url('css/admin_css/buttons.css') ?>">
<link rel="stylesheet" href="<?= base_url('css/admin_css/forms.css') ?>">
<link rel="stylesheet" href="<?= base_url('css/admin_css/cards.css') ?>">
<link rel="stylesheet" href="<?= base_url('css/admin_css/tables.css') ?>">
<link rel="stylesheet" href="<?= base_url('css/admin_css/notices.css') ?>">
<link rel="stylesheet" href="<?= base_url('css/admin_css/modal.css') ?>">
<link rel="stylesheet" href="<?= base_url('css/admin_css/profile.css') ?>">
</head>
<body>


<header class="site-header">
  <nav class="nav">
    <a href="<?= site_url('/admin/home') ?>" class="brand">
      <img class="brand-mark brand-logo" src="<?=base_url('babeques/LOGO_KBBQpng.png')?>" alt="Kaye's BBQ logo" width="80" height="80" style="width:80px;height:80px;object-fit:contain;">
    </a>


  <ul class="nav-links">
      <li><a href="<?= site_url('/admin/home') ?>" >Home</a></li>
      <li><a href="<?= site_url('/admin/menus') ?>" >Menus</a></li>
      <li><a href="<?= site_url('/admin/orders') ?>">Orders</a></li>
      <li><a href="<?= site_url('/admin/sales') ?>">Sales</a></li>
  </ul>

    <div class="nav-actions">
      <a href="<?= site_url('/admin/profile') ?>" class="nav-user" title="View profile">
        <span class="nav-avatar" aria-hidden="true">
          <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="8" r="3.4" stroke="currentColor" stroke-width="1.7"/><path d="M5 19c1.1-3.5 3.9-5.2 7-5.2s5.9 1.7 7 5.2" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </span>
        Hi, <span class="nav-user-name">Kaye</span>
      </a>
      <form action="<?= site_url('/logout') ?>" method="post" style="display:inline;margin:0;">
        <?= csrf_field() ?>
        <button type="submit" class="logout-link" style="appearance:none;-webkit-appearance:none;cursor:pointer;">Logout</button>
      </form>
    </div>
  </nav>
</header>

<section style="padding-bottom:0;">
  <div class="container">
    <div class="eyebrow" style="color:var(--ember);">Admin Panel</div>
    <h1 style="font-size:38px;margin-bottom:6px;">My Profile</h1>
    <p style="color:var(--ink-soft);max-width:60ch;">Manage your account details and password, and see a quick snapshot of your admin activity.</p>
  </div>
</section>

<section style="padding-bottom:0;">
  <div class="container">
    <div id="profile-notice" class="notice notice-success" style="display:none;"></div>
  </div>
</section>

<section>
  <div class="container">

    <div class="profile-layout">

      <div class="profile-summary-card">
        <div class="profile-avatar">
          <svg viewBox="0 0 24 24" width="34" height="34" fill="none" xmlns="http://www.w3.org/2000/svg">
            <circle cx="12" cy="8" r="4" stroke="#fff8ec" stroke-width="1.6"/>
            <path d="M4 20c1.6-4.6 5-7 8-7s6.4 2.4 8 7" stroke="#fff8ec" stroke-width="1.6" stroke-linecap="round"/>
          </svg>
        </div>
        <div class="profile-summary-name" id="profile-name">Kaye</div>
        <div class="profile-summary-email" id="profile-email">kaye@kayesbbq.example</div>
        <div class="profile-summary-meta">Shop Administrator · Since Jan 2026</div>

        <div class="profile-stats">
          <div class="profile-stat">
            <div class="profile-stat-value">47</div>
            <div class="profile-stat-label">Orders</div>
          </div>
          <div class="profile-stat">
            <div class="profile-stat-value">24</div>
            <div class="profile-stat-label">Menu Items</div>
          </div>
          <div class="profile-stat">
            <div class="profile-stat-value">₱18,450</div>
            <div class="profile-stat-label">Revenue</div>
          </div>
        </div>

        <a href="<?= site_url('/admin/orders') ?>" class="btn btn-outline" style="width:100%;text-align:center;margin-top:8px;">View Orders</a>
      </div>

      <div class="form-card">
        <fieldset>
          <legend>Account Details</legend>

          <div class="field">
            <label for="profile-name-input">Full Name</label>
            <input type="text" id="profile-name-input" value="Kaye">
          </div>

          <div class="field">
            <label for="profile-email-input">Email Address</label>
            <input type="email" id="profile-email-input" value="kaye@kayesbbq.example">
          </div>
        </fieldset>

        <fieldset style="margin-bottom:0;">
          <legend>Password</legend>

          <div class="field">
            <label for="profile-current-password">Current Password</label>
            <input type="password" id="profile-current-password" autocomplete="current-password" placeholder="Required to change password">
          </div>

          <div class="field-row">
            <div class="field">
              <label for="profile-new-password">New Password</label>
              <input type="password" id="profile-new-password" autocomplete="new-password" placeholder="Leave blank to keep current password">
            </div>
            <div class="field">
              <label for="profile-confirm-password">Confirm New Password</label>
              <input type="password" id="profile-confirm-password" autocomplete="new-password" placeholder="Re-enter new password">
            </div>
          </div>
        </fieldset>

        <span class="form-error" id="profile-form-error" style="display:block;margin-bottom:8px;"></span>

        <div style="display:flex;gap:12px;justify-content:flex-end;margin-top:8px;">
          <button type="button" class="btn btn-outline" id="profile-cancel-btn">Cancel</button>
          <button type="button" class="btn btn-primary" id="profile-save-btn">Save Changes</button>
        </div>
      </div>

    </div>

  </div>
</section>

<footer class="site-footer">
  <div class="container">
    <div class="footer-bottom">
      <span>© 2026 Kaye's Barbeque. All rights reserved.</span>
      <span>Smoked daily, served fresh.</span>
    </div>
  </div>
</footer>

<script src="<?= base_url('js/admin/profile.js') ?>"></script>

<script src="<?= base_url('js/admin/notifications.js') ?>"></script>

</body>
</html>