<?php
if ($GLOBALS['wpdb']->prefix !== 'wpi_upgrade_') {
    throw new RuntimeException('Owned upgrade store required');
}
$data = get_option('wpi_upgrade_fixture');
$result = array('version' => Hyyan\WPI\Plugin::getVersion(), 'wordpress' => $GLOBALS['wp_version'], 'woocommerce' => WC_VERSION, 'polylang' => POLYLANG_VERSION, 'active' => is_plugin_active('woo-poly-integration/__init__.php'), 'checks' => array());
if ($result['version'] !== '1.5.1' || !$result['active']) { throw new RuntimeException('Upgrade inactive or wrong version'); }
foreach (array('features' => 'wpi-features', 'metas' => 'wpi-metas-list', 'polylang' => 'polylang') as $field => $option) {
    $result['checks'][$field] = get_option($option) === $data[$field];
    if (!$result['checks'][$field]) { throw new RuntimeException('Changed upgrade settings ' . $field); }
}
foreach ($data['snapshots'] as $id => $before) {
    $p = wc_get_product($id);
    $after = array('type' => $p->get_type(), 'price' => $p->get_regular_price(), 'stock' => $p->get_stock_quantity(), 'sku' => $p->get_sku(), 'language' => pll_get_post_language($id), 'meta' => get_post_meta($id));
    if ($before !== $after) { throw new RuntimeException('Changed upgrade product ' . $id); }
    $result['checks']['products'][$id] = true;
}
foreach ($data['children']['en'] as $size => $id) {
    $related = array_map('intval', Hyyan\WPI\Product\Variation::getRelatedVariation($id, true));
    sort($related);
    $expected = array($id, $data['children']['de'][$size]);
    sort($expected);
    if ($related !== $expected) { throw new RuntimeException('Changed variation relationship'); }
}
$result['checks']['variation_relationships'] = true;
$result['checks']['minimum_woocommerce'] = Hyyan\WPI\Plugin::WOOCOMMERCE_VERSION;
echo wp_json_encode($result);
