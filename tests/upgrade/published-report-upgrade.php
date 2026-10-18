<?php
if ($GLOBALS['wpdb']->prefix !== 'wpi_upgrade_') { throw new RuntimeException('Owned upgrade store required'); }
wp_set_current_user(1);
$phase = getenv('WPI_REPORT_PHASE');
if (!in_array($phase, array('prepare', 'capture', 'verify', 'cleanup'), true)) { throw new RuntimeException('Unknown fixture phase'); }
$key = 'wpi_published_report_fixture';
$data = get_option($key);
if ($phase === 'prepare') {
    if ($data || Hyyan\WPI\Plugin::getVersion() !== '1.5.0') { throw new RuntimeException('Clean published baseline required'); }
    $data = array('products' => array(), 'orders' => array(), 'notice' => get_option('hyyan-wpi-flash-messages', null));
    update_option($key, $data, false);
    $password = wp_generate_password(24, false);
    $user = wp_insert_user(array('user_login' => 'published-report-review', 'user_pass' => $password, 'role' => 'shop_manager'));
    if (is_wp_error($user)) { throw new RuntimeException($user->get_error_message()); }
    $data['user'] = $user;
    update_option($key, $data, false);
    foreach (array('en' => 2, 'de' => 3) as $lang => $quantity) {
        PLL()->curlang = PLL()->model->get_language($lang);
        $p = new WC_Product_Simple();
        $p->set_name('Published report upgrade ' . $lang);
        $p->set_status('publish');
        $p->set_regular_price('10');
        $p->save();
        $data['products'][$lang] = $p->get_id();
        pll_set_post_language($p->get_id(), $lang);
        update_option($key, $data, false);
        $o = wc_create_order(array('status' => 'completed'));
        $data['orders'][] = $o->get_id();
        update_option($key, $data, false);
        $o->add_product($p, $quantity);
        $o->calculate_totals();
        $o->save();
        pll_set_post_language($o->get_id(), $lang);
    }
    pll_save_post_translations($data['products']);
    echo wp_json_encode(array('version' => '1.5.0', 'products' => $data['products'], 'orders' => $data['orders'], 'username' => 'published-report-review', 'password' => $password)), "\n";
    return;
}
if (!$data) { throw new RuntimeException('Owned fixture missing'); }
if ($phase === 'cleanup') {
    foreach ($data['orders'] as $id) { $order = wc_get_order($id); if ($order) { $order->delete(true); } }
    foreach ($data['products'] as $id) { wp_delete_post($id, true); }
    require_once ABSPATH . 'wp-admin/includes/user.php';
    if (get_userdata($data['user'])->user_login !== 'published-report-review') { throw new RuntimeException('User ownership changed'); }
    wp_delete_user($data['user']);
    if ($data['notice'] === null) { delete_option('hyyan-wpi-flash-messages'); } else { update_option('hyyan-wpi-flash-messages', $data['notice']); }
    foreach ($data['orders'] as $id) { if (wc_get_order($id)) { throw new RuntimeException('Owned order remained'); } }
    foreach ($data['products'] as $id) { if (get_post($id)) { throw new RuntimeException('Owned product remained'); } }
    if (get_userdata($data['user']) || get_option('hyyan-wpi-flash-messages', null) !== $data['notice']) { throw new RuntimeException('Cleanup failed'); }
    delete_option($key);
    echo wp_json_encode(array('removed_orders' => 2, 'removed_products' => 2, 'removed_user' => true, 'notice_restored' => true)), "\n";
    return;
}
if (($phase === 'capture' && Hyyan\WPI\Plugin::getVersion() !== '1.5.0') || ($phase === 'verify' && Hyyan\WPI\Plugin::getVersion() !== '1.5.1')) { throw new RuntimeException('Wrong verification version'); }
wp_set_current_user($data['user']);
update_user_meta($data['user'], 'user_lang', 'en');
require_once WC_ABSPATH . 'includes/admin/reports/class-wc-admin-report.php';
$_GET = array('tab' => 'orders', 'report' => 'sales_by_product');
$handler = new Hyyan\WPI\Reports();
$report = new WC_Admin_Report();
$actual = array();
foreach (array('all', 'en', 'de') as $lang) {
    $_GET['lang'] = $lang;
    PLL()->curlang = $lang === 'all' ? false : PLL()->model->get_language($lang);
    foreach (array('quantity' => array('_qty', 'order_item_qty'), 'amount' => array('_line_total', 'order_item_total')) as $metric => $field) {
        $rows = $report->get_order_report_data(array('data' => array('_product_id' => array('type' => 'order_item_meta', 'order_item_type' => 'line_item', 'function' => '', 'name' => 'product_id'), $field[0] => array('type' => 'order_item_meta', 'order_item_type' => 'line_item', 'function' => 'SUM', 'name' => $field[1])), 'where_meta' => array(array('type' => 'order_item_meta', 'meta_key' => '_product_id', 'meta_value' => array_values($data['products']), 'operator' => 'IN')), 'group_by' => 'product_id', 'query_type' => 'get_results', 'nocache' => true));
        $expected = $lang === 'all' ? 5 : ($lang === 'en' ? 2 : 3);
        if ($metric === 'amount') { $expected *= 10; }
        if ($GLOBALS['wpdb']->last_error || count($rows) !== 1 || (float) $rows[0]->{$field[1]} !== (float) $expected) { throw new RuntimeException('Native report mismatch ' . $lang . ' ' . $metric . ' ' . wp_json_encode($rows) . ' ' . $GLOBALS['wpdb']->last_error); }
        $actual[$lang][$metric] = array('product' => (int) $rows[0]->product_id, 'value' => (float) $rows[0]->{$field[1]});
    }
}
if ($phase === 'capture') {
    $data['baseline'] = $actual;
    update_option($key, $data, false);
} elseif ($phase === 'verify') {
    if ($actual !== $data['baseline']) { throw new RuntimeException('Published report changed'); }
} else { throw new RuntimeException('Unknown phase'); }
echo wp_json_encode(array('version' => Hyyan\WPI\Plugin::getVersion(), 'cases' => 6, 'reports' => $actual, 'baseline_retained' => $phase === 'verify')), "\n";
