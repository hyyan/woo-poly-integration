<?php
use Hyyan\WPI\Ajax;
use Hyyan\WPI\Endpoints;
use Hyyan\WPI\HooksInterface;
use Hyyan\WPI\Product\Variation;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class RuntimeCompatibilityTest extends TestCase
{
    #[DataProvider('endpointUrls')]
    public function testTranslatedEndpointRetainsQuery(string $url, string $expected): void
    {
        $language = PLL()->curlang;
        try {
            PLL()->curlang = PLL()->model->get_language('de');
            $ajax = (new ReflectionClass(Ajax::class))->newInstanceWithoutConstructor();
            self::assertSame($expected, $ajax->filter_woocommerce_ajax_get_endpoint($url, 'get_refreshed_fragments'));
        } finally { PLL()->curlang = $language; }
    }
    public static function endpointUrls(): array
    {
        return array(
            array('https://example.test/?wc-ajax=get_refreshed_fragments','/de/?wc-ajax=get_refreshed_fragments'),
            array('/?wc-ajax=checkout&test=one%26two','/de/?wc-ajax=checkout&test=one%26two'),
            array('//example.test/?wc-ajax=add_to_cart','/de/?wc-ajax=add_to_cart'),
            array('/?wc-ajax=checkout#receipt','/de/?wc-ajax=checkout')
        );
    }
    #[DataProvider('accountUrls')]
    public function testAccountEndpointRetainsPermalinkAndQuery(string $permalink, string $expected): void
    {
        $original = get_option('permalink_structure');
        try {
            update_option('permalink_structure','/%postname%/');
            $endpoints = (new ReflectionClass(Endpoints::class))->newInstanceWithoutConstructor();
            self::assertSame($expected, $endpoints->rebuildUrl('orders','',$permalink));
        } finally { update_option('permalink_structure',$original); }
    }
    public static function accountUrls(): array
    {
        return array(
            array('https://example.test/de/account/','https://example.test/de/account/orders/'),
            array('https://example.test/de/account/?page=2&filter=a%26b','https://example.test/de/account/orders/?page=2&filter=a%26b'),
            array('/de/account/?page=2','/de/account/orders/?page=2'),
            array('//example.test/de/account/?page=2','//example.test/de/account/orders/?page=2')
        );
    }
    #[DataProvider('copyFailures')]
    public function testVariationCopyRestoresTimeoutAndSupportsRestrictedHosts(bool $fail): void
    {
        $original = (int) ini_get('max_execution_time');
        $user = get_current_user_id(); $ids = array(); $parents = array();
        $exception = static function () { throw new RuntimeException('Fixture copy failure'); };
        try {
            wp_set_current_user(1);
            foreach (array('en','de') as $lang) {
                $product = new WC_Product_Variable(); $product->set_name('Runtime variation fixture '.$lang); $product->save();
                $ids[] = $product->get_id(); $parents[$lang] = $product->get_id(); pll_set_post_language($product->get_id(),$lang);
            }
            pll_save_post_translations($parents);
            $child = new WC_Product_Variation(); $child->set_parent_id($parents['en']); $child->set_regular_price('12'); $child->save();
            $ids[] = $child->get_id(); pll_set_post_language($child->get_id(),'en');
            wc_set_time_limit(31); $before = ini_get('max_execution_time');
            if ($fail) { add_action(HooksInterface::PRODUCT_VARIATION_COPY_META_ACTION,$exception,100); }
            $caught = null;
            try { (new Variation(wc_get_product($parents['en']),wc_get_product($parents['de'])))->duplicate(); }
            catch (RuntimeException $error) { $caught = $error->getMessage(); }
            self::assertSame($fail ? 'Fixture copy failure' : null,$caught);
            self::assertSame($before,ini_get('max_execution_time'));
            if (!$fail) {
                $children = wc_get_product($parents['de'])->get_children();
                self::assertCount(1,$children);
                self::assertSame('12',wc_get_product($children[0])->get_regular_price());
                self::assertSame($child->get_id(),(int)get_post_meta($children[0],Variation::DUPLICATE_KEY,true));
            }
        } finally {
            remove_action(HooksInterface::PRODUCT_VARIATION_COPY_META_ACTION,$exception,100);
            foreach ($parents as $id) { foreach (get_posts(array('post_type'=>'product_variation','post_parent'=>$id,'post_status'=>'any','numberposts'=>-1,'lang'=>'')) as $post) { wp_delete_post($post->ID,true); } }
            foreach (array_reverse($ids) as $id) { wp_delete_post($id,true); }
            wc_set_time_limit($original); wp_set_current_user($user);
        }
    }
    public static function copyFailures(): array { return array(array(false),array(true)); }
}
