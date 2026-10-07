<?php
use Hyyan\WPI\Utilities;
use PHPUnit\Framework\TestCase;

final class WooCommerceCatalogLocaleTest extends TestCase
{
    #[PHPUnit\Framework\Attributes\RunInSeparateProcess]
    #[PHPUnit\Framework\Attributes\PreserveGlobalState(false)]
    public function testNativeCatalogReloadsAfterLocaleSwitch(): void
    {
        $previous = PLL()->curlang;
        $hooks = array_map(static fn($hook) => clone $hook, $GLOBALS['wp_filter']);
        Utilities::switch_pll_locale('en_US');
        PLL()->pll_language_defined();
        $mo = new MO();
        self::assertTrue($mo->import_from_file(WP_LANG_DIR.'/plugins/woocommerce-de_DE.mo'));
        $email = WC()->mailer()->get_emails()['WC_Email_New_Order'];
        Utilities::switchLocale('en_US');
        $source = $email->get_default_subject();
        try {
            switch_to_locale('de_DE');
            Utilities::switch_pll_locale('de_DE');
            self::assertSame($mo->translate('Cart'), __('Cart', 'woocommerce'));
            Utilities::switchLocale('de_DE');
            self::assertSame('de_DE', get_locale());
            foreach (array('Cart', 'Shipping', 'Subtotal') as $label) {
                self::assertNotSame($label, $mo->translate($label));
                self::assertSame($mo->translate($label), __($label, 'woocommerce'));
            }
            self::assertSame($mo->translate($source), $email->get_default_subject());
            Utilities::switchLocale('en_US');
            self::assertSame('en_US', get_locale());
            self::assertSame('Cart', __('Cart', 'woocommerce'));
            self::assertSame($source, $email->get_default_subject());
        } finally {
            while (is_locale_switched()) { restore_previous_locale(); }
            $GLOBALS['wp_filter'] = $hooks;
            PLL()->curlang = $previous;
        }
    }
}
