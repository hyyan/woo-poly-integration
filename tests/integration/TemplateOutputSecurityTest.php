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

    public function testExistingRichTextLinksAndSharingActionsRemainAvailable(): void
    {
        $about = $this->render('Admin/about');
        self::assertStringContainsString('href="https://wordpress.org/plugins/woocommerce/"', $about);
        self::assertStringContainsString('href="https://wordpress.org/plugins/polylang/"', $about);
        $support = $this->render('Admin/support');
        self::assertStringContainsString('<strong>I will never ask', $support);
        self::assertStringContainsString('twitter.com/intent/tweet', $support);
        self::assertStringContainsString('facebook.com/sharer/sharer.php', $support);
        self::assertStringContainsString('https://github.com/hyyan/woo-poly-integration', $support);
        self::assertStringContainsString('<b>Thank you!</b>', $this->render('Messages/support'));
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
