<?php
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Hyyan\WPI\Product\Meta;

final class NativeSkuLookupCompatibilityTest extends TestCase
{
    private array $products = array();
    private $language;
    private $status;

    protected function setUp(): void
    {
        $this->language = PLL()->curlang;
        PLL()->curlang = PLL()->model->get_language('de');
        $this->status = $GLOBALS['wp_post_statuses']['wpi_sku_hidden'] ?? null;
        register_post_status('wpi_sku_hidden', array('exclude_from_search' => true));
    }

    protected function tearDown(): void
    {
        foreach (array_reverse($this->products) as $id) { wp_delete_post($id, true); }
        PLL()->curlang = $this->language;
        if ($this->status === null) { unset($GLOBALS['wp_post_statuses']['wpi_sku_hidden']); }
        else { $GLOBALS['wp_post_statuses']['wpi_sku_hidden'] = $this->status; }
    }

    private function product(string $status, string $type, string $language, string $sku, int $parent = 0): int
    {
        $id = wp_insert_post(array('post_type' => $type, 'post_status' => $status,
            'post_title' => 'Native SKU fixture', 'post_parent' => $parent,
            'post_date' => date('Y-m-d H:i:s', time() + ($status === 'future' ? 86400 : -3600))));
        $this->products[] = $id;
        pll_set_post_language($id, $language);
        update_post_meta($id, '_sku', wp_slash($sku));
        self::assertSame($status, get_post_status($id));
        return $id;
    }

    #[DataProvider('statuses')]
    public function testDuplicateStatusAndTypeBoundariesRemain(string $status, string $type, bool $expected): void
    {
        $sku = 'native-status-sku';
        $source = $this->product('publish', 'product', 'en', $sku);
        $this->product($status, $type, 'en', $sku, $type === 'product_variation' ? $source : 0);
        $handler = (new ReflectionClass(Meta::class))->newInstanceWithoutConstructor();
        self::assertSame($expected, $handler->suppressInvalidDuplicatedSKUErrorMsg(false, $source, $sku));
    }

    public static function statuses(): array
    {
        $cases = array();
        foreach (array('publish', 'draft', 'pending', 'future', 'private', 'inherit', 'wpi_sku_hidden', 'wpi_unregistered', 'trash', 'auto-draft') as $status) {
            foreach (array('product', 'product_variation') as $type) {
                $cases[$status . ' ' . $type] = array($status, $type, !in_array($status, array('trash', 'auto-draft'), true));
            }
        }
        $cases['other post type'] = array('publish', 'post', false);
        return $cases;
    }

    #[DataProvider('skuValues')]
    public function testOnlyTranslatedLanguageDuplicatesRemainAllowed(string $sku): void
    {
        $source = $this->product('publish', 'product', 'en', $sku);
        $this->product('publish', 'product', 'de', $sku);
        $handler = (new ReflectionClass(Meta::class))->newInstanceWithoutConstructor();
        self::assertFalse($handler->suppressInvalidDuplicatedSKUErrorMsg(true, $source, $sku));
        $this->product('private', 'product', 'en', $sku);
        self::assertTrue($handler->suppressInvalidDuplicatedSKUErrorMsg(false, $source, $sku));
    }

    public static function skuValues(): array
    {
        return array('zero' => array('0'), 'quote' => array("same'sku"), 'backslash' => array('same\\sku'));
    }
}
