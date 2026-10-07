<?php
use Hyyan\WPI\Cart;
use Hyyan\WPI\Utilities;
use Hyyan\WPI\Product\Variation;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class VariationRelationshipPathsTest extends TestCase
{
    private array $parents = array();
    private array $children = array();
    private $language;
    private $page;
    private int $user;

    protected function setUp(): void
    {
        $this->language = PLL()->curlang;
        $this->page = $GLOBALS['pagenow'] ?? null;
        $this->user = get_current_user_id();
        $GLOBALS['pagenow'] = 'index.php';
        wp_set_current_user(1);
        foreach (array('en', 'de') as $lang) {
            $parent = new WC_Product_Variable();
            $parent->set_name('Relationship paths ' . $lang);
            $parent->set_status('publish');
            $parent->save();
            $this->parents[$lang] = $parent->get_id();
            pll_set_post_language($parent->get_id(), $lang);
            $child = new WC_Product_Variation();
            $child->set_parent_id($parent->get_id());
            $child->set_status('publish');
            $child->set_regular_price('12');
            $child->save();
            $this->children[$lang] = $child->get_id();
            pll_set_post_language($child->get_id(), $lang);
        }
        pll_save_post_translations($this->parents);
        update_post_meta($this->children['en'], Variation::DUPLICATE_KEY, $this->children['en']);
        PLL()->curlang = PLL()->model->get_language('de');
    }

    protected function tearDown(): void
    {
        foreach ($this->parents as $id) {
            foreach (get_posts(array('post_type' => 'product_variation', 'post_parent' => $id,
                'post_status' => 'any', 'numberposts' => -1, 'lang' => '')) as $child) {
                wp_delete_post($child->ID, true);
            }
            wp_delete_post($id, true);
        }
        PLL()->curlang = $this->language;
        if ($this->page === null) { unset($GLOBALS['pagenow']); }
        else { $GLOBALS['pagenow'] = $this->page; }
        wp_set_current_user($this->user);
    }

    #[DataProvider('relationshipValues')]
    public function testCartLookupMatchesOnlyIntegerRelationships(string $form, bool $valid): void
    {
        $value = $this->value($form);
        update_post_meta($this->children['de'], Variation::DUPLICATE_KEY, $value);
        $cart = (new ReflectionClass(Cart::class))->newInstanceWithoutConstructor();
        $target = $cart->getVariationTranslation($this->children['en'], 'de');
        if ($valid) { self::assertNotFalse($target); self::assertSame($this->children['de'], $target->get_id()); }
        else { self::assertFalse($target); }
        self::assertSame($value, get_post_meta($this->children['de'], Variation::DUPLICATE_KEY, true));
    }

    #[DataProvider('relationshipValues')]
    public function testStockAndCouponLookupMatchesOnlyIntegerRelationships(string $form, bool $valid): void
    {
        $value = $this->value($form);
        update_post_meta($this->children['de'], Variation::DUPLICATE_KEY, $value);
        self::assertSame($valid ? $this->children['de'] : $this->children['en'],
            Utilities::get_translated_variation($this->children['en'], 'de'));
        self::assertSame($value, get_post_meta($this->children['de'], Variation::DUPLICATE_KEY, true));
    }

    #[DataProvider('relationshipValues')]
    public function testCopyReusesOnlyValidIntegerRelationships(string $form, bool $valid): void
    {
        update_post_meta($this->children['de'], Variation::DUPLICATE_KEY, $this->value($form));
        $copy = new Variation(wc_get_product($this->parents['en']), wc_get_product($this->parents['de']));
        $copy->duplicate();
        $ids = get_posts(array('post_type' => 'product_variation', 'post_parent' => $this->parents['de'],
            'post_status' => 'any', 'numberposts' => -1, 'lang' => '', 'fields' => 'ids'));
        self::assertCount(1, $ids);
        if ($valid) { self::assertContains($this->children['de'], $ids); }
        else { self::assertNotContains($this->children['de'], $ids); }
        self::assertSame('12', wc_get_product($ids[0])->get_regular_price());
    }

    private function value(string $form): string
    {
        return $form === 'padded' ? '000' . $this->children['en']
            : (string) $this->children['en'] . ($form === 'space' ? ' ' : ($form === 'suffix' ? 'suffix' : ''));
    }

    public static function relationshipValues(): array
    {
        return array('canonical' => array('canonical', true), 'padded' => array('padded', true),
            'trailing space' => array('space', false), 'text suffix' => array('suffix', false));
    }
}
