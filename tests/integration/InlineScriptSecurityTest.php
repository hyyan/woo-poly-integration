<?php
use Hyyan\WPI\Utilities;
use Hyyan\WPI\Product\Meta;
use Hyyan\WPI\Product\Variable;
use Hyyan\WPI\Taxonomies\Attributes;
use Hyyan\WPI\HooksInterface;
use PHPUnit\Framework\TestCase;

final class InlineScriptSecurityTest extends TestCase
{
    private array $get;
    private $page;
    private $language;
    private array $hooks;
    private $features;
    private $screen;
    private $translation;

    protected function setUp(): void
    {
        require_once ABSPATH.'wp-admin/includes/admin.php';
        $this->get = $_GET;
        $this->page = $GLOBALS['pagenow'] ?? null;
        $this->language = PLL()->curlang;
        $this->screen = $GLOBALS['current_screen'] ?? null;
        $this->features = get_option('wpi-features');
        $this->hooks = $GLOBALS['wp_filter'];
        foreach ($this->hooks as $name => $hook) { $this->hooks[$name] = clone $hook; }
        $GLOBALS['wp_filter']['admin_print_scripts'] = new WP_Hook();
        $GLOBALS['wp_filter']['current_screen'] = new WP_Hook();
        $this->translation = static function ($translation, $text, $domain) {
            return $domain === 'woo-poly-integration' ? "Marker \" ' </script><img src=x onerror=alert(1)> <a href='https://example.test/'>Help</a>" : $translation;
        };
    }
    protected function tearDown(): void
    {
        $_GET = $this->get;
        $GLOBALS['pagenow'] = $this->page;
        PLL()->curlang = $this->language;
        $GLOBALS['current_screen'] = $this->screen;
        $GLOBALS['wp_filter'] = $this->hooks;
        if ($this->features === false) { delete_option('wpi-features'); } else { update_option('wpi-features', $this->features); }
    }
    private function handler(string $class) { return (new ReflectionClass($class))->newInstanceWithoutConstructor(); }
    private function capture(callable $callback): string
    {
        ob_start();
        try { $callback(); return ob_get_contents(); } finally { ob_end_clean(); }
    }
    public function testWrapperEscapesItsIdAndRetainsExecutableCode(): void
    {
        $output = Utilities::jsScriptWrapper('" onload="alert(1)', 'window.fixture = 1;', false, true);
        self::assertStringNotContainsString('id="hyyan-wpi-" onload=', $output);
        self::assertStringContainsString('&quot;', $output);
        self::assertStringContainsString('window.fixture = 1;', $output);
    }
    public function testAttributeLabelsCannotTerminateScripts(): void
    {
        $_GET = array('page' => 'product_attributes'); $GLOBALS['pagenow'] = 'edit.php';
        set_current_screen('product_page_product_attributes');
        add_filter('gettext', $this->translation, 10, 3);
        $output = $this->capture(fn() => $this->handler(Attributes::class)->addAttrsTranslateLinks());
        self::assertStringNotContainsString('</script><img', $output);
        self::assertStringContainsString('encodeURIComponent(attrName)', $output);
        self::assertStringContainsString('.text(', $output);
    }
    public function testAttributeLinksUseTheResolvedScreenWithoutQueryInput(): void
    {
        $_GET = array();
        $GLOBALS['pagenow'] = 'edit.php';
        set_current_screen('product_page_product_attributes');
        $output = $this->capture(fn() => $this->handler(Attributes::class)->addAttrsTranslateLinks());
        self::assertStringContainsString('encodeURIComponent(attrName)', $output);
    }

    public function testAttributeQueryCannotSelectAnotherResolvedScreen(): void
    {
        $_GET = array('page' => 'product_attributes');
        $GLOBALS['pagenow'] = 'edit.php';
        set_current_screen('edit-product');
        self::assertFalse($this->handler(Attributes::class)->addAttrsTranslateLinks());
        self::assertSame('', $this->capture(fn() => $this->handler(Attributes::class)->addAttrsTranslateLinks()));
    }

    public function testVariableDialogEncodesDataAndFiltersItsHtml(): void
    {
        $_GET = array(); $GLOBALS['pagenow'] = 'post-new.php'; PLL()->curlang = PLL()->model->get_language('de');
        add_filter('gettext', $this->translation, 10, 3);
        $this->handler(Variable::class)->handleVariableLimitation();
        $output = $this->capture(fn() => do_action('admin_print_scripts'));
        self::assertStringNotContainsString('</script><img', $output);
        preg_match('/var HYYAN_WPI_VARIABLES = (.*?);/s', $output, $match);
        $data = json_decode($match[1], true);
        self::assertIsArray($data);
        self::assertStringNotContainsString('onerror', $data['content']);
        self::assertStringContainsString('<a href="https://example.test/">Help</a>', str_replace("'", '"', $data['content']));
        self::assertSame('en', $data['defaultLang']);
    }
    public function testLanguageWarningUsesTextInsteadOfHtml(): void
    {
        add_filter('gettext', $this->translation, 10, 3);
        $this->handler(Variable::class)->shouldDisableLangSwitcher();
        set_current_screen('settings_page_mlang');
        $output = $this->capture(fn() => do_action('admin_print_scripts'));
        self::assertStringNotContainsString('</script><img', $output);
        self::assertStringContainsString('.text(', $output);
        self::assertStringContainsString('pointer-events', $output);
    }
    public function testFieldsLockerSupportsEmptySelectorFilter(): void
    {
        $features = (array) $this->features; $features['fields-locker'] = 'on'; update_option('wpi-features', $features);
        add_filter(HooksInterface::FIELDS_LOCKER_SELECTORS_FILTER, static fn() => array(), 100);
        $output = $this->capture(fn() => $this->handler(Meta::class)->addFieldsLocker());
        self::assertStringContainsString('var disabledSelectors = "";', $output);
        self::assertStringNotContainsString('Array', $output);
    }
    public function testNormalVariableDialogRetainsHelpLinkAndLanguage(): void
    {
        $_GET = array(); $GLOBALS['pagenow'] = 'post-new.php'; PLL()->curlang = PLL()->model->get_language('de');
        $this->handler(Variable::class)->handleVariableLimitation();
        $output = $this->capture(fn() => do_action('admin_print_scripts'));
        preg_match('/var HYYAN_WPI_VARIABLES = (.*?);/s', $output, $match);
        $data = json_decode($match[1], true);
        self::assertSame('Wrong Language For Variable Product', $data['title']);
        self::assertSame('en', $data['defaultLang']);
        self::assertStringContainsString('target="_blank"', $data['content']);
        self::assertStringContainsString('Read more, to know why</a>', $data['content']);
    }
    public function testNativeDefaultLanguageRowActionIsDisabledForVariations(): void
    {
        $this->handler(Variable::class)->shouldDisableLangSwitcher();
        $output = apply_filters('pll_default_lang_row_action', '<a href="?pll_action=default-lang">Change</a>');
        self::assertStringNotContainsString('href=', $output);
        self::assertStringContainsString('aria-disabled="true"', $output);
    }

    public function testNativeDefaultLanguageActionRemainsAvailableWithoutVariations(): void
    {
        add_filter('wp_count_posts', static function ($counts, $type) {
            $counts = clone $counts;
            if ($type === 'product_variation') { $counts->publish = 0; }
            return $counts;
        }, 10, 2);
        $this->handler(Variable::class)->shouldDisableLangSwitcher();
        $action = '<a href="?pll_action=default-lang">Change</a>';
        self::assertSame($action, apply_filters('pll_default_lang_row_action', $action));
    }

}
