<?php
use Hyyan\WPI\Reports;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ReportStorageQueryTest extends TestCase
{
    #[PHPUnit\Framework\Attributes\RunInSeparateProcess]
    #[PHPUnit\Framework\Attributes\PreserveGlobalState(false)]
    #[DataProvider('storageAndLanguages')]
    public function testNativeReportQueryWorksInBothOrderStores(bool $hpos, string $language): void
    {
        $storage = get_option('woocommerce_custom_orders_table_enabled');
        $sync = get_option('woocommerce_custom_orders_table_data_sync_enabled');
        $get = $_GET;
        $current = PLL()->curlang;
        $products = $orders = array();
        add_filter('woocommerce_email_enabled_new_order', '__return_false');
        try {
            $synchronizer = wc_get_container()->get(Automattic\WooCommerce\Internal\DataStores\Orders\DataSynchronizer::class);
            while ($ids = $synchronizer->get_next_batch_to_process(100)) { $synchronizer->process_batch($ids); }
            update_option('woocommerce_custom_orders_table_data_sync_enabled', 'no');
            update_option('woocommerce_custom_orders_table_enabled', $hpos ? 'yes' : 'no');
            foreach (array('en'=>2,'de'=>3) as $slug=>$quantity) {
                PLL()->curlang = PLL()->model->get_language($slug);
                $p = new WC_Product_Simple();
                $p->set_name('Report storage regression '.$slug);
                $p->set_regular_price('10');
                $p->save();
                pll_set_post_language($p->get_id(), $slug);
                $products[$slug] = $p->get_id();
                $order = wc_create_order(array('status'=>'completed'));
                $order->add_product($p, $quantity);
                $order->calculate_totals();
                $order->save();
                pll_set_post_language($order->get_id(), $slug);
                $orders[] = $order;
            }
            pll_save_post_translations($products);
            PLL()->curlang = $language === 'all' ? false : PLL()->model->get_language($language);
            $_GET = array('tab'=>'orders','report'=>'sales_by_product','lang'=>$language);
            $reports = new Reports();
            require_once WC_ABSPATH.'includes/admin/reports/class-wc-admin-report.php';
            $report = new WC_Admin_Report();
            $result = $report->get_order_report_data(array('data'=>array(
                '_product_id'=>array('type'=>'order_item_meta','order_item_type'=>'line_item','function'=>'','name'=>'product_id'),
                '_qty'=>array('type'=>'order_item_meta','order_item_type'=>'line_item','function'=>'SUM','name'=>'order_item_qty'),
            ),'where_meta'=>array(array('type'=>'order_item_meta','meta_key'=>'_product_id','meta_value'=>array_values($products),'operator'=>'IN')),'group_by'=>'product_id','query_type'=>'get_results','nocache'=>true));
            self::assertSame('', $GLOBALS['wpdb']->last_error);
            self::assertCount(1, $result);
            self::assertEquals($language === 'all' ? 5 : 2, $result[0]->order_item_qty);
        } finally {
            foreach ($orders as $order) { $order->delete(true); }
            $synchronizer = wc_get_container()->get(Automattic\WooCommerce\Internal\DataStores\Orders\DataSynchronizer::class);
            while ($ids = $synchronizer->get_next_batch_to_process(100)) { $synchronizer->process_batch($ids); }
            remove_filter('woocommerce_email_enabled_new_order', '__return_false');
            foreach ($products as $id) { wp_delete_post($id, true); }
            update_option('woocommerce_custom_orders_table_enabled', $storage);
            if ($sync === false) { delete_option('woocommerce_custom_orders_table_data_sync_enabled'); }
            else { update_option('woocommerce_custom_orders_table_data_sync_enabled', $sync); }
            $_GET = $get;
            PLL()->curlang = $current;
        }
    }

    public static function storageAndLanguages(): array
    {
        return array(array(false,'all'),array(false,'en'),array(true,'all'),array(true,'en'));
    }
}
