<?php
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Hyyan\WPI\Endpoints;
use Hyyan\WPI\MessagesInterface;
use Hyyan\WPI\Tools\FlashMessages;

final class EndpointScreenSecurityTest extends TestCase
{
    private array $get;
    private $screen;
    private $tab;
    private $messages;
    private $screenHook;

    protected function setUp(): void
    {
        $this->get = $_GET;
        $this->screen = $GLOBALS['current_screen'] ?? null;
        $this->tab = $GLOBALS['current_tab'] ?? null;
        $this->messages = get_option(FlashMessages::getOptionName(), null);
        $this->screenHook = $GLOBALS['wp_filter']['current_screen'] ?? null;
        $GLOBALS['wp_filter']['current_screen'] = new WP_Hook();
        delete_option(FlashMessages::getOptionName());
    }

    protected function tearDown(): void
    {
        $_GET = $this->get;
        $GLOBALS['current_screen'] = $this->screen;
        if ($this->tab === null) { unset($GLOBALS['current_tab']); }
        else { $GLOBALS['current_tab'] = $this->tab; }
        if ($this->screenHook === null) { unset($GLOBALS['wp_filter']['current_screen']); }
        else { $GLOBALS['wp_filter']['current_screen'] = $this->screenHook; }
        if ($this->messages === null) { delete_option(FlashMessages::getOptionName()); }
        else { update_option(FlashMessages::getOptionName(), $this->messages); }
    }

    #[DataProvider('screenContexts')]
    public function testEndpointNoticeUsesNativeSettingsContext(string $screen, $tab, array $request, bool $expected): void
    {
        $_GET = $request;
        $GLOBALS['current_tab'] = $tab;
        set_current_screen($screen);
        $handler = (new ReflectionClass(Endpoints::class))->newInstanceWithoutConstructor();
        $handler->showFlashMessages();
        $messages = get_option(FlashMessages::getOptionName(), array());
        self::assertSame($expected, isset($messages[MessagesInterface::MSG_ENDPOINTS_TRANSLATION]));
        if ($expected) {
            self::assertStringContainsString('group=WooCommerce%20Endpoints', $messages[MessagesInterface::MSG_ENDPOINTS_TRANSLATION]['message']);
        }
    }

    public static function screenContexts(): array
    {
        return array(
            'native advanced without query' => array('woocommerce_page_wc-settings', 'advanced', array(), true),
            'query cannot override advanced' => array('woocommerce_page_wc-settings', 'advanced', array('tab' => 'general'), true),
            'normal advanced' => array('woocommerce_page_wc-settings', 'advanced', array('tab' => 'advanced'), true),
            'general ignores advanced query' => array('woocommerce_page_wc-settings', 'general', array('tab' => 'advanced'), false),
            'unset native tab ignores query' => array('woocommerce_page_wc-settings', null, array('tab' => 'advanced'), false),
            'non scalar native tab' => array('woocommerce_page_wc-settings', array('advanced'), array('tab' => 'advanced'), false),
            'other screen' => array('dashboard', 'advanced', array('tab' => 'advanced'), false),
        );
    }
}
