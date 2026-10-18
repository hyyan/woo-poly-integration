<?php
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class NativeCatalogCompatibilityTest extends TestCase
{
    private array $catalogs;

    protected function setUp(): void
    {
        $this->catalogs = array();
        foreach (array('woocommerce', 'woo-poly-integration') as $domain) {
            $this->catalogs[$domain] = $GLOBALS['l10n'][$domain] ?? null;
            unset($GLOBALS['l10n'][$domain]);
            $GLOBALS['l10n'][$domain] = new MO();
        }
    }

    protected function tearDown(): void
    {
        foreach ($this->catalogs as $domain => $catalog) {
            if ($catalog === null) { unset($GLOBALS['l10n'][$domain]); }
            else { $GLOBALS['l10n'][$domain] = $catalog; }
        }
    }

    private function lookup(string $text, ?string $context): string
    {
        return $context === null ? __($text, 'woo-poly-integration') : _x($text, $context, 'woo-poly-integration');
    }

    #[DataProvider('nativeMessages')]
    public function testExistingWooCommerceCatalogRetainsTranslation(string $text, ?string $context): void
    {
        $GLOBALS['l10n']['woocommerce']->add_entry(new Translation_Entry(array('singular' => $text,
            'context' => $context, 'translations' => array('Existing Woo ' . $text))));
        self::assertSame('Existing Woo ' . $text, $this->lookup($text, $context));
    }

    #[DataProvider('nativeMessages')]
    public function testCurrentPluginCatalogHasPrecedence(string $text, ?string $context): void
    {
        foreach (array('woocommerce' => 'Existing Woo ', 'woo-poly-integration' => 'Current plugin ') as $domain => $prefix) {
            $GLOBALS['l10n'][$domain]->add_entry(new Translation_Entry(array('singular' => $text,
                'context' => $context, 'translations' => array($prefix . $text))));
        }
        self::assertSame('Current plugin ' . $text, $this->lookup($text, $context));
    }

    #[DataProvider('nativeMessages')]
    public function testExistingWooCommerceGenericFiltersRetainArguments(string $text, ?string $context): void
    {
        $hook = $context === null ? 'gettext' : 'gettext_with_context';
        $seen = null;
        $filter = static function ($translated, $source, $third, $fourth = null) use ($text, $context, &$seen) {
            $domain = $context === null ? $third : $fourth;
            if ($domain === 'woocommerce' && $source === $text) {
                $seen = array($source, $context === null ? null : $third, $domain);
                return 'Existing Woo filter ' . $text;
            }
            return $translated;
        };
        add_filter($hook, $filter, 10, 4);
        try {
            self::assertSame('Existing Woo filter ' . $text, $this->lookup($text, $context));
            self::assertSame(array($text, $context, 'woocommerce'), $seen);
        } finally { remove_filter($hook, $filter, 10); }
    }

    #[DataProvider('nativeMessages')]
    public function testExistingWooCommerceDomainFiltersRetainArguments(string $text, ?string $context): void
    {
        $hook = $context === null ? 'gettext_woocommerce' : 'gettext_with_context_woocommerce';
        $filter = static function ($translated, $source) use ($text) {
            return $source === $text ? 'Existing Woo domain ' . $text : $translated;
        };
        add_filter($hook, $filter, 10, 4);
        try { self::assertSame('Existing Woo domain ' . $text, $this->lookup($text, $context)); }
        finally { remove_filter($hook, $filter, 10); }
    }

    #[DataProvider('nativeMessages')]
    public function testExplicitCurrentSourceEntryPrecedesWooCommerceCatalog(string $text, ?string $context): void
    {
        $GLOBALS['l10n']['woocommerce']->add_entry(new Translation_Entry(array('singular' => $text,
            'context' => $context, 'translations' => array('Existing Woo ' . $text))));
        $GLOBALS['l10n']['woo-poly-integration']->add_entry(new Translation_Entry(array('singular' => $text,
            'context' => $context, 'translations' => array($text))));
        self::assertSame($text, $this->lookup($text, $context));
    }

    public static function nativeMessages(): array
    {
        return array(
            array('Sort code', null),
            array('Bank', null),
            array('Account number', null),
            array('IBAN', null),
            array('BIC', null),
            array('Our bank details', null),
            array('Your personal data will be used to process your order, support your experience throughout this website, and for other purposes described in our %s.', null),
            array('Your personal data will be used to support your experience throughout this website, to manage access to your account, and for other purposes described in our %s.', null),
            array('You cannot add that amount of &quot;%1$s&quot; to the cart because there is not enough stock (%2$s remaining).', null),
            array('The current language used by WordPress. Default = English', null),
            array('Coupon: %s', null),
            array('shop', 'Page slug'),
            array('Shop', 'Page title'),
            array('cart', 'Page slug'),
            array('Cart', 'Page title'),
            array('checkout', 'Page slug'),
            array('Checkout', 'Page title'),
            array('my-account', 'Page slug'),
            array('My account', 'Page title'),
        );
    }
}
