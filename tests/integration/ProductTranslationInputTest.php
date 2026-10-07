<?php
use Hyyan\WPI\Product\Meta;
use Hyyan\WPI\Product\Variable;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class ProductTranslationInputTest extends TestCase
{
    private array $ids = array();
    private array $handlers = array();
    private WC_Product_Variable $source;
    private WC_Product_Variable $target;
    private array $originalGet;
    private $originalPage;
    private $originalLanguage;
    private int $originalUser;

    protected function setUp(): void
    {
        $this->originalGet = $_GET;
        $this->originalPage = $GLOBALS['pagenow'] ?? null;
        $this->originalLanguage = PLL()->curlang;
        $this->originalUser = get_current_user_id();
        $_GET = array();
        $GLOBALS['pagenow'] = 'index.php';
        wp_set_current_user(1);
        foreach (array('en', 'de') as $language) {
            $product = new WC_Product_Variable();
            $product->set_name('Translation input '.$language);
            $product->set_status('publish');
            $attribute = new WC_Product_Attribute();
            $attribute->set_name('Size');
            $attribute->set_options(array('Small', 'Large'));
            $attribute->set_variation(true);
            $product->set_attributes(array($attribute));
            $product->set_default_attributes(array('size' => $language === 'en' ? 'small' : 'large'));
            $product->set_weight($language === 'en' ? '5' : '1');
            $product->save();
            pll_set_post_language($product->get_id(), $language);
            $this->ids[] = $product->get_id();
            if ($language === 'en') {
                $this->source = $product;
            } else {
                $this->target = $product;
            }
        }
        $variation = new WC_Product_Variation();
        $variation->set_parent_id($this->source->get_id());
        $variation->set_status('publish');
        $variation->set_regular_price('10');
        $variation->set_attributes(array('size' => 'small'));
        $variation->save();
        $this->ids[] = $variation->get_id();
        $GLOBALS['pagenow'] = 'post-new.php';
        $_GET = array('from_post' => (string) $this->source->get_id(), 'new_lang' => 'de',
            '_wpnonce' => wp_create_nonce('new-post-translation'));
    }

    protected function tearDown(): void
    {
        $_GET = array();
        wp_set_current_user(1);
        $children = get_posts(array('post_type' => 'product_variation', 'post_parent__in' => array($this->source->get_id(), $this->target->get_id()), 'posts_per_page' => -1, 'post_status' => 'any', 'lang' => '', 'fields' => 'ids'));
        foreach (array_unique(array_merge($children, array_reverse($this->ids))) as $id) {
            wp_delete_post($id, true);
        }
        $_GET = $this->originalGet;
        $GLOBALS['pagenow'] = $this->originalPage;
        PLL()->curlang = $this->originalLanguage;
        wp_set_current_user($this->originalUser);
        foreach ($this->handlers as $handler) {
            foreach (array('delete_post_metadata', 'add_post_metadata', 'update_post_metadata') as $hook) {
                remove_filter($hook, array($handler, 'skipDefaultAttributesMeta'), 10);
            }
        }
        remove_filter('pll_copy_post_metas', array(Meta::class, 'wpi_filter_pll_metas'), 10);
    }

    private function handler(string $class)
    {
        $handler = (new ReflectionClass($class))->newInstanceWithoutConstructor();
        $this->handlers[] = $handler;
        return $handler;
    }

    private function invoke(string $method): void
    {
        $class = $method === 'syncTaxonomiesAndProductAttributes' ? Meta::class : Variable::class;
        $this->handler($class)->$method($this->target->get_id(), get_post($this->target->get_id()), false);
    }

    #[DataProvider('invalidRequests')]
    public function testMalformedTranslationRequestDoesNotCopyOrChangeProducts(string $method, string $field, $value): void
    {
        if ($value === null) {
            unset($_GET[$field]);
        } else {
            $_GET[$field] = $value;
        }
        $before = get_post_meta($this->target->get_id(), '_default_attributes', true);
        $this->invoke($method);
        self::assertSame($before, get_post_meta($this->target->get_id(), '_default_attributes', true));
        self::assertSame('1', get_post_meta($this->target->get_id(), '_weight', true));
        self::assertSame('5', get_post_meta($this->source->get_id(), '_weight', true));
        self::assertSame(array(), wc_get_product($this->target->get_id())->get_children());
    }

    public static function invalidRequests(): array
    {
        $cases = array();
        foreach (array('syncTaxonomiesAndProductAttributes', 'duplicateVariations', 'syncDefaultAttributes') as $method) {
            foreach (array('array source' => array('from_post', array('1')), 'expression source' => array('from_post', '1 OR 1'), 'float source' => array('from_post', 1.5), 'array language' => array('new_lang', array('de')), 'unknown language' => array('new_lang', 'unknown-language'), 'missing language' => array('new_lang', null)) as $name => $request) {
                $cases[$method.' '.$name] = array_merge(array($method), $request);
            }
        }
        return $cases;
    }

    public function testNormalTranslationCopiesMetadataVariationsAndDefaults(): void
    {
        $this->invoke('syncTaxonomiesAndProductAttributes');
        self::assertSame('5', get_post_meta($this->target->get_id(), '_weight', true));
        $this->invoke('duplicateVariations');
        $children = wc_get_product($this->target->get_id())->get_children();
        self::assertCount(1, $children);
        self::assertSame('de', pll_get_post_language($children[0]));
        self::assertSame('10', wc_get_product($children[0])->get_regular_price());
        $this->invoke('syncDefaultAttributes');
        self::assertSame(array('size' => 'small'), get_post_meta($this->target->get_id(), '_default_attributes', true));
    }

    public function testTranslationRetainsEveryCustomDefaultAttribute(): void
    {
        $_GET = array();
        $GLOBALS['pagenow'] = 'index.php';
        pll_save_post_translations(array('en' => $this->source->get_id(), 'de' => $this->target->get_id()));
        $attributes = array();
        foreach (array('Size' => array('Small', 'Large'), 'Finish' => array('Matte', 'Gloss')) as $name => $options) {
            $attribute = new WC_Product_Attribute();
            $attribute->set_name($name);
            $attribute->set_options($options);
            $attribute->set_variation(true);
            $attributes[] = $attribute;
        }
        $this->source->set_attributes($attributes);
        $this->source->set_default_attributes(array('size' => 'small', 'finish' => 'matte'));
        $this->source->save();
        self::assertSame(array('de' => array('size' => 'small', 'finish' => 'matte')),
            Hyyan\WPI\Utilities::getDefaultAttributesTranslation($this->source->get_id(), 'de'));
    }

    public function testDeniedSourceReadPermissionPreventsMetadataCopy(): void
    {
        $this->source->set_status('private');
        $this->source->save();
        wp_set_current_user(get_user_by('login', 'lab-contributor')->ID);
        self::assertFalse(current_user_can('read_post', $this->source->get_id()));
        $this->invoke('syncTaxonomiesAndProductAttributes');
        self::assertSame('1', get_post_meta($this->target->get_id(), '_weight', true));
    }

    #[DataProvider('unverifiedCreationRequests')]
    public function testUnverifiedCreationCannotCopyProducts(string $method, string $nonceCase, string $login): void
    {
        if ($login !== 'lab-admin') { wp_set_current_user(get_user_by('login', $login)->ID); }
        $_GET['_wpnonce'] = wp_create_nonce('new-post-translation');
        if ($nonceCase === 'missing') { unset($_GET['_wpnonce']); }
        if ($nonceCase === 'invalid') { $_GET['_wpnonce'] = 'invalid'; }
        if ($nonceCase === 'array') { $_GET['_wpnonce'] = array('invalid'); }
        if ($nonceCase === 'wrong action') { $_GET['_wpnonce'] = wp_create_nonce('update-post_' . $this->target->get_id()); }
        $this->invoke($method);
        self::assertSame('1', get_post_meta($this->target->get_id(), '_weight', true));
        self::assertSame(array('size' => 'large'), get_post_meta($this->target->get_id(), '_default_attributes', true));
        self::assertSame(array(), wc_get_product($this->target->get_id())->get_children());
    }

    public static function unverifiedCreationRequests(): array
    {
        $cases = array();
        foreach (array('syncTaxonomiesAndProductAttributes', 'duplicateVariations', 'syncDefaultAttributes') as $method) {
            foreach (array('missing', 'invalid', 'array', 'wrong action') as $nonce) {
                $cases[$method . ' ' . $nonce] = array($method, $nonce, 'lab-admin');
            }
            foreach (array('lab-subscriber', 'lab-customer', 'lab-contributor') as $login) {
                $cases[$method . ' ' . $login] = array($method, 'valid', $login);
            }
        }
        return $cases;
    }

    #[DataProvider('creationEntryRequests')]
    public function testCreationEntryVerifiesBeforeCallbacks(string $class, string $case, string $login, bool $expected, bool $update): void
    {
        if ($login !== 'lab-admin') { wp_set_current_user(get_user_by('login', $login)->ID); }
        $_GET['_wpnonce'] = wp_create_nonce('new-post-translation');
        if ($case === 'missing nonce') { unset($_GET['_wpnonce']); }
        if ($case === 'invalid nonce') { $_GET['_wpnonce'] = 'invalid'; }
        if ($case === 'array nonce') { $_GET['_wpnonce'] = array('invalid'); }
        if ($case === 'wrong action') { $_GET['_wpnonce'] = wp_create_nonce('update-post_' . $this->target->get_id()); }
        if ($case === 'no source') { unset($_GET['from_post']); }
        if ($case === 'malformed source') { $_GET['from_post'] .= ' OR 1=1'; }
        if ($case === 'unknown language') { $_GET['new_lang'] = 'not-registered'; }
        $methods = $class === Meta::class ? array('syncTaxonomiesAndProductAttributes') : array('duplicateVariations', 'syncDefaultAttributes');
        $handler = $this->getMockBuilder($class)->disableOriginalConstructor()->onlyMethods($methods)->getMock();
        $post = get_post($this->target->get_id());
        foreach ($methods as $method) {
            $handler->expects($expected ? $this->once() : $this->never())->method($method)
                ->with($this->target->get_id(), $post, $update);
        }
        $handler->handleNewProduct($this->target->get_id(), $post, $update);
    }

    public static function creationEntryRequests(): array
    {
        $cases = array();
        foreach (array(Meta::class, Variable::class) as $class) {
            foreach (array('missing nonce', 'invalid nonce', 'array nonce', 'wrong action', 'malformed source', 'unknown language') as $case) {
                $cases[$class . ' ' . $case] = array($class, $case, 'lab-admin', false, false);
            }
            foreach (array('lab-subscriber', 'lab-customer', 'lab-contributor') as $login) {
                $cases[$class . ' denied ' . $login] = array($class, 'valid', $login, false, false);
            }
            foreach (array('lab-admin', 'lab-manager') as $login) {
                $cases[$class . ' authorized ' . $login] = array($class, 'valid', $login, true, false);
            }
            $cases[$class . ' existing update'] = array($class, 'valid', 'lab-admin', false, true);
            $cases[$class . ' no source'] = array($class, 'no source', 'lab-admin', false, false);
        }
        return $cases;
    }

    public function testShopManagerNativeCreationNoncePreservesProductCopy(): void
    {
        wp_set_current_user(get_user_by('login', 'lab-manager')->ID);
        $_GET['_wpnonce'] = wp_create_nonce('new-post-translation');
        self::assertTrue(current_user_can('edit_products'));
        $this->invoke('syncTaxonomiesAndProductAttributes');
        $this->invoke('duplicateVariations');
        $this->invoke('syncDefaultAttributes');
        self::assertSame('5', get_post_meta($this->target->get_id(), '_weight', true));
        self::assertSame(array('size' => 'small'), get_post_meta($this->target->get_id(), '_default_attributes', true));
        self::assertCount(1, wc_get_product($this->target->get_id())->get_children());
    }


    public function testLegacyNativeProductTranslationLinkReceivesCreationNonce(): void
    {
        $link = add_query_arg(array('post_type' => 'product', 'from_post' => $this->source->get_id(),
            'new_lang' => 'de'), admin_url('post-new.php'));
        $result = apply_filters('pll_get_new_post_translation_link', $link,
            PLL()->model->get_language('de'), $this->source->get_id());
        wp_parse_str(wp_parse_url(html_entity_decode($result, ENT_QUOTES, 'UTF-8'), PHP_URL_QUERY), $args);
        self::assertArrayHasKey('_wpnonce', $args);
        self::assertNotFalse(wp_verify_nonce($args['_wpnonce'], 'new-post-translation'));
        unset($args['_wpnonce']);
        self::assertSame(array('post_type' => 'product', 'from_post' => (string) $this->source->get_id(), 'new_lang' => 'de'), $args);
    }

    public function testCurrentNativeProductTranslationLinkIsUnchanged(): void
    {
        $link = wp_nonce_url(add_query_arg(array('post_type' => 'product', 'from_post' => $this->source->get_id(),
            'new_lang' => 'de'), admin_url('post-new.php')), 'new-post-translation');
        self::assertSame($link, apply_filters('pll_get_new_post_translation_link', $link,
            PLL()->model->get_language('de'), $this->source->get_id()));
        $page = wp_insert_post(array('post_type' => 'page', 'post_status' => 'draft', 'post_title' => 'Nonce page fixture'));
        $this->ids[] = $page;
        $pageLink = add_query_arg(array('post_type' => 'page', 'from_post' => $page, 'new_lang' => 'de'), admin_url('post-new.php'));
        self::assertSame($pageLink, apply_filters('pll_get_new_post_translation_link', $pageLink,
            PLL()->model->get_language('de'), $page));
    }

}
