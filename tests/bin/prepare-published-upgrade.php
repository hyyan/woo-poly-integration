<?php
if ($GLOBALS['wpdb']->prefix !== 'wpi_upgrade_' || get_option('wpi_upgrade_fixture')) {
    throw new RuntimeException('Fresh upgrade fixture required');
}
wp_set_current_user(1);
$data = array('parents' => array(), 'children' => array(), 'simple' => array());
foreach (array('en', 'de') as $lang) {
    $p = new WC_Product_Variable();
    $p->set_name('Published upgrade variable ' . $lang);
    $p->set_status('publish');
    $a = new WC_Product_Attribute();
    $a->set_name('Size');
    $a->set_options(array('small', 'large'));
    $a->set_variation(true);
    $p->set_attributes(array($a));
    $p->set_default_attributes(array('size' => 'small'));
    $p->set_weight('5');
    $p->update_meta_data('_upgrade_custom', 'retained value');
    $p->save();
    pll_set_post_language($p->get_id(), $lang);
    $data['parents'][$lang] = $p->get_id();
    foreach (array('small' => '12', 'large' => '14') as $size => $price) {
        $v = new WC_Product_Variation();
        $v->set_parent_id($p->get_id());
        $v->set_status('publish');
        $v->set_regular_price($price);
        $v->set_attributes(array('size' => $size));
        $v->set_manage_stock(true);
        $v->set_stock_quantity(20);
        $v->save();
        pll_set_post_language($v->get_id(), $lang);
        $data['children'][$lang][$size] = $v->get_id();
    }
    WC_Product_Variable::sync($p->get_id());
    $p = new WC_Product_Simple();
    $p->set_name('Published upgrade simple ' . $lang);
    $p->set_status('publish');
    $p->set_regular_price('10');
    $p->set_sku('upgrade-' . $lang);
    $p->save();
    pll_set_post_language($p->get_id(), $lang);
    $data['simple'][$lang] = $p->get_id();
}
pll_save_post_translations($data['parents']);
pll_save_post_translations($data['simple']);
foreach (array('small', 'large') as $size) {
    foreach (array('en', 'de') as $lang) {
        update_post_meta($data['children'][$lang][$size], Hyyan\WPI\Product\Variation::DUPLICATE_KEY, $data['children']['en'][$size]);
    }
}
$features = get_option('wpi-features');
$features['fields-locker'] = 'off';
$features['importsync'] = 'off';
update_option('wpi-features', $features);
$data['features'] = get_option('wpi-features');
$data['metas'] = get_option('wpi-metas-list');
$data['polylang'] = get_option('polylang');
$data['snapshots'] = array();
foreach (array_merge(array_values($data['parents']), array_values($data['simple']), array_values($data['children']['en']), array_values($data['children']['de'])) as $id) {
    $p = wc_get_product($id);
    $data['snapshots'][$id] = array('type' => $p->get_type(), 'price' => $p->get_regular_price(), 'stock' => $p->get_stock_quantity(), 'sku' => $p->get_sku(), 'language' => pll_get_post_language($id), 'meta' => get_post_meta($id));
}
update_option('wpi_upgrade_fixture', $data, false);
echo wp_json_encode(array('version' => Hyyan\WPI\Plugin::getVersion(), 'woocommerce' => WC_VERSION, 'fixture' => $data));
