<?php
if ($GLOBALS['wpdb']->prefix !== 'wpi_upgrade_') { throw new RuntimeException('Owned upgrade store required'); }
$phase = getenv('WPI_CHECKOUT_PHASE');
if (!in_array($phase, array('prepare', 'capture', 'verify', 'orders', 'cleanup'), true)) { throw new RuntimeException('Unknown checkout phase'); }
$option = 'wpi_published_checkout_fixture';
$fixture = get_option($option);
if ($phase === 'prepare' && $fixture) { throw new RuntimeException('Clean checkout fixture required'); }
if ($phase === 'orders' || $phase === 'cleanup') {
    if (!$fixture) { throw new RuntimeException('Owned checkout fixture missing'); }
    $data = get_option('wpi_published_data_fixture');
    $orders = array();
    foreach (wc_get_orders(array('limit' => -1, 'orderby' => 'ID', 'order' => 'ASC')) as $order) {
        if ($order->get_billing_email() !== 'published-upgrade@example.invalid') { continue; }
        $items = $order->get_items();
        if (count($items) !== 1) { throw new RuntimeException('Unexpected owned order items'); }
        $item = reset($items);
        if (!in_array($item->get_product_id(), $data['products'], true)) { throw new RuntimeException('Unexpected order product ownership'); }
        $orders[$order->get_id()] = $order;
    }
    if ($phase === 'cleanup') {
        foreach ($orders as $id => $order) { wp_delete_post($id, true); if (get_post($id)) { throw new RuntimeException('Owned order remained'); } }
        foreach ($fixture['options'] as $key => $value) { if ($value === null) { delete_option($key); } else { update_option($key, $value); } }
        foreach ($fixture['options'] as $key => $value) { if (get_option($key, null) !== $value) { throw new RuntimeException('Checkout option restoration failed'); } }
        delete_option($option);
        echo wp_json_encode(array('removed_orders' => count($orders), 'checkout_options_restored' => true)), "\n";
    } else {
        $version = Hyyan\WPI\Plugin::getVersion();
        if (!in_array($version, array('1.5.0', '1.5.1'), true)) { throw new RuntimeException('Published or candidate checkout required'); }
        $expected = $version === '1.5.0' ? 2 : 4;
        if (count($orders) !== $expected) { throw new RuntimeException('Unexpected checkout order count'); }
        $actual = array();
        $langs = array();
        foreach ($orders as $id => $order) {
            $items = $order->get_items();
            $item = reset($items);
            $lang = pll_get_post_language($id);
            if (!in_array($lang, array('en', 'de'), true) || $order->get_status() !== 'on-hold' || $order->get_payment_method() !== 'cheque' || $order->get_customer_id() !== 0 || (float) $order->get_total() !== 32.0 || (float) $order->get_discount_total() !== 6.0 || $item->get_quantity() !== 2 || (float) $item->get_subtotal() !== 38.0 || (float) $item->get_total() !== 32.0 || $order->get_used_coupons() !== array('published-data-upgrade')) { throw new RuntimeException('Native checkout result mismatch'); }
            $langs[] = $lang;
            $actual[$id] = array('post' => (array) get_post($id), 'meta' => get_post_meta($id), 'language' => $lang, 'item' => $item->get_data(), 'total' => $order->get_total(), 'discount' => $order->get_discount_total(), 'coupons' => $order->get_used_coupons());
        }
        $expectedLangs = $expected === 2 ? array('en', 'de') : array('en', 'de', 'en', 'de');
        if ($langs !== $expectedLangs) { throw new RuntimeException('Native order language sequence mismatch'); }
        foreach ($data['products'] as $id) { if (wc_get_product($id)->get_stock_quantity('edit') !== 17 - 2 * $expected) { throw new RuntimeException('Linked checkout stock mismatch'); } }
        $coupon = new WC_Coupon($data['coupon']);
        if ($coupon->get_usage_count() !== $expected) { throw new RuntimeException('Shared coupon usage mismatch'); }
        if ($version === '1.5.0') { $fixture['baseline_orders'] = $actual; update_option($option, $fixture, false); }
        else { foreach ($fixture['baseline_orders'] as $id => $baseline) { if (!isset($actual[$id]) || $actual[$id] !== $baseline) { throw new RuntimeException('Published order changed after update'); } } }
        echo wp_json_encode(array('version' => $version, 'native_orders' => $expected, 'languages' => $langs, 'total_each' => 32, 'discount_each' => 6, 'quantity_each' => 2, 'stock_each' => 17 - 2 * $expected, 'coupon_uses' => $coupon->get_usage_count(), 'published_orders_retained' => $version === '1.5.1', 'order_ids' => array_keys($orders))), "\n";
        return;
    }
}
putenv('WPI_DATA_PHASE=' . $phase);
(function () { require __DIR__ . '/published-cart-upgrade.php'; })();
if ($phase === 'prepare') {
    $fixture = array('options' => array());
    foreach (array('woocommerce_cheque_settings', 'woocommerce_enable_guest_checkout') as $key) { $fixture['options'][$key] = get_option($key, null); }
    update_option($option, $fixture, false);
    update_option('woocommerce_cheque_settings', array('enabled' => 'yes', 'title' => 'Upgrade cheque', 'description' => 'Offline upgrade acceptance', 'instructions' => 'Offline upgrade acceptance'));
    update_option('woocommerce_enable_guest_checkout', 'yes');
}
