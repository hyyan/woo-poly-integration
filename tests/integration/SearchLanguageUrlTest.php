<?php
use Hyyan\WPI\Pages;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class SearchLanguageUrlTest extends TestCase
{
    private $originalQuery;
    private $originalShop;
    private array $pages = array();
    private Pages $handler;

    protected function setUp(): void
    {
        $this->originalQuery = $GLOBALS['wp_query'];
        $this->originalShop = get_option('woocommerce_shop_page_id');
        $GLOBALS['wp_query'] = new WP_Query();
        $GLOBALS['wp_query']->is_post_type_archive = true;
        $GLOBALS['wp_query']->set('post_type', 'product');
        foreach (array('en', 'de') as $language) {
            $id = wp_insert_post(array(
                'post_type' => 'page',
                'post_status' => 'publish',
                'post_title' => 'Search regression shop ' . $language,
                'post_name' => 'search-regression-shop-' . $language,
            ));
            $this->pages[$language] = $id;
            pll_set_post_language($id, $language);
        }
        pll_save_post_translations($this->pages);
        update_option('woocommerce_shop_page_id', $this->pages['en']);
        $this->handler = (new ReflectionClass(Pages::class))->newInstanceWithoutConstructor();
    }

    protected function tearDown(): void
    {
        update_option('woocommerce_shop_page_id', $this->originalShop);
        $GLOBALS['wp_query'] = $this->originalQuery;
        foreach ($this->pages as $id) {
            wp_delete_post($id, true);
        }
    }

    public function testProductSearchPreservesBothLanguageUrlsAndQuery(): void
    {
        $GLOBALS['wp_query']->is_search = true;
        foreach (array('en' => '/?s=shirt&post_type=product', 'de' => '/de/?s=shirt&post_type=product') as $language => $path) {
            $url = home_url($path);
            self::assertSame($url, $this->handler->translateShopUrl($url, $language));
        }
    }

    public function testShopArchiveStillTranslatesSlugAndPreservesQuery(): void
    {
        $source = get_post($this->pages['en'])->post_name;
        $translated = get_post($this->pages['de'])->post_name;
        $url = home_url('/de/' . $source . '/?orderby=price');
        self::assertSame(home_url('/de/' . $translated . '/?orderby=price'),
            $this->handler->translateShopUrl($url, 'de'));
    }

    #[DataProvider('shopUrlComponents')]
    public function testShopSlugTranslationDoesNotChangeOtherUrlComponents(string $url, string $expected): void
    {
        $values = array('%source%' => get_post($this->pages['en'])->post_name,
                        '%target%' => get_post($this->pages['de'])->post_name);
        self::assertSame(strtr($expected, $values), $this->handler->translateShopUrl(strtr($url, $values), 'de'));
    }

    public static function shopUrlComponents(): array
    {
        return array(
            array('https://%source%.example.test/de/%source%/?return=%source%', 'https://%source%.example.test/de/%target%/?return=%source%'),
            array('https://example.test/de/%source%/#%source%', 'https://example.test/de/%target%/#%source%'),
            array('https://%source%.example.test/de/', 'https://%source%.example.test/de/'),
            array('https://example.test/de/?return=%source%', 'https://example.test/de/?return=%source%'),
            array('https://example.test/de/%source%-sale/', 'https://example.test/de/%source%-sale/'),
            array('/de/%source%/page/2/?return=%source%#%source%', '/de/%target%/page/2/?return=%source%#%source%'),
            array('//%source%.example.test/de/%source%/?return=%source%', '//%source%.example.test/de/%target%/?return=%source%'),
        );
    }

    public function testNonProductArchiveRemainsUnchanged(): void
    {
        $url = home_url('/?orderby=price');
        $GLOBALS['wp_query']->is_post_type_archive = false;
        self::assertSame($url, $this->handler->translateShopUrl($url, 'de'));
    }
}
