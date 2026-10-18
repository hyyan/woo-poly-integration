<?php
global $wpdb;
if ($GLOBALS['wpdb']->prefix !== 'wpi_upgrade_') { throw new RuntimeException('Owned upgrade store required'); }
wp_set_current_user(1);
$option = 'wpi_published_data_fixture';
$phase = getenv('WPI_DATA_PHASE');
$data = get_option($option);
if ($phase === 'prepare') {
    if ($data || Hyyan\WPI\Plugin::getVersion() !== '1.5.0') { throw new RuntimeException('Clean published baseline required'); }
    $data = array('products' => array(), 'terms' => array(), 'options' => array());
    $data['options']['hyyan-wpi-flash-messages'] = get_option('hyyan-wpi-flash-messages', null);
    update_option($option, $data, false);
    $wpdb->insert($wpdb->prefix . 'woocommerce_attribute_taxonomies', array('attribute_name' => 'upgrade_colour', 'attribute_label' => 'Upgrade colour', 'attribute_type' => 'select', 'attribute_orderby' => 'menu_order', 'attribute_public' => 1));
    $attribute = (int) $wpdb->insert_id;
    if (!$attribute) { throw new RuntimeException('Attribute fixture creation failed'); }
    delete_transient('wc_attribute_taxonomies');
    $data['attribute'] = $attribute;
    update_option($option, $data, false);
    register_taxonomy('pa_upgrade_colour', array('product'), array('hierarchical' => false));
    foreach (array('en', 'de') as $lang) {
        foreach (array('product_cat', 'product_tag', 'pa_upgrade_colour') as $tax) {
            $term = wp_insert_term('Upgrade ' . $tax . ' ' . $lang, $tax);
            if (is_wp_error($term)) { throw new RuntimeException($term->get_error_message()); }
            $data['terms'][$tax][$lang] = $term['term_id'];
            pll_set_term_language($term['term_id'], $lang);
            update_option($option, $data, false);
        }
    }
    foreach ($data['terms'] as $tax => $terms) { pll_save_term_translations($terms); }
    $shipping = wp_insert_term('Published upgrade shipping', 'product_shipping_class');
    if (is_wp_error($shipping)) { throw new RuntimeException($shipping->get_error_message()); }
    $data['shipping'] = $shipping['term_id'];
    update_option($option, $data, false);
    $upload = wp_upload_bits('published-upgrade.png', null, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+a8xoAAAAASUVORK5CYII='));
    if ($upload['error']) { throw new RuntimeException($upload['error']); }
    $data['image_file'] = $upload['file'];
    $data['image'] = wp_insert_attachment(array('post_title' => 'Published upgrade image', 'post_mime_type' => 'image/png', 'post_status' => 'inherit'), $upload['file']);
    update_post_meta($data['image'], '_wp_attachment_image_alt', 'Published upgrade image alt');
    update_option($option, $data, false);
    foreach (array('en', 'de') as $lang) {
        $p = new WC_Product_Simple();
        $p->set_name('Published data upgrade ' . $lang);
        $p->set_status('publish');
        $p->set_description('Independent description ' . $lang);
        $p->set_short_description('Independent excerpt ' . $lang);
        $p->set_regular_price('19');
        $p->set_manage_stock(true);
        $p->set_stock_quantity(17);
        $p->set_weight('2.5');
        $p->set_shipping_class_id($data['shipping']);
        $p->set_category_ids(array($data['terms']['product_cat'][$lang]));
        $p->set_tag_ids(array($data['terms']['product_tag'][$lang]));
        $p->set_image_id($data['image']);
        $p->set_gallery_image_ids(array($data['image']));
        $a = new WC_Product_Attribute();
        $a->set_id($attribute);
        $a->set_name('pa_upgrade_colour');
        $a->set_options(array($data['terms']['pa_upgrade_colour'][$lang]));
        $a->set_visible(true);
        $p->set_attributes(array($a));
        $p->update_meta_data('_published_upgrade_custom', 'Retained independent ' . $lang);
        $p->save();
        pll_set_post_language($p->get_id(), $lang);
        $data['products'][$lang] = $p->get_id();
        update_option($option, $data, false);
    }
    pll_save_post_translations($data['products']);
    foreach ($data['products'] as $lang => $id) {
        foreach (array('product_cat', 'product_tag') as $tax) {
            wp_set_object_terms($id, array((int) $data['terms'][$tax][$lang]), $tax);
            $assigned = wp_get_object_terms($id, $tax, array('fields' => 'ids'));
            if (is_wp_error($assigned) || array_map('intval', $assigned) !== array((int) $data['terms'][$tax][$lang])) {
                throw new RuntimeException('Translated fixture assignment failed');
            }
        }
    }
    $coupon = new WC_Coupon();
    $coupon->set_code('published-data-upgrade');
    $coupon->set_discount_type('fixed_product');
    $coupon->set_amount('3');
    $coupon->set_product_ids(array($data['products']['en']));
    $coupon->set_product_categories(array($data['terms']['product_cat']['en']));
    $coupon->set_usage_limit(4);
    $coupon->set_minimum_amount('10');
    $coupon->save();
    $data['coupon'] = $coupon->get_id();
    update_option($option, $data, false);
    echo wp_json_encode(array('prepared_version' => Hyyan\WPI\Plugin::getVersion(), 'products' => $data['products'], 'coupon' => $data['coupon'])), "\n";
    return;
}
if (!$data) { throw new RuntimeException('Owned fixture missing'); }
if ($phase === 'cleanup') {
    foreach ($data['products'] as $id) { wp_delete_post($id, true); }
    wp_delete_post($data['coupon'], true);
    wp_delete_attachment($data['image'], true);
    foreach ($data['terms'] as $tax => $terms) { foreach ($terms as $id) { wp_delete_term($id, $tax); } }
    wp_delete_term($data['shipping'], 'product_shipping_class');
    $wpdb->delete($wpdb->prefix . 'woocommerce_attribute_taxonomies', array('attribute_id' => $data['attribute']), array('%d'));
    delete_transient('wc_attribute_taxonomies');
    foreach ($data['options'] as $key => $value) { if ($value === null) { delete_option($key); } else { update_option($key, $value); } }
    foreach ($data['options'] as $key => $value) { if (get_option($key, null) !== $value) { throw new RuntimeException('Option restoration failed'); } }
    foreach (array_merge(array_values($data['products']), array($data['image'], $data['coupon'])) as $id) { if (get_post($id)) { throw new RuntimeException('Owned post remained'); } }
    foreach ($data['terms'] as $tax => $terms) { foreach ($terms as $id) { if (term_exists($id, $tax)) { throw new RuntimeException('Owned term remained'); } } }
    if (file_exists($data['image_file']) || term_exists($data['shipping'], 'product_shipping_class') || wc_attribute_taxonomy_id_by_name('upgrade_colour')) { throw new RuntimeException('Owned resource remained'); }
    delete_option($option);
    echo wp_json_encode(array('removed_products' => 2, 'removed_terms' => 7, 'removed_attribute' => true, 'removed_image' => true, 'removed_coupon' => true, 'options_restored' => true)), "\n";
    return;
}
$actual = array('products' => array(), 'terms' => array(), 'settings' => array());
foreach ($data['products'] as $lang => $id) {
    $p = wc_get_product($id);
    if (!$p) { throw new RuntimeException('Missing product'); }
    $actual['products'][$lang] = array('post' => (array) get_post($id), 'meta' => get_post_meta($id), 'language' => pll_get_post_language($id), 'translations' => pll_get_post_translations($id), 'price' => $p->get_price('edit'), 'stock' => $p->get_stock_quantity('edit'), 'shipping' => $p->get_shipping_class_id('edit'), 'image' => $p->get_image_id('edit'), 'gallery' => $p->get_gallery_image_ids('edit'), 'attributes' => array());
    foreach ($p->get_attributes() as $key => $a) { $actual['products'][$lang]['attributes'][$key] = $a->get_data(); }
    foreach (array('product_cat', 'product_tag', 'pa_upgrade_colour', 'product_shipping_class') as $tax) { $actual['products'][$lang]['terms'][$tax] = wp_get_object_terms($id, $tax, array('fields' => 'ids')); }
}
foreach ($data['terms'] as $tax => $terms) {
    foreach ($terms as $lang => $id) { $actual['terms'][$tax][$lang] = array('term' => (array) get_term($id, $tax), 'meta' => get_term_meta($id), 'language' => pll_get_term_language($id), 'translations' => pll_get_term_translations($id)); }
}
$actual['shipping'] = (array) get_term($data['shipping'], 'product_shipping_class');
$actual['attribute'] = null;
foreach (wc_get_attribute_taxonomies() as $attribute) { if ((int) $attribute->attribute_id === (int) $data['attribute']) { $actual['attribute'] = (array) $attribute; } }
if (!$actual['attribute']) { throw new RuntimeException('Missing attribute'); }
$actual['image'] = array('post' => (array) get_post($data['image']), 'meta' => get_post_meta($data['image']), 'sha256' => hash_file('sha256', $data['image_file']));
$coupon = new WC_Coupon($data['coupon']);
$actual['coupon'] = array('post' => (array) get_post($data['coupon']), 'meta' => get_post_meta($data['coupon']), 'code' => $coupon->get_code(), 'amount' => $coupon->get_amount(), 'products' => $coupon->get_product_ids(), 'categories' => $coupon->get_product_categories(), 'limit' => $coupon->get_usage_limit());
foreach (array('wpi-features', 'wpi-metas-list', 'polylang') as $key) { $actual['settings'][$key] = get_option($key); }
if ($phase === 'capture') {
    if (Hyyan\WPI\Plugin::getVersion() !== '1.5.0') { throw new RuntimeException('Published capture required'); }
    $data['baseline'] = $actual;
    update_option($option, $data, false);
    echo wp_json_encode(array('version' => '1.5.0', 'snapshot' => $actual)), "\n";
} elseif ($phase === 'verify') {
    if (Hyyan\WPI\Plugin::getVersion() !== '1.5.1') { throw new RuntimeException('Candidate verification required'); }
    if ($actual !== $data['baseline']) { throw new RuntimeException('Stored upgrade data changed ' . wp_json_encode($actual)); }
    $urls = array();
    foreach ($data['products'] as $lang => $id) { $urls[$lang] = get_permalink($id); }
    echo wp_json_encode(array('version' => '1.5.1', 'wordpress' => $GLOBALS['wp_version'], 'woocommerce' => WC_VERSION, 'polylang' => POLYLANG_VERSION, 'exact_snapshot_retained' => true, 'groups' => array_keys($actual), 'urls' => $urls, 'products' => $data['products'])), "\n";
} else { throw new RuntimeException('Unknown fixture phase'); }
