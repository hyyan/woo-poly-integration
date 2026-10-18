<?php
if (!in_array($GLOBALS['wpdb']->prefix, array('wpi_package_', 'wpi_upgrade_'), true)) { throw new RuntimeException('Owned store required'); }
$phase = getenv('WPI_SCRIPT_PHASE');
$option = 'wpi_native_inline_script_fixture';
$fixture = get_option($option);
if ($phase === 'probe') {
    $before = array_keys(wp_scripts()->registered);
    $capture = static function ($id, $code, $jquery = false) {
        ob_start();
        try { Hyyan\WPI\Utilities::jsScriptWrapper($id, $code, $jquery); return ob_get_contents(); }
        finally { ob_end_clean(); }
    };
    $first = $capture('repeat', 'window.firstNativeWrapper = 1;');
    $second = $capture('repeat', 'window.secondNativeWrapper = 1;');
    $jquery = $capture('ready', 'window.readyNativeWrapper = 1;', true);
    $returned = Hyyan\WPI\Utilities::jsScriptWrapper('returned', 'window.returnedNativeWrapper = 1;', false, true);
    if (substr_count($first, 'window.firstNativeWrapper = 1;') !== 1 || strpos($second, 'window.firstNativeWrapper') !== false || substr_count($second, 'window.secondNativeWrapper = 1;') !== 1) { throw new RuntimeException('Repeated print contract failed'); }
    if (strpos($jquery, 'jQuery(function ($)') === false || strpos($returned, 'id="hyyan-wpi-returned"') === false || array_keys(wp_scripts()->registered) !== $before) { throw new RuntimeException('Ready, return or cleanup contract failed'); }
    echo wp_json_encode(array('modern_renderer' => function_exists('wp_print_inline_script_tag'), 'first' => $first, 'second' => $second, 'jquery' => $jquery, 'returned' => $returned, 'registration_restored' => true)), "\n";
    return;
}
if ($phase === 'prepare') {
    if ($fixture) { throw new RuntimeException('Clean script fixture required'); }
    $fixture = array('parents' => array(), 'children' => array(), 'order' => 0, 'attribute' => 0, 'drafts' => array(), 'notice' => get_option('hyyan-wpi-flash-messages', null));
    update_option($option, $fixture, false);
    delete_option('hyyan-wpi-flash-messages');
    global $wpdb;
    $wpdb->insert($wpdb->prefix . 'woocommerce_attribute_taxonomies', array('attribute_name' => 'native_script', 'attribute_label' => 'Native script attribute', 'attribute_type' => 'select', 'attribute_orderby' => 'menu_order', 'attribute_public' => 1));
    $fixture['attribute'] = (int) $wpdb->insert_id;
    update_option($option, $fixture, false);
    delete_transient('wc_attribute_taxonomies');
    foreach (array('en', 'de') as $lang) {
        $parent = new WC_Product_Variable();
        $parent->set_name('Native inline script parent ' . $lang);
        $parent->set_status('publish');
        $parent->set_weight('2');
        $attribute = new WC_Product_Attribute();
        $attribute->set_name('Size');
        $attribute->set_options(array('large'));
        $attribute->set_variation(true);
        $parent->set_attributes(array($attribute));
        $parent->save();
        pll_set_post_language($parent->get_id(), $lang);
        $fixture['parents'][$lang] = $parent->get_id();
        update_option($option, $fixture, false);
        $child = new WC_Product_Variation();
        $child->set_parent_id($parent->get_id());
        $child->set_status('publish');
        $child->set_attributes(array('size' => 'large'));
        $child->set_regular_price('12');
        $child->save();
        pll_set_post_language($child->get_id(), $lang);
        $fixture['children'][$lang] = $child->get_id();
        update_option($option, $fixture, false);
    }
    pll_save_post_translations($fixture['parents']);
    foreach ($fixture['children'] as $id) { update_post_meta($id, Hyyan\WPI\Product\Variation::DUPLICATE_KEY, $fixture['children']['en']); }
    $order = wc_create_order(array('status' => 'pending'));
    if (is_wp_error($order)) { throw new RuntimeException($order->get_error_message()); }
    $fixture['order'] = $order->get_id();
    update_option($option, $fixture, false);
    echo wp_json_encode(array("parents" => $fixture["parents"], "children" => $fixture["children"], "order" => $fixture["order"], "attribute" => $fixture["attribute"])), "\n";
    return;
}
if (!$fixture) { throw new RuntimeException('Script fixture required'); }
if ($phase === 'capture-features') {
    if (array_key_exists('features', $fixture)) { throw new RuntimeException('Features already captured'); }
    $fixture['features'] = get_option('wpi-features');
    update_option($option, $fixture, false);
    echo "Feature options captured for restoration\n";
    return;
}
if ($phase === 'record-draft') {
    $id = (int) getenv('WPI_SCRIPT_DRAFT');
    $post = get_post($id);
    $user = get_user_by('login', 'wpi-native-script-review');
    if (!$post || !$user || $post->post_type !== 'product' || !in_array($post->post_status, array('auto-draft', 'draft'), true) || (int) $post->post_author !== (int) $user->ID) { throw new RuntimeException('Owned browser draft required'); }
    $fixture['drafts'][] = $id;
    update_option($option, $fixture, false);
    echo "Owned browser draft recorded\n";
    return;
}
if ($phase === 'cleanup') {
    $ids = array_merge(array_values($fixture['children']), array_values($fixture['parents']), array($fixture['order']), $fixture['drafts']);
    foreach ($ids as $id) { if ($id < 1) { continue; } wp_delete_post($id, true); if (get_post($id)) { throw new RuntimeException('Post cleanup failed'); } }
    $GLOBALS['wpdb']->delete($GLOBALS['wpdb']->prefix . 'woocommerce_attribute_taxonomies', array('attribute_id' => $fixture['attribute']), array('%d'));
    delete_transient('wc_attribute_taxonomies');
    if ($fixture['notice'] === null) { delete_option('hyyan-wpi-flash-messages'); } else { update_option('hyyan-wpi-flash-messages', $fixture['notice']); }
    if (get_option('hyyan-wpi-flash-messages', null) !== $fixture['notice']) { throw new RuntimeException('Notice restoration failed'); }
    if (array_key_exists('features', $fixture)) {
        if ($fixture['features'] === false) { delete_option('wpi-features'); } else { update_option('wpi-features', $fixture['features']); }
        if (get_option('wpi-features') !== $fixture['features']) { throw new RuntimeException('Feature option restoration failed'); }
    }
    delete_option($option);
    echo "Owned products, variation children, order, browser drafts and attribute removed, notice restored\n";
    return;
}
throw new RuntimeException('Unknown script phase');
