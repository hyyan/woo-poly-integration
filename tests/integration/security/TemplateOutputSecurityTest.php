<?php
use Hyyan\WPI\Plugin;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class TemplateOutputSecurityTest extends TestCase
{
    private function render(string $view): string
    {
        $settings = new class {
            public function show_navigation(): void {}
            public function show_forms(): void {}
        };
        return Plugin::getView($view, array('self' => $settings));
    }

    #[DataProvider('views')]
    public function testTranslatedTemplateTextCannotCreateScriptElements(string $view): void
    {
        $filter = function ($translation, $text, $domain) {
            return $domain === 'woo-poly-integration'
                ? '<script>window.woopolyTemplateMarker=true</script><strong>Template marker</strong>'
                : $translation;
        };
        add_filter('gettext', $filter, 10, 3);
        try {
            $output = $this->render($view);
            self::assertStringNotContainsString('<script>window.woopolyTemplateMarker', $output);
            self::assertStringContainsString('Template marker', $output);
        } finally {
            remove_filter('gettext', $filter, 10);
        }
    }

    public static function views(): array
    {
        return array_map(function ($view) { return array($view); }, array(
            'Admin/getHelp', 'Admin/about', 'Admin/support', 'Messages/activateError',
            'Messages/support', 'Messages/endpointsTranslations', 'admin',
        ));
    }

    public function testProjectInformationAndOptionalFollowLinksRemainAvailable(): void
    {
        $about = $this->render('Admin/about');
        self::assertStringContainsString('href="https://wordpress.org/plugins/woocommerce/"', $about);
        self::assertStringContainsString('href="https://wordpress.org/plugins/polylang/"', $about);
        $support = $this->render('Admin/support');
        self::assertStringContainsString('If WooPoly is useful to you', $support);
        self::assertStringNotContainsString('twitter.com', $support);
        self::assertStringContainsString('https://www.linkedin.com/in/hyyan/', $support);
        self::assertStringContainsString('https://github.com/hyyan/woo-poly-integration', $support);
        self::assertStringContainsString('Thanks for using WooPoly', $this->render('Messages/support'));
    }

    public function testSettingsRenderingPreservesNativeFormsAndInlineControls(): void
    {
        $settings = new class extends \Hyyan\WPI\Admin\Settings {
            public function __construct() {}
            public function show_navigation() { echo '<a href="#native-settings">Native settings</a>'; }
            public function show_forms() {
                echo '<form method="post" action="options.php">';
                wp_nonce_field('woopoly-output-test');
                echo '<input type="submit" value="Save changes"></form><script>window.woopolyControl=true</script>';
            }
        };
        ob_start();
        try {
            $settings->outputPage();
            $output = ob_get_contents();
        } finally {
            ob_end_clean();
        }
        self::assertStringContainsString('<form method="post" action="options.php">', $output);
        self::assertStringContainsString('name="_wpnonce"', $output);
        self::assertStringContainsString('<script>window.woopolyControl=true</script>', $output);
        self::assertStringContainsString('https://www.linkedin.com/in/hyyan/', $output);
        self::assertStringContainsString('Plugin releases', $output);
    }

    public function testStatusHelpTipsRemoveUnsafeFilteredMarkupAndPreserveAccessibleControls(): void
    {
        require_once dirname(__DIR__, 3).'/src/Hyyan/WPI/Admin/StatusReport.php';
        $filter = function ($html) {
            return str_replace('<span ', '<span onclick="alert(1)" ', $html).'<script>alert(1)</script>';
        };
        add_filter('wc_help_tip', $filter);
        ob_start();
        try {
            woo_poly_integration_status_report();
            $output = ob_get_contents();
        } finally {
            ob_end_clean();
            remove_filter('wc_help_tip', $filter);
        }
        self::assertStringNotContainsString('<script>', $output);
        self::assertStringNotContainsString('onclick=', $output);
        self::assertSame(3, substr_count($output, 'class="woocommerce-help-tip"'));
        self::assertSame(3, substr_count($output, 'data-tip="'));
        self::assertSame(3, substr_count($output, 'aria-label="'));
        self::assertSame(3, substr_count($output, 'tabindex="0"'));
    }

    public function testEndpointTranslationLinkPreservesItsDestinationAndLabel(): void
    {
        $output = $this->render('Messages/endpointsTranslations');
        $document = new DOMDocument();
        @$document->loadHTML($output);
        $link = $document->getElementsByTagName('a')->item(0);
        self::assertNotNull($link);
        self::assertSame('Translate', $link->textContent);
        parse_str(parse_url($link->getAttribute('href'), PHP_URL_QUERY), $query);
        self::assertSame('mlang_strings', $query['page']);
        self::assertSame('WooCommerce Endpoints', $query['group']);
    }
}
