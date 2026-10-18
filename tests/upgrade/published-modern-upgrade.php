<?php
if ($GLOBALS['wpdb']->prefix !== 'wpi_package_' || wp_get_environment_type() !== 'local') { throw new RuntimeException('Owned current upgrade store required'); }
if (has_filter('pre_wp_mail', '__return_true') === false) { throw new RuntimeException('Suppressed mail transport required'); }
$phase = getenv('WPI_MODERN_PHASE');
$key = 'wpi_published_modern_fixture';
$f = get_option($key);
if ($phase === 'prepare') {
    if ($f || Hyyan\WPI\Plugin::getVersion() !== '1.5.0') { throw new RuntimeException('Clean current published baseline required'); }
    $f = array('products' => array(), 'pages' => array(), 'options' => array(), 'translations' => array(), 'baseline' => array(), 'emails' => array(), 'mode' => 'classic-cpt');
    foreach (array('woocommerce_cart_page_id', 'woocommerce_checkout_page_id', 'woocommerce_cheque_settings', 'woocommerce_enable_guest_checkout', 'woocommerce_custom_orders_table_enabled', 'woocommerce_custom_orders_table_data_sync_enabled', 'woocommerce_customer_on_hold_order_settings', 'woocommerce_new_order_settings', 'woocommerce_email_footer_text', 'hyyan-wpi-flash-messages') as $option) { $f['options'][$option] = get_option($option, null); }
    update_option($key, $f, false);
    $password = wp_generate_password(24, false);
    $user = wp_insert_user(array('user_login' => 'published-modern-customer', 'user_pass' => $password, 'user_email' => 'published-modern@example.invalid', 'role' => 'customer'));
    if (is_wp_error($user)) { throw new RuntimeException($user->get_error_message()); }
    $f['user'] = $user;
    update_option($key, $f, false);
    foreach (array('en', 'de') as $lang) {
        $language = PLL()->model->get_language($lang);
        $f['translations'][$lang] = array('exists' => metadata_exists('term', $language->term_id, '_pll_strings_translations'), 'value' => get_term_meta($language->term_id, '_pll_strings_translations', true));
        $mo = new PLL_MO(); $mo->import_from_db($language);
        foreach (array('Upgrade order subject' => 'Aktualisierung Bestellung Betreff', 'Upgrade order heading' => 'Aktualisierung Bestellung Titel', 'Upgrade order footer' => 'Aktualisierung Bestellung Fuss') as $source => $target) { $mo->add_entry(new Translation_Entry(array('singular' => $source, 'translations' => array($lang === 'de' ? $target : $source)))); }
        $mo->export_to_db($language);
        $p = new WC_Product_Variable(); $p->set_name('Published modern upgrade ' . $lang); $p->set_status('publish'); $p->set_weight('1'); $p->set_length('1'); $p->set_width('1'); $p->set_height('1');
        $a = new WC_Product_Attribute(); $a->set_name('size'); $a->set_options(array('large')); $a->set_visible(true); $a->set_variation(true); $p->set_attributes(array($a)); $p->set_default_attributes(array('size' => 'large')); $p->save(); pll_set_post_language($p->get_id(), $lang);
        $f['products'][$lang] = array('parent' => $p->get_id()); update_option($key, $f, false);
        $v = new WC_Product_Variation(); $v->set_parent_id($p->get_id()); $v->set_status('publish'); $v->set_regular_price('12'); $v->set_virtual(true); $v->set_attributes(array('size' => 'large')); $v->set_manage_stock(true); $v->set_stock_quantity(30); $v->set_weight('1'); $v->set_length('1'); $v->set_width('1'); $v->set_height('1'); $v->save(); pll_set_post_language($v->get_id(), $lang);
        $f['products'][$lang]['child'] = $v->get_id(); update_option($key, $f, false); WC_Product_Variable::sync($p->get_id());
    }
    pll_save_post_translations(array('en' => $f['products']['en']['parent'], 'de' => $f['products']['de']['parent']));
    foreach ($f['products'] as $lang => $ids) { update_post_meta($ids['child'], Hyyan\WPI\Product\Variation::DUPLICATE_KEY, $f['products']['en']['child']); }
    foreach (array('classic', 'block') as $format) { foreach (array('cart', 'checkout') as $kind) {
        foreach (array('en', 'de') as $lang) {
            $content = $format === 'classic' ? '[woocommerce_' . $kind . ']' : get_post_field('post_content', $f['options']['woocommerce_' . $kind . '_page_id']);
            if ($format === 'block' && !has_block('woocommerce/' . $kind, $content)) { throw new RuntimeException('Native owned block template required'); }
            $id = wp_insert_post(array('post_type' => 'page', 'post_status' => 'publish', 'post_title' => 'Published modern ' . $format . ' ' . $kind . ' ' . $lang, 'post_name' => 'published-modern-' . $format . '-' . $kind . '-' . $lang, 'post_content' => $content));
            if (!$id) { throw new RuntimeException('Owned page insertion failed'); } pll_set_post_language($id, $lang); $f['pages'][$format][$kind][$lang] = $id; update_option($key, $f, false);
        }
        pll_save_post_translations($f['pages'][$format][$kind]);
    } }
    update_option('woocommerce_cheque_settings', array('enabled' => 'yes', 'title' => 'Upgrade cheque', 'description' => 'Offline upgrade acceptance', 'instructions' => 'Offline upgrade acceptance'));
    foreach (array('new_order', 'customer_on_hold_order') as $type) { $settings = (array) get_option('woocommerce_' . $type . '_settings'); $settings['enabled'] = 'yes'; $settings['subject'] = 'Upgrade order subject'; $settings['heading'] = 'Upgrade order heading'; if ($type === 'new_order') { $settings['recipient'] = 'published-modern-shop@example.invalid'; } update_option('woocommerce_' . $type . '_settings', $settings); }
    update_option('woocommerce_email_footer_text', 'Upgrade order footer');
    foreach (array('cart', 'checkout') as $kind) { update_option('woocommerce_' . $kind . '_page_id', $f['pages']['classic'][$kind]['en']); }
    update_option($key, $f, false); flush_rewrite_rules();
    echo wp_json_encode(array('user' => $user, 'username' => 'published-modern-customer', 'password' => $password)), "\n"; return;
}
if (!$f) { throw new RuntimeException('Owned current upgrade fixture missing'); }
if ($phase === 'mode') {
    $mode = getenv('WPI_MODERN_MODE');
    if (!in_array($mode, array('classic-cpt', 'block-cpt', 'classic-sync', 'block-sync', 'classic-nosync', 'block-nosync'), true)) { throw new RuntimeException('Unknown current upgrade mode'); }
    $format = strpos($mode, 'classic') === 0 ? 'classic' : 'block';
    foreach (array('cart', 'checkout') as $kind) { update_option('woocommerce_' . $kind . '_page_id', $f['pages'][$format][$kind]['en']); }
    update_option('woocommerce_custom_orders_table_enabled', substr($mode, -4) === '-cpt' ? 'no' : 'yes');
    update_option('woocommerce_custom_orders_table_data_sync_enabled', substr($mode, -5) === '-sync' ? 'yes' : 'no');
    $f['mode'] = $mode; update_option($key, $f, false); flush_rewrite_rules();
}
if ($phase === 'urls' || $phase === 'mode') {
    $format = strpos($f['mode'], 'classic') === 0 ? 'classic' : 'block';
    $urls = array(); $checkout = array(); $accounts = array();
    foreach ($f['products'] as $lang => $ids) { $urls[$lang] = get_permalink($ids['parent']); $checkout[$lang] = get_permalink($f['pages'][$format]['checkout'][$lang]); $accounts[$lang] = get_permalink(pll_get_post(wc_get_page_id('myaccount'), $lang)); }
    echo wp_json_encode(array('mode' => $f['mode'], 'products' => $f['products'], 'urls' => $urls, 'checkout' => $checkout, 'accounts' => $accounts, 'hpos' => Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled(), 'sync' => get_option('woocommerce_custom_orders_table_data_sync_enabled'))), "\n"; return;
}
$orders = wc_get_orders(array('type' => 'shop_order', 'customer' => $f['user'], 'limit' => -1, 'orderby' => 'ID', 'order' => 'ASC'));
if ($phase === 'cleanup') {
    foreach ($orders as $o) { if ($o->get_billing_email() !== 'published-modern@example.invalid') { throw new RuntimeException('Order cleanup ownership mismatch'); } $id = $o->get_id(); $o->delete(true); if (wc_get_order($id)) { throw new RuntimeException('Owned order remained'); } }
    foreach ($f['products'] as $ids) { foreach (array($ids['child'], $ids['parent']) as $id) { wp_delete_post($id, true); } }
    foreach ($f['pages'] as $formats) { foreach ($formats as $pages) { foreach ($pages as $id) { wp_delete_post($id, true); } } }
    require_once ABSPATH . 'wp-admin/includes/user.php'; if (get_userdata($f['user'])->user_login !== 'published-modern-customer') { throw new RuntimeException('Customer ownership mismatch'); } wp_delete_user($f['user']);
    foreach ($f['translations'] as $lang => $before) { $id = PLL()->model->get_language($lang)->term_id; if ($before['exists']) { update_term_meta($id, '_pll_strings_translations', wp_slash($before['value'])); } else { delete_term_meta($id, '_pll_strings_translations'); } if (get_term_meta($id, '_pll_strings_translations', true) !== $before['value']) { throw new RuntimeException('Owned translations restoration failed'); } }
    foreach ($f['options'] as $name => $value) { if ($value === null) { delete_option($name); } else { update_option($name, $value); } }
    foreach ($f['options'] as $name => $value) { if (get_option($name, null) !== $value) { throw new RuntimeException('Owned option restoration failed'); } }
    delete_option($key); echo wp_json_encode(array('removed_orders' => count($orders), 'removed_products' => 4, 'removed_pages' => 8, 'removed_customer' => true, 'options_restored' => true, 'translations_restored' => true)), "\n"; return;
}
if (!in_array($phase, array('capture', 'verify', 'orders'), true)) { throw new RuntimeException('Unknown current upgrade phase'); }
$version = Hyyan\WPI\Plugin::getVersion(); $expected = $phase === 'orders' ? (int) getenv('WPI_MODERN_EXPECTED') : 2;
if (count($orders) !== $expected || ($phase === 'capture' ? $version !== '1.5.0' : $version !== '1.5.1')) { throw new RuntimeException('Current purchase phase mismatch'); }
$actual = array();
foreach ($orders as $i => $order) {
    $lang = $i % 2 === 0 ? 'en' : 'de'; $items = $order->get_items(); $line = reset($items);
    if (count($items) !== 1 || $order->get_customer_id() !== (int) $f['user'] || $order->get_billing_email() !== 'published-modern@example.invalid' || $order->get_payment_method() !== 'cheque' || $order->get_status() !== 'on-hold' || pll_get_post_language($order->get_id()) !== $lang || $line->get_product_id() !== $f['products'][$lang]['parent'] || $line->get_variation_id() !== $f['products'][$lang]['child'] || $line->get_quantity() !== 1 || $line->get_meta('size') !== 'large' || (float) $order->get_total() !== 12.0) { throw new RuntimeException('Current native purchase mismatch ' . $order->get_id()); }
    $actual[$order->get_id()] = array('customer' => $order->get_customer_id(), 'language' => $lang, 'status' => $order->get_status(), 'total' => $order->get_total(), 'billing' => $order->get_address('billing'), 'payment' => $order->get_payment_method(), 'items' => array_map(static function ($item) { return json_decode(wp_json_encode($item->get_data()), true); }, $items));
}
foreach ($f['products'] as $ids) { if (wc_get_product($ids['child'])->get_stock_quantity('edit') !== 30 - $expected) { throw new RuntimeException('Current linked purchase stock mismatch'); } }
if ($phase !== 'capture') {
    foreach ($actual as $id => $row) {
        $types = array();
        foreach ($f['emails'] as $email) {
            if ($email['order'] !== $id) { continue; }
            $german = $row['language'] === 'de';
            if ($email['language'] !== $row['language'] || $email['subject'] !== ($german ? 'Aktualisierung Bestellung Betreff' : 'Upgrade order subject') || $email['english_heading'] !== !$german || $email['german_heading'] !== $german || $email['english_footer'] !== !$german || $email['german_footer'] !== $german) { throw new RuntimeException('Localized native email mismatch ' . $id); }
            $types[] = $email['type'];
        }
        sort($types);
        if ($types !== array('customer_on_hold_order', 'new_order')) { throw new RuntimeException('Native email pair missing ' . $id); }
    }
}
if ($phase === 'capture') { $f['baseline'] = $actual; update_option($key, $f, false); }
else { foreach ($f['baseline'] as $id => $before) { if (!isset($actual[$id]) || $before !== $actual[$id]) { throw new RuntimeException('Published current order changed'); } } }
echo wp_json_encode(array('version' => $version, 'orders' => $expected, 'stock_each' => 30 - $expected, 'order_ids' => array_keys($actual), 'published_orders_retained' => $phase !== 'capture', 'email_observations' => $f['emails'])), "\n";
