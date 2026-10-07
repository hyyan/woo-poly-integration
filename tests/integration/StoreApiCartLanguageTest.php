<?php
use Hyyan\WPI\Utilities;
use PHPUnit\Framework\TestCase;

final class StoreApiCartLanguageTest extends TestCase
{
    private $originalLocaleHook;

    protected function setUp(): void
    {
        $this->originalLocaleHook = isset($GLOBALS['wp_filter']['locale']) ? clone $GLOBALS['wp_filter']['locale'] : null;
        if ($this->originalLocaleHook) {
            foreach ($this->originalLocaleHook->callbacks as $callbacks) {
                foreach ($callbacks as $registered) {
                    $fn = $registered['function'];
                    if (is_array($fn) && $fn[0] instanceof PLL_Frontend_Filters && $fn[1] === 'get_locale') {
                        remove_filter('locale', $fn);
                    }
                }
            }
        }
    }

    protected function tearDown(): void
    {
        if ($this->originalLocaleHook) { $GLOBALS['wp_filter']['locale'] = $this->originalLocaleHook; }
        else { unset($GLOBALS['wp_filter']['locale']); }
    }

    #[PHPUnit\Framework\Attributes\RunInSeparateProcess]
    #[PHPUnit\Framework\Attributes\PreserveGlobalState(false)]
    public function testNativeCartResponseTranslatesDisplayWithoutChangingCartIdentity(): void
    {
        $originalUser = get_current_user_id();
        $originalLanguage = PLL()->curlang;
        $originalDirection = $GLOBALS['text_direction'] ?? null;
        $originalCookies = $_COOKIE;
        $originalStrings = $GLOBALS['l10n']['pll_string'] ?? null;
        $originalCart = WC()->cart;
        $originalSession = WC()->session;
        $originalCustomer = WC()->customer;
        $products = array();
        $originalHooks = array_map(static function ($hook) { return clone $hook; }, $GLOBALS['wp_filter']);
        $originalServer = $GLOBALS['wp_rest_server'] ?? null;
        try {
            rest_get_server();
            Automattic\WooCommerce\StoreApi\StoreApi::container()->get(Automattic\WooCommerce\StoreApi\RoutesController::class)->register_all_routes();
            wc_load_cart();
            WC()->cart = new WC_Cart();
            foreach (array('en', 'de') as $language) {
                $product = new WC_Product_Simple();
                $product->set_name('Store API cart '.$language);
                $product->set_regular_price('12.50');
                $product->set_status('publish');
                $product->set_virtual(true);
                $product->save();
                pll_set_post_language($product->get_id(), $language);
                $products[$language] = $product->get_id();
            }
            pll_save_post_translations($products);
            Utilities::switch_pll_locale('de_DE');
            $key = WC()->cart->add_to_cart($products['de'], 2);
            foreach (array(array('en', 'en_US', 'de'), array('de', 'de_DE', 'en'), array('en', false, 'en'), array('de', false, 'de')) as $context) {
                list($language, $locale, $cookie) = $context;
                $_COOKIE[PLL_COOKIE] = $cookie;
                if ($locale) { Utilities::switch_pll_locale($locale); } else { PLL()->curlang = false; }
                $beforeLanguage = PLL()->curlang;
                $request = new WP_REST_Request('GET', '/wc/store/v1/cart');
                $request->set_param('lang', $language);
                $response = rest_do_request($request);
                self::assertSame(200, $response->get_status());
                self::assertSame($beforeLanguage, PLL()->curlang);
                $data = $response->get_data();
                self::assertSame('Store API cart '.$language, $data['items'][0]['name']);
                self::assertSame($key, $data['items'][0]['key']);
                self::assertSame($products['de'], $data['items'][0]['id']);
                self::assertSame(2, $data['items'][0]['quantity']);
                self::assertSame('2500', ((array) $data['items'][0]['totals'])['line_total']);
                self::assertSame($products['de'], WC()->cart->get_cart()[$key]['product_id']);
                $hydrated = Automattic\WooCommerce\Blocks\Package::container()->get(Automattic\WooCommerce\Blocks\Domain\Services\Hydration::class)->get_rest_api_response_data('/wc/store/v1/cart');
                self::assertSame('Store API cart '.$language, $hydrated['body']['items'][0]['name']);
                self::assertSame($beforeLanguage, PLL()->curlang);
            }
            Utilities::switch_pll_locale('en_US');
            WC()->cart->cart_contents[$key]['data']->set_price('15.00');
            WC()->cart->calculate_totals();
            $response = rest_do_request(new WP_REST_Request('GET', '/wc/store/v1/cart'));
            $data = $response->get_data();
            self::assertSame('1500', ((array) $data['items'][0]['prices'])['price']);
            self::assertSame('3000', ((array) $data['items'][0]['totals'])['line_total']);
            PLL()->curlang = false;
            foreach (array('unknown-language', array('en')) as $cookie) {
                $_COOKIE[PLL_COOKIE] = $cookie;
                $response = rest_do_request(new WP_REST_Request('GET', '/wc/store/v1/cart'));
                self::assertSame('Store API cart de', $response->get_data()['items'][0]['name']);
                self::assertFalse(PLL()->curlang);
            }
            Utilities::switch_pll_locale('en_US');
            wp_set_current_user(0);
            $private = wc_get_product($products['en']);
            $private->set_status('private');
            $private->set_short_description('Private translation details');
            $private->save();
            self::assertSame('publish', get_post_status($products['de']));
            $response = rest_do_request(new WP_REST_Request('GET', '/wc/store/v1/cart'));
            self::assertSame('Store API cart de', $response->get_data()['items'][0]['name']);
            self::assertStringNotContainsString('Private translation details', $response->get_data()['items'][0]['short_description']);
            $cartTranslator = new Hyyan\WPI\Cart();
            self::assertSame($products['de'], $cartTranslator->translateCartItemProductId($products['de']));
            wp_set_current_user(1);
            self::assertTrue(current_user_can('read_post', $products['en']));
            $response = rest_do_request(new WP_REST_Request('GET', '/wc/store/v1/cart'));
            self::assertSame('Store API cart en', $response->get_data()['items'][0]['name']);
            self::assertStringContainsString('Private translation details', $response->get_data()['items'][0]['short_description']);
            self::assertSame($products['en'], $cartTranslator->translateCartItemProductId($products['de']));
            wp_set_current_user(0);
            $denied = new WP_REST_Request('POST', '/wc/store/v1/cart/remove-item');
            $denied->set_param('key', $key);
            $response = rest_do_request($denied);
            self::assertSame(401, $response->get_status());
            self::assertSame(2, WC()->cart->get_cart_contents_count());
        } finally {
            wp_set_current_user($originalUser);
            foreach ($products as $id) { wc_get_product($id)->delete(true); }
            $GLOBALS['wp_filter'] = $originalHooks;
            $GLOBALS['wp_rest_server'] = $originalServer;
            WC()->cart = $originalCart;
            WC()->session = $originalSession;
            WC()->customer = $originalCustomer;
            PLL()->curlang = $originalLanguage;
            if ($originalDirection !== null) { $GLOBALS['text_direction'] = $originalDirection; } else { unset($GLOBALS['text_direction']); }
            $_COOKIE = $originalCookies;
            if ($originalStrings) { $GLOBALS['l10n']['pll_string'] = $originalStrings; } else { unset($GLOBALS['l10n']['pll_string']); }
        }
    }

    public function testVariationDisplayCannotExposeAPrivateTranslatedParent(): void
    {
        $originalUser = get_current_user_id();
        $originalLanguage = PLL()->curlang;
        $originalDirection = $GLOBALS['text_direction'] ?? null;
        $products = array();
        $variations = array();
        $originalStrings = $GLOBALS['l10n']['pll_string'] ?? null;
        $originalHooks = array_map(static function ($hook) { return clone $hook; }, $GLOBALS['wp_filter']);
        try {
            foreach (array('en', 'de') as $language) {
                $parent = new WC_Product_Variable();
                $parent->set_name('Private variation parent '.$language);
                $parent->set_status($language === 'en' ? 'private' : 'publish');
                $parent->save();
                pll_set_post_language($parent->get_id(), $language);
                $products[$language] = $parent->get_id();
                $variation = new WC_Product_Variation();
                $variation->set_parent_id($parent->get_id());
                $variation->set_status('publish');
                $variation->set_regular_price('12.50');
                $variation->update_meta_data(Hyyan\WPI\Product\Variation::DUPLICATE_KEY, 'private-cart-translation-test');
                $variation->save();
                $variations[$language] = $variation->get_id();
            }
            pll_save_post_translations($products);
            Utilities::switch_pll_locale('en_US');
            $translator = new Hyyan\WPI\Cart();
            wp_set_current_user(0);
            self::assertFalse($translator->getVariationTranslation($variations['de']));
            $item = array('product_id' => $products['de'], 'variation_id' => $variations['de']);
            self::assertSame('original-permalink', $translator->translateCartItemPermalink('original-permalink', $item));
            self::assertSame(array(), $translator->translateCartItemData(array(), $item));
            wp_set_current_user(1);
            self::assertSame($variations['en'], $translator->getVariationTranslation($variations['de'])->get_id());
        } finally {
            foreach (array_merge(array_values($variations), array_values($products)) as $id) { wc_get_product($id)->delete(true); }
            $GLOBALS['wp_filter'] = $originalHooks;
            if ($originalStrings) { $GLOBALS['l10n']['pll_string'] = $originalStrings; } else { unset($GLOBALS['l10n']['pll_string']); }
            wp_set_current_user($originalUser);
            PLL()->curlang = $originalLanguage;
            if ($originalDirection !== null) { $GLOBALS['text_direction'] = $originalDirection; } else { unset($GLOBALS['text_direction']); }
        }
    }


    #[PHPUnit\Framework\Attributes\RunInSeparateProcess]
    #[PHPUnit\Framework\Attributes\PreserveGlobalState(false)]
    public function testNativeGlobalSelectedTermsTranslateWithoutChangingStoredSelection(): void
    {
        $language=PLL()->curlang; $strings=$GLOBALS['l10n']['pll_string'] ?? null; $direction=$GLOBALS['text_direction'] ?? null;
        $cart=WC()->cart; $session=WC()->session; $customer=WC()->customer;
        $hooks=array_map(static function($hook){return clone $hook;},$GLOBALS['wp_filter']);
        $server=$GLOBALS['wp_rest_server'] ?? null; $cachedTaxonomies=PLL()->model->cache->get('taxonomies');
        $parents=array(); $variants=array(); $terms=array(); $attribute=0;
        $taxonomy='pa_wpi_cart_test_color';
        try {
            $attribute=wc_create_attribute(array('name'=>'Cart test color','slug'=>'wpi_cart_test_color'));
            self::assertIsInt($attribute);
            register_taxonomy($taxonomy,array('product'),array('public'=>true,'label'=>'Cart test color'));
            add_filter('pll_get_taxonomies',static function($taxonomies) use($taxonomy){$taxonomies[$taxonomy]=$taxonomy;return $taxonomies;});
            PLL()->model->cache->clean('taxonomies');
            self::assertTrue(pll_is_translated_taxonomy($taxonomy));
            rest_get_server();
            Automattic\WooCommerce\StoreApi\StoreApi::container()->get(Automattic\WooCommerce\StoreApi\RoutesController::class)->register_all_routes();
            wc_load_cart(); WC()->cart=new WC_Cart();
            foreach(array('en'=>'Blue','de'=>'Blau') as $lang=>$name){
                $term=wp_insert_term($name,$taxonomy,array('slug'=>strtolower($name)));self::assertIsArray($term);
                $terms[$lang]=$term['term_id'];pll_set_term_language($term['term_id'],$lang);
                $p=new WC_Product_Variable();$p->set_name('Global cart '.$lang);$p->set_status('publish');
                $a=new WC_Product_Attribute();$a->set_id($attribute);$a->set_name($taxonomy);$a->set_options(array($term['term_id']));$a->set_variation(true);
                $p->set_attributes(array($a));$p->save();pll_set_post_language($p->get_id(),$lang);$parents[$lang]=$p->get_id();
                $v=new WC_Product_Variation();$v->set_parent_id($p->get_id());$v->set_attributes(array($taxonomy=>strtolower($name)));$v->set_regular_price('14.95');$v->set_status('publish');$v->set_virtual(true);$v->save();$variants[$lang]=$v->get_id();
                WC_Product_Variable::sync($p->get_id());
            }
            pll_save_term_translations($terms);pll_save_post_translations($parents);
            foreach($variants as $id){update_post_meta($id,Hyyan\WPI\Product\Variation::DUPLICATE_KEY,$variants['en']);}
            Utilities::switch_pll_locale('de_DE');
            $key=WC()->cart->add_to_cart($parents['de'],2,$variants['de'],array('attribute_'.$taxonomy=>'blau'));self::assertNotFalse($key);
            Utilities::switch_pll_locale('en_US');
            $response=rest_do_request(new WP_REST_Request('GET','/wc/store/v1/cart'));self::assertSame(200,$response->get_status());
            self::assertSame('Blue',$response->get_data()['items'][0]['variation'][0]['value']);
            self::assertSame('blau',WC()->cart->get_cart()[$key]['variation']['attribute_'.$taxonomy]);
            self::assertSame($key,WC()->cart->add_to_cart($parents['en'],1,$variants['en'],array('attribute_'.$taxonomy=>'blue')));
            self::assertSame(3,WC()->cart->get_cart_contents_count());
            Utilities::switch_pll_locale('de_DE');
            $response=rest_do_request(new WP_REST_Request('GET','/wc/store/v1/cart'));
            self::assertSame('Blau',$response->get_data()['items'][0]['variation'][0]['value']);
        } finally {
            foreach($variants as $id){wc_get_product($id)->delete(true);}foreach($parents as $id){wc_get_product($id)->delete(true);}
            foreach($terms as $id){wp_delete_term($id,$taxonomy);}if(is_int($attribute)&&$attribute){wc_delete_attribute($attribute);}unregister_taxonomy($taxonomy);
            $GLOBALS['wp_filter']=$hooks;$GLOBALS['wp_rest_server']=$server;
            if($cachedTaxonomies===false){PLL()->model->cache->clean('taxonomies');}else{PLL()->model->cache->set('taxonomies',$cachedTaxonomies);}
            WC()->cart=$cart;WC()->session=$session;WC()->customer=$customer;PLL()->curlang=$language;
            if($strings){$GLOBALS['l10n']['pll_string']=$strings;}else{unset($GLOBALS['l10n']['pll_string']);}
            if($direction!==null){$GLOBALS['text_direction']=$direction;}else{unset($GLOBALS['text_direction']);}
        }
    }

}
