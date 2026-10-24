<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Kaye's Barbeque</title>
<link rel="stylesheet" href="<?=base_url('css/customer_css/styles.css')?>">
<link rel="stylesheet" href="<?=base_url('css/customer_css/customer_common.css')?>">
<link rel="stylesheet" href="<?=base_url('css/customer_css/profile.css')?>">
<link rel="stylesheet" href="<?=base_url('css/customer_css/password_toggle.css')?>">
</head>
<body>

<?php $active = 'profile'; include __DIR__ . '/components/header.php'; ?>

<section class="section-flush-bottom">
  <div class="container">
    <div class="eyebrow eyebrow-ember">Account</div>
    <h1 class="page-title">My Profile</h1>
    <p class="page-intro">Manage your contact details and delivery address, and see a quick snapshot of your order history.</p>
  </div>
</section>

<section>
  <div class="container">

    <div class="profile-layout">

      <div class="profile-summary-card">
        <div class="profile-avatar">
          <svg viewBox="0 0 24 24" width="34" height="34" fill="none" xmlns="http://www.w3.org/2000/svg">
            <circle cx="12" cy="8" r="4" stroke="var(--cream)" stroke-width="1.6"/>
            <path d="M4 20c1.6-4.6 5-7 8-7s6.4 2.4 8 7" stroke="var(--cream)" stroke-width="1.6" stroke-linecap="round"/>
          </svg>
        </div>
        <div class="profile-summary-name" id="profile-name"><?= esc( $user['FIRST_NAME'] . ' ' . $user['LAST_NAME'] ) ?></div>
        <div class="profile-summary-email" id="profile-email"><?= esc( $user['EMAIL'] ) ?></div>
        <div class="profile-summary-meta">Member since Jan 2026</div>

        <div class="profile-stats">
          <div class="profile-stat">
            <div class="profile-stat-value">18</div>
            <div class="profile-stat-label">Orders</div>
          </div>
          <div class="profile-stat">
            <div class="profile-stat-value">₱6,120</div>
            <div class="profile-stat-label">Total Spent</div>
          </div>
          <div class="profile-stat">
            <div class="profile-stat-value">Pork BBQ</div>
            <div class="profile-stat-label">Most Ordered</div>
          </div>
        </div>

        <a href="<?=site_url('/customer/history')?>" class="btn btn-outline profile-history-btn">View Order History</a>
      </div>

      <div class="form-card">
        <fieldset>
          <legend>Contact Details</legend>

          <div class="field-row">
            <div class="field">
              <label for="profile-first-name">First Name</label>
              <input type="text" id="profile-first-name" value="<?= esc( $user['FIRST_NAME'] ) ?>">
            </div>
            <div class="field">
              <label for="profile-last-name">Last Name</label>
              <input type="text" id="profile-last-name" value="<?= esc( $user['LAST_NAME'] ) ?>">
            </div>
          </div>

          <div class="field">
            <label for="profile-email-input">Email Address</label>
            <input type="email" id="profile-email-input" value="<?= esc( $user['EMAIL'] ) ?>">
          </div>

          <div class="field">
            <label for="profile-phone">Phone Number</label>
            <input type="tel" id="profile-phone" value="<?= esc( $user['PHONE_NO'] ) ?>">
          </div>
        </fieldset>

        <fieldset class="fieldset-last">
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

        <div class="profile-actions">
          <button type="button" class="btn btn-outline-light" id="profile-cancel-btn">Cancel</button>
          <button type="button" class="btn btn-primary" id="profile-save-btn">Save Changes</button>
        </div>
      </div>

    </div>

  </div>
</section>

<?php include __DIR__ . '/components/footer.php'; ?>

<?php $extra_scripts = ['js/customer/password_toggle.js']; include __DIR__ . '/components/scripts.php'; ?>

</body>
</html>