<?php
if ($GLOBALS['wpdb']->prefix !== 'wpi_upgrade_') { throw new RuntimeException('Owned stock matrix store required'); }
$phase = getenv('WPI_STOCK_MATRIX_PHASE');
if (!in_array($phase, array('prepare', 'capture', 'verify', 'cleanup'), true)) { throw new RuntimeException('Unknown stock matrix phase'); }
$option = 'wpi_published_stock_matrix_fixture';
$fixture = get_option($option);
if ($phase === 'prepare') {
    if ($fixture || Hyyan\WPI\Plugin::getVersion() !== '1.5.0') { throw new RuntimeException('Clean published stock matrix required'); }
    putenv('WPI_DATA_PHASE=prepare');
    (function () { require __DIR__ . '/published-cart-upgrade.php'; })();
    $data = get_option('wpi_published_data_fixture');
    $fixture = array('products' => array());
    update_option($option, $fixture, false);
    foreach (array('global', 'wildcard', 'parent', 'backorder') as $mode) {
        foreach (array('en', 'de') as $lang) {
            $term = get_term($data['terms']['pa_upgrade_colour'][$lang], 'pa_upgrade_colour');
            if (!$term || is_wp_error($term)) { throw new RuntimeException('Owned global attribute term missing'); }
            $parent = new WC_Product_Variable();
            $parent->set_name('Published stock matrix ' . $mode . ' ' . $lang);
            $parent->set_status('publish');
            $attribute = new WC_Product_Attribute();
            $attribute->set_id($data['attribute']);
            $attribute->set_name('pa_upgrade_colour');
            $attribute->set_options(array($term->term_id));
            $attribute->set_visible(true);
            $attribute->set_variation(true);
            $parent->set_attributes(array($attribute));
            $parent->set_default_attributes(array('pa_upgrade_colour' => $term->slug));
            $parent->set_manage_stock($mode === 'parent');
            if ($mode === 'parent') { $parent->set_stock_quantity(5); }
            $parent->save();
            pll_set_post_language($parent->get_id(), $lang);
            $fixture['products'][$mode][$lang] = array('parent' => $parent->get_id());
            update_option($option, $fixture, false);
            $child = new WC_Product_Variation();
            $child->set_parent_id($parent->get_id());
            $child->set_status('publish');
            $child->set_regular_price('18');
            $child->set_virtual(true);
            $child->set_attributes(array('pa_upgrade_colour' => $mode === 'wildcard' ? '' : $term->slug));
            $child->set_manage_stock($mode !== 'parent');
            if ($mode !== 'parent') { $child->set_stock_quantity($mode === 'backorder' ? 0 : 8); }
            $child->set_backorders($mode === 'backorder' ? 'notify' : 'no');
            $child->save();
            pll_set_post_language($child->get_id(), $lang);
            $fixture['products'][$mode][$lang]['child'] = $child->get_id();
            update_option($option, $fixture, false);
            WC_Product_Variable::sync($parent->get_id());
        }
        pll_save_post_translations(array('en' => $fixture['products'][$mode]['en']['parent'], 'de' => $fixture['products'][$mode]['de']['parent']));
        foreach (array('en', 'de') as $lang) {
            update_post_meta($fixture['products'][$mode][$lang]['child'], Hyyan\WPI\Product\Variation::DUPLICATE_KEY,
                ($lang === 'de' ? '000' : '') . $fixture['products'][$mode]['en']['child']);
        }
    }
    $urls = array();
    foreach ($fixture['products'] as $mode => $products) { foreach ($products as $lang => $ids) { $urls[$mode][$lang] = get_permalink($ids['parent']); } }
    echo wp_json_encode(array('version' => Hyyan\WPI\Plugin::getVersion(), 'products' => $fixture['products'], 'urls' => $urls)), "\n";
    return;
}
if (!$fixture) { throw new RuntimeException('Owned stock matrix missing'); }
if ($phase === 'cleanup') {
    $removed = 0;
    foreach ($fixture['products'] as $products) {
        foreach ($products as $ids) {
            foreach (array($ids['child'], $ids['parent']) as $id) {
                wp_delete_post($id, true);
                if (get_post($id)) { throw new RuntimeException('Owned stock matrix product remained'); }
                ++$removed;
            }
        }
    }
    delete_option($option);
    echo wp_json_encode(array('removed_stock_matrix_products' => $removed, 'stock_matrix_fixture_removed' => !get_option($option))), "\n";
    putenv('WPI_DATA_PHASE=cleanup');
    (function () { require __DIR__ . '/published-cart-upgrade.php'; })();
    return;
}
$version = Hyyan\WPI\Plugin::getVersion();
if (($phase === 'capture' && $version !== '1.5.0') || ($phase === 'verify' && $version !== '1.5.1')) { throw new RuntimeException('Stock matrix version mismatch'); }
$snapshot = array();
foreach ($fixture['products'] as $products) {
    foreach ($products as $ids) {
        foreach ($ids as $id) { $snapshot[$id] = array('post' => (array) get_post($id), 'meta' => get_post_meta($id), 'language' => pll_get_post_language($id)); }
    }
}
if ($phase === 'capture') { $fixture['snapshot'] = $snapshot; }
elseif ($snapshot !== $fixture['snapshot']) { throw new RuntimeException('Published stock matrix data changed'); }
putenv('WPI_DATA_PHASE=' . $phase);
(function () { require __DIR__ . '/published-cart-upgrade.php'; })();
$data = get_option('wpi_published_data_fixture');
$language = PLL()->curlang;
$actual = array();
try {
    foreach ($fixture['products'] as $mode => $products) {
        foreach ($products as $lang => $ids) {
            Hyyan\WPI\Utilities::switch_pll_locale($lang === 'en' ? 'en_US' : 'de_DE');
            $term = get_term($data['terms']['pa_upgrade_colour'][$lang], 'pa_upgrade_colour');
            $attributes = array('attribute_pa_upgrade_colour' => $term->slug);
            WC()->session = new WC_Session_Handler();
            WC()->cart = new WC_Cart();
            WC()->cart->get_cart_from_session();
            wc_clear_notices();
            $key = WC()->cart->add_to_cart($ids['parent'], 2, $ids['child'], $attributes);
            if (!$key) { throw new RuntimeException('Native stock matrix addition failed ' . $mode . ' ' . $lang); }
            WC()->cart->calculate_totals();
            $items = WC()->cart->get_cart();
            $item = reset($items);
            $child = wc_get_product($ids['child']);
            $row = array('parent' => $item['product_id'], 'variation' => $item['variation_id'], 'quantity' => $item['quantity'],
                'attributes' => $item['variation'], 'total' => (float) WC()->cart->total,
                'stock_owner' => $child->get_stock_managed_by_id(), 'on_backorder' => $child->is_on_backorder(2),
                'parent_stock' => wc_get_product($ids['parent'])->get_stock_quantity('edit'), 'child_stock' => $child->get_stock_quantity('edit'));
            if (count($items) !== 1 || $row['parent'] !== $ids['parent'] || $row['variation'] !== $ids['child'] ||
                $row['quantity'] !== 2 || $row['attributes'] !== $attributes || $row['total'] !== 36.0 ||
                $row['stock_owner'] !== ($mode === 'parent' ? $ids['parent'] : $ids['child']) ||
                $row['on_backorder'] !== ($mode === 'backorder')) { throw new RuntimeException('Native stock matrix mismatch ' . wp_json_encode($row)); }
            WC()->cart->empty_cart();
            wc_clear_notices();
            $largeQuantity = $mode === 'parent' ? 6 : 9;
            $large = WC()->cart->add_to_cart($ids['parent'], $largeQuantity, $ids['child'], $attributes);
            $row['excess_quantity_accepted'] = (bool) $large;
            if ((bool) $large !== ($mode === 'backorder')) { throw new RuntimeException('Native stock limit changed ' . $mode . ' ' . $lang); }
            WC()->cart->empty_cart();
            wc_clear_notices();
            $actual[$mode][$lang] = $row;
        }
    }
} finally {
    WC()->cart->empty_cart();
    wc_clear_notices();
    PLL()->curlang = $language;
}
if ($phase === 'capture') { $fixture['baseline'] = $actual; update_option($option, $fixture, false); }
elseif ($actual !== $fixture['baseline']) { throw new RuntimeException('Native stock matrix upgrade behavior changed'); }
echo wp_json_encode(array('version' => $version, 'stock_matrix_cases' => 8, 'stock_limit_cases' => 8, 'results' => $actual, 'matches_published' => $phase === 'verify')), "\n";
