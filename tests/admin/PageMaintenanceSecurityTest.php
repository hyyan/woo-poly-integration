<?php
use Hyyan\WPI\Plugin;
use Hyyan\WPI\Tools\FlashMessages;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class PageMaintenanceSecurityTest extends TestCase
{
    private array $options = array();
    private array $pages = array();
    private $screen;
    private $language;
    private int $user;
    private array $hooks;

    protected function setUp(): void
    {
        $this->screen = $GLOBALS['current_screen'] ?? null;
        $this->language = PLL()->curlang;
        $this->user = get_current_user_id();
        $this->hooks = $GLOBALS['wp_filter'];
        foreach ($this->hooks as $name => $hook) { $this->hooks[$name] = clone $hook; }
        remove_action('current_screen', array(Plugin::class, 'wpi_ensure_woocommerce_pages_translated'));
        foreach (array('wpi-features', 'wpi_wcpagecheck_passed', 'hyyan-wpi-flash-messages', 'woocommerce_cart_page_id', 'woocommerce_checkout_page_id', 'woocommerce_myaccount_page_id', 'woocommerce_shop_page_id') as $name) {
            $this->options[$name] = get_option($name);
        }
        wp_set_current_user(1);
        PLL()->curlang = PLL()->model->get_language('en');
        set_current_screen('edit-page');
        foreach (array('cart', 'checkout', 'myaccount', 'shop') as $type) {
            $translations = array();
            foreach (array('en', 'de') as $language) {
                $id = wp_insert_post(array('post_type' => 'page', 'post_status' => $language === 'de' && $type === 'cart' ? 'draft' : 'publish', 'post_title' => 'Page notice '.$type.' '.$language));
                $this->pages[] = $id; $translations[$language] = $id; pll_set_post_language($id, $language);
            }
            pll_save_post_translations($translations);
            update_option('woocommerce_'.$type.'_page_id', $translations['en']);
        }
        $features = (array) get_option('wpi-features'); $features['checkpages'] = 'off'; update_option('wpi-features', $features);
        update_option('wpi_wcpagecheck_passed', false);
        update_option('hyyan-wpi-flash-messages', array());
    }
    protected function tearDown(): void
    {
        wp_set_current_user(1);
        foreach ($this->pages as $id) { wp_delete_post($id, true); }
        foreach ($this->options as $name => $value) {
            if ($value === false) { delete_option($name); } else { update_option($name, $value); }
        }
        PLL()->curlang = $this->language;
        $GLOBALS['current_screen'] = $this->screen;
        $GLOBALS['wp_filter'] = $this->hooks;
        wp_set_current_user($this->user);
    }
    #[DataProvider('deniedRoles')]
    public function testDeniedRolesCannotRunPageMaintenance(string $login): void
    {
        wp_set_current_user(get_user_by('login', $login)->ID);
        Plugin::wpi_ensure_woocommerce_pages_translated();
        self::assertFalse(get_option('wpi_wcpagecheck_passed'));
        self::assertSame(array(), get_option(FlashMessages::getOptionName(), array()));
    }
    public static function deniedRoles(): array
    {
        return array(array('lab-subscriber'), array('lab-customer'), array('lab-contributor'));
    }
    public function testAuthorizedManagerGetsWarningAndPreservedEditLink(): void
    {
        wp_set_current_user(get_user_by('login', 'lab-manager')->ID);
        Plugin::wpi_ensure_woocommerce_pages_translated();
        $message = get_option(FlashMessages::getOptionName(), array())['pagechecks']['message'];
        self::assertStringContainsString('is in status draft', $message);
        $dom = new DOMDocument(); @$dom->loadHTML($message);
        $link = $dom->getElementsByTagName('a')->item(0);
        self::assertStringContainsString('post.php?post=', $link->getAttribute('href'));
        self::assertStringContainsString('action=edit', $link->getAttribute('href'));
    }
    public function testTranslatedWarningFiltersEventsAndKeepsNativeLink(): void
    {
        $filter = static function ($translated, $text, $domain) {
            return $domain === 'woo-poly-integration' && strpos($text, 'is in status') !== false ? $translated.'<img src=x onerror="Marker"><script>Marker()</script>' : $translated;
        };
        add_filter('gettext', $filter, 10, 3);
        Plugin::wpi_ensure_woocommerce_pages_translated();
        $message = get_option(FlashMessages::getOptionName(), array())['pagechecks']['message'];
        self::assertStringNotContainsString('onerror', $message);
        self::assertStringNotContainsString('<script>', $message);
        self::assertStringContainsString('<a href=', $message);
    }
}
