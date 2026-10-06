<?php
use Hyyan\WPI\Taxonomies\Categories;
use PHPUnit\Framework\TestCase;

final class CategoryOutputSecurityTest extends TestCase
{
    private int $term;
    private array $originalGet;

    protected function setUp(): void
    {
        $this->originalGet = $_GET;
        $result = wp_insert_term('Category output regression', 'product_cat');
        self::assertNotWPError($result);
        $this->term = $result['term_id'];
        $_GET = array('from_tag' => (string) $this->term, 'new_lang' => 'de');
    }

    protected function tearDown(): void
    {
        wp_delete_term($this->term, 'product_cat');
        $_GET = $this->originalGet;
    }

    private static function assertNotWPError($value): void
    {
        self::assertFalse(is_wp_error($value));
    }

    private function render(): string
    {
        ob_start();
        try {
            $categories = (new ReflectionClass(Categories::class))->newInstanceWithoutConstructor();
            $categories->copyProductCatCustomFields();
            return ob_get_contents();
        } finally {
            ob_end_clean();
        }
    }

    public function testStoredDisplayTypeCannotCloseTheScriptElement(): void
    {
        update_term_meta($this->term, 'display_type', '</script><script>window.woopolyCategoryMarker=true</script>');
        $output = $this->render();
        self::assertSame(1, substr_count($output, '</script>'));
        self::assertStringNotContainsString('<script>window.woopolyCategoryMarker', $output);
    }

    public function testPlaceholderUrlCannotInjectJavaScriptStatements(): void
    {
        $url = "https://example.test/image';window.woopolyImageMarker=true;//";
        $filter = function () use ($url) { return $url; };
        add_filter('woocommerce_placeholder_img_src', $filter);
        try {
            $output = $this->render();
            self::assertStringNotContainsString($url, $output);
            self::assertStringContainsString('example.test', $output);
        } finally {
            remove_filter('woocommerce_placeholder_img_src', $filter);
        }
    }

    public function testExistingDisplayTypeAndPlaceholderRemainAvailable(): void
    {
        update_term_meta($this->term, 'display_type', 'subcategories');
        $output = $this->render();
        self::assertStringContainsString('subcategories', $output);
        self::assertStringContainsString('product_cat_thumbnail', $output);
        self::assertStringContainsString('product_cat_thumbnail_id', $output);
        self::assertStringContainsString('selected', $output);
    }
}
