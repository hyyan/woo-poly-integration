<?php
if (!in_array($GLOBALS['wpdb']->prefix, array('wpi_package_', 'wpi_upgrade_'), true)) {
    throw new RuntimeException('Owned store required');
}
$phase = getenv('WPI_NAVIGATION_PHASE');
$option = 'wpi_public_navigation_fixture';
$fixture = get_option($option);
if ($phase === 'prepare') {
    if ($fixture) { throw new RuntimeException('Clean navigation fixture required'); }
    $fixture = array('sidebars' => get_option('sidebars_widgets'), 'widget' => get_option('widget_woocommerce_layered_nav'), 'attribute' => 0, 'terms' => array(), 'products' => array());
    update_option($option, $fixture, false);
    global $wpdb;
    $wpdb->insert($wpdb->prefix . 'woocommerce_attribute_taxonomies', array('attribute_name' => 'wpinav', 'attribute_label' => 'Navigation colour', 'attribute_type' => 'select', 'attribute_orderby' => 'menu_order', 'attribute_public' => 1));
    $fixture['attribute'] = (int) $wpdb->insert_id;
    if (!$fixture['attribute']) { throw new RuntimeException('Attribute creation failed'); }
    update_option($option, $fixture, false);
    delete_transient('wc_attribute_taxonomies');
    register_taxonomy('pa_wpinav', array('product'), array('hierarchical' => false));
    foreach (array('match', 'other') as $kind) {
        foreach (array('en', 'de') as $lang) {
            $term = wp_insert_term('Navigation ' . $kind . ' ' . $lang, 'pa_wpinav', array('slug' => 'nav-' . $kind . '-' . $lang));
            if (is_wp_error($term)) { throw new RuntimeException($term->get_error_message()); }
            $fixture['terms'][$kind][$lang] = $term['term_id'];
            pll_set_term_language($term['term_id'], $lang);
            update_option($option, $fixture, false);
        }
        pll_save_term_translations($fixture['terms'][$kind]);
    }
    foreach (array('match', 'other') as $kind) {
        foreach (array('en', 'de') as $lang) {
            $product = new WC_Product_Simple();
            $product->set_name('Public navigation ' . $kind . ' ' . $lang);
            $product->set_status('publish');
            $product->set_regular_price('12');
            $product->set_virtual(true);
            $attribute = new WC_Product_Attribute();
            $attribute->set_id($fixture['attribute']);
            $attribute->set_name('pa_wpinav');
            $attribute->set_options(array($fixture['terms'][$kind][$lang]));
            $attribute->set_visible(true);
            $product->set_attributes(array($attribute));
            $product->save();
            pll_set_post_language($product->get_id(), $lang);
            $fixture['products'][$kind][$lang] = $product->get_id();
            update_option($option, $fixture, false);
        }
        pll_save_post_translations($fixture['products'][$kind]);
    }
    foreach ($fixture['products'] as $kind => $products) {
        foreach ($products as $lang => $id) { wp_set_object_terms($id, array((int) $fixture['terms'][$kind][$lang]), 'pa_wpinav'); }
    }
    $sidebars = is_array($fixture['sidebars']) ? $fixture['sidebars'] : array();
    $sidebars['sidebar-1'][] = 'woocommerce_layered_nav-99';
    update_option('sidebars_widgets', $sidebars);
    $widgets = is_array($fixture['widget']) ? $fixture['widget'] : array();
    $widgets[99] = array('title' => 'Navigation colour', 'attribute' => 'wpinav', 'display_type' => 'list', 'query_type' => 'and');
    $widgets['_multiwidget'] = 1;
    update_option('widget_woocommerce_layered_nav', $widgets);
    echo wp_json_encode(array('products' => $fixture['products'], 'terms' => $fixture['terms'], 'url' => home_url('/'))), "\n";
    return;
}
if (!$fixture) { throw new RuntimeException('Navigation fixture required'); }
if ($phase === 'verify') {
    $language = PLL()->curlang;
    $get = $_GET;
    try {
        PLL()->curlang = PLL()->model->get_language('de');
        $query = array('filter_wpinav' => 'nav-match-en');
        $_GET = $query;
        if (!in_array('filter_wpinav', apply_filters('query_vars', array()), true)) { throw new RuntimeException('Public query variable missing'); }
        $query = apply_filters('request', $query);
        if ($query['filter_wpinav'] !== 'nav-match-de' || $_GET['filter_wpinav'] !== 'nav-match-de') { throw new RuntimeException('Public term translation failed'); }
        $chosen = WC_Query::get_layered_nav_chosen_attributes();
        if ($chosen['pa_wpinav']['terms'] !== array('nav-match-de')) { throw new RuntimeException('Native chosen terms differ'); }
        echo wp_json_encode(array('query' => $query, 'chosen' => $chosen, 'active_widget' => (bool) is_active_widget(false, false, 'woocommerce_layered_nav', true))), "\n";
    } finally {
        PLL()->curlang = $language;
        $_GET = $get;
    }
    return;
}
if ($phase === 'cleanup') {
    foreach ($fixture['products'] as $products) {
        foreach ($products as $id) { wp_delete_post($id, true); if (get_post($id)) { throw new RuntimeException('Product cleanup failed'); } }
    }
    foreach ($fixture['terms'] as $terms) {
        foreach ($terms as $id) { wp_delete_term($id, 'pa_wpinav'); }
    }
    $GLOBALS['wpdb']->delete($GLOBALS['wpdb']->prefix . 'woocommerce_attribute_taxonomies', array('attribute_id' => $fixture['attribute']), array('%d'));
    delete_transient('wc_attribute_taxonomies');
    foreach (array('sidebars_widgets' => 'sidebars', 'widget_woocommerce_layered_nav' => 'widget') as $key => $saved) {
        if ($fixture[$saved] === false) { delete_option($key); } else { update_option($key, $fixture[$saved]); }
        if (get_option($key) !== $fixture[$saved]) { throw new RuntimeException('Widget option restoration failed'); }
    }
    delete_option($option);
    echo "Four products, four terms, attribute and widget options cleaned\n";
    return;
}
throw new RuntimeException('Unknown navigation phase');
