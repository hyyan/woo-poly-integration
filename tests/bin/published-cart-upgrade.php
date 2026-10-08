<?php
if ($GLOBALS['wpdb']->prefix !== 'wpi_upgrade_') { throw new RuntimeException('Owned upgrade store required'); }
$phase = getenv('WPI_DATA_PHASE');
if (!in_array($phase, array('prepare', 'capture', 'verify', 'cleanup'), true)) { throw new RuntimeException('Unknown fixture phase'); }
$option = 'wpi_published_cart_fixture';
$cartData = get_option($option);
if ($phase === 'cleanup') {
    if (!$cartData) { throw new RuntimeException('Owned cart fixture missing'); }
    foreach ($cartData['pages'] as $pages) { foreach ($pages as $id) { wp_delete_post($id, true); } }
    wp_delete_post($cartData['minimum_coupon'], true);
    foreach ($cartData['options'] as $key => $value) { if ($value === null) { delete_option($key); } else { update_option($key, $value); } }
    foreach ($cartData['options'] as $key => $value) { if (get_option($key, null) !== $value) { throw new RuntimeException('Cart option restoration failed'); } }
    foreach ($cartData['pages'] as $pages) { foreach ($pages as $id) { if (get_post($id)) { throw new RuntimeException('Owned cart page remained'); } } }
    if (get_post($cartData['minimum_coupon'])) { throw new RuntimeException('Owned minimum coupon remained'); }
    delete_option($option);
}
(function () { require __DIR__ . '/published-data-upgrade.php'; })();
$data = get_option('wpi_published_data_fixture');
if ($phase === 'cleanup') { echo wp_json_encode(array('cart_pages_removed' => 4, 'minimum_coupon_removed' => true, 'cart_options_restored' => true)), "\n"; return; }
if ($phase === 'prepare') {
    if ($cartData) { throw new RuntimeException('Clean cart fixture required'); }
    $cartData = array('pages' => array(), 'options' => array());
    foreach (array('woocommerce_cart_page_id', 'woocommerce_checkout_page_id', 'woocommerce_enable_coupons') as $key) { $cartData['options'][$key] = get_option($key, null); }
    update_option($option, $cartData, false);
    foreach (array('cart', 'checkout') as $kind) {
        foreach (array('en', 'de') as $lang) {
            $id = wp_insert_post(array('post_type' => 'page', 'post_status' => 'publish', 'post_title' => 'Published cart upgrade ' . $kind . ' ' . $lang, 'post_name' => 'published-upgrade-' . $kind . '-' . $lang, 'post_content' => '[woocommerce_' . $kind . ']'), true);
            if (is_wp_error($id)) { throw new RuntimeException($id->get_error_message()); }
            pll_set_post_language($id, $lang);
            $cartData['pages'][$kind][$lang] = $id;
            update_option($option, $cartData, false);
        }
        pll_save_post_translations($cartData['pages'][$kind]);
        update_option('woocommerce_' . $kind . '_page_id', $cartData['pages'][$kind]['en']);
    }
    update_option('woocommerce_enable_coupons', 'yes');
    foreach ($data['products'] as $id) { $product = wc_get_product($id); $product->set_virtual(true); $product->save(); }
    $coupon = new WC_Coupon();
    $coupon->set_code('published-cart-minimum');
    $coupon->set_discount_type('fixed_cart');
    $coupon->set_amount('3');
    $coupon->set_minimum_amount('50');
    $coupon->save();
    $cartData['minimum_coupon'] = $coupon->get_id();
    update_option($option, $cartData, false);
    $urls = array();
    foreach ($cartData['pages'] as $kind => $pages) { foreach ($pages as $lang => $id) { $urls[$kind][$lang] = get_permalink($id); } }
    echo wp_json_encode(array('cart_urls' => $urls, 'products' => $data['products'], 'coupon' => 'published-data-upgrade')), "\n";
    return;
}
if (!$cartData) { throw new RuntimeException('Owned cart fixture missing'); }
if (!defined('WOOCOMMERCE_CART')) { define('WOOCOMMERCE_CART', true); }
wp_set_current_user(0);
WC()->session = new WC_Session_Handler();
WC()->customer = new WC_Customer(0);
WC()->cart = new WC_Cart();
$actual = array();
foreach (array('en', 'de') as $lang) {
    Hyyan\WPI\Utilities::switch_pll_locale($lang === 'en' ? 'en_US' : 'de_DE');
    foreach (array('none' => 38, 'published-data-upgrade' => 32, 'published-cart-minimum' => 38) as $code => $expected) {
        WC()->session = new WC_Session_Handler();
        WC()->cart = new WC_Cart();
        WC()->cart->get_cart_from_session();
        wc_clear_notices();
        $key = WC()->cart->add_to_cart($data['products'][$lang], 2);
        if (!$key) { throw new RuntimeException('Native cart addition failed'); }
        $applied = $code === 'none' ? false : WC()->cart->add_discount($code);
        WC()->cart->calculate_totals();
        $items = WC()->cart->get_cart();
        $item = reset($items);
        $row = array('quantity' => $item['quantity'], 'product_id' => $item['product_id'], 'subtotal' => (float) WC()->cart->subtotal, 'discount' => (float) WC()->cart->get_cart_discount_total(), 'total' => (float) WC()->cart->total, 'applied' => $applied, 'coupons' => WC()->cart->get_applied_coupons());
        if (count($items) !== 1 || $row['quantity'] !== 2 || $row['product_id'] !== $data['products'][$lang] || $row['subtotal'] !== 38.0 || $row['total'] !== (float) $expected || $applied !== ($code === 'published-data-upgrade')) { throw new RuntimeException('Native cart result mismatch ' . wp_json_encode($row)); }
        $actual[$lang][$code] = $row;
    }
}
WC()->cart->empty_cart();
wc_clear_notices();
if ($phase === 'capture') { $cartData['baseline'] = $actual; update_option($option, $cartData, false); }
elseif ($actual !== $cartData['baseline']) { throw new RuntimeException('Native cart upgrade result changed'); }
echo wp_json_encode(array('native_cart_cases' => 6, 'version' => Hyyan\WPI\Plugin::getVersion(), 'results' => $actual, 'matches_published' => $phase === 'verify')), "\n";
