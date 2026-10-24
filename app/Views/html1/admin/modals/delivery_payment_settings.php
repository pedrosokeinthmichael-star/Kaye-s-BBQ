<?php
/**
 * Delivery & Payment Settings block for the Orders page.
 * Included from orders.php. This is the content that used to live on the
 * standalone Settings page (delivery_payment.php): barangays + delivery fee
 * history, and the GCash account + archive.
 *
 * Expects the same variables the Settings page received:
 *   $gcash, $barangay_list, $fee_history
 * (all optional -- missing ones simply render as empty).
 */
?>
<style>
  /* -------------------- Settings panel header -------------------- */
  .settings-panel-head-row {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 12px;
    flex-wrap: wrap;
    margin-bottom: 4px;
  }
  .settings-panel-head-left { display: flex; align-items: flex-start; gap: 14px; }
  .panel-icon-badge {
    flex: none;
    width: 40px;
    height: 40px;
    border-radius: 11px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--ember, #c9622a);
    color: #fff;
  }
  .panel-icon-badge svg { width: 20px; height: 20px; }
  .panel-icon-badge.blue { background: #2f7de1; }
  .settings-panel-head-row h2 { margin: 0 0 3px; font-size: 19px; }
  .settings-section-note {
    color: var(--ink-soft, #8a7a68);
    font-size: 13.5px;
    margin: 0;
    max-width: 60ch;
  }

  /* -------------------- Tab groups --------------------
     A "dp-tab-group" bundles one tab bar with the panels it controls, so
     the Delivery Settings tabs and the Payment Settings tabs each manage
     only their own panels rather than every .dp-tab-panel on the page. */
  .dp-tab-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    flex-wrap: wrap;
    margin: 18px 0 16px;
  }
  .dp-tab-bar { display: flex; gap: 8px; flex-wrap: wrap; }
  .dp-tab-btn {
    border: 1px solid var(--line, #e6dcc9);
    background: #fff;
    padding: 8px 16px;
    border-radius: 8px;
    font-family: var(--font-display, inherit);
    font-size: 13px;
    font-weight: 600;
    letter-spacing: .01em;
    color: var(--ink-soft, #8a7a68);
    cursor: pointer;
    transition: background .15s ease, color .15s ease, border-color .15s ease;
  }
  .dp-tab-btn.active {
    background: var(--ember, #c9622a);
    border-color: var(--ember, #c9622a);
    color: #fff;
  }
  .dp-tab-btn:hover:not(.active) { color: var(--ink, #3a2b1e); border-color: var(--ink-soft, #8a7a68); }
  .dp-tab-row-right { display: flex; align-items: center; gap: 14px; flex-wrap: wrap; }
  .dp-inline-toggle { display: flex; align-items: center; gap: 8px; font-size: 13px; color: var(--ink-soft, #8a7a68); }
  .dp-inline-toggle .switch { transform: scale(.85); }

  /* -------------------- Status badge -------------------- */
  .status-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 12.5px;
    font-weight: 600;
    padding: 3px 10px 3px 8px;
    border-radius: 999px;
  }
  .status-badge .status-dot { width: 7px; height: 7px; border-radius: 50%; }
  .status-badge.is-active { background: rgba(43, 158, 90, .12); color: #237a45; }
  .status-badge.is-active .status-dot { background: #2fa860; }
  .status-badge.is-inactive { background: rgba(0, 0, 0, .06); color: var(--ink-soft, #8a7a68); }
  .status-badge.is-inactive .status-dot { background: #a89c8c; }

  /* -------------------- Row-action icon buttons -------------------- */
  .table-icon-btn {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    border: none;
    background: rgba(0, 0, 0, .05);
    color: var(--ink-soft, #6b5c4d);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: background .15s ease, color .15s ease;
  }
  .table-icon-btn svg { width: 13px; height: 13px; }
  .table-icon-btn:hover { background: rgba(0, 0, 0, .1); color: var(--ink, #3a2b1e); }
  .table-icon-btn.danger { color: var(--brick, #b5502f); background: rgba(198, 75, 35, .1); }
  .table-icon-btn.danger:hover { background: rgba(198, 75, 35, .18); }
  .table-actions-cell { display: flex; align-items: center; gap: 8px; }
  .table-update-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    border: 1px solid var(--ember, #c9622a);
    background: rgba(201, 98, 42, .08);
    color: var(--ember, #c9622a);
    border-radius: 7px;
    padding: 6px 12px;
    font-size: 12.5px;
    font-weight: 600;
    cursor: pointer;
    transition: background .15s ease, color .15s ease;
  }
  .table-update-btn svg { width: 13px; height: 13px; }
  .table-update-btn:hover { background: var(--ember, #c9622a); color: #fff; }
  #dp-settings-panels table th.num, #dp-settings-panels table td.num { text-align: left; }

  /* -------------------- GCash tab layout -------------------- */
  .gcash-layout { display: grid; grid-template-columns: 1fr 300px; gap: 20px; align-items: start; }
  .gcash-main-card {
    display: flex;
    gap: 22px;
    align-items: center;
    flex-wrap: wrap;
    padding: 18px;
    border: 1px solid var(--line, #e6dcc9);
    border-radius: 14px;
    background: #fdfaf5;
  }
  .gcash-qr-block { display: flex; flex-direction: column; align-items: center; gap: 8px; flex: none; }
  .qr-preview-lg {
    width: 110px;
    height: 110px;
    border-radius: 12px;
    border: 1px solid rgba(0, 0, 0, .12);
    object-fit: contain;
    background: #fff;
    display: none;
  }
  .qr-caption { font-size: 12px; color: var(--ink-soft, #8a7a68); }
  .gcash-details-block { display: flex; flex-direction: column; gap: 10px; flex: 1 1 200px; }
  .gcash-detail { display: flex; flex-direction: column; gap: 1px; }
  .gcash-detail-label { font-size: 12px; color: var(--ink-soft, #8a7a68); }
  .gcash-detail-value { font-size: 15px; font-weight: 600; color: var(--ink, #3a2b1e); }
  .gcash-side-card { display: flex; flex-direction: column; gap: 12px; }
  .gcash-note {
    display: flex;
    gap: 8px;
    font-size: 12.5px;
    line-height: 1.5;
    color: #1f4f8a;
    background: rgba(47, 125, 225, .08);
    border: 1px solid rgba(47, 125, 225, .18);
    border-radius: 10px;
    padding: 10px 12px;
  }
  .gcash-note svg { flex: none; width: 15px; height: 15px; margin-top: 1px; color: #2f7de1; }
  .gcash-empty-card { flex-direction: column; align-items: center; text-align: center; gap: 14px; padding: 30px 18px; }
  .gcash-empty-body { display: flex; flex-direction: column; align-items: center; gap: 10px; max-width: 360px; }
  .gcash-empty-icon {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    background: rgba(47, 125, 225, .1);
    color: #2f7de1;
    display: flex;
    align-items: center;
    justify-content: center;
  }
  .gcash-empty-icon svg { width: 22px; height: 22px; }
  .gcash-empty-text { font-size: 13px; color: var(--ink-soft, #8a7a68); line-height: 1.5; margin: 0; }
  .btn-dark {
    background: var(--ink, #3a2b1e);
    color: #fff;
    border: none;
    border-radius: 8px;
    padding: 9px 14px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
  }
  .btn-dark:hover { background: #241a10; }

  @media (max-width: 900px) {
    .gcash-layout { grid-template-columns: 1fr; }
  }
</style>

<section id="dp-settings" style="padding-bottom:0;">
  <div class="container">
    <div class="eyebrow" style="color:var(--ember);">Settings</div>
    <h2 style="font-size:30px;margin-bottom:6px;">Delivery &amp; Payment Settings</h2>
    <p style="color:var(--ink-soft);max-width:60ch;">Manage your delivery options and payment methods for customer orders.</p>
    <?php
      // Messages from BarangayController / GcashController redirects (e.g. "can't delete, has past orders").
      $flashError = session()->getFlashdata( 'error' );
      $flashOk    = session()->getFlashdata( 'success' );
      $flashText  = $flashError ?: $flashOk;
    ?>
    <div id="settings-notice" class="notice <?= $flashError ? 'notice-error' : 'notice-success' ?>" style="<?= $flashText ? '' : 'display:none;' ?>margin-top:14px;"><?= esc( (string) $flashText ) ?></div>
  </div>
</section>

<?php
  // Data handed to js/admin/order-settings.js through data-* attributes on
  // #dp-settings-panels (replaces the values that used to be echoed inline).
  $dpBarangays = array_map( function ( $b ) {
      return [
          'id'     => $b['BARANGAY_ID'],
          'name'   => $b['BARANGAY_NAME'],
          'fee'    => (float) $b['DELIVERY_FEE'],
          'active' => in_array( strtolower( (string) ( $b['BARANGAY_STATUS'] ?? '' ) ), [ 'active', '1' ], true ),
      ];
  }, $barangay_list ?? [] );

  $dpFeeHistory = array_map( function ( $h ) {
      return [
          'date'     => substr( $h['UPDATED_AT'], 0, 10 ),
          'barangay' => $h['BARANGAY_NAME'] ?? 'Removed barangay',
          'oldFee'   => (float) ( $h['OLD_FEE'] ?? $h['DELIVERY_FEE'] ?? 0 ),
          'newFee'   => (float) ( $h['NEW_FEE'] ?? $h['ADD_FEE'] ?? 0 ),
      ];
  }, $fee_history ?? [] );

  $dpGcash = ! empty( $gcash ) ? [
      'id'     => $gcash['GCASH_ID'],
      'name'   => $gcash['GCASH_NAME'],
      'number' => $gcash['GCASH_NUMBER'],
      'qr'     => $gcash['GCASH_QR'] ? base_url( 'uploads/' . $gcash['GCASH_QR'] ) : null,
  ] : null;
?>
<section id="dp-settings-panels"
  data-barangays="<?= esc( json_encode( $dpBarangays ), 'attr' ) ?>"
  data-fee-history="<?= esc( json_encode( $dpFeeHistory ), 'attr' ) ?>"
  data-gcash="<?= esc( json_encode( $dpGcash ), 'attr' ) ?>"
  data-barangay-store-url="<?= esc( site_url( 'admin/barangays/store' ), 'attr' ) ?>"
  data-barangay-update-url="<?= esc( site_url( 'admin/barangays/update' ), 'attr' ) ?>"
  data-barangay-delete-url="<?= esc( site_url( 'admin/barangays/delete' ), 'attr' ) ?>"
  data-gcash-store-url="<?= esc( site_url( 'admin/gcash/store' ), 'attr' ) ?>"
  data-gcash-update-url="<?= esc( site_url( 'admin/gcash/update' ), 'attr' ) ?>"
  data-settings-url="<?= esc( site_url( 'admin/settings/update' ), 'attr' ) ?>"
  data-csrf-name="<?= esc( csrf_token(), 'attr' ) ?>"
  data-csrf-hash="<?= esc( csrf_hash(), 'attr' ) ?>">
  <div class="container">

    <!-- ==================== DELIVERY SETTINGS ==================== -->
    <div class="panel dp-tab-group">
      <div class="settings-panel-head-row">
        <div class="settings-panel-head-left">
          <div class="panel-icon-badge">
            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="1" y="8" width="14" height="8" rx="1.4" stroke="currentColor" stroke-width="1.7"/><path d="M15 11h3.5L21 14v2h-6v-5z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><circle cx="6" cy="18.5" r="1.9" stroke="currentColor" stroke-width="1.7"/><circle cx="17.5" cy="18.5" r="1.9" stroke="currentColor" stroke-width="1.7"/></svg>
          </div>
          <div>
            <h2>Delivery Settings</h2>
            <p class="settings-section-note">Manage barangays, delivery fee, and view delivery fee history.</p>
          </div>
        </div>
      </div>

      <div class="dp-tab-row">
        <div class="dp-tab-bar" role="tablist">
          <button type="button" class="dp-tab-btn active" data-dp-tab-target="delivery-areas-tab" aria-selected="true">Barangays</button>
          <button type="button" class="dp-tab-btn" data-dp-tab-target="delivery-fee-history-tab" aria-selected="false">Delivery Fee History</button>
        </div>
        <div class="dp-tab-row-right">
          <label class="dp-inline-toggle">
            <label class="switch">
              <input type="checkbox" id="enable-delivery-toggle" <?= ( $shop_settings['delivery_enabled'] ?? true ) ? 'checked' : '' ?>>
              <span class="switch-track"></span>
            </label>
            Enable Delivery
          </label>
          <button type="button" class="btn btn-primary btn-sm" id="open-add-barangay-modal">
            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><line x1="12" y1="5" x2="12" y2="19" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><line x1="5" y1="12" x2="19" y2="12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
            Add Barangay
          </button>
        </div>
      </div>

      <div class="dp-tab-panel" id="delivery-areas-tab">
        <div class="table-wrap">
          <table>
            <thead>
              <tr>
                <th>#</th>
                <th>Barangay</th>
                <th class="num">Delivery Fee</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody id="delivery-areas-table-body"></tbody>
          </table>
          <p class="empty-note" id="barangay-empty-note" style="display:none;">No barangays added yet — click "Add Barangay" to add the areas you deliver to.</p>
        </div>
      </div>

      <div class="dp-tab-panel" id="delivery-fee-history-tab" hidden>
        <div class="table-wrap">
          <table>
            <thead>
              <tr>
                <th>Date</th>
                <th>Barangay</th>
                <th class="num">Old Fee</th>
                <th class="num">New Fee</th>
              </tr>
            </thead>
            <tbody id="delivery-fee-history-table-body"></tbody>
          </table>
          <p class="empty-note" id="delivery-fee-history-empty-note" style="display:none;">No delivery fee changes recorded yet.</p>
        </div>
      </div>
    </div>

    <div class="grate-divider"></div>

    <!-- ==================== PAYMENT SETTINGS ==================== -->
    <div class="panel dp-tab-group">
      <div class="settings-panel-head-row">
        <div class="settings-panel-head-left">
          <div class="panel-icon-badge blue">
            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="2" y="6" width="20" height="12" rx="2" stroke="currentColor" stroke-width="1.6"/><circle cx="12" cy="12" r="2.6" stroke="currentColor" stroke-width="1.6"/></svg>
          </div>
          <div>
            <h2>Payment Settings</h2>
            <p class="settings-section-note">Manage your GCash payment details and view past GCash settings.</p>
          </div>
        </div>
        <label class="dp-inline-toggle">
          <label class="switch">
            <input type="checkbox" id="enable-online-payments-toggle" <?= ( $shop_settings['gcash_enabled'] ?? true ) ? 'checked' : '' ?>>
            <span class="switch-track"></span>
          </label>
          Enable GCash Payments
        </label>
      </div>

      <div class="dp-tab-row">
        <div class="dp-tab-bar" role="tablist">
          <button type="button" class="dp-tab-btn active" data-dp-tab-target="gcash-tab" aria-selected="true">GCash</button>
          <button type="button" class="dp-tab-btn" data-dp-tab-target="gcash-archive-tab" aria-selected="false">GCash Archive</button>
        </div>
      </div>

      <?php $hasGcash = !empty( $gcash ); ?>
      <div class="dp-tab-panel" id="gcash-tab">
        <div class="gcash-layout">

          <!-- View mode: read-only summary of the current GCash details -->
          <div class="gcash-main-card" id="gcash-view-card" style="<?= $hasGcash ? '' : 'display:none;' ?>">
            <div class="gcash-qr-block">
              <img id="gcash-qr-preview-view" class="qr-preview-lg" src="<?= $hasGcash && $gcash['GCASH_QR'] ? base_url( 'uploads/' . $gcash['GCASH_QR'] ) : '' ?>" style="<?= $hasGcash && $gcash['GCASH_QR'] ? 'display:block;' : '' ?>" alt="GCash QR preview">
              <span class="qr-caption">Scan to pay</span>
            </div>
            <div class="gcash-details-block">
              <div class="gcash-detail">
                <span class="gcash-detail-label">Account Name</span>
                <span class="gcash-detail-value" id="gcash-view-name"><?= $hasGcash ? esc( $gcash['GCASH_NAME'] ) : '—' ?></span>
              </div>
              <div class="gcash-detail">
                <span class="gcash-detail-label">GCash Number</span>
                <span class="gcash-detail-value" id="gcash-view-number"><?= $hasGcash ? esc( $gcash['GCASH_NUMBER'] ) : '—' ?></span>
              </div>
              <span class="status-badge is-active" id="gcash-view-status"><span class="status-dot"></span>Active</span>
            </div>
            <button type="button" class="table-icon-btn" id="gcash-edit-btn" title="Edit GCash details">
              <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4 20h4l10.5-10.5a2.1 2.1 0 0 0 0-3l-1-1a2.1 2.1 0 0 0-3 0L4 16v4z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/></svg>
            </button>
          </div>

          <!-- Empty state: no GCash account saved yet -->
          <div class="gcash-main-card gcash-empty-card" id="gcash-empty-card" style="<?= $hasGcash ? 'display:none;' : '' ?>">
            <div class="gcash-empty-body">
              <div class="gcash-empty-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="2" y="6" width="20" height="12" rx="2" stroke="currentColor" stroke-width="1.6"/><circle cx="12" cy="12" r="2.6" stroke="currentColor" stroke-width="1.6"/></svg>
              </div>
              <p class="gcash-empty-text">No GCash account added yet. Add your GCash name, number and QR code so customers can pay you at checkout.</p>
              <button type="button" class="btn btn-primary btn-sm" id="gcash-add-btn">
                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><line x1="12" y1="5" x2="12" y2="19" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><line x1="5" y1="12" x2="19" y2="12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                Add GCash Details
              </button>
            </div>
          </div>

          <!-- Edit mode: real add/update form, hidden until "Add GCash Details" or the edit pencil is clicked -->
          <div class="gcash-main-card" id="gcash-edit-card" style="display:none;">
            <form id="gcash-form" method="post" enctype="multipart/form-data" action="<?= site_url( 'admin/gcash/store' ) ?>" style="display:flex;gap:22px;align-items:center;flex-wrap:wrap;width:100%;">
              <div class="qr-upload-wrap" style="flex:1 1 100%;">
                <div class="qr-preview-wrap">
                  <img id="gcash-qr-preview" class="qr-preview" src="" alt="GCash QR preview">
                </div>
                <div style="flex:1 1 260px;">
                  <div class="field-row">
                    <div class="field">
                      <label for="gcash-name-input">GCash Account Name</label>
                      <input type="text" id="gcash-name-input" name="gcashName" placeholder="e.g. Kaye D. Santos">
                    </div>
                    <div class="field">
                      <label for="gcash-number-input">GCash Number</label>
                      <input type="text" id="gcash-number-input" name="gcashNumber" placeholder="e.g. 0917 123 4567" inputmode="numeric">
                    </div>
                  </div>
                  <div class="field">
                    <label for="gcash-qr-input">GCash QR Code</label>
                    <input type="file" id="gcash-qr-input" name="gcashQr" accept="image/*">
                    <div style="margin-top:8px;">
                      <button type="button" class="btn btn-ghost btn-sm" id="gcash-qr-remove" style="display:none;">Remove QR</button>
                    </div>
                  </div>
                  <span class="form-error" id="gcash-error" style="display:block;margin-top:4px;"></span>
                  <div style="display:flex;gap:10px;margin-top:14px;">
                    <button type="submit" class="btn btn-primary btn-sm" id="update-gcash-btn">Save Changes</button>
                    <button type="button" class="btn btn-outline btn-sm" id="reset-gcash-btn">Cancel</button>
                  </div>
                </div>
              </div>
            </form>
          </div>

          <div class="gcash-side-card">
            <div class="gcash-note">
              <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.6"/><path d="M12 11v5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/><circle cx="12" cy="8" r="1" fill="currentColor"/></svg>
              <span>QR code, name and number shown to customers during checkout.</span>
            </div>
            <button type="button" class="btn-dark" id="view-gcash-archive-btn">View GCash Archive</button>
          </div>

        </div>
      </div>

      <div class="dp-tab-panel" id="gcash-archive-tab" hidden>
        <div class="settings-subpanel-head">
          <h3>GCash Archive</h3>
        </div>
        <p class="settings-section-note">Previously used GCash accounts, kept here for reference.</p>
        <div class="table-wrap">
          <table>
            <thead>
              <tr>
                <th>QR</th>
                <th>Account Name</th>
                <th>GCash Number</th>
                <th>Archived On</th>
              </tr>
            </thead>
            <tbody id="gcash-archive-table-body"></tbody>
          </table>
          <p class="empty-note" id="gcash-archive-empty-note" style="display:none;">No archived GCash accounts yet — updating the details above will move the previous one here.</p>
        </div>
      </div>
    </div>

    <div style="display:flex;gap:12px;justify-content:flex-end;padding:18px 4px 0;">
      <button type="button" class="btn btn-outline" id="settings-cancel-btn">Cancel</button>
      <button type="button" class="btn btn-primary" id="settings-save-btn">Save Changes</button>
    </div>
  </div>
</section>

<!-- Add/Edit Barangay modal -- same shell/classes as add_item_modal.php -->
<div id="add-barangay-modal-backdrop" class="admin-modal-backdrop" style="display:none;">
  <div class="admin-modal" role="dialog" aria-modal="true" aria-labelledby="add-barangay-modal-title">
    <div class="admin-modal-head">
      <div>
        <div class="admin-modal-eyebrow">Delivery Settings</div>
        <h3 id="add-barangay-modal-title">Add Delivery Area</h3>
      </div>
      <button type="button" class="admin-modal-close" id="add-barangay-modal-close" aria-label="Close">
        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><line x1="18" y1="6" x2="6" y2="18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><line x1="6" y1="6" x2="18" y2="18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
      </button>
    </div>
    <div class="admin-modal-body">
      <form id="add-barangay-form" method="post" action="<?= site_url( 'admin/barangays/store' ) ?>">
        <?= csrf_field() ?>
        <input type="hidden" id="modal-barangay-index" value="">

        <div class="admin-form-section">
          <div class="field">
            <label for="modal-barangay-name">Barangay Name</label>
            <input type="text" id="modal-barangay-name" name="barangayName" placeholder="e.g. Balulang" required>
          </div>
        </div>

        <div class="admin-form-section">
          <div class="field">
            <label for="modal-barangay-fee">Delivery Fee (₱)</label>
            <input type="number" id="modal-barangay-fee" name="barangayFee" min="0" step="1" placeholder="e.g. 50" required>
          </div>
        </div>

        <div class="admin-form-section">
          <div class="switch-wrap">
            <label class="switch">
              <input type="checkbox" id="modal-barangay-active" name="barangayActive" value="1" checked>
              <span class="switch-track"></span>
            </label>
            <span class="switch-caption">Active</span>
          </div>
        </div>

        <div class="admin-form-footer">
          <div class="form-actions" style="margin-top:0;">
            <button type="submit" class="btn btn-primary" id="add-barangay-submit-btn">
              <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><line x1="12" y1="5" x2="12" y2="19" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><line x1="5" y1="12" x2="19" y2="12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
              Add Delivery Area
            </button>
            <button type="button" class="btn btn-outline" id="add-barangay-cancel-btn">Cancel</button>
            <button type="button" class="btn btn-ghost" id="delete-barangay-btn" style="display:none;color:var(--brick);">Delete</button>
          </div>
        </div>
        <span class="form-error" id="add-barangay-error"></span>
      </form>
    </div>
  </div>
</div>

<script src="<?= base_url('js/admin/order_settings.js') ?>"></script>