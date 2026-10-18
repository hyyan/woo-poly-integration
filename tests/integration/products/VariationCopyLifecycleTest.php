<?php
use Hyyan\WPI\HooksInterface;
use Hyyan\WPI\Product\Variable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class VariationCopyLifecycleTest extends TestCase
{
    public function testSaveCallbackIgnoresADeletedProductPost(): void
    {
        $product = new WC_Product_Variable();
        $product->set_name('Deleted product save callback regression');
        $product->save();
        $id = $product->get_id();
        try {
            wp_delete_post($id, true);
            self::assertNull(get_post($id));
            // WooCommerce can retain the product object until deferred sync at shutdown.
            do_action('woocommerce_after_product_object_save', $product, $product->get_data_store());
            self::assertNull(get_post($id));
        } finally {
            wp_delete_post($id, true);
        }
    }

    #[DataProvider('copyOutcomes')]
    public function testCopyRestoresVisibilityAndSaveCallbacks(bool $fail): void
    {
        $get = $_GET;
        $page = $GLOBALS['pagenow'] ?? null;
        $user = get_current_user_id();
        $language = PLL()->curlang;
        $visibility = isset($GLOBALS['wp_filter']['woocommerce_hide_invisible_variations']) ? clone $GLOBALS['wp_filter']['woocommerce_hide_invisible_variations'] : null;
        $saves = isset($GLOBALS['wp_filter']['woocommerce_after_product_object_save']) ? clone $GLOBALS['wp_filter']['woocommerce_after_product_object_save'] : null;
        $parents = array();
        $during = array();
        $observer = static function () use (&$during, $fail) {
            $during[] = apply_filters('woocommerce_hide_invisible_variations', true);
            if ($fail) { throw new RuntimeException('Owned copy lifecycle failure'); }
        };
        $variable = (new ReflectionClass(Variable::class))->newInstanceWithoutConstructor();
        try {
            $_GET = array();
            $GLOBALS['pagenow'] = 'index.php';
            wp_set_current_user(1);
            PLL()->curlang = PLL()->model->get_language('en');
            remove_all_filters('woocommerce_hide_invisible_variations');
            foreach (array('en', 'de') as $slug) {
                $parent = new WC_Product_Variable();
                $parent->set_name('Copy lifecycle fixture '.$slug);
                $parent->set_status('publish');
                $parent->save();
                $parents[$slug] = $parent->get_id();
                pll_set_post_language($parent->get_id(), $slug);
            }
            pll_save_post_translations($parents);
            $child = new WC_Product_Variation();
            $child->set_parent_id($parents['en']);
            $child->set_regular_price('12');
            $child->save();
            pll_set_post_language($child->get_id(), 'en');
            add_action(HooksInterface::PRODUCT_VARIATION_COPY_META_ACTION, $observer, 100);
            add_action('woocommerce_after_product_object_save', array($variable, 'after_product_save'), 10, 2);
            $GLOBALS['pagenow'] = 'post.php';
            $caught = null;
            try { $variable->duplicateVariations($parents['en'], get_post($parents['en']), true); }
            catch (RuntimeException $error) { $caught = $error->getMessage(); }
            self::assertSame($fail ? 'Owned copy lifecycle failure' : null, $caught);
            self::assertNotEmpty($during);
            self::assertSame(array(false), array_values(array_unique($during)));
            self::assertSame(10, has_action('woocommerce_after_product_object_save', array($variable, 'after_product_save')));
            self::assertTrue(apply_filters('woocommerce_hide_invisible_variations', true));
        } finally {
            $GLOBALS['pagenow'] = 'index.php';
            remove_action(HooksInterface::PRODUCT_VARIATION_COPY_META_ACTION, $observer, 100);
            foreach ($parents as $id) {
                foreach (get_posts(array('post_type'=>'product_variation', 'post_parent'=>$id, 'post_status'=>'any', 'numberposts'=>-1, 'lang'=>'')) as $post) { wp_delete_post($post->ID, true); }
                wp_delete_post($id, true);
            }
            foreach (array('woocommerce_hide_invisible_variations'=>$visibility, 'woocommerce_after_product_object_save'=>$saves) as $hook=>$value) {
                if ($value === null) { unset($GLOBALS['wp_filter'][$hook]); }
                else { $GLOBALS['wp_filter'][$hook] = $value; }
            }
            $_GET = $get;
            if ($page === null) { unset($GLOBALS['pagenow']); }
            else { $GLOBALS['pagenow'] = $page; }
            PLL()->curlang = $language;
            wp_set_current_user($user);
        }
    }

    public static function copyOutcomes(): array
    {
        return array(array(false), array(true));
    }
}
