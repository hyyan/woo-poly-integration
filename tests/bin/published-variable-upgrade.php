<?php
if ($GLOBALS['wpdb']->prefix !== 'wpi_upgrade_') { throw new RuntimeException('Owned upgrade store required'); }
$phase = getenv('WPI_VARIABLE_PHASE');
if (!in_array($phase, array('prepare', 'capture', 'verify', 'orders', 'cleanup'), true)) { throw new RuntimeException('Unknown variable phase'); }
$option = 'wpi_published_variable_fixture';
$fixture = get_option($option);
if ($phase === 'prepare') {
    if ($fixture) { throw new RuntimeException('Clean variable fixture required'); }
    putenv('WPI_CHECKOUT_PHASE=prepare');
    (function () { require __DIR__ . '/published-checkout-upgrade.php'; })();
    wp_set_current_user(1);
    $fixture = array('parents' => array(), 'children' => array());
    update_option($option, $fixture, false);
    foreach (array('en', 'de') as $lang) {
        $p = new WC_Product_Variable();
        $p->set_name('Published variation upgrade ' . $lang);
        $p->set_status('publish');
        $a = new WC_Product_Attribute();
        $a->set_name('Size');
        $a->set_options(array('small', 'large'));
        $a->set_variation(true);
        $p->set_attributes(array($a));
        $p->set_default_attributes(array('size' => 'small'));
        $p->save();
        pll_set_post_language($p->get_id(), $lang);
        $fixture['parents'][$lang] = $p->get_id();
        update_option($option, $fixture, false);
        foreach (array('small' => '12', 'large' => '14') as $size => $price) {
            $v = new WC_Product_Variation();
            $v->set_parent_id($p->get_id());
            $v->set_status('publish');
            $v->set_regular_price($price);
            $v->set_attributes(array('size' => $size));
            $v->set_virtual(true);
            $v->set_manage_stock(true);
            $v->set_stock_quantity(20);
            $v->save();
            pll_set_post_language($v->get_id(), $lang);
            $fixture['children'][$lang][$size] = $v->get_id();
            update_option($option, $fixture, false);
        }
        WC_Product_Variable::sync($p->get_id());
    }
    pll_save_post_translations($fixture['parents']);
    foreach (array('small', 'large') as $size) {
        foreach (array('en', 'de') as $lang) { update_post_meta($fixture['children'][$lang][$size], Hyyan\WPI\Product\Variation::DUPLICATE_KEY, $lang === 'de' ? '000' . $fixture['children']['en'][$size] : (string) $fixture['children']['en'][$size]); }
    }
    $coupon = new WC_Coupon();
    $coupon->set_code('published-variable-upgrade');
    $coupon->set_discount_type('fixed_product');
    $coupon->set_amount('2');
    $coupon->set_product_ids(array($fixture['children']['en']['large']));
    $coupon->save();
    $fixture['coupon'] = $coupon->get_id();
    update_option($option, $fixture, false);
    $urls = array();
    foreach ($fixture['parents'] as $lang => $id) { $urls[$lang] = get_permalink($id); }
    echo wp_json_encode(array('variable_products' => $fixture['parents'], 'children' => $fixture['children'], 'urls' => $urls)), "\n";
    return;
}
if (!$fixture) { throw new RuntimeException('Owned variable fixture missing'); }
$ids = array_merge(array_values($fixture['parents']), array_values($fixture['children']['en']), array_values($fixture['children']['de']));
if ($phase === 'orders' || $phase === 'cleanup') {
    $orders = array();
    foreach (wc_get_orders(array('limit' => -1, 'orderby' => 'ID', 'order' => 'ASC')) as $order) {
        if ($order->get_billing_email() !== 'published-variable@example.invalid') { continue; }
        $items = $order->get_items();
        if (count($items) !== 1) { throw new RuntimeException('Unexpected owned variable items'); }
        $item = reset($items);
        if (!in_array($item->get_product_id(), $fixture['parents'], true) || !in_array($item->get_variation_id(), array($fixture['children']['en']['large'], $fixture['children']['de']['large']), true)) { throw new RuntimeException('Unexpected variation ownership'); }
        $orders[$order->get_id()] = $order;
    }
    if ($phase === 'cleanup') {
        foreach ($orders as $id => $order) { wp_delete_post($id, true); if (get_post($id)) { throw new RuntimeException('Owned variable order remained'); } }
        foreach ($ids as $id) { wp_delete_post($id, true); if (get_post($id)) { throw new RuntimeException('Owned variable product remained'); } }
        wp_delete_post($fixture['coupon'], true);
        delete_option($option);
        echo wp_json_encode(array('removed_variable_orders' => count($orders), 'removed_variable_products' => count($ids), 'removed_variable_coupon' => !get_post($fixture['coupon']))), "\n";
        putenv('WPI_CHECKOUT_PHASE=cleanup');
        (function () { require __DIR__ . '/published-checkout-upgrade.php'; })();
        return;
    }
    $version = Hyyan\WPI\Plugin::getVersion();
    if (!in_array($version, array('1.5.0', '1.5.1'), true)) { throw new RuntimeException('Published or candidate variable checkout required'); }
    $expected = $version === '1.5.0' ? 2 : 4;
    if (count($orders) !== $expected) { throw new RuntimeException('Unexpected variable order count'); }
    $langs = array();
    $actual = array();
    foreach ($orders as $id => $order) {
        $items = $order->get_items();
        $item = reset($items);
        $lang = pll_get_post_language($id);
        if (!in_array($lang, array('en', 'de'), true) || $item->get_meta('size') !== 'large' || $item->get_quantity() !== 2 || (float) $item->get_subtotal() !== 28.0 || (float) $item->get_total() !== 24.0 || (float) $order->get_total() !== 24.0 || (float) $order->get_discount_total() !== 4.0 || $order->get_payment_method() !== 'cheque' || $order->get_status() !== 'on-hold' || $order->get_customer_id() !== 0 || $order->get_used_coupons() !== array('published-variable-upgrade')) { throw new RuntimeException('Native variable checkout mismatch'); }
        $langs[] = $lang;
        $actual[$id] = array('post' => (array) get_post($id), 'meta' => get_post_meta($id), 'language' => $lang, 'items' => array('product' => $item->get_product_id(), 'variation' => $item->get_variation_id(), 'quantity' => $item->get_quantity(), 'size' => $item->get_meta('size'), 'total' => $item->get_total(), 'subtotal' => $item->get_subtotal()), 'total' => $order->get_total(), 'coupons' => $order->get_used_coupons());
    }
    if ($langs !== ($expected === 2 ? array('en', 'de') : array('en', 'de', 'en', 'de'))) { throw new RuntimeException('Variable order languages changed'); }
    foreach (array('en', 'de') as $lang) {
        if (wc_get_product($fixture['children'][$lang]['large'])->get_stock_quantity('edit') !== 20 - 2 * $expected || wc_get_product($fixture['children'][$lang]['small'])->get_stock_quantity('edit') !== 20) { throw new RuntimeException('Linked variation stock mismatch'); }
    }
    $coupon = new WC_Coupon($fixture['coupon']);
    if ($coupon->get_usage_count() !== $expected) { throw new RuntimeException('Variable coupon usage mismatch'); }
    if ($version === '1.5.0') { $fixture['baseline_orders'] = $actual; update_option($option, $fixture, false); }
    else { foreach ($fixture['baseline_orders'] as $id => $before) { if (!isset($actual[$id]) || $actual[$id] !== $before) { throw new RuntimeException('Published variable order changed'); } } }
    echo wp_json_encode(array('version' => $version, 'variable_orders' => $expected, 'languages' => $langs, 'total_each' => 24, 'discount_each' => 4, 'quantity_each' => 2, 'large_stock_each' => 20 - 2 * $expected, 'small_stock_each' => 20, 'coupon_uses' => $coupon->get_usage_count(), 'order_ids' => array_keys($orders), 'published_orders_retained' => $version === '1.5.1')), "\n";
    return;
}
$actual = array();
foreach ($ids as $id) { $actual[$id] = array('post' => (array) get_post($id), 'meta' => get_post_meta($id), 'language' => pll_get_post_language($id)); }
if ($phase === 'capture') { if (Hyyan\WPI\Plugin::getVersion() !== '1.5.0') { throw new RuntimeException('Published variable capture required'); } $fixture['baseline_products'] = $actual; update_option($option, $fixture, false); }
else { if (Hyyan\WPI\Plugin::getVersion() !== '1.5.1' || $actual !== $fixture['baseline_products']) { throw new RuntimeException('Variable stored upgrade data changed'); } }
putenv('WPI_CHECKOUT_PHASE=' . $phase);
(function () { require __DIR__ . '/published-checkout-upgrade.php'; })();
echo wp_json_encode(array('variable_snapshot_retained' => $phase === 'verify', 'products' => count($ids))), "\n";
