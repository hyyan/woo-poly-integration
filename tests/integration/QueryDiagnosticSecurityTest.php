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
    public function testOptionalDiagnosticsUseTheNativeLoggerWithoutRequestSecrets(): void
    {
        $handler = new class extends WC_Log_Handler {
            public array $entries = array();
            public function handle($timestamp, $level, $message, $context) {
                $this->entries[] = array('level' => $level, 'message' => $message, 'context' => $context);
                return true;
            }
        };
        $originalLogger = wc_get_logger();
        $logger = new WC_Logger(array($handler), 'debug');
        $filter = function () use ($logger) { return $logger; };
        add_filter('woocommerce_logging_class', $filter);
        try {
            $coupon = new WC_Coupon();
            $coupon->set_code('diagnostic-native-fixture');
            $integration = (new ReflectionClass(Coupon::class))->newInstanceWithoutConstructor();
            $_SERVER['REQUEST_URI'] = "/cart/?token=private-token\nFORGED_ENTRY";
            Coupon::$enable_logging = false;
            $integration->couponLoaded($coupon);
            self::assertSame(array(), $handler->entries);
            Coupon::$enable_logging = true;
            $integration->couponLoaded($coupon);
            self::assertCount(1, $handler->entries);
            $entry = $handler->entries[0];
            self::assertSame('debug', $entry['level']);
            self::assertSame('woo-poly-integration', $entry['context']['source']);
            self::assertStringContainsString('diagnostic-native-fixture', $entry['message']);
            self::assertStringContainsString('/cart/', $entry['message']);
            self::assertStringNotContainsString('private-token', $entry['message']);
            self::assertStringNotContainsString('FORGED_ENTRY', $entry['message']);
        } finally {
            remove_filter('woocommerce_logging_class', $filter);
            $restore = function () use ($originalLogger) { return $originalLogger; };
            add_filter('woocommerce_logging_class', $restore);
            wc_get_logger();
            remove_filter('woocommerce_logging_class', $restore);
        }
    }

    public static function requests(): array
    {
        return array(array(array('nested')), array("/cart/?token=private-token\nFORGED_ENTRY"), array('/cart/'));
    }

    #[DataProvider('corruptRelationshipValues')]
    public function testRelatedLookupDoesNotCoerceMalformedMetadata(string $suffix): void
    {
        $source = $this->product('en', 'exact-relationship-source');
        $related = $this->product('de', 'exact-relationship-target');
        $corrupt = $this->product('de', 'corrupt-relationship-target');
        update_post_meta($related, Variation::DUPLICATE_KEY, $source);
        update_post_meta($corrupt, Variation::DUPLICATE_KEY, (string) $source . $suffix);
        $ids = Variation::getRelatedVariation($source, true);
        self::assertContains((string) $related, $ids);
        self::assertNotContains((string) $corrupt, $ids);
    }

    public static function corruptRelationshipValues(): array
    {
        return array('text suffix' => array('suffix'), 'SQL text suffix' => array(' OR 1=1'),
            'decimal representation' => array('.0'), 'exponent representation' => array('e0'), 'trailing space' => array(' '),
            'trailing newline' => array("\n"));
    }

    public function testRelatedLookupPreservesStoredPaddedIntegerReferences(): void
    {
        $source = $this->product('en', 'padded-existing-source');
        $target = $this->product('de', 'padded-existing-target');
        $stored = '000' . $source;
        update_post_meta($source, Variation::DUPLICATE_KEY, $stored);
        update_post_meta($target, Variation::DUPLICATE_KEY, $stored);
        $ids = Variation::getRelatedVariation($target, true);
        self::assertContains((string) $source, $ids);
        self::assertContains((string) $target, $ids);
        self::assertSame($stored, get_post_meta($source, Variation::DUPLICATE_KEY, true));
        self::assertSame($stored, get_post_meta($target, Variation::DUPLICATE_KEY, true));
    }

    public function testRelatedLookupKeepsLargeNeighboringIdentifiersDistinct(): void
    {
        $wanted = PHP_INT_MAX - 1;
        $related = $this->product('en', 'large-relationship-target');
        $neighbor = $this->product('de', 'neighbor-relationship-target');
        update_post_meta($related, Variation::DUPLICATE_KEY, (string) $wanted);
        update_post_meta($neighbor, Variation::DUPLICATE_KEY, (string) PHP_INT_MAX);
        $ids = Variation::getRelatedVariation($wanted, true);
        self::assertContains((string) $related, $ids);
        self::assertNotContains((string) $neighbor, $ids);
    }


    public function testRelationshipWriterStoresCanonicalIntegerIds(): void
    {
        $source = $this->product('en', 'padded-relationship-source');
        $handler = (new ReflectionClass(Variation::class))->newInstanceWithoutConstructor();
        $handler->addDuplicateMeta('000' . $source);
        self::assertSame((string) $source, get_post_meta($source, Variation::DUPLICATE_KEY, true));
        self::assertContains((string) $source, Variation::getRelatedVariation($source, true));
    }

    public function testRelationshipWriterRejectsFractionalIdsBeforeMetadataOperations(): void
    {
        $calls = array();
        $observer = static function ($check, $id, $key, $value) use (&$calls) {
            if ($key === Variation::DUPLICATE_KEY) {
                $calls[] = array($id, $value);
                return false;
            }
            return $check;
        };
        add_filter('update_post_metadata', $observer, PHP_INT_MAX, 4);
        try {
            $handler = (new ReflectionClass(Variation::class))->newInstanceWithoutConstructor();
            $handler->addDuplicateMeta(1.5);
            self::assertSame(array(), $calls);
        } finally {
            remove_filter('update_post_metadata', $observer, PHP_INT_MAX);
        }
    }

}
