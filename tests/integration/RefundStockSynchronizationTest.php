<?php
use Hyyan\WPI\Product\Variation;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class RefundStockSynchronizationTest extends TestCase
{
    #[DataProvider('refundModes')]
    public function testNativeRefundKeepsLinkedStockAndUnselectedProducts(string $type, bool $restock): void
    {
        $language = PLL()->curlang;
        $user = get_current_user_id();
        $products = array();
        $parents = array();
        $siblings = array();
        $orders = array();
        $refunds = array();
        wp_set_current_user(1);
        try {
            foreach (array('en', 'de') as $lang) {
                if ($type === 'variation') {
                    $parent = new WC_Product_Variable();
                    $parent->set_name('Refund parent ' . $lang);
                    $parent->set_status('publish');
                    $parent->save();
                    pll_set_post_language($parent->get_id(), $lang);
                    $parents[$lang] = $parent->get_id();
                    $product = new WC_Product_Variation();
                    $product->set_parent_id($parent->get_id());
                } else {
                    $product = new WC_Product_Simple();
                }
                $product->set_name('Refund product ' . $lang);
                $product->set_status('publish');
                $product->set_regular_price('12');
                $product->set_manage_stock(true);
                $product->set_stock_quantity(20);
                $product->save();
                pll_set_post_language($product->get_id(), $lang);
                $products[$lang] = $product->get_id();
                $sibling = new WC_Product_Simple();
                $sibling->set_name('Unselected refund product ' . $lang);
                $sibling->set_manage_stock(true);
                $sibling->set_stock_quantity(7);
                $sibling->save();
                $siblings[] = $sibling->get_id();
            }
            if ($type === 'variation') {
                pll_save_post_translations($parents);
                foreach ($products as $lang => $id) { update_post_meta($id, Variation::DUPLICATE_KEY, ($lang === 'de' ? '000' : '') . $products['en']); }
            } else {
                pll_save_post_translations($products);
            }
            foreach ($products as $lang => $id) {
                PLL()->curlang = PLL()->model->get_language($lang);
                $order = wc_create_order();
                self::assertInstanceOf(WC_Order::class, $order);
                $orders[] = $order;
                $order->add_product(wc_get_product($id), 2);
                $order->calculate_totals();
                $order->save();
                do_action('woocommerce_checkout_update_order_meta', $order->get_id());
                wc_reduce_stock_levels($order->get_id());
                self::assertSame($lang, pll_get_post_language($order->get_id()));
            }
            foreach ($products as $id) { self::assertSame(16, wc_get_product($id)->get_stock_quantity()); }
            foreach ($orders as $index => $order) {
                $items = $order->get_items();
                $item = reset($items);
                $refund = wc_create_refund(array('order_id' => $order->get_id(), 'amount' => 12,
                    'refund_payment' => false, 'restock_items' => $restock,
                    'line_items' => array($item->get_id() => array('qty' => 1, 'refund_total' => 12, 'refund_tax' => array()))));
                self::assertInstanceOf(WC_Order_Refund::class, $refund);
                $refunds[] = $refund;
                self::assertSame(12.0, wc_get_order($order->get_id())->get_total_refunded());
                foreach ($products as $id) { self::assertSame($restock ? 17 + $index : 16, wc_get_product($id)->get_stock_quantity()); }
                foreach ($siblings as $id) { self::assertSame(7, wc_get_product($id)->get_stock_quantity()); }
            }
        } finally {
            foreach ($refunds as $refund) { $refund->delete(true); }
            foreach ($orders as $order) { $order->delete(true); }
            foreach (array_merge(array_values($products), $siblings, array_values($parents)) as $id) { wp_delete_post($id, true); }
            PLL()->curlang = $language;
            wp_set_current_user($user);
        }
    }

    public static function refundModes(): array
    {
        return array('simple restocked' => array('simple', true), 'variation restocked' => array('variation', true),
            'simple retained' => array('simple', false), 'variation retained' => array('variation', false));
    }
}
