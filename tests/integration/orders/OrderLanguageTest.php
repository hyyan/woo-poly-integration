<?php
use Hyyan\WPI\Order;
use PHPUnit\Framework\TestCase;

final class OrderLanguageTest extends TestCase
{
    private WC_Order $order;
    private $originalLanguage;
    private array $originalCookies;

    protected function setUp(): void
    {
        $this->originalLanguage = PLL()->curlang;
        $this->originalCookies = $_COOKIE;
        unset($_COOKIE[PLL_COOKIE]);
        $this->order = wc_create_order();
    }

    protected function tearDown(): void
    {
        $this->order->delete(true);
        PLL()->curlang = $this->originalLanguage;
        $_COOKIE = $this->originalCookies;
        restore_previous_locale();
    }

    public function testClassicCheckoutKeepsItsExistingLanguageStorage(): void
    {
        foreach (array('en', 'de') as $language) {
            PLL()->curlang = PLL()->model->get_language($language);
            do_action('woocommerce_checkout_update_order_meta', $this->order->get_id());
            self::assertSame($language, Order::getOrderLangauge($this->order->get_id()));
        }
    }

    public function testBlockCheckoutStoresTheCurrentLanguage(): void
    {
        foreach (array('en', 'de') as $language) {
            PLL()->curlang = PLL()->model->get_language($language);
            do_action('woocommerce_store_api_checkout_update_order_meta', $this->order);
            self::assertSame($language, Order::getOrderLangauge($this->order->get_id()));
        }
    }

    public function testBlockCheckoutUsesValidPolylangCookieWhenLanguageIsAbsent(): void
    {
        PLL()->curlang = false;
        $_COOKIE[PLL_COOKIE] = 'de';
        do_action('woocommerce_store_api_checkout_update_order_meta', $this->order);
        self::assertSame('de', Order::getOrderLangauge($this->order->get_id()));
        self::assertSame('de_DE', pll_current_language('locale'));
    }

    public function testExplicitBlockLanguageWinsOverCookie(): void
    {
        PLL()->curlang = PLL()->model->get_language('en');
        $_COOKIE[PLL_COOKIE] = 'de';
        do_action('woocommerce_store_api_checkout_update_order_meta', $this->order);
        self::assertSame('en', Order::getOrderLangauge($this->order->get_id()));
    }

    public function testInvalidCookieDoesNotChangeExistingOrderLanguage(): void
    {
        pll_set_post_language($this->order->get_id(), 'de');
        PLL()->curlang = false;
        foreach (array('unknown-language', array('de')) as $cookie) {
            $_COOKIE[PLL_COOKIE] = $cookie;
            do_action('woocommerce_store_api_checkout_update_order_meta', $this->order);
            self::assertSame('de', Order::getOrderLangauge($this->order->get_id()));
            self::assertFalse(pll_current_language());
        }
    }
}
