<?php
use Hyyan\WPI\HooksInterface;
use Hyyan\WPI\Product\Product;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class FeaturedProductSecurityTest extends TestCase
{
    private array $ids = array();
    private int $source;
    private int $translated;
    private int $unrelated;

    protected function setUp(): void
    {
        wp_set_current_user(1);
        foreach (array('en', 'de') as $language) {
            $product = new WC_Product_Simple();
            $product->set_name('Featured regression ' . $language);
            $product->set_status('publish');
            $product->save();
            $this->ids[] = $product->get_id();
            pll_set_post_language($product->get_id(), $language);
            if ($language === 'en') {
                $this->source = $product->get_id();
            } else {
                $this->translated = $product->get_id();
            }
        }
        pll_save_post_translations(array('en' => $this->source, 'de' => $this->translated));
        $other = new WC_Product_Simple();
        $other->set_name('Unrelated featured regression');
        $other->save();
        $this->unrelated = $other->get_id();
        $this->ids[] = $this->unrelated;
        $_GET = $_POST = $_REQUEST = array();
    }

    protected function tearDown(): void
    {
        wp_set_current_user(1);
        foreach (array_reverse($this->ids) as $id) {
            wp_delete_post($id, true);
        }
        $_GET = $_POST = $_REQUEST = array();
    }

    private function invoke($nonce, $id = null): void
    {
        $_GET = array('product_id' => $id ?? $this->source);
        if ($nonce !== null) {
            $_GET['_wpnonce'] = $nonce;
        }
        $_REQUEST = $_GET;
        ob_start();
        try { Product::sync_ajax_woocommerce_feature_product(); } catch (WooPolyTestDie $e) {}
        finally { ob_end_clean(); }
    }

    #[DataProvider('invalidProductIds')]
    public function testMalformedProductIdCannotToggleValidPrefix($value): void
    {
        $id = $value === 'suffix' ? $this->source . 'suffix' : $value;
        $this->invoke(wp_create_nonce('woocommerce-feature-product'), $id);
        self::assertFalse(wc_get_product($this->source)->get_featured());
        self::assertFalse(wc_get_product($this->translated)->get_featured());
    }

    public static function invalidProductIds(): array
    {
        return array(array('suffix'), array(-1), array(0), array(true), array(array(1)),
            array((string) PHP_INT_MAX . '0'));
    }

    #[DataProvider('deniedProducts')]
    public function testFeaturedRequiresPermissionForEveryAffectedProduct(bool $translation): void
    {
        $id = $translation ? $this->translated : $this->source;
        $deny = function ($caps, $cap, $user, $args) use ($id) {
            return $cap === 'edit_post' && ($args[0] ?? null) === $id ? array('do_not_allow') : $caps;
        };
        add_filter('map_meta_cap', $deny, 100, 4);
        try {
            $this->invoke(wp_create_nonce('woocommerce-feature-product'));
            self::assertFalse(wc_get_product($this->translated)->get_featured());
        } finally { remove_filter('map_meta_cap', $deny, 100); }
    }

    public static function deniedProducts(): array { return array(array(false), array(true)); }

    #[DataProvider('unauthorizedRoles')]
    public function testUnauthorizedRoleCannotFeatureTranslationWithValidNonce(string $login): void
    {
        wp_set_current_user(get_user_by('login', $login)->ID);
        $this->invoke(wp_create_nonce('woocommerce-feature-product'));
        self::assertFalse(wc_get_product($this->source)->get_featured());
        self::assertFalse(wc_get_product($this->translated)->get_featured());
    }

    public static function unauthorizedRoles(): array
    {
        return array(array('lab-subscriber'), array('lab-customer'), array('lab-contributor'));
    }

    #[DataProvider('invalidNonces')]
    public function testInvalidNonceCannotFeatureTranslation($nonce): void
    {
        $this->invoke($nonce);
        self::assertFalse(wc_get_product($this->translated)->get_featured());
    }

    public static function invalidNonces(): array
    {
        return array('missing' => array(null), 'invalid' => array('invalid'), 'nested' => array(array('invalid')));
    }

    #[DataProvider('authorizedRoles')]
    public function testAuthorizedTogglePreservesOriginalSynchronization(string $login): void
    {
        wp_set_current_user(get_user_by('login', $login)->ID);
        self::assertSame(5, has_action('wp_ajax_woocommerce_feature_product',
            array(Product::class, 'sync_ajax_woocommerce_feature_product')));
        $this->invoke(wp_create_nonce('woocommerce-feature-product'));
        self::assertFalse(wc_get_product($this->source)->get_featured());
        self::assertTrue(wc_get_product($this->translated)->get_featured());
        self::assertFalse(wc_get_product($this->unrelated)->get_featured());
        $source = wc_get_product($this->source);
        $source->set_featured(true);
        $source->save();
        $this->invoke(wp_create_nonce('woocommerce-feature-product'));
        self::assertFalse(wc_get_product($this->translated)->get_featured());
    }

    public static function authorizedRoles(): array
    {
        return array(array('lab-admin'), array('lab-manager'));
    }

    public function testPostNonceKeepsWooCommerceRequestContract(): void
    {
        $_GET = array('product_id' => $this->source);
        $_POST = array('_wpnonce' => wp_create_nonce('woocommerce-feature-product'));
        $_REQUEST = array_merge($_GET, $_POST);
        Product::sync_ajax_woocommerce_feature_product();
        self::assertTrue(wc_get_product($this->translated)->get_featured());
    }

    public function testDisabledVisibilitySynchronizationRemainsDisabled(): void
    {
        $disable = function ($metas) { $metas[] = '_visibility'; return $metas; };
        add_filter(HooksInterface::PRODUCT_DISABLED_META_SYNC_FILTER, $disable);
        try {
            $this->invoke(wp_create_nonce('woocommerce-feature-product'));
            self::assertFalse(wc_get_product($this->translated)->get_featured());
        } finally {
            remove_filter(HooksInterface::PRODUCT_DISABLED_META_SYNC_FILTER, $disable);
        }
    }

    public function testMissingProductIdIsHarmless(): void
    {
        $_GET = $_REQUEST = array('_wpnonce' => wp_create_nonce('woocommerce-feature-product'));
        Product::sync_ajax_woocommerce_feature_product();
        self::assertFalse(wc_get_product($this->translated)->get_featured());
    }
}
