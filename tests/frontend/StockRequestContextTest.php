<?php
use Hyyan\WPI\Product\Stock;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class StockRequestContextTest extends TestCase
{
    private array $hooks;
    private array $post;
    private array $products = array();
    private int $user;
    private $language;
    protected function setUp(): void
    {
        $this->hooks = $GLOBALS['wp_filter'];
        foreach ($this->hooks as $name => $hook) { $this->hooks[$name] = clone $hook; }
        $this->post = $_POST; $this->user = get_current_user_id(); $this->language = PLL()->curlang;
        wp_set_current_user(0);
        remove_action('woocommerce_product_set_stock', array(Stock::class, 'SyncStockSimple'));
        remove_action('woocommerce_variation_set_stock', array(Stock::class, 'SyncStockVariation'));
        foreach (array('en','de') as $lang) {
            $product = new WC_Product_Simple(); $product->set_name('Stock request context '.$lang);
            $product->set_status('publish'); $product->set_regular_price('10');
            $product->set_manage_stock(true); $product->set_stock_quantity(20); $product->save();
            $this->products[$lang] = $product->get_id(); pll_set_post_language($product->get_id(), $lang);
        }
        pll_save_post_translations($this->products);
        PLL()->curlang = PLL()->model->get_language('en');
        self::assertFalse(is_admin());
    }
    protected function tearDown(): void
    {
        foreach ($this->products as $id) { wp_delete_post($id, true); }
        $_POST = $this->post; wp_set_current_user($this->user); PLL()->curlang = $this->language;
        $GLOBALS['wp_filter'] = $this->hooks;
    }
    #[DataProvider('postedTypes')]
    public function testFrontendRequestCannotDisableTranslatedStockSynchronization(array $post): void
    {
        $_POST = $post;
        new Stock();
        self::assertNotFalse(has_action('woocommerce_product_set_stock', array(Stock::class,'SyncStockSimple')));
        wc_update_product_stock($this->products['en'], 1, 'decrease');
        self::assertSame(19, wc_get_product($this->products['en'])->get_stock_quantity());
        self::assertSame(19, wc_get_product($this->products['de'])->get_stock_quantity());
    }
    public static function postedTypes(): array
    {
        return array(array(array()), array(array('product-type'=>'simple')), array(array('product-type'=>array('variable'))));
    }
}
