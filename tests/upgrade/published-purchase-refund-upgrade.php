<?php
if ($GLOBALS['wpdb']->prefix !== 'wpi_upgrade_') { throw new RuntimeException('Owned purchase store required'); }
$phase = getenv('WPI_PURCHASE_PHASE');
$option = 'wpi_published_purchase_refund_fixture';
$fixture = get_option($option);
if ($phase === 'prepare') {
    if ($fixture || Hyyan\WPI\Plugin::getVersion() !== '1.5.0') { throw new RuntimeException('Clean published purchase baseline required'); }
    update_option($option, array('baseline' => array()), false);
    putenv('WPI_VARIABLE_PHASE=prepare');
    (function () { require __DIR__ . '/published-variable-upgrade.php'; })();
    return;
}
if (!$fixture) { throw new RuntimeException('Owned purchase fixture missing'); }
$data = get_option('wpi_published_data_fixture');
$variable = get_option('wpi_published_variable_fixture');
if (!$data || !$variable) { throw new RuntimeException('Owned shared purchase fixtures missing'); }
$orders = array();
foreach (wc_get_orders(array('type' => 'shop_order', 'limit' => -1, 'orderby' => 'ID', 'order' => 'ASC')) as $order) {
    $email = $order->get_billing_email();
    if (!in_array($email, array('published-upgrade@example.invalid', 'published-variable@example.invalid'), true)) { continue; }
    $items = $order->get_items();
    $item = reset($items);
    $owned = $email === 'published-upgrade@example.invalid' ? in_array($item->get_product_id(), $data['products'], true) : in_array($item->get_variation_id(), array($variable['children']['en']['large'], $variable['children']['de']['large']), true);
    if (count($items) !== 1 || !$owned) { throw new RuntimeException('Purchase order ownership mismatch'); }
    $orders[$order->get_id()] = $order;
}
if ($phase === 'list') {
    $rows = array();
    foreach ($orders as $order) {
        $items = $order->get_items(); $item = reset($items);
        $rows[] = array('id' => $order->get_id(), 'item' => $item->get_id(), 'type' => $order->get_billing_email() === 'published-upgrade@example.invalid' ? 'simple' : 'variation', 'language' => pll_get_post_language($order->get_id()), 'refund_unit' => (float) $item->get_total() / $item->get_quantity(), 'refunded' => (float) $order->get_total_refunded(), 'url' => admin_url('post.php?post=' . $order->get_id() . '&action=edit'));
    }
    echo wp_json_encode($rows), "\n";
    return;
}
if ($phase === 'cleanup') {
    $refunds = 0;
    foreach ($orders as $order) {
        foreach ($order->get_refunds() as $refund) { $refund->delete(true); ++$refunds; }
        $orderId = $order->get_id();
        $order->delete(true);
        if (wc_get_order($orderId)) { throw new RuntimeException('Owned purchase order remained'); }
    }
    delete_option($option);
    putenv('WPI_VARIABLE_PHASE=cleanup');
    (function () { require __DIR__ . '/published-variable-upgrade.php'; })();
    echo wp_json_encode(array('removed_purchase_orders' => count($orders), 'removed_purchase_refunds' => $refunds)), "\n";
    return;
}
$version = Hyyan\WPI\Plugin::getVersion();
$expected = $phase === 'orders' ? 8 : 4;
if (!in_array($phase, array('capture', 'verify', 'orders'), true) || count($orders) !== $expected || ($phase === 'capture' ? $version !== '1.5.0' : $version !== '1.5.1')) { throw new RuntimeException('Purchase verification phase mismatch'); }
$actual = array(); $languages = array();
foreach ($orders as $id => $order) {
    $simple = $order->get_billing_email() === 'published-upgrade@example.invalid';
    $items = $order->get_items(); $item = reset($items);
    $language = pll_get_post_language($id);
    $total = $simple ? 32 : 24; $subtotal = $simple ? 38 : 28;
    if (!in_array($language, array('en', 'de'), true) || $order->get_customer_id() !== 0 || $order->get_status() !== 'on-hold' || $order->get_payment_method() !== 'cheque' || $item->get_quantity() !== 2 || (float) $item->get_subtotal() !== (float) $subtotal || (float) $item->get_total() !== (float) $total || (float) $order->get_total() !== (float) $total || (float) $order->get_discount_total() !== (float) ($subtotal - $total) || $order->get_used_coupons() !== array($simple ? 'published-data-upgrade' : 'published-variable-upgrade')) { throw new RuntimeException('Native purchase result mismatch'); }
    if (!$simple && $item->get_meta('size') !== 'large') { throw new RuntimeException('Selected variation attribute changed'); }
    $refunds = $order->get_refunds();
    if (count($refunds) !== 1 || (float) $order->get_total_refunded() !== $total / 2.0) { throw new RuntimeException('Native partial refund mismatch'); }
    $refund = reset($refunds); $lines = $refund->get_items(); $line = reset($lines);
    if (count($lines) !== 1 || $line->get_quantity() !== -1 || (float) $line->get_total() !== -$total / 2.0 || (int) $line->get_meta('_refunded_item_id') !== $item->get_id()) { throw new RuntimeException('Native refund line mismatch'); }
    $meta = get_post_meta($id); unset($meta['_edit_lock']);
    $actual[$id] = array('post' => (array) get_post($id), 'meta' => $meta, 'language' => $language, 'order' => json_decode(wp_json_encode($order->get_data()), true), 'items' => array_map(static function ($entry) { return json_decode(wp_json_encode($entry->get_data()), true); }, $items), 'refund' => array('post' => (array) get_post($refund->get_id()), 'meta' => get_post_meta($refund->get_id()), 'data' => json_decode(wp_json_encode($refund->get_data()), true), 'items' => array_map(static function ($entry) { return json_decode(wp_json_encode($entry->get_data()), true); }, $lines)));
    $languages[] = ($simple ? 'simple:' : 'variation:') . $language;
}
$sequence = array('simple:en', 'simple:de', 'variation:en', 'variation:de');
if ($languages !== ($expected === 4 ? $sequence : array_merge($sequence, $sequence))) { throw new RuntimeException('Purchase language sequence changed'); }
foreach ($data['products'] as $id) { if (wc_get_product($id)->get_stock_quantity('edit') !== 17 - intdiv($expected, 2)) { throw new RuntimeException('Shared simple stock mismatch'); } }
foreach ($variable['children'] as $children) {
    if (wc_get_product($children['large'])->get_stock_quantity('edit') !== 20 - intdiv($expected, 2) || wc_get_product($children['small'])->get_stock_quantity('edit') !== 20) { throw new RuntimeException('Selected and sibling variation stock mismatch'); }
}
foreach (array($data['coupon'], $variable['coupon']) as $id) { if ((new WC_Coupon($id))->get_usage_count() !== intdiv($expected, 2)) { throw new RuntimeException('Purchase coupon usage mismatch'); } }
if ($phase === 'capture') { $fixture['baseline'] = $actual; update_option($option, $fixture, false); }
else { foreach ($fixture['baseline'] as $id => $before) { if (!isset($actual[$id]) || $actual[$id] !== $before) { throw new RuntimeException('Published purchase or refund changed after update'); } } }
if ($phase !== 'orders') {
    putenv('WPI_VARIABLE_PHASE=' . $phase);
    (function () { require __DIR__ . '/published-variable-upgrade.php'; })();
}
echo wp_json_encode(array('version' => $version, 'native_purchases' => $expected, 'partial_refunds' => $expected, 'simple_stock_each' => 17 - intdiv($expected, 2), 'large_stock_each' => 20 - intdiv($expected, 2), 'small_stock_each' => 20, 'published_orders_and_refunds_retained' => $phase !== 'capture')), "\n";
