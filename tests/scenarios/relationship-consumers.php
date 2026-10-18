<?php
if (!in_array($GLOBALS['wpdb']->prefix, array('wpi_package_', 'wpi_upgrade_'), true)) { throw new RuntimeException('Owned store required'); }
$phase = getenv('WPI_RELATIONSHIP_PHASE');
$option = 'wpi_relationship_consumers_fixture';
$fixture = get_option($option);
if ($phase === 'prepare') {
    if ($fixture) { throw new RuntimeException('Clean relationship fixture required'); }
    wp_set_current_user(1);
    $fixture = array('parents' => array(), 'children' => array(), 'simple' => 0, 'coupon' => 0);
    update_option($option, $fixture, false);
    foreach (array('en', 'de') as $lang) {
        $parent = new WC_Product_Variable();
        $parent->set_name('Relationship security variation ' . $lang);
        $parent->set_status('publish');
        $attribute = new WC_Product_Attribute();
        $attribute->set_name('Size');
        $attribute->set_options(array('large'));
        $attribute->set_variation(true);
        $parent->set_attributes(array($attribute));
        $parent->set_default_attributes(array('size' => 'large'));
        $parent->save();
        pll_set_post_language($parent->get_id(), $lang);
        $fixture['parents'][$lang] = $parent->get_id();
        update_option($option, $fixture, false);
        $child = new WC_Product_Variation();
        $child->set_parent_id($parent->get_id());
        $child->set_status('publish');
        $child->set_attributes(array('size' => 'large'));
        $child->set_regular_price('12');
        $child->set_virtual(true);
        $child->set_manage_stock(true);
        $child->set_stock_quantity(9);
        $child->save();
        pll_set_post_language($child->get_id(), $lang);
        $fixture['children'][$lang] = $child->get_id();
        update_option($option, $fixture, false);
        WC_Product_Variable::sync($parent->get_id());
    }
    pll_save_post_translations($fixture['parents']);
    foreach ($fixture['children'] as $lang => $id) { update_post_meta($id, Hyyan\WPI\Product\Variation::DUPLICATE_KEY, ($lang === 'de' ? '000' : '') . $fixture['children']['en']); }
    $simple = new WC_Product_Simple();
    $simple->set_name('Relationship security unrelated product');
    $simple->set_status('publish');
    $simple->set_regular_price('12');
    $simple->set_virtual(true);
    $simple->set_manage_stock(true);
    $simple->set_stock_quantity(7);
    $simple->save();
    pll_set_post_language($simple->get_id(), 'en');
    $fixture['simple'] = $simple->get_id();
    update_option($option, $fixture, false);
    update_post_meta($simple->get_id(), Hyyan\WPI\Product\Variation::DUPLICATE_KEY, $fixture['children']['en']);
    $coupon = new WC_Coupon();
    $coupon->set_code('relationship-security');
    $coupon->set_discount_type('fixed_product');
    $coupon->set_amount('2');
    $coupon->set_product_ids(array($fixture['children']['en']));
    $coupon->save();
    $fixture['coupon'] = $coupon->get_id();
    update_option($option, $fixture, false);
    wc_update_product_stock(wc_get_product($fixture['children']['en']), 5, 'set');
    $stocks = array();
    foreach ($fixture['children'] as $lang => $id) {
        $stocks[$lang] = wc_get_product($id)->get_stock_quantity();
        if ($stocks[$lang] !== 5) { throw new RuntimeException('Valid variation stock did not synchronize'); }
    }
    $stocks['simple'] = wc_get_product($fixture['simple'])->get_stock_quantity();
    if ($stocks['simple'] !== 7) { throw new RuntimeException('Unrelated simple stock changed'); }
    $coupon = new WC_Coupon($fixture['coupon']);
    foreach ($fixture['children'] as $id) { if (!$coupon->is_valid_for_product(wc_get_product($id))) { throw new RuntimeException('Valid variation coupon rejected'); } }
    if ($coupon->is_valid_for_product(wc_get_product($fixture['simple']))) { throw new RuntimeException('Unrelated simple coupon accepted'); }
    $excluded = new WC_Coupon();
    $excluded->set_excluded_product_ids(array($fixture['children']['en']));
    $handler = (new ReflectionClass(Hyyan\WPI\Coupon::class))->newInstanceWithoutConstructor();
    $handler->couponLoaded($excluded);
    foreach ($fixture['children'] as $id) { if (!in_array($id, $excluded->get_excluded_product_ids(), true)) { throw new RuntimeException('Valid variation exclusion lost'); } }
    if (in_array($fixture['simple'], $excluded->get_excluded_product_ids(), true)) { throw new RuntimeException('Unrelated simple excluded'); }
    echo wp_json_encode(array('stocks' => $stocks, 'coupon_ids' => $coupon->get_product_ids(), 'excluded_ids' => $excluded->get_excluded_product_ids(), 'simple' => $fixture['simple'], 'parents' => $fixture['parents'], 'children' => $fixture['children'], 'urls' => array('simple' => get_permalink($fixture['simple']), 'variation' => get_permalink($fixture['parents']['en'])))), "\n";
    return;
}
if ($phase === 'cart' && $fixture) {
    if (isset($fixture['cart'])) { throw new RuntimeException('Clean owned cart required'); }
    $fixture['cart_option'] = get_option('woocommerce_cart_page_id', null);
    $fixture['cart'] = wp_insert_post(array('post_type' => 'page', 'post_status' => 'publish', 'post_title' => 'Relationship security cart', 'post_content' => '[woocommerce_cart]'), true);
    if (is_wp_error($fixture['cart'])) { throw new RuntimeException('Owned cart creation failed'); }
    update_option($option, $fixture, false);
    update_option('woocommerce_cart_page_id', $fixture['cart']);
    echo wp_json_encode(array('cart_url' => get_permalink($fixture['cart']))), "\n";
    return;
}
if ($phase !== 'cleanup' || !$fixture) { throw new RuntimeException('Known phase and fixture required'); }
$ids = array_merge(array_values($fixture['children']), array_values($fixture['parents']), array($fixture['simple'], $fixture['coupon']));
if (isset($fixture['cart'])) {
    $ids[] = $fixture['cart'];
    if ($fixture['cart_option'] === null) { delete_option('woocommerce_cart_page_id'); }
    else { update_option('woocommerce_cart_page_id', $fixture['cart_option']); }
    if (get_option('woocommerce_cart_page_id', null) !== $fixture['cart_option']) { throw new RuntimeException('Cart option restoration failed'); }
}
foreach ($ids as $id) { wp_delete_post($id, true); if (get_post($id)) { throw new RuntimeException('Owned relationship fixture remained'); } }
delete_option($option);
echo wp_json_encode(array('removed_posts' => count($ids), 'fixture_removed' => !get_option($option))), "\n";
