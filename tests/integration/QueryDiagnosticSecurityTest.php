<?php
use Hyyan\WPI\Coupon;
use Hyyan\WPI\Product\Meta;
use Hyyan\WPI\Product\Variation;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class QueryDiagnosticSecurityTest extends TestCase
{
    private array $products = array();
    private int $user;
    private array $server;
    private $logging;
    private string $logFile;
    private string $originalLog;
    protected function setUp(): void
    {
        $this->user = get_current_user_id();
        wp_set_current_user(1);
        $this->server = $_SERVER;
        $this->logging = Coupon::$enable_logging;
        $this->originalLog = (string) ini_get('error_log');
        $this->logFile = tempnam(sys_get_temp_dir(), 'wpi-log-');
        ini_set('error_log', $this->logFile);
    }
    protected function tearDown(): void
    {
        foreach ($this->products as $id) { wp_delete_post($id, true); }
        $_SERVER = $this->server;
        Coupon::$enable_logging = $this->logging;
        ini_set('error_log', $this->originalLog);
        unlink($this->logFile);
        wp_set_current_user($this->user);
    }
    private function product(string $lang, string $sku): int
    {
        $id = wp_insert_post(array('post_type' => 'product', 'post_status' => 'publish', 'post_title' => 'Query fixture'));
        $this->products[] = $id;
        pll_set_post_language($id, $lang);
        update_post_meta($id, '_sku', wp_slash($sku));
        self::assertSame($sku, get_post_meta($id, '_sku', true));
        return $id;
    }
    #[DataProvider('skus')]
    public function testSkuQueryMatchesStoredValueWithoutAddingSlashes(string $sku): void
    {
        $source = $this->product('en', $sku);
        $this->product('en', $sku);
        $meta = (new ReflectionClass(Meta::class))->newInstanceWithoutConstructor();
        self::assertTrue($meta->suppressInvalidDuplicatedSKUErrorMsg(true, $source, $sku));
    }
    #[DataProvider('skus')]
    public function testNativeFalseLookupCannotBypassSameLanguageDuplicates(string $sku): void
    {
        $source = $this->product('en', $sku);
        $this->product('en', $sku);
        $meta = (new ReflectionClass(Meta::class))->newInstanceWithoutConstructor();
        self::assertTrue($meta->suppressInvalidDuplicatedSKUErrorMsg(false, $source, $sku));
    }
    public static function skus(): array
    {
        return array(array('plain-sku'), array("quote'sku"), array('back\\slash'), array("sku' OR 1=1 --"));
    }
    public function testEmptySkuRetainsNativeNoDuplicateResult(): void
    {
        $source = $this->product('en', '');
        $this->product('en', '');
        $meta = (new ReflectionClass(Meta::class))->newInstanceWithoutConstructor();
        self::assertFalse($meta->suppressInvalidDuplicatedSKUErrorMsg(false, $source, ''));
    }
    public function testSkuQueryDoesNotTreatInjectionTextAsSql(): void
    {
        $source = $this->product('en', 'source');
        $this->product('en', 'other');
        $meta = (new ReflectionClass(Meta::class))->newInstanceWithoutConstructor();
        self::assertFalse($meta->suppressInvalidDuplicatedSKUErrorMsg(true, $source, "sku' OR 1=1 --"));
    }
    public function testRelatedLookupReflectsRelationshipChangesImmediately(): void
    {
        $source = $this->product('en', 'source');
        $target = $this->product('de', 'target');
        update_post_meta($target, Variation::DUPLICATE_KEY, $source);
        self::assertContains((string) $target, Variation::getRelatedVariation($source, true));
        delete_post_meta($target, Variation::DUPLICATE_KEY);
        self::assertNotContains((string) $target, Variation::getRelatedVariation($source, true));
    }
    #[DataProvider('requests')]
    public function testOptionalCouponDiagnosticsNeverWarnForMalformedRequestShape($uri): void
    {
        Coupon::$enable_logging = true;
        $_SERVER['REQUEST_URI'] = $uri;
        $warnings = array();
        set_error_handler(static function ($severity, $message) use (&$warnings) { $warnings[] = $message; return true; });
        try {
            $coupon = new WC_Coupon();
            $coupon->set_code('diagnostic-fixture');
            $handler = (new ReflectionClass(Coupon::class))->newInstanceWithoutConstructor();
            ini_set('error_log', $this->logFile);
            $handler->couponLoaded($coupon);
        } finally { restore_error_handler(); }
        self::assertSame(array(), $warnings);
        self::assertStringNotContainsString('private-token', (string) file_get_contents($this->logFile));
        self::assertStringNotContainsString('FORGED_ENTRY', (string) file_get_contents($this->logFile));
    }
    public static function requests(): array
    {
        return array(array(array('nested')), array("/cart/?token=private-token\nFORGED_ENTRY"), array('/cart/'));
    }
}
