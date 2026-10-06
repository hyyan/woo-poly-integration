<?php
use Hyyan\WPI\Widgets\LayeredNav;
use PHPUnit\Framework\TestCase;

final class NavigationRequestTest extends TestCase
{
    private $sidebars;
    private $language;
    private array $request;
    private $chosen;
    private array $terms = array();
    private int $attribute;
    private LayeredNav $handler;

    protected function setUp(): void
    {
        $this->sidebars = get_option('sidebars_widgets');
        $this->language = PLL()->curlang;
        $this->request = $_GET;
        $this->chosen = $GLOBALS['_chosen_attributes'] ?? null;
        update_option('sidebars_widgets', array('sidebar-1' => array('woocommerce_layered_nav-99')));
        $GLOBALS['_wp_sidebars_widgets'] = array();
        $this->attribute = wc_create_attribute(array('name' => 'Navigation regression', 'slug' => 'navregression', 'type' => 'select'));
        delete_transient('wc_attribute_taxonomies');
        WC_Cache_Helper::invalidate_cache_group('woocommerce-attributes');
        register_taxonomy('pa_navregression', array('product'));
        foreach (array('en' => 'nav-source', 'de' => 'nav-target') as $language => $slug) {
            $term = wp_insert_term($slug, 'pa_navregression', array('slug' => $slug));
            $this->terms[$language] = $term['term_id'];
            pll_set_term_language($term['term_id'], $language);
        }
        pll_save_term_translations($this->terms);
        PLL()->curlang = PLL()->model->get_language('de');
        $this->handler = (new ReflectionClass(LayeredNav::class))->newInstanceWithoutConstructor();
        $_GET = array();
        $GLOBALS['_chosen_attributes'] = array();
        self::assertFalse(is_admin());
        self::assertNotFalse(is_active_widget(false, false, 'woocommerce_layered_nav', true));
    }

    protected function tearDown(): void
    {
        foreach ($this->terms as $id) {
            wp_delete_term($id, 'pa_navregression');
        }
        wc_delete_attribute($this->attribute);
        unregister_taxonomy('pa_navregression');
        delete_transient('wc_attribute_taxonomies');
        WC_Cache_Helper::invalidate_cache_group('woocommerce-attributes');
        update_option('sidebars_widgets', $this->sidebars);
        $GLOBALS['_wp_sidebars_widgets'] = array();
        PLL()->curlang = $this->language;
        $_GET = $this->request;
        $GLOBALS['_chosen_attributes'] = $this->chosen;
    }

    public function testArrayFilterIsIgnoredWithoutFailure(): void
    {
        $_GET['filter_navregression'] = array('nav-source');
        $this->handler->layeredNavInit();
        self::assertSame(array(), $GLOBALS['_chosen_attributes']);
        self::assertArrayNotHasKey('filter_navregression', $_GET);
    }

    public function testNativeSlugFilterUsesTranslatedSlug(): void
    {
        $_GET['filter_navregression'] = 'nav-source';
        $this->handler->layeredNavInit();
        self::assertSame('nav-target', $_GET['filter_navregression']);
        self::assertSame(array('nav-target'), $GLOBALS['_chosen_attributes']['pa_navregression']['terms']);
    }

    public function testLegacyIntegerFiltersRetainTranslationContract(): void
    {
        $_GET['filter_navregression'] = (string) $this->terms['en'];
        $this->handler->layeredNavInit();
        self::assertSame((string) $this->terms['de'], $_GET['filter_navregression']);
        self::assertSame(array($this->terms['de']), $GLOBALS['_chosen_attributes']['pa_navregression']['terms']);
    }

    public function testUnknownSlugAndMultipleSelectionRemainAvailable(): void
    {
        $_GET['filter_navregression'] = 'nav-source,unknown-slug';
        $this->handler->layeredNavInit();
        self::assertSame('nav-target,unknown-slug', $_GET['filter_navregression']);
    }
}
