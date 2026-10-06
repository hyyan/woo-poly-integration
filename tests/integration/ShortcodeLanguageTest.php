<?php
use Hyyan\WPI\Pages;
use PHPUnit\Framework\TestCase;

final class ShortcodeLanguageTest extends TestCase
{
    private array $products = array();
    private array $categories = array();
    private $originalLanguage;
    private Pages $handler;

    protected function setUp(): void
    {
        $this->originalLanguage = PLL()->curlang;
        $this->handler = (new ReflectionClass(Pages::class))->newInstanceWithoutConstructor();
        foreach (array('en', 'de') as $language) {
            $product = new WC_Product_Simple();
            $product->set_name('Shortcode regression ' . $language);
            $product->set_status('publish');
            $product->set_regular_price('20');
            $product->save();
            $this->products[$language] = $product->get_id();
            pll_set_post_language($product->get_id(), $language);
            $term = wp_insert_term('Shortcode regression ' . $language, 'product_cat');
            $this->categories[$language] = $term['term_id'];
            pll_set_term_language($term['term_id'], $language);
        }
        pll_save_post_translations($this->products);
        pll_save_term_translations($this->categories);
    }

    protected function tearDown(): void
    {
        PLL()->curlang = $this->originalLanguage;
        foreach ($this->products as $id) {
            wp_delete_post($id, true);
        }
        foreach ($this->categories as $id) {
            wp_delete_term($id, 'product_cat');
        }
    }

    public function testProductIdsTranslateInBothLanguagesAndRemoveConflictingSingleId(): void
    {
        foreach (array('en', 'de') as $language) {
            PLL()->curlang = PLL()->model->get_language($language);
            $result = $this->handler->addShortcodeLanguageFilter(
                array('p' => $this->products['en'], 'orderby' => 'price'),
                array('ids' => (string) $this->products['en']));
            self::assertSame(array($this->products[$language]), $result['post__in']);
            self::assertArrayNotHasKey('p', $result);
            self::assertSame('price', $result['orderby']);
        }
    }

    public function testProductShortcodeWithoutIdsPreservesExplicitLanguageAndDefaultsCurrentLanguage(): void
    {
        PLL()->curlang = PLL()->model->get_language('de');
        self::assertSame(array('lang' => 'en'),
            $this->handler->addShortcodeLanguageFilter(array('lang' => 'en'), array('ids' => '')));
        self::assertSame(array('lang' => 'de'),
            $this->handler->addShortcodeLanguageFilter(array(), array()));
    }

    public function testCategoryIdsAndParentTranslateInBothLanguages(): void
    {
        foreach (array('en', 'de') as $language) {
            PLL()->curlang = PLL()->model->get_language($language);
            $result = $this->handler->addShortcodeLanguageFilterCategories(
                array('columns' => '3'), array(),
                array('ids' => (string) $this->categories['en'], 'parent' => (string) $this->categories['en']),
                'product_categories');
            self::assertSame((string) $this->categories[$language], $result['ids']);
            self::assertSame($this->categories[$language], $result['parent']);
            self::assertSame('3', $result['columns']);
        }
    }

    public function testCategoryShortcodeWithoutIdsOrParentPreservesAttributes(): void
    {
        $out = array('hide_empty' => '0', 'columns' => '3');
        self::assertSame($out, $this->handler->addShortcodeLanguageFilterCategories(
            $out, array(), array('ids' => '', 'parent' => ''), 'product_categories'));
    }
    public function testCommaSeparatedIdsKeepEveryTranslatedEntry(): void
    {
        PLL()->curlang = PLL()->model->get_language('de');
        $products = $this->handler->addShortcodeLanguageFilter(array(),
            array('ids' => implode(',', $this->products)));
        self::assertSame(array($this->products['de'], $this->products['de']), $products['post__in']);
        $categories = $this->handler->addShortcodeLanguageFilterCategories(array(), array(),
            array('ids' => implode(',', $this->categories)), 'product_categories');
        self::assertSame($this->categories['de'] . ',' . $this->categories['de'], $categories['ids']);
    }

}
