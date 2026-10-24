<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Menu Management — Kaye's Barbeque Admin</title>
<link rel="stylesheet" href="<?=base_url('css/admin_css/base.css')?>">
<link rel="stylesheet" href="<?=base_url('css/admin_css/header.css')?>">
<link rel="stylesheet" href="<?=base_url('css/admin_css/footer.css')?>">
<link rel="stylesheet" href="<?=base_url('css/admin_css/buttons.css')?>">
<link rel="stylesheet" href="<?=base_url('css/admin_css/forms.css')?>">
<link rel="stylesheet" href="<?=base_url('css/admin_css/cards.css')?>">
<link rel="stylesheet" href="<?=base_url('css/admin_css/tables.css')?>">
<link rel="stylesheet" href="<?=base_url('css/admin_css/notices.css')?>">
<link rel="stylesheet" href="<?=base_url('css/admin_css/modal.css')?>">
</head>
<body>

<header class="site-header">
  <nav class="nav">
    <a href="<?= site_url('/admin/home') ?>" class="brand">
      <img class="brand-mark brand-logo" src="<?=base_url('babeques/LOGO_KBBQpng.png')?>" alt="Kaye's BBQ logo" width="80" height="80" style="width:80px;height:80px;object-fit:contain;">
    </a>


  <ul class="nav-links">
      <li><a href="<?= site_url('/admin/home') ?>" >Home</a></li>
      <li><a href="<?= site_url('/admin/menus') ?>" class="active">Menus</a></li>
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

<section >
  <div class="container">
    <div class="eyebrow" style="color:var(--ember);">Admin Panel</div>
    <h1 style="font-size:38px;margin-bottom:6px;">Menu Management</h1>
    <p style="color:var(--ink-soft);max-width:60ch;">Add new items, and update price or availability for anything already on the menu. Changes are saved right in this browser.</p>
  </div>
</section>

<section>
  <div class="container">
    <div class="highlight-grid">
      <div class="highlight-card">
        <div class="highlight-icon top">
          <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4 6h16M4 12h16M4 18h10" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
        </div>
        <?php
          $barbeque_list = $barbeque_list ?? [];
          $totalCount = count( $barbeque_list );
          $availableCount = count( array_filter( $barbeque_list, fn( $item ) => ( $item['STATUS'] ?? '' ) === 'Available' ) );
          $unavailableCount = $totalCount - $availableCount;
          // History rows don't carry the item photo, so look it up from the
          // live menu list (by MENU_ID when present, otherwise by name).
          // Deleted items simply won't be found and get a placeholder.
          $imageById = [];
          $imageByName = [];
          $stockById = [];
          $stockByName = [];
          foreach ( $barbeque_list as $m ) {
            if ( isset( $m['MENU_ID'] ) ) $stockById[ $m['MENU_ID'] ] = (int) ( $m['STOCK'] ?? 0 );
            if ( isset( $m['NAME'] ) )    $stockByName[ $m['NAME'] ]  = (int) ( $m['STOCK'] ?? 0 );
            if ( empty( $m['IMAGE'] ) ) continue;
            if ( isset( $m['MENU_ID'] ) ) $imageById[ $m['MENU_ID'] ] = $m['IMAGE'];
            if ( isset( $m['NAME'] ) )    $imageByName[ $m['NAME'] ] = $m['IMAGE'];
          }
          $historyImage = function ( array $row ) use ( $imageById, $imageByName ) {
            $file = $row['IMAGE']
              ?? ( isset( $row['MENU_ID'] ) ? ( $imageById[ $row['MENU_ID'] ] ?? null ) : null )
              ?? ( $imageByName[ $row['ITEM_NAME'] ?? '' ] ?? null );
            return ! empty( $file ) ? base_url( 'uploads/' . $file ) : null;
          };
          // Current total stock of the menu item (live value from the menu
          // list). Deleted items aren't in the list, so fall back to the
          // stock value recorded on the history row.
          $currentStock = function ( array $row ) use ( $stockById, $stockByName ) {
            if ( isset( $row['MENU_ID'] ) && isset( $stockById[ $row['MENU_ID'] ] ) ) return $stockById[ $row['MENU_ID'] ];
            if ( isset( $stockByName[ $row['ITEM_NAME'] ?? '' ] ) ) return $stockByName[ $row['ITEM_NAME'] ];
            return (int) ( $row['STOCK'] ?? 0 );
          };
        ?>
        <div>
          <div class="highlight-label">Total Items</div>
          <div class="highlight-value" id="m-total"><?= esc( $totalCount ) ?></div>
          <div class="highlight-sub">Across all categories</div>
        </div>
      </div>
      <div class="highlight-card">
        <div class="highlight-icon margin">
          <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M20 6L9 17l-5-5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </div>
        <div>
          <div class="highlight-label">Available</div>
          <div class="highlight-value" id="m-available"><?= esc( $availableCount ) ?></div>
          <div class="highlight-sub">Visible to customers now</div>
        </div>
      </div>
      <div class="highlight-card">
        <div class="highlight-icon attention">
          <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><line x1="18" y1="6" x2="6" y2="18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><line x1="6" y1="6" x2="18" y2="18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
        </div>
        <div>
          <div class="highlight-label">Unavailable</div>
          <div class="highlight-value" id="m-unavailable"><?= esc( $unavailableCount ) ?></div>
          <div class="highlight-sub">Hidden from customers</div>
        </div>
      </div>
    </div>
  </div>
</section>

<div class="grate-divider"></div>

<section >
  <div class="container">
    <?php if ( session()->getFlashdata( 'success' ) ) : ?>
      <div id="menu-notice" class="notice notice-success"><?= esc( session()->getFlashdata( 'success' ) ) ?></div>
    <?php else : ?>
      <div id="menu-notice" class="notice notice-success" style="display:none;"></div>
    <?php endif; ?>
  </div>
</section>

<section>
  <div class="container">
    <div class="tabs" role="tablist" aria-label="Menu views">
      <button type="button" class="tab-btn active" data-tab-target="menu-items-panel" role="tab" aria-selected="true">All Menu Items</button>
      <button type="button" class="tab-btn" data-tab-target="price-history-panel" role="tab" aria-selected="false">Price History</button>
      <button type="button" class="tab-btn" data-tab-target="inventory-panel" role="tab" aria-selected="false">Inventory</button>
    </div>

    <div class="panel tab-content" id="menu-items-panel">
      <div class="panel-head">
        <h2>
          <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M6 3h12v18l-3-2-3 2-3-2-3 2V3z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/></svg>
          All Menu Items
        </h2>
        <div style="display:flex;align-items:center;gap:12px;">
          <span class="panel-note">Click Edit to change price, stock, or availability</span>
          <button type="button" class="btn btn-primary btn-sm" id="open-add-item-btn">
            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><line x1="12" y1="5" x2="12" y2="19" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><line x1="5" y1="12" x2="19" y2="12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
            Add Item
          </button>
        </div>
      </div>
      <p class="panel-sub">Everything currently on the menu, grouped by category.</p>
      <div class="table-wrap admin-table-wrap">
        <table>
          <thead>
            <tr>
              <th>Item</th>
              <th>Category</th>
              <th class="num">Price</th>
              <th class="num" title="Current stock on hand — click Edit to update it">Stock</th>
              <th>Availability</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="menu-manage-body">
            <?php foreach ( $barbeque_list as $item ) : ?>
              <?php
                $isAvailable = ( $item['STATUS'] ?? '' ) === 'Available';
                $itemJson = json_encode( [
                  'MENU_ID'       => $item['MENU_ID'] ?? null,
                  'NAME'          => $item['NAME'] ?? '',
                  'MENU_CATEGORY' => $item['MENU_CATEGORY'] ?? '',
                  'PRICE'         => $item['PRICE'] ?? 0,
                  'STOCK'         => $item['STOCK'] ?? 0,
                  'STATUS'        => $item['STATUS'] ?? 'Unavailable',
                  'IMAGE'         => $item['IMAGE'] ?? null,
                  'DESCRIPTION'   => $item['DESCRIPTION'] ?? '',
                ] );
              ?>
              <tr data-item='<?= esc( $itemJson, 'attr' ) ?>'>
                <td class="item-name-cell">
                  <div class="menu-item-cell">
                    <?php if ( ! empty( $item['IMAGE'] ) ) : ?>
                      <img src="<?= base_url( 'uploads/' . $item['IMAGE'] ) ?>" alt="" class="menu-item-thumb">
                    <?php else : ?>
                      <span class="menu-item-thumb menu-item-thumb-empty" aria-hidden="true"></span>
                    <?php endif; ?>
                    <span><?= esc( $item['NAME'] ?? '' ) ?></span>
                  </div>
                </td>
                <td class="item-category-cell"><?= esc( $item['MENU_CATEGORY'] ?? '' ) ?></td>
                <?php
                  $priceValue = (float) ( $item['PRICE'] ?? 0 );
                  $priceDisplay = ( $priceValue == (int) $priceValue ) ? (string) (int) $priceValue : number_format( $priceValue, 2 );
                ?>
                <td class="num item-price-cell">₱<?= esc( $priceDisplay ) ?></td>
                <td class="num">
                  <div class="stock-cell">
                    <span><?= esc( $item['STOCK'] ?? 0 ) ?></span>
                  </div>
                </td>
                <td>
                  <span class="status <?= $isAvailable ? 'status-available' : 'status-unavailable' ?>"><?= $isAvailable ? 'Available' : 'Unavailable' ?></span>
                </td>
                <td>
                  <div class="row-actions">
                    <?php if ( ! $isAvailable ) : ?>
                      <?php /* Restore = mark available again. Posts to the same update route as the
                               Update Price modal, re-sending the current price so nothing else changes. */ ?>
                      <form method="post" action="<?= site_url( 'admin/menus/update/' . ( $item['MENU_ID'] ?? '' ) ) ?>" style="margin:0;">
                        <input type="hidden" name="formAction" value="update_price">
                        <input type="hidden" name="menuPrice" value="<?= esc( $item['PRICE'] ?? 0 ) ?>">
                        <input type="hidden" name="menuAvailable" value="1">
                        <button type="submit" class="btn btn-primary btn-sm" data-action="restore-item">Restore</button>
                      </form>
                    <?php else : ?>
                      <button type="button" class="btn btn-ghost btn-sm" data-action="add-stock" data-id="<?= esc( $item['MENU_ID'] ?? '' ) ?>">Add Stock</button>
                      <button type="button" class="btn btn-ghost btn-sm" data-action="update-price" data-id="<?= esc( $item['MENU_ID'] ?? '' ) ?>">Update Price</button>
                    <?php endif; ?>
                    <button type="button" class="btn btn-primary btn-sm" data-view-item="1" data-id="<?= esc( $item['MENU_ID'] ?? '' ) ?>" data-name="<?= esc( $item['NAME'] ?? '' ) ?>">View</button>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <p id="menu-manage-empty" class="empty-note" style="display:none;">No menu items yet. Click "Add Item" to add your first one.</p>
    </div>

    <div class="panel tab-content" id="price-history-panel" hidden>
      <div class="panel-head">
        <h2>
          <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.6"/><path d="M12 7v5l3.5 2" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
          Price History
        </h2>
        <span class="panel-note">Every price change made to a menu item — click View for details</span>
      </div>
      <p class="panel-sub">A record of old and new prices, most recent change first.</p>
      <?php $price_history_list = $price_history_list ?? []; ?>
      <div class="table-wrap admin-table-wrap">
        <table>
          <thead>
            <tr>
              <th>Menu Item</th>
              <th>Category</th>
              <th class="num">Previous Price</th>
              <th class="num">Updated Price</th>
              <th class="num">Price Difference</th>
              <th>Date Updated</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="price-history-body">
            <?php foreach ( $price_history_list as $row ) : ?>
              <?php
                $oldPrice   = (float) ( $row['OLD_PRICE'] ?? 0 );
                $newPrice   = (float) ( $row['PRICE'] ?? 0 );
                $difference = $newPrice - $oldPrice;
                $diffClass  = $difference > 0 ? 'price-up' : ( $difference < 0 ? 'price-down' : '' );
                $diffSign   = $difference > 0 ? '+' : ( $difference < 0 ? '−' : '' );
                $changedAt  = ! empty( $row['UPDATED_AT'] ) ? date( 'M j, Y g:i A', strtotime( $row['UPDATED_AT'] ) ) : '—';
                $historyJson = json_encode( [
                  'type'      => 'price',
                  'item'      => $row['ITEM_NAME'] ?? 'Deleted item',
                  'category'  => $row['MENU_CATEGORY'] ?? '—',
                  'image'     => $historyImage( $row ),
                  'oldPrice'  => $oldPrice,
                  'newPrice'   => $newPrice,
                  'changedAt' => ! empty( $row['UPDATED_AT'] ) ? date( 'l, F j, Y \· g:i A', strtotime( $row['UPDATED_AT'] ) ) : '—',
                ] );
              ?>
              <tr tabindex="0" role="button" aria-haspopup="dialog" data-menu-id="<?= esc( $row['MENU_ID'] ?? '' ) ?>" data-history='<?= esc( $historyJson, 'attr' ) ?>'>
                <td><?= esc( $row['ITEM_NAME'] ?? 'Deleted item' ) ?></td>
                <td class="item-category-cell"><?= esc( $row['MENU_CATEGORY'] ?? '—' ) ?></td>
                <td class="num">₱<?= esc( number_format( $oldPrice, 2 ) ) ?></td>
                <td class="num">₱<?= esc( number_format( $newPrice, 2 ) ) ?></td>
                <td class="num <?= $diffClass ?>"><?= $diffSign ?>₱<?= esc( number_format( abs( $difference ), 2 ) ) ?></td>
                <td><?= esc( $changedAt ) ?></td>
                <td class="history-actions-cell">
                  <div class="row-actions"><button type="button" class="btn btn-primary btn-sm history-view-btn">View</button></div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <p id="price-history-empty" class="empty-note" style="<?= empty( $price_history_list ) ? '' : 'display:none;' ?>">No price changes recorded yet. Edit an item's price to start tracking history.</p>
    </div>

    <div class="panel tab-content" id="inventory-panel" hidden>
      <div class="panel-head">
        <h2>
          <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M3 7l9-4 9 4-9 4-9-4z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/><path d="M3 7v10l9 4 9-4V7" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/></svg>
          Inventory
        </h2>
        <span class="panel-note">Every stock adjustment made to a menu item — click View for details</span>
      </div>
      <p class="panel-sub">A record of stock added or removed, most recent change first.</p>
      <?php
        $inventory_list = $inventory_list ?? [];

        // Total Stock for each row = the stock right after THAT change, so a
        // new add/deduct never alters older rows. The list is newest-first:
        // the newest row of an item starts from its current stock, and each
        // older row is the next newer row's total minus that row's change.
        $runningStock   = [];
        $resultingStock = [];
        foreach ( $inventory_list as $idx => $histRow ) {
          $key = isset( $histRow['MENU_ID'] ) ? 'id:' . $histRow['MENU_ID'] : 'name:' . ( $histRow['ITEM_NAME'] ?? '' );
          if ( ! isset( $runningStock[ $key ] ) ) {
            $runningStock[ $key ] = $currentStock( $histRow );
          }
          $resultingStock[ $idx ] = $runningStock[ $key ];
          $runningStock[ $key ]  -= (int) ( $histRow['ADDED_STOCK'] ?? 0 );
        }
      ?>
      <div class="table-wrap admin-table-wrap">
        <table>
          <thead>
            <tr>
              <th>Menu Item</th>
              <th>Category</th>
              <th class="num">Stock Added</th>
              <th class="num">Total Stock</th>
              <th>Date Updated</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="inventory-body">
            <?php foreach ( $inventory_list as $idx => $row ) : ?>
              <?php
                $added      = (int) ( $row['ADDED_STOCK'] ?? 0 );
                $totalStock = $resultingStock[ $idx ];
                $diffClass  = $added > 0 ? 'price-up' : ( $added < 0 ? 'price-down' : '' );
                $diffSign   = $added > 0 ? '+' : ( $added < 0 ? '−' : '' );
                $changedAt  = ! empty( $row['UPDATED_AT'] ) ? date( 'M j, Y g:i A', strtotime( $row['UPDATED_AT'] ) ) : '—';
                $historyJson = json_encode( [
                  'type'      => 'inventory',
                  'item'      => $row['ITEM_NAME'] ?? 'Deleted item',
                  'category'  => $row['MENU_CATEGORY'] ?? '—',
                  'image'     => $historyImage( $row ),
                  'added'     => $added,
                  'stock'     => $totalStock,
                  'changedAt' => ! empty( $row['UPDATED_AT'] ) ? date( 'l, F j, Y \· g:i A', strtotime( $row['UPDATED_AT'] ) ) : '—',
                ] );
              ?>
              <tr tabindex="0" role="button" aria-haspopup="dialog" data-menu-id="<?= esc( $row['MENU_ID'] ?? '' ) ?>" data-history='<?= esc( $historyJson, 'attr' ) ?>'>
                <td><?= esc( $row['ITEM_NAME'] ?? 'Deleted item' ) ?></td>
                <td class="item-category-cell"><?= esc( $row['MENU_CATEGORY'] ?? '—' ) ?></td>
                <td class="num <?= $diffClass ?>"><?= $diffSign ?><?= esc( abs( $added ) ) ?></td>
                <td class="num"><?= esc( $totalStock ) ?></td>
                <td><?= esc( $changedAt ) ?></td>
                <td class="history-actions-cell">
                  <div class="row-actions"><button type="button" class="btn btn-primary btn-sm history-view-btn">View</button></div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <p id="inventory-empty" class="empty-note" style="<?= empty( $inventory_list ) ? '' : 'display:none;' ?>">No stock changes recorded yet. Edit an item's stock to start tracking history.</p>
    </div>
  </div>
</section>

<?php /* "View" popup: shows ONE item's full details. menus.js fills it from the
          clicked row's data-item JSON. */ ?>
<style>
  #view-item-modal { position: fixed; inset: 0; background: rgba(27, 21, 18, .6); display: flex; align-items: center; justify-content: center; z-index: 1000; padding: 16px; }
  #view-item-modal[hidden] { display: none; }

  #view-item-modal .view-box {
    display: grid; grid-template-columns: 300px 1fr;
    width: 100%; max-width: 760px; max-height: 90vh; overflow: hidden;
    background: #fff8ec; color: var(--ink);
    border-radius: 10px; border: 1px solid var(--line);
    box-shadow: 0 24px 60px rgba(0, 0, 0, .35);
    animation: view-pop .18s ease-out;
  }
  @keyframes view-pop { from { opacity: 0; transform: translateY(8px) scale(.98); } to { opacity: 1; transform: none; } }

  /* Photo column */
  #view-item-modal .view-media { position: relative; min-height: 340px; background: var(--charcoal); }
  #view-item-modal .view-photo,
  #view-item-modal .view-photo-empty { position: absolute; inset: 0; width: 100%; height: 100%; }
  #view-item-modal .view-photo { object-fit: cover; }
  #view-item-modal .view-photo[hidden], #view-item-modal .view-photo-empty[hidden] { display: none; }
  #view-item-modal .view-photo-empty { display: flex; align-items: center; justify-content: center; color: var(--mustard); opacity: .55; }
  #view-item-modal .view-photo-empty svg { width: 64px; height: 64px; }
  #view-item-modal .view-badge { position: absolute; left: 14px; bottom: 14px; padding: 3px; border-radius: 999px; background: #fff8ec; line-height: 0; }

  /* Details column */
  #view-item-modal .view-body { position: relative; padding: 28px 30px 24px; overflow-y: auto; display: flex; flex-direction: column; min-width: 0; }
  #view-item-modal .view-close {
    position: absolute; top: 14px; right: 14px; width: 34px; height: 34px;
    display: flex; align-items: center; justify-content: center;
    border: 1px solid var(--line); border-radius: 50%; background: transparent; color: var(--ink);
    font-size: 22px; line-height: 1; cursor: pointer; transition: border-color .15s ease, color .15s ease;
  }
  #view-item-modal .view-close:hover { border-color: var(--ember); color: var(--ember); }
  #view-item-modal .view-close:focus-visible { outline: 2px solid var(--ember); outline-offset: 2px; }

  #view-item-modal .view-category { align-self: flex-start; font-family: var(--font-body); font-size: 13px; font-weight: 600; color: var(--brick); background: rgba(110, 42, 28, .09); border-radius: 999px; padding: 3px 12px; margin: 0 40px 12px 0; }
  #view-item-modal .view-body h2 { margin: 0 40px 6px 0; font-size: 34px; line-height: 1.1; overflow-wrap: anywhere; }
  #view-item-modal .view-price { font-family: var(--font-display); font-size: 30px; font-weight: 600; color: var(--ember); line-height: 1.1; }

  #view-item-modal .view-stock { display: flex; align-items: center; gap: 10px; margin: 18px 0 0; padding: 14px 0; border-top: 1px solid var(--line); border-bottom: 1px solid var(--line); }
  #view-item-modal .view-stock-num { font-family: var(--font-display); font-size: 26px; line-height: 1; }
  #view-item-modal .view-stock-text { font-size: 14px; color: var(--ink-soft); }
  #view-item-modal .view-stock-flag { margin-left: auto; font-size: 12.5px; font-weight: 600; padding: 3px 10px; border-radius: 999px; }
  #view-item-modal .view-stock-flag[hidden] { display: none; }
  #view-item-modal .view-stock-flag.low { background: rgba(217, 164, 65, .22); color: #8a6415; }
  #view-item-modal .view-stock-flag.out { background: rgba(198, 75, 35, .15); color: var(--brick); }

  #view-item-modal .view-desc { margin: 16px 0 0; font-size: 15px; line-height: 1.65; color: var(--ink); white-space: pre-wrap; overflow-wrap: anywhere; max-width: 60ch; }
  #view-item-modal .view-desc.is-empty { color: var(--ink-soft); font-style: italic; }
  #view-item-modal .view-foot { margin-top: auto; padding-top: 20px; font-family: var(--font-mono); font-size: 11.5px; color: var(--ink-soft); }

  @media (max-width: 620px) {
    #view-item-modal .view-box { grid-template-columns: 1fr; overflow-y: auto; }
    #view-item-modal .view-media { min-height: 0; height: 210px; }
    #view-item-modal .view-body { overflow: visible; padding: 22px 20px 20px; }
    #view-item-modal .view-body h2 { font-size: 28px; }
  }
  @media (prefers-reduced-motion: reduce) { #view-item-modal .view-box { animation: none; } }
</style>
<div id="view-item-modal" hidden>
  <div class="view-box" role="dialog" aria-modal="true" aria-labelledby="view-item-title">
    <div class="view-media">
      <img class="view-photo" id="view-item-photo" alt="" hidden>
      <div class="view-photo-empty" id="view-item-photo-empty" aria-hidden="true">
        <svg viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M20 3C13 10 10 15 10 21c0 6 4.5 11 10 11s10-5 10-11c0-2.6-1-5-2.5-7 0 3-1.5 5-3 5.5.8-3 .3-7-2-10-1 3-3 5.5-5.5 7.5-1.7 1.3-2.5 3-2.5 5 0 3 2.2 5.5 5.5 5.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
      </div>
      <span class="view-badge"><span class="status status-available" id="view-item-status">Available</span></span>
    </div>

    <div class="view-body">
      <button type="button" class="view-close" id="view-item-close" aria-label="Close">&times;</button>
      <span class="view-category" id="view-item-category">Category</span>
      <h2 id="view-item-title">Item</h2>
      <div class="view-price" id="view-item-price">—</div>

      <div class="view-stock">
        <span class="view-stock-num" id="view-item-stock">0</span>
        <span class="view-stock-text">in stock</span>
        <span class="view-stock-flag" id="view-item-stock-flag" hidden></span>
      </div>

      <p class="view-desc" id="view-item-desc">—</p>
      <div class="view-foot">Item #<span id="view-item-id">—</span></div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/modals/add_item_modal.php'; ?>
<?php include __DIR__ . '/modals/add_stock_modal.php'; ?>
<?php include __DIR__ . '/modals/update_price_modal.php'; ?>
<?php include __DIR__ . '/modals/history_modal.php'; ?>


<footer class="site-footer">
  <div class="container">
    <div class="footer-bottom">
      <span>© 2026 Kaye's Barbeque. All rights reserved.</span>
      <span>Smoked daily, served fresh.</span>
    </div>
  </div>
</footer>




<script src="<?= base_url('js/admin/notifications.js') ?>"></script>
<script src="<?= base_url('js/admin/menus.js') ?>"></script>


</body>
</html>