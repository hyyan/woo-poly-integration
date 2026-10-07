<?php
use Hyyan\WPI\Utilities;
use PHPUnit\Framework\TestCase;

final class StoreApiRequestLocaleTest extends TestCase
{
    #[PHPUnit\Framework\Attributes\RunInSeparateProcess]
    #[PHPUnit\Framework\Attributes\PreserveGlobalState(false)]
    public function testNativeCouponErrorUsesCookieLanguageAndRestoresCaller(): void
    {
        $language = PLL()->curlang;
        $locale = get_locale();
        $cookies = $_COOKIE;
        $hooks = array_map(static fn($hook) => clone $hook, $GLOBALS['wp_filter']);
        $cart = WC()->cart;
        $session = WC()->session;
        $customer = WC()->customer;
        $server = $GLOBALS['wp_rest_server'] ?? null;
        $product = new WC_Product_Simple();
        $coupon = new WC_Coupon();
        $observed = array();
        $gettext = static function ($translated, $text, $domain) use (&$observed) {
            if ($domain === 'woocommerce' && $text === 'The minimum spend for coupon "%1$s" is %2$s.') {
                $observed[] = get_locale();
                if (get_locale() === 'de_DE') { return 'German minimum %1$s is %2$s.'; }
            }
            return $translated;
        };
        try {
            rest_get_server();
            Automattic\WooCommerce\StoreApi\StoreApi::container()->get(Automattic\WooCommerce\StoreApi\RoutesController::class)->register_all_routes();
            wc_load_cart(); WC()->cart = new WC_Cart();
            $product->set_name('Coupon locale regression'); $product->set_status('publish'); $product->set_regular_price('20'); $product->set_virtual(true); $product->save();
            pll_set_post_language($product->get_id(), 'de');
            $coupon->set_code('locale-regression-'.wp_generate_password(8, false)); $coupon->set_discount_type('fixed_cart'); $coupon->set_amount('5'); $coupon->set_minimum_amount('30'); $coupon->save();
            WC()->cart->add_to_cart($product->get_id());
            foreach ($GLOBALS['wp_filter']['locale']->callbacks as $callbacks) {
                foreach ($callbacks as $registered) {
                    $fn = $registered['function'];
                    if (is_array($fn) && $fn[0] instanceof PLL_Frontend_Filters && $fn[1] === 'get_locale') {
                        remove_filter('locale', $fn);
                    }
                }
            }
            PLL()->curlang = false; $_COOKIE[PLL_COOKIE] = 'de';
            add_filter('gettext', $gettext, 999, 3);
            $request = new WP_REST_Request('POST', '/wc/store/v1/cart/apply-coupon');
            $request->set_header('Nonce', wp_create_nonce('wc_store_api')); $request->set_param('code', $coupon->get_code());
            $response = rest_do_request($request);
            self::assertSame(400, $response->get_status());
            self::assertStringContainsString('German minimum', $response->get_data()['message']);
            self::assertSame(array('de_DE'), $observed);
            self::assertFalse(PLL()->curlang);
            self::assertSame($locale, get_locale());
            self::assertEmpty(WC()->cart->get_applied_coupons());
            self::assertSame(1, WC()->cart->get_cart_contents_count());
        } finally {
            remove_filter('gettext', $gettext, 999);
            $coupon->delete(true); $product->delete(true);
            $GLOBALS['wp_filter'] = $hooks; $GLOBALS['wp_rest_server'] = $server;
            WC()->cart = $cart; WC()->session = $session; WC()->customer = $customer;
            $_COOKIE = $cookies; PLL()->curlang = $language;
            restore_previous_locale();
        }
    }
    #[PHPUnit\Framework\Attributes\RunInSeparateProcess]
    #[PHPUnit\Framework\Attributes\PreserveGlobalState(false)]
    public function testNestedCheckoutAndUnrelatedRequestsPreserveCallerContext(): void
    {
        $hooks = array_map(static fn($hook) => clone $hook, $GLOBALS['wp_filter']);
        $server = $GLOBALS['wp_rest_server'] ?? null;
        $language = PLL()->curlang;
        $cookies = $_COOKIE;
        $strings = $GLOBALS['l10n']['pll_string'] ?? null;
        $direction = $GLOBALS['text_direction'] ?? null;
        foreach ($GLOBALS['wp_filter']['locale']->callbacks as $callbacks) {
            foreach ($callbacks as $registered) {
                $fn = $registered['function'];
                if (is_array($fn) && $fn[0] instanceof PLL_Frontend_Filters && $fn[1] === 'get_locale') { remove_filter('locale', $fn); }
            }
        }
        $locale = get_locale();
        try {
            rest_get_server();
            new Hyyan\WPI\Cart();
            $callback = static fn() => array('locale' => get_locale(), 'language' => pll_current_language());
            foreach (array('/cart/locale-probe', '/checkout/locale-probe') as $route) {
                register_rest_route('wc/store/v1', $route, array('methods' => 'GET', 'permission_callback' => '__return_true', 'callback' => $callback));
            }
            register_rest_route('wpi-test/v1', '/locale', array('methods' => 'GET', 'permission_callback' => '__return_true', 'callback' => $callback));
            PLL()->curlang = false;
            foreach (array('unknown-language', array('de')) as $cookie) {
                $_COOKIE[PLL_COOKIE] = $cookie;
                self::assertSame(array('locale' => $locale, 'language' => false), rest_do_request(new WP_REST_Request('GET', '/wc/store/v1/cart/locale-probe'))->get_data());
            }
            $_COOKIE[PLL_COOKIE] = 'de';
            self::assertSame(array('locale' => $locale, 'language' => false), rest_do_request(new WP_REST_Request('GET', '/wpi-test/v1/locale'))->get_data());
            register_rest_route('wc/store/v1', '/checkout/nested-locale', array('methods' => 'GET', 'permission_callback' => '__return_true', 'callback' => static function () {
                $inner = rest_do_request(new WP_REST_Request('GET', '/wc/store/v1/cart/locale-probe'));
                self::assertSame(array('locale' => 'de_DE', 'language' => 'de'), $inner->get_data());
                self::assertSame('de_DE', get_locale());
                return new WP_Error('locale_probe', 'Scoped checkout error', array('status' => 400));
            }));
            $response = rest_do_request(new WP_REST_Request('GET', '/wc/store/v1/checkout/nested-locale'));
            self::assertSame(400, $response->get_status());
            self::assertSame($locale, get_locale());
            self::assertFalse(PLL()->curlang);
            self::assertSame($strings, $GLOBALS['l10n']['pll_string'] ?? null);
            self::assertSame($direction, $GLOBALS['text_direction'] ?? null);
        } finally {
            PLL()->curlang = $language; $_COOKIE = $cookies;
            $GLOBALS['wp_filter'] = $hooks; $GLOBALS['wp_rest_server'] = $server;
            restore_previous_locale();
        }
    }

}
