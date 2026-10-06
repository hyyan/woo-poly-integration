<?php
use Hyyan\WPI\Plugin;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class SettingsRendererSecurityTest extends TestCase
{
    private array $globals = array();
    private array $hooks;
    protected function setUp(): void
    {
        require_once ABSPATH.'wp-admin/includes/admin.php';
        foreach (array('wp_settings_sections', 'wp_settings_fields', 'wp_registered_settings', 'new_allowed_options') as $name) {
            $this->globals[$name] = $GLOBALS[$name] ?? null;
        }
        $this->hooks = $GLOBALS['wp_filter'];
        foreach ($this->hooks as $name => $hook) { $this->hooks[$name] = clone $hook; }
    }
    protected function tearDown(): void
    {
        foreach ($this->globals as $name => $value) { $GLOBALS[$name] = $value; }
        $GLOBALS['wp_filter'] = $this->hooks;
        delete_option('wpi-render-a'); delete_option('wpi-render-b');
    }
    private function renderer(): WeDevs_Settings_API { return (new ReflectionClass(WeDevs_Settings_API::class))->newInstanceWithoutConstructor(); }
    private function capture(callable $callback): string
    {
        ob_start(); try { $callback(); return ob_get_contents(); } finally { ob_end_clean(); }
    }
    private function document(string $html): DOMDocument
    {
        $dom = new DOMDocument(); @$dom->loadHTML($html); return $dom;
    }
    #[DataProvider('fieldTypes')]
    public function testFieldArgumentsCannotCreateEventAttributesOrScripts(string $type): void
    {
        $payload = '" onmouseover="Marker';
        $args = array('id' => 'field'.$payload, 'section' => 'wpi-render'.$payload, 'type' => $type,
            'size' => 'regular'.$payload, 'placeholder' => 'Prompt'.$payload, 'min' => '1'.$payload, 'max' => '9'.$payload,
            'step' => '1'.$payload, 'std' => $type === 'multicheck' ? array('key'.$payload => 'key'.$payload) : 'key'.$payload,
            'options' => array('key'.$payload => '<script>Marker()</script>Label', 'button_label' => 'Browse'.$payload),
            'desc' => '<strong>Safe rich text</strong><a href="https://example.test/">Help</a><img src=x onerror="Marker"><script>Marker()</script>');
        $html = $this->capture(fn() => $this->renderer()->{'callback_'.$type}($args));
        $xpath = new DOMXPath($this->document($html));
        self::assertSame(0, $xpath->query('//*[@onmouseover="Marker" or @onerror="Marker"]')->length);
        self::assertSame(0, $xpath->query('//script[contains(text(), "Marker()")]')->length);
        self::assertGreaterThan(0, $xpath->query('//strong[text()="Safe rich text"]')->length);
        self::assertGreaterThan(0, $xpath->query('//a[@href="https://example.test/"]')->length);
    }
    public static function fieldTypes(): array
    {
        return array_map(static fn($type) => array($type), array('text', 'url', 'number', 'checkbox', 'multicheck', 'radio', 'select', 'textarea', 'html', 'file', 'password', 'color'));
    }
    public function testSectionsNavigationAndNativeFieldsRetainSafeHtml(): void
    {
        $api = $this->renderer(); $marker = '<script>Marker()</script>Title';
        $api->set_sections(array(array('id' => 'wpi-render-a', 'title' => $marker, 'desc' => '<strong>Safe rich text</strong><img src=x onerror="Marker">'), array('id' => 'wpi-render-b', 'title' => 'Second')));
        $api->set_fields(array('wpi-render-a' => array(array('name' => 'field', 'label' => $marker, 'type' => 'checkbox', 'desc' => '<a href="https://example.test/">Help</a>'))));
        $api->admin_init();
        $html = $this->capture(function () use ($api) { $api->show_navigation(); $api->show_forms(); });
        $xpath = new DOMXPath($this->document($html));
        self::assertSame(0, $xpath->query('//script[contains(text(), "Marker()")]')->length);
        self::assertSame(0, $xpath->query('//*[@onerror="Marker"]')->length);
        self::assertSame(2, $xpath->query('//form[@action="options.php"]')->length);
        self::assertSame(2, $xpath->query('//input[@name="_wpnonce"]')->length);
        self::assertGreaterThan(0, $xpath->query('//strong[text()="Safe rich text"]')->length);
    }
    public function testSettingsLinkEscapesTranslatedLabelAndRetainsDestination(): void
    {
        $filter = static fn($translated, $text) => $text === 'Settings' ? '<script>Marker()</script>Settings' : $translated;
        add_filter('gettext', $filter, 10, 2);
        $html = Plugin::settingsLinkHTML();
        $xpath = new DOMXPath($this->document($html));
        self::assertSame(0, $xpath->query('//script')->length);
        $url = $xpath->query('//a')->item(0)->getAttribute('href');
        self::assertSame(admin_url('options-general.php?page=hyyan-wpi'), $url);
    }
    public function testSelectedQuoteValueIsComparedBeforeAttributeEscaping(): void
    {
        $value = 'quote " & item';
        $args = array('id' => 'select', 'section' => 'wpi-render-a', 'std' => $value, 'size' => 'regular', 'options' => array($value => 'Choice'), 'desc' => '');
        $html = $this->capture(fn() => $this->renderer()->callback_select($args));
        $xpath = new DOMXPath($this->document($html));
        $selected = $xpath->query('//option[@selected]')->item(0);
        self::assertNotNull($selected);
        self::assertSame($value, $selected->getAttribute('value'));
    }
    public function testRichEditorWrapperCannotCreateEventAttributes(): void
    {
        $args = array('id' => 'editor', 'section' => 'wpi-render-a', 'std' => '<strong>Editable</strong>', 'size' => '500px" onmouseover="Marker', 'options' => array(), 'desc' => '<a href="https://example.test/">Help</a>');
        $html = $this->capture(fn() => $this->renderer()->callback_wysiwyg($args));
        $xpath = new DOMXPath($this->document($html));
        self::assertSame(0, $xpath->query('//*[@onmouseover="Marker"]')->length);
        self::assertSame(1, $xpath->query('//textarea')->length);
        self::assertStringContainsString('Editable', $xpath->query('//textarea')->item(0)->textContent);
    }

}
