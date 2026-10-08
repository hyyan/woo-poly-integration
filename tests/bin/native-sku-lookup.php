<?php
if (!in_array($GLOBALS['wpdb']->prefix, array('wpi_package_', 'wpi_upgrade_'), true)) { throw new RuntimeException('Owned lab required'); }
$ids = array(); $checks = array(); $oldLanguage = PLL()->curlang; $oldStatus = isset($GLOBALS['wp_post_statuses']['wpi_sku_hidden']) ? $GLOBALS['wp_post_statuses']['wpi_sku_hidden'] : null;
register_post_status('wpi_sku_hidden', array('exclude_from_search' => true));
PLL()->curlang = PLL()->model->get_language('de');
$handler = (new ReflectionClass('Hyyan\\WPI\\Product\\Meta'))->newInstanceWithoutConstructor();
$create = function ($status, $type, $language, $sku, $parent = 0) use (&$ids) {
    $id = wp_insert_post(array('post_type' => $type, 'post_status' => $status, 'post_title' => 'Native SKU parity fixture', 'post_parent' => $parent,
        'post_date' => date('Y-m-d H:i:s', time() + ($status === 'future' ? 86400 : -3600))));
    if (!$id || is_wp_error($id)) { throw new RuntimeException('Fixture creation failed'); }
    $ids[] = $id; pll_set_post_language($id, $language); update_post_meta($id, '_sku', wp_slash($sku)); return $id;
};
try {
    foreach (array('0', "native-parity'sku", 'native-parity\\sku') as $sku) {
        $source = $create('publish', 'product', 'en', $sku);
        foreach (array('publish', 'draft', 'pending', 'future', 'private', 'inherit', 'wpi_sku_hidden', 'wpi_unregistered', 'trash', 'auto-draft') as $status) {
            foreach (array('product', 'product_variation') as $type) {
                $target = $create($status, $type, 'en', $sku, $type === 'product_variation' ? $source : 0);
                $baseline = $GLOBALS['wpdb']->get_col($GLOBALS['wpdb']->prepare("SELECT p.ID FROM {$GLOBALS['wpdb']->posts} p LEFT JOIN {$GLOBALS['wpdb']->postmeta} m ON p.ID=m.post_id WHERE p.post_type IN ('product','product_variation') AND p.post_status NOT IN ('trash','auto-draft') AND m.meta_key='_sku' AND m.meta_value=%s AND m.post_id<>%d", $sku, $source));
                $expected = false;
                foreach ($baseline as $id) { if (pll_get_post_language($id) === pll_get_post_language($source)) { $expected = true; } }
                $actual = $handler->suppressInvalidDuplicatedSKUErrorMsg(false, $source, $sku);
                $checks[] = array('sku' => $sku, 'status' => get_post_status($target), 'type' => $type, 'expected' => $expected, 'actual' => $actual, 'passed' => $expected === $actual);
                wp_delete_post($target, true);
            }
        }
        wp_delete_post($source, true);
    }
} finally {
    foreach (array_reverse($ids) as $id) { if (get_post($id)) { wp_delete_post($id, true); } }
    PLL()->curlang = $oldLanguage;
    if ($oldStatus === null) { unset($GLOBALS['wp_post_statuses']['wpi_sku_hidden']); } else { $GLOBALS['wp_post_statuses']['wpi_sku_hidden'] = $oldStatus; }
}
foreach ($ids as $id) { if (get_post($id)) { throw new RuntimeException('Owned cleanup failed'); } }
$passed = count(array_filter($checks, function ($check) { return $check['passed']; }));
echo wp_json_encode(array('wordpress' => get_bloginfo('version'), 'woocommerce' => WC_VERSION, 'passed' => $passed, 'total' => count($checks), 'owned_posts_removed' => count($ids), 'checks' => $checks)), "\n";
if ($passed !== count($checks)) { throw new RuntimeException('Native SKU parity mismatch'); }
