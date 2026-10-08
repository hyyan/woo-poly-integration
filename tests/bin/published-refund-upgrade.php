<?php
if ($GLOBALS['wpdb']->prefix !== 'wpi_upgrade_') { throw new RuntimeException('Owned refund upgrade store required'); }
$phase = getenv('WPI_REFUND_PHASE');
if (!in_array($phase, array('prepare', 'create-orders', 'capture', 'verify', 'orders', 'cleanup'), true)) { throw new RuntimeException('Unknown refund phase'); }
$option = 'wpi_published_refund_fixture';
$fixture = get_option($option);
if ($phase === 'prepare') {
    if ($fixture || Hyyan\WPI\Plugin::getVersion() !== '1.5.0') { throw new RuntimeException('Clean published refund baseline required'); }
    $fixture = array('orders' => array());
    update_option($option, $fixture, false);
    putenv('WPI_VARIABLE_PHASE=prepare');
    (function () { require __DIR__ . '/published-variable-upgrade.php'; })();
    echo wp_json_encode(array('refund_fixture_prepared' => true)), "\n";
    return;
}
if (!$fixture) { throw new RuntimeException('Owned refund fixture missing'); }
$data = get_option('wpi_published_data_fixture');
$variable = get_option('wpi_published_variable_fixture');
if (!$data || !$variable) { throw new RuntimeException('Owned shared fixtures missing'); }
if ($phase === 'create-orders') {
    $version = Hyyan\WPI\Plugin::getVersion();
    if (($version !== '1.5.0' && $version !== '1.5.1') || count($fixture['orders']) !== ($version === '1.5.0' ? 0 : 4)) { throw new RuntimeException('Refund order phase mismatch'); }
    $language = PLL()->curlang;
    $result = array();
    try {
        foreach (array('simple' => $data['products'], 'variation' => array('en' => $variable['children']['en']['large'], 'de' => $variable['children']['de']['large'])) as $type => $products) {
            foreach ($products as $lang => $id) {
                PLL()->curlang = PLL()->model->get_language($lang);
                $order = wc_create_order();
                if (is_wp_error($order)) { throw new RuntimeException($order->get_error_message()); }
                $fixture['orders'][] = $order->get_id();
                update_option($option, $fixture, false);
                $order->set_customer_id(0);
                $order->set_billing_first_name('Published');
                $order->set_billing_last_name('Refund');
                $order->set_billing_email('published-refund@example.invalid');
                $order->set_payment_method('cheque');
                $order->add_product(wc_get_product($id), 2);
                $order->calculate_totals();
                $order->save();
                do_action('woocommerce_checkout_update_order_meta', $order->get_id());
                wc_reduce_stock_levels($order->get_id());
                $order->update_status('on-hold');
                $items = $order->get_items();
                $item = reset($items);
                $result[] = array('order' => $order->get_id(), 'item' => $item->get_id(), 'type' => $type, 'language' => $lang, 'quantity' => 2, 'total' => $order->get_total(), 'refund_unit' => $type === 'simple' ? 19 : 14, 'url' => admin_url('post.php?post=' . $order->get_id() . '&action=edit'));
            }
        }
    } finally { PLL()->curlang = $language; }
    echo wp_json_encode(array('version' => $version, 'created_orders' => $result)), "\n";
    return;
}
if ($phase === 'cleanup') {
    $refundCount = 0;
    foreach ($fixture['orders'] as $id) {
        $order = wc_get_order($id);
        if (!$order || $order->get_billing_email() !== 'published-refund@example.invalid') { throw new RuntimeException('Refund cleanup ownership mismatch'); }
        foreach ($order->get_refunds() as $refund) { $refund->delete(true); ++$refundCount; }
        $order->delete(true);
        if (get_post($id) || wc_get_order($id)) { throw new RuntimeException('Owned refund order remained'); }
    }
    delete_option($option);
    echo wp_json_encode(array('removed_orders' => count($fixture['orders']), 'removed_refunds' => $refundCount, 'refund_fixture_removed' => !get_option($option))), "\n";
    putenv('WPI_VARIABLE_PHASE=cleanup');
    (function () { require __DIR__ . '/published-variable-upgrade.php'; })();
    return;
}
$version = Hyyan\WPI\Plugin::getVersion();
$expected = $phase === 'orders' && $version === '1.5.1' ? 8 : 4;
if (count($fixture['orders']) !== $expected) { throw new RuntimeException('Expected refund orders missing'); }
$actual = array();
foreach ($fixture['orders'] as $index => $id) {
    $order = wc_get_order($id);
    $type = $index % 4 < 2 ? 'simple' : 'variation';
    $lang = $index % 2 === 0 ? 'en' : 'de';
    $price = $type === 'simple' ? 19 : 14;
    $product = $type === 'simple' ? $data['products'][$lang] : $variable['children'][$lang]['large'];
    $items = $order->get_items();
    $item = reset($items);
    $refunds = $order->get_refunds();
    if (count($items) !== 1 || $item->get_quantity() !== 2 || ($type === 'simple' ? $item->get_product_id() : $item->get_variation_id()) !== $product || pll_get_post_language($id) !== $lang || (float) $order->get_total() !== 2.0 * $price || (float) $order->get_total_refunded() !== (float) $price || count($refunds) !== 1) { throw new RuntimeException('Native partial refund mismatch'); }
    $refund = reset($refunds);
    $refundItems = $refund->get_items();
    $refundItem = reset($refundItems);
    if ((float) $refund->get_amount() !== (float) $price || count($refundItems) !== 1 || $refundItem->get_quantity() !== -1 || (float) $refundItem->get_total() !== -(float) $price || (int) $refundItem->get_meta('_refunded_item_id') !== $item->get_id()) { throw new RuntimeException('Native refund amount mismatch'); }
    $orderMeta = get_post_meta($id);
    unset($orderMeta['_edit_lock']);
    $actual[$id] = array('post' => (array) get_post($id), 'meta' => $orderMeta, 'language' => $lang, 'order' => json_decode(wp_json_encode($order->get_data()), true), 'items' => array_map(static function ($line) { return json_decode(wp_json_encode($line->get_data()), true); }, $items), 'refund' => array('post' => (array) get_post($refund->get_id()), 'meta' => get_post_meta($refund->get_id()), 'data' => json_decode(wp_json_encode($refund->get_data()), true), 'items' => array_map(static function ($line) { return json_decode(wp_json_encode($line->get_data()), true); }, $refund->get_items())));
}
foreach ($data['products'] as $id) { if (wc_get_product($id)->get_stock_quantity('edit') !== 17 - intdiv($expected, 2)) { throw new RuntimeException('Linked simple refund stock mismatch'); } }
foreach ($variable['children'] as $children) {
    if (wc_get_product($children['large'])->get_stock_quantity('edit') !== 20 - intdiv($expected, 2) || wc_get_product($children['small'])->get_stock_quantity('edit') !== 20) { throw new RuntimeException('Linked variation refund stock mismatch'); }
}
if ($phase === 'capture') {
    if ($version !== '1.5.0') { throw new RuntimeException('Published refund capture required'); }
    $fixture['baseline_orders'] = $actual;
    update_option($option, $fixture, false);
} else {
    if ($version !== '1.5.1') { throw new RuntimeException('Candidate refund verification required'); }
    foreach ($fixture['baseline_orders'] as $id => $before) { if (!isset($actual[$id]) || $actual[$id] !== $before) { throw new RuntimeException('Published order or refund changed'); } }
}
if ($phase === 'capture' || $phase === 'verify') {
    putenv('WPI_VARIABLE_PHASE=' . $phase);
    (function () { require __DIR__ . '/published-variable-upgrade.php'; })();
}
echo wp_json_encode(array('version' => $version, 'orders' => $expected, 'partial_refunds' => $expected, 'simple_stock_each' => 17 - intdiv($expected, 2), 'large_stock_each' => 20 - intdiv($expected, 2), 'small_stock_each' => 20, 'published_refunds_retained' => $version === '1.5.1')), "\n";
