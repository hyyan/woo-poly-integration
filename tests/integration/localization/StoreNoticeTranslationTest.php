<?php
use Hyyan\WPI\Privacy;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class StoreNoticeTranslationTest extends TestCase
{
    #[DataProvider('notices')]
    public function testTranslationPreservesNativeNoticeControls(string $source, string $target): void
    {
        $options = array('woocommerce_demo_store' => get_option('woocommerce_demo_store'),
            'woocommerce_demo_store_notice' => get_option('woocommerce_demo_store_notice'));
        $hook = isset($GLOBALS['wp_filter']['woocommerce_demo_store']) ? clone $GLOBALS['wp_filter']['woocommerce_demo_store'] : null;
        $strings = $GLOBALS['l10n']['pll_string'] ?? null;
        $mo = new PLL_MO();
        $mo->add_entry(new Translation_Entry(array('singular' => $source, 'translations' => array($target))));
        $GLOBALS['l10n']['pll_string'] = $mo;
        update_option('woocommerce_demo_store', 'yes');
        update_option('woocommerce_demo_store_notice', $source);
        try {
            remove_all_filters('woocommerce_demo_store');
            ob_start(); woocommerce_demo_store(); $native = ob_get_clean();
            $handler = (new ReflectionClass(Privacy::class))->newInstanceWithoutConstructor();
            add_filter('woocommerce_demo_store', array($handler, 'translateDemoStoreNotice'), 10, 2);
            ob_start(); woocommerce_demo_store(); $translated = ob_get_clean();
            $before = $this->document($native);
            $after = $this->document($translated);
            $original = $before->getElementsByTagName('p')->item(0);
            $notice = $after->getElementsByTagName('p')->item(0);
            self::assertNotNull($notice);
            self::assertSame(md5($source), $notice->getAttribute('data-notice-id'));
            foreach ($original->attributes as $attribute) {
                self::assertSame($attribute->value, $notice->getAttribute($attribute->name));
            }
            $dismiss = $after->getElementsByTagName('a')->item(0);
            $originalDismiss = $before->getElementsByTagName('a')->item(0);
            self::assertSame($originalDismiss->textContent, $dismiss->textContent);
            foreach ($originalDismiss->attributes as $attribute) {
                self::assertSame($attribute->value, $dismiss->getAttribute($attribute->name));
            }
            self::assertStringContainsString(wp_kses_post($target), $translated);
            self::assertSame(0, $after->getElementsByTagName('script')->length);
            self::assertStringNotContainsString('onclick=', $translated);
        } finally {
            foreach ($options as $key => $value) {
                if ($value === false) { delete_option($key); } else { update_option($key, $value); }
            }
            if ($hook === null) { unset($GLOBALS['wp_filter']['woocommerce_demo_store']); }
            else { $GLOBALS['wp_filter']['woocommerce_demo_store'] = $hook; }
            if ($strings === null) { unset($GLOBALS['l10n']['pll_string']); }
            else { $GLOBALS['l10n']['pll_string'] = $strings; }
        }
    }

    private function document(string $html): DOMDocument
    {
        $document = new DOMDocument();
        @$document->loadHTML('<?xml encoding="UTF-8"><html><body>'.$html.'</body></html>');
        return $document;
    }

    public static function notices(): array
    {
        return array(array('Notice regression', 'Hinweis Pruefung'),
            array('Dismiss', 'Hinweis Schliessen'),
            array('<strong>Notice regression</strong>', '<strong onclick="alert(1)">Hinweis</strong><script>alert(1)</script>'));
    }
}
