<?php
/**
 * components/scripts.php — Kaye's Barbeque (customer pages)
 * -----------------------------------------------------------------
 * Sets window.APP_BASE_URL and loads the scripts every customer page
 * needs, then any page-specific ones, in the order given.
 *
 * Set before including (optional):
 *   $extra_scripts  e.g. ['js/customer/item_modal.js']
 *
 * Page-specific inline globals (e.g. window.MIN_ORDER_AMOUNT) should
 * be printed BEFORE this include so they exist when the scripts run.
 */
$extra_scripts = $extra_scripts ?? [];
?>
<script>window.APP_BASE_URL = "<?=base_url()?>";</script>
<script src="<?=base_url('js/customer/customer.js')?>"></script>
<script src="<?=base_url('js/customer/order_notifications.js')?>"></script>
<?php foreach ( $extra_scripts as $script ) : ?>
<script src="<?=base_url($script)?>"></script>
<?php endforeach; ?>