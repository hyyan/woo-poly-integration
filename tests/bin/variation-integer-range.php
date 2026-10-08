<?php
if (!in_array($GLOBALS['wpdb']->prefix, array('wpi_package_', 'wpi_upgrade_'), true)) { throw new RuntimeException('Owned range store required'); }
$fixture = get_option('wpi_relationship_consumers_fixture');
if (!$fixture) { throw new RuntimeException('Owned range fixture required'); }
$key = Hyyan\WPI\Product\Variation::DUPLICATE_KEY;
$source = $fixture['parents']['en'];
$target = $fixture['simple'];
$maximumTarget = $fixture['parents']['de'];
$original = array();
foreach (array($source, $target, $maximumTarget) as $id) { $original[$id] = get_post_meta($id, $key); }
$request = $_GET;
$calls = 0;
$observer = static function ($check, $id, $metaKey, $value) use ($key, &$calls) {
    if ($metaKey === $key) { ++$calls; return false; }
    return $check;
};
try {
    update_post_meta($source, $key, (string) PHP_INT_MAX . '0');
    update_post_meta($target, $key, $source);
    update_post_meta($maximumTarget, $key, (string) PHP_INT_MAX);
    if (Hyyan\WPI\Product\Variation::getRelatedVariation((string) PHP_INT_MAX . '0', true) !== false) { throw new RuntimeException('Overflow input accepted'); }
    $ids = Hyyan\WPI\Product\Variation::getRelatedVariation($source, true);
    if (!in_array((string) $target, $ids, true) || in_array((string) $maximumTarget, $ids, true)) { throw new RuntimeException('Overflow metadata aliased a source'); }
    $maximumIds = Hyyan\WPI\Product\Variation::getRelatedVariation('000' . PHP_INT_MAX, true);
    if (!in_array((string) $maximumTarget, $maximumIds, true)) { throw new RuntimeException('Padded maximum valid input rejected'); }
    $_GET['product_id'] = (string) PHP_INT_MAX . '0';
    if (Hyyan\WPI\Utilities::getRequestInteger('product_id') !== 0) { throw new RuntimeException('Overflow request accepted'); }
    $_GET['product_id'] = '000' . PHP_INT_MAX;
    if (Hyyan\WPI\Utilities::getRequestInteger('product_id') !== PHP_INT_MAX) { throw new RuntimeException('Padded maximum request rejected'); }
    add_filter('update_post_metadata', $observer, PHP_INT_MAX, 4);
    $handler = (new ReflectionClass(Hyyan\WPI\Product\Variation::class))->newInstanceWithoutConstructor();
    $handler->addDuplicateMeta((string) PHP_INT_MAX . '0');
    if ($calls !== 0) { throw new RuntimeException('Overflow reached metadata writer'); }
    echo wp_json_encode(array('overflow_input_rejected' => true, 'overflow_source_ignored' => true, 'padded_maximum_retained' => true, 'request_range_verified' => true, 'overflow_metadata_calls' => $calls)), "\n";
} finally {
    remove_filter('update_post_metadata', $observer, PHP_INT_MAX);
    $_GET = $request;
    foreach ($original as $id => $values) {
        delete_post_meta($id, $key);
        foreach ($values as $value) { add_post_meta($id, $key, $value); }
        if (get_post_meta($id, $key) !== $values) { throw new RuntimeException('Range fixture metadata restoration failed'); }
    }
}
