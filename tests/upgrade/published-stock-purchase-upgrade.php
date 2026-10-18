<?php
if ($GLOBALS['wpdb']->prefix !== 'wpi_upgrade_') { throw new RuntimeException('Owned stock purchase store required'); }
$phase = getenv('WPI_STOCK_PURCHASE_PHASE');
$option = 'wpi_published_stock_purchase_fixture';
$fixture = get_option($option);
if ($phase === 'prepare') {
    if ($fixture || Hyyan\WPI\Plugin::getVersion() !== '1.5.0') { throw new RuntimeException('Clean published stock purchase baseline required'); }
    $fixture = array('baseline' => array(), 'options' => array());
    foreach (array('woocommerce_cheque_settings', 'woocommerce_enable_guest_checkout') as $key) { $fixture['options'][$key] = get_option($key, null); }
    update_option($option, $fixture, false);
    putenv('WPI_STOCK_MATRIX_PHASE=prepare');
    (function () { require __DIR__ . '/published-stock-matrix-upgrade.php'; })();
    update_option('woocommerce_cheque_settings', array('enabled' => 'yes', 'title' => 'Upgrade cheque', 'description' => 'Offline upgrade acceptance', 'instructions' => 'Offline upgrade acceptance'));
    update_option('woocommerce_enable_guest_checkout', 'yes');
    return;
}
if (!$fixture) { throw new RuntimeException('Owned stock purchase fixture missing'); }
$matrix = get_option('wpi_published_stock_matrix_fixture');
$cart = get_option('wpi_published_cart_fixture');
if (!$matrix || !$cart) { throw new RuntimeException('Owned stock purchase products missing'); }
$orders = array();
foreach (wc_get_orders(array('type' => 'shop_order', 'limit' => -1, 'orderby' => 'ID', 'order' => 'ASC')) as $order) {
    if ($order->get_billing_email() !== 'published-stock-purchase@example.invalid') { continue; }
    $items = $order->get_items();
    if (count($items) !== 1) { throw new RuntimeException('Stock purchase item ownership mismatch'); }
    $item = reset($items); $owned = false;
    foreach ($matrix['products'] as $products) {
        foreach ($products as $ids) { if ($item->get_product_id() === $ids['parent'] && $item->get_variation_id() === $ids['child']) { $owned = true; } }
    }
    if (!$owned) { throw new RuntimeException('Stock purchase product ownership mismatch'); }
    $orders[$order->get_id()] = $order;
}
if ($phase === 'urls') {
    $urls = array();
    foreach ($matrix['products'] as $mode => $products) { foreach ($products as $lang => $ids) { $urls[$mode][$lang] = get_permalink($ids['parent']); } }
    $checkout = array();
    foreach ($cart['pages']['checkout'] as $lang => $id) { $checkout[$lang] = get_permalink($id); }
    echo wp_json_encode(array('products' => $matrix['products'], 'urls' => $urls, 'checkout' => $checkout)), "\n";
    return;
}
if ($phase === 'cleanup') {
    foreach ($orders as $id => $order) {
        if ($order->get_refunds()) { throw new RuntimeException('Unexpected stock purchase refund'); }
        $order->delete(true);
        if (wc_get_order($id)) { throw new RuntimeException('Owned stock purchase order remained'); }
    }
    foreach ($fixture['options'] as $key => $value) { if ($value === null) { delete_option($key); } else { update_option($key, $value); } }
    foreach ($fixture['options'] as $key => $value) { if (get_option($key, null) !== $value) { throw new RuntimeException('Stock purchase option restoration failed'); } }
    delete_option($option);
    putenv('WPI_STOCK_MATRIX_PHASE=cleanup');
    (function () { require __DIR__ . '/published-stock-matrix-upgrade.php'; })();
    echo wp_json_encode(array('removed_stock_purchase_orders' => count($orders), 'checkout_options_restored' => true)), "\n";
    return;
}
$version = Hyyan\WPI\Plugin::getVersion();
$expected = $phase === 'orders' ? 16 : 8;
if (!in_array($phase, array('capture', 'verify', 'orders'), true) || count($orders) !== $expected || ($phase === 'capture' ? $version !== '1.5.0' : $version !== '1.5.1')) { throw new RuntimeException('Stock purchase phase mismatch'); }
$actual = array(); $sequence = array(); $stocks = array();
foreach ($orders as $id => $order) {
    $items = $order->get_items(); $item = reset($items); $mode = null; $lang = null;
    foreach ($matrix['products'] as $kind => $products) { foreach ($products as $language => $ids) { if ($item->get_variation_id() === $ids['child']) { $mode = $kind; $lang = $language; } } }
    $term = get_term(get_option('wpi_published_data_fixture')['terms']['pa_upgrade_colour'][$lang], 'pa_upgrade_colour');
    if (!$term || is_wp_error($term) || pll_get_post_language($id) !== $lang || $order->get_customer_id() !== 0 || $order->get_status() !== 'on-hold' || $order->get_payment_method() !== 'cheque' || $item->get_quantity() !== 1 || (float) $order->get_total() !== 18.0 || (float) $item->get_total() !== 18.0 || $item->get_meta('pa_upgrade_colour') !== $term->slug || $order->get_used_coupons()) { throw new RuntimeException('Native stock purchase mismatch'); }
    $meta = get_post_meta($id); unset($meta['_edit_lock']);
    $actual[$id] = array('post' => (array) get_post($id), 'meta' => $meta, 'language' => $lang, 'order' => json_decode(wp_json_encode($order->get_data()), true), 'items' => array_map(static function ($line) { return json_decode(wp_json_encode($line->get_data()), true); }, $items));
    $sequence[] = $mode . ':' . $lang;
}
$cycle = array('global:en', 'global:de', 'wildcard:en', 'wildcard:de', 'parent:en', 'parent:de', 'backorder:en', 'backorder:de');
if ($sequence !== ($expected === 8 ? $cycle : array_merge($cycle, $cycle))) { throw new RuntimeException('Native stock purchase sequence mismatch'); }
foreach ($matrix['products'] as $mode => $products) {
    foreach ($products as $lang => $ids) {
        $parent = wc_get_product($ids['parent']); $child = wc_get_product($ids['child']);
        $stock = ($mode === 'parent' ? 5 : ($mode === 'backorder' ? 0 : 8)) - intdiv($expected, 4);
        if ($child->get_stock_managed_by_id() !== ($mode === 'parent' ? $ids['parent'] : $ids['child']) || ($mode === 'parent' ? $parent->get_stock_quantity('edit') : $child->get_stock_quantity('edit')) !== $stock || ($mode === 'parent' ? $child->get_stock_quantity('edit') !== null : $parent->get_stock_quantity('edit') !== null)) { throw new RuntimeException('Native stock deduction mismatch ' . $mode . ' ' . $lang); }
        $stocks[$mode][$lang] = $stock;
    }
}
if ($phase === 'capture') { $fixture['baseline'] = $actual; update_option($option, $fixture, false); }
else { foreach ($fixture['baseline'] as $id => $before) { if (!isset($actual[$id]) || $actual[$id] !== $before) { throw new RuntimeException('Published stock purchase changed after update'); } } }
if ($phase !== 'orders') {
    putenv('WPI_STOCK_MATRIX_PHASE=' . $phase);
    (function () { require __DIR__ . '/published-stock-matrix-upgrade.php'; })();
}
echo wp_json_encode(array('version' => $version, 'native_stock_purchases' => $expected, 'quantity_each' => 1, 'total_each' => 18, 'stocks' => $stocks, 'published_orders_retained' => $phase !== 'capture')), "\n";
