<?php
use Hyyan\WPI\Endpoints;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class EndpointLanguageSwitchTest extends TestCase
{
    private array $queryVars;
    private array $requestVars;
    private array $catalogs = array();
    private $language;
    private $permalinks;
    private Endpoints $handler;

    protected function setUp(): void
    {
        $this->queryVars = WC()->query->query_vars;
        $this->requestVars = $GLOBALS['wp']->query_vars;
        $this->language = PLL()->curlang;
        $this->permalinks = get_option('permalink_structure');
        PLL()->curlang = PLL()->model->get_language('en');
        WC()->query->query_vars = array('orders' => 'order-history', 'edit-address' => 'edit-address', 'view-order' => 'view-order', 'lost-password' => 'lost-password');
        foreach (array('en', 'de') as $slug) {
            $object = PLL()->model->get_language($slug);
            $mo = new PLL_MO(); $mo->import_from_db($object);
            $this->catalogs[$slug] = clone $mo;
            foreach (array('order-history' => 'bestellverlauf', 'edit-address' => 'adressen', 'view-order' => 'bestellung', 'lost-password' => 'passwort-vergessen') as $source => $target) {
                $mo->add_entry(new Translation_Entry(array('singular' => $source, 'translations' => array($slug === 'de' ? $target : $source))));
            }
            $mo->export_to_db($object);
        }
        $this->handler = (new ReflectionClass(Endpoints::class))->newInstanceWithoutConstructor();
        $this->handler->regsiterEndpointsTranslations();
    }
    protected function tearDown(): void
    {
        foreach ($this->catalogs as $slug => $mo) { $mo->export_to_db(PLL()->model->get_language($slug)); }
        WC()->query->query_vars = $this->queryVars;
        $GLOBALS['wp']->query_vars = $this->requestVars;
        PLL()->curlang = $this->language;
        update_option('permalink_structure', $this->permalinks);
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
    public function testNonEndpointLanguageLinkIsUnchanged(): void
    {
        $GLOBALS['wp']->query_vars = array();
        self::assertSame('https://example.test/de/account/', $this->handler->correctPolylangSwitcherLinks('https://example.test/de/account/', 'de'));
    }
}
