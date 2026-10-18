<?php
use Hyyan\WPI\Endpoints;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class EndpointLanguageSwitchTest extends TestCase
{
    private array $queryVars;
    private array $requestVars;
    private array $catalogs = array();
    private array $hooks;
    private $strings;
    private $language;
    private $permalinks;
    private Endpoints $handler;

    protected function setUp(): void
    {
        $this->strings = $GLOBALS['l10n']['pll_string'] ?? null;
        $this->hooks = $GLOBALS['wp_filter'];
        foreach ($this->hooks as $tag => $hook) {
            $this->hooks[$tag] = clone $hook;
            foreach ($hook->callbacks as $priority => $callbacks) {
                foreach ($callbacks as $callback) {
                    $function = $callback['function'];
                    $owner = is_array($function) ? $function[0] : ($function instanceof Closure ? (new ReflectionFunction($function))->getClosureThis() : null);
                    if ($owner instanceof Endpoints) { remove_filter($tag, $function, $priority); }
                }
            }
        }
        $this->queryVars = WC()->query->query_vars;
        $this->requestVars = $GLOBALS['wp']->query_vars;
        $this->language = PLL()->curlang;
        $this->permalinks = get_option('permalink_structure');
        PLL()->curlang = PLL()->model->get_language('en');
        WC()->query->query_vars = array('orders' => 'order-history', 'edit-address' => 'edit-address', 'view-order' => 'view-order', 'lost-password' => 'lost-password', 'order-received' => 'purchase-complete');
        foreach (array('en', 'de') as $slug) {
            $object = PLL()->model->get_language($slug);
            $mo = new PLL_MO(); $mo->import_from_db($object);
            $this->catalogs[$slug] = clone $mo;
            foreach (array('order-history' => 'bestellverlauf', 'edit-address' => 'adressen', 'view-order' => 'bestellung', 'lost-password' => 'passwort-vergessen', 'purchase-complete' => 'kauf-abgeschlossen') as $source => $target) {
                $mo->add_entry(new Translation_Entry(array('singular' => $source, 'translations' => array($slug === 'de' ? $target : $source))));
            }
            $mo->export_to_db($object);
        }
        $this->selectCurrentLanguage('en');
        $this->handler = new Endpoints();
    }
    protected function tearDown(): void
    {
        foreach ($this->catalogs as $slug => $mo) { $mo->export_to_db(PLL()->model->get_language($slug)); }
        WC()->query->query_vars = $this->queryVars;
        $GLOBALS['wp']->query_vars = $this->requestVars;
        PLL()->curlang = $this->language;
        update_option('permalink_structure', $this->permalinks);
        $GLOBALS['wp_rewrite']->init();
        $GLOBALS['wp_filter'] = $this->hooks;
        if ($this->strings) { $GLOBALS['l10n']['pll_string'] = $this->strings; } else { unset($GLOBALS['l10n']['pll_string']); }
    }
    #[DataProvider('switches')]
    public function testLanguageSwitchPreservesConfiguredEndpointAndValue(string $endpoint, string $value, string $slug, bool $pretty, string $expected): void
    {
        update_option('permalink_structure', $pretty ? '/%postname%/' : '');
        $GLOBALS['wp']->query_vars = array($endpoint => $value);
        $link = $pretty ? 'https://example.test/account/?filter=one%26two' : 'https://example.test/?page_id=42';
        self::assertSame($expected, $this->handler->correctPolylangSwitcherLinks($link, $slug));
    }
    public static function switches(): array
    {
        return array(
            array('orders', '', 'de', true, 'https://example.test/account/bestellverlauf/?filter=one%26two'),
            array('orders', '2', 'de', true, 'https://example.test/account/bestellverlauf/2/?filter=one%26two'),
            array('orders', '', 'en', true, 'https://example.test/account/order-history/?filter=one%26two'),
            array('edit-address', 'billing', 'de', true, 'https://example.test/account/adressen/billing/?filter=one%26two'),
            array('view-order', '123', 'de', true, 'https://example.test/account/bestellung/123/?filter=one%26two'),
            array('lost-password', '', 'de', true, 'https://example.test/account/passwort-vergessen/?filter=one%26two'),
            array('orders', '2', 'de', false, 'https://example.test/?page_id=42&bestellverlauf=2'),
            array('orders', '', 'en', false, 'https://example.test/?page_id=42&order-history'),
        );
    }
    #[DataProvider('lateLocaleUrls')]
    public function testNativeEndpointUrlUsesLanguageSelectedAfterInitialization(string $structure, string $permalink, string $expected): void
    {
        $GLOBALS['wp_rewrite']->set_permalink_structure($structure);
        $GLOBALS['wp_rewrite']->init();
        WC()->query->query_vars['order-received'] = 'purchase-complete';
        $this->selectCurrentLanguage('de');
        self::assertSame($expected, wc_get_endpoint_url('order-received', '123', $permalink));
    }
    public static function lateLocaleUrls(): array
    {
        return array(
            array('/%postname%/', 'https://example.test/de/checkout/', 'https://example.test/de/checkout/kauf-abgeschlossen/123/'),
            array('/%postname%', 'https://example.test/de/checkout/', 'https://example.test/de/checkout/kauf-abgeschlossen/123'),
            array('/%postname%/', 'https://example.test/de/checkout/?filter=one%26two', 'https://example.test/de/checkout/kauf-abgeschlossen/123/?filter=one%26two'),
            array('', 'https://example.test/?page_id=42&lang=de', 'https://example.test/?page_id=42&lang=de&kauf-abgeschlossen=123'),
        );
    }
    public function testLateLanguageKeepsQueryAddedByExistingEndpointFilter(): void
    {
        $GLOBALS['wp_rewrite']->set_permalink_structure('/%postname%/');
        $GLOBALS['wp_rewrite']->init();
        WC()->query->query_vars['order-received'] = 'purchase-complete';
        $this->selectCurrentLanguage('de');
        add_filter('woocommerce_get_endpoint_url', static function ($url) { return add_query_arg('tracking', rawurlencode('one&two'), $url); }, 5);
        self::assertSame('https://example.test/de/checkout/kauf-abgeschlossen/123/?tracking=one%26two', wc_get_endpoint_url('order-received', '123', 'https://example.test/de/checkout/'));
    }
    private function selectCurrentLanguage(string $slug): void
    {
        PLL()->curlang = PLL()->model->get_language($slug);
        $mo = new PLL_MO(); $mo->import_from_db(PLL()->curlang);
        $GLOBALS['l10n']['pll_string'] = $mo;
    }
    public function testNonEndpointLanguageLinkIsUnchanged(): void
    {
        $GLOBALS['wp']->query_vars = array();
        self::assertSame('https://example.test/de/account/', $this->handler->correctPolylangSwitcherLinks('https://example.test/de/account/', 'de'));
    }
}
