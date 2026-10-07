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
        $_GET = array('from_post' => (string) $this->source->get_id(), 'new_lang' => 'de');
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
}
