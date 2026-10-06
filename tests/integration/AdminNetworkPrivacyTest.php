<?php
use Hyyan\WPI\Plugin;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class AdminNetworkPrivacyTest extends TestCase
{
    private function document(string $view): DOMDocument
    {
        $document = new DOMDocument();
        @$document->loadHTML('<?xml encoding="UTF-8"><html><body>'.Plugin::getView($view).'</body></html>');
        return $document;
    }
    #[DataProvider('views')]
    public function testAdministrationViewsDoNotLoadThirdPartyResources(string $view): void
    {
        $xpath = new DOMXPath($this->document($view));
        self::assertSame(0,$xpath->query('//script|//iframe|//img[@src]')->length);
    }
    public static function views(): array { return array(array('badges'),array('social'),array('Messages/support')); }
    public function testProjectLinksRetainReleaseLicenseAndRepositoryAccess(): void
    {
        $links = $this->document('badges')->getElementsByTagName('a');
        self::assertCount(3,$links);
        self::assertSame('https://wordpress.org/plugins/woo-poly-integration',$links[0]->getAttribute('href'));
        self::assertSame('https://github.com/hyyan/woo-poly-integration/blob/master/LICENSE',$links[1]->getAttribute('href'));
        self::assertSame('https://github.com/hyyan/woo-poly-integration',$links[2]->getAttribute('href'));
        foreach ($links as $link) { self::assertNotSame('',trim($link->textContent)); }
    }
    public function testSocialActionsRetainOriginalShareDataAndRepository(): void
    {
        $links=$this->document('social')->getElementsByTagName('a');
        self::assertCount(3,$links);
        parse_str(parse_url($links[0]->getAttribute('href'),PHP_URL_QUERY),$tweet);
        self::assertSame('https://wordpress.org/plugins/woo-poly-integration/',$tweet['url'] ?? null);
        self::assertSame('Hyyan WooCommerce Polylang Integration ,makes you run multilingual store easily.',$tweet['text'] ?? null);
        self::assertSame('HyyanAF',$tweet['via'] ?? null);
        parse_str(parse_url($links[1]->getAttribute('href'),PHP_URL_QUERY),$facebook);
        self::assertSame('https://wordpress.org/plugins/woo-poly-integration/',$facebook['u'] ?? null);
        self::assertSame('https://github.com/hyyan/woo-poly-integration',$links[2]->getAttribute('href'));
    }
    public function testTranslatedActionLabelsCannotInjectActiveMarkup(): void
    {
        $filter=static function ($translated,$text,$domain) { return $domain==='woo-poly-integration' ? '<img src="https://example.test/pixel" onerror="alert(1)">' : $translated; };
        add_filter('gettext',$filter,100,3);
        try {
            foreach (array('badges','social') as $view) {
                $document=$this->document($view);
                self::assertSame(0,$document->getElementsByTagName('img')->length);
                self::assertStringContainsString('<img src=', $document->textContent);
            }
        } finally { remove_filter('gettext',$filter,100); }
    }
}
