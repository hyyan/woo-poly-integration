<?php
if (!in_array($GLOBALS['wpdb']->prefix, array('wpi_package_', 'wpi_upgrade_'), true)) { throw new RuntimeException('Owned invalid stock store required'); }
$phase = getenv('WPI_INVALID_STOCK_PHASE');
$fixture = get_option('wpi_relationship_consumers_fixture');
if (!$fixture) { throw new RuntimeException('Owned relationship fixture required'); }
$option = 'wpi_invalid_stock_order_fixture';
if ($phase === 'cleanup') {
    $owned = get_option($option);
    if ($owned) {
        $order = wc_get_order($owned);
        if (!$order || $order->get_billing_email() !== 'invalid-stock@example.invalid') { throw new RuntimeException('Owned stock order mismatch'); }
        $order->delete(true);
        if (wc_get_order($owned)) { throw new RuntimeException('Owned stock order remained'); }
        delete_option($option);
    }
    echo wp_json_encode(array('stock_order_removed' => !get_option($option))), "\n";
    return;
}
$key = Hyyan\WPI\Product\Variation::DUPLICATE_KEY;
$source = $fixture['children']['en'];
$target = $fixture['children']['de'];
if ($phase === 'probe') {
    pll_save_post_translations($fixture['children']);
    if (pll_get_post($target, 'en') !== $source) { throw new RuntimeException('Native variation translation missing'); }
    $actual = array();
    foreach (array('missing' => null, 'malformed' => 'not-a-variation', 'overflow' => (string) PHP_INT_MAX . '0') as $kind => $meta) {
        update_post_meta($source, $key, $source);
        wc_update_product_stock(wc_get_product($target), 9, 'set');
        delete_post_meta($source, $key);
        if ($meta !== null) { update_post_meta($source, $key, $meta); }
        $before = get_post_meta($source, $key);
        wc_update_product_stock(wc_get_product($target), 4, 'set');
        if (wc_get_product($source)->get_stock_quantity() !== 4 || wc_get_product($target)->get_stock_quantity() !== 4 ||
                wc_get_product($fixture['simple'])->get_stock_quantity() !== 7 || get_post_meta($source, $key) !== $before ||
                has_action('woocommerce_variation_set_stock', array('Hyyan\\WPI\\Product\\Stock', 'SyncStockVariation')) !== 10) {
            throw new RuntimeException('Invalid source stock mismatch ' . $kind);
        }
        $targetMeta = get_post_meta($target, $key, true);
        $copy = new Hyyan\WPI\Product\Variation(wc_get_product($fixture['parents']['en']), wc_get_product($fixture['parents']['de']));
        $copy->duplicate();
        $copy->duplicate();
        if (!get_post($target) || get_post_meta($target, $key, true) !== $targetMeta || get_post_meta($source, $key) !== $before) {
            throw new RuntimeException('Repeated invalid source copy mismatch ' . $kind);
        }
        $actual[$kind] = array('source_stock' => 4, 'target_stock' => 4, 'unrelated_stock' => 7, 'relationship_unchanged' => true, 'callback_restored' => true, 'repeated_copy_retained' => true);
    }
    echo wp_json_encode(array('invalid_source_cases' => 3, 'results' => $actual)), "\n";
    return;
}
if ($phase === 'order') {
    if (get_option($option)) { throw new RuntimeException('Clean stock order fixture required'); }
    PLL()->curlang = PLL()->model->get_language('de');
    $order = wc_create_order();
    if (is_wp_error($order)) { throw new RuntimeException($order->get_error_message()); }
    update_option($option, $order->get_id(), false);
    $order->set_customer_id(0);
    $order->set_billing_email('invalid-stock@example.invalid');
    $order->add_product(wc_get_product($target), 1);
    $order->calculate_totals();
    $order->save();
    do_action('woocommerce_checkout_update_order_meta', $order->get_id());
    echo wp_json_encode(array('order' => $order->get_id(), 'url' => method_exists($order, 'get_edit_order_url') ? $order->get_edit_order_url() : admin_url('post.php?post=' . $order->get_id() . '&action=edit'))), "\n";
    return;
}
if ($phase === 'verify-order') {
    $order = wc_get_order(get_option($option));
    $items = $order->get_items();
    $item = reset($items);
    if ($order->get_billing_email() !== 'invalid-stock@example.invalid' || count($items) !== 1 ||
            $item->get_variation_id() !== $target || $item->get_quantity() !== 1 || (int) $item->get_meta('_reduced_stock') !== 1 ||
            wc_get_product($source)->get_stock_quantity() !== 3 || wc_get_product($target)->get_stock_quantity() !== 3 ||
            wc_get_product($fixture['simple'])->get_stock_quantity() !== 7) { throw new RuntimeException('Browser native stock reduction mismatch'); }
    echo wp_json_encode(array('order' => $order->get_id(), 'native_reduced_quantity' => 1, 'source_stock' => 3, 'target_stock' => 3, 'unrelated_stock' => 7)), "\n";
    return;
}
throw new RuntimeException('Unknown invalid stock phase');
