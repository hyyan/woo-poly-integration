<?php
use PHPUnit\Framework\TestCase;

final class StatusOutputSecurityTest extends TestCase
{
    public function testLanguageLocaleIsEscapedInStatusReport(): void
    {
        require_once dirname(Hyyan_WPI_DIR) . '/src/Hyyan/WPI/Admin/StatusReport.php';
        $language = PLL()->model->get_language('de');
        $locale = $language->locale;
        $language->locale = '<script>window.woopolyStatusMarker=true</script>';
        ob_start();
        try {
            do_action('woocommerce_system_status_report');
            $output = ob_get_contents();
            self::assertStringNotContainsString('<script>', $output);
            self::assertStringContainsString('&lt;script&gt;', $output);
        } finally {
            ob_end_clean();
            $language->locale = $locale;
        }
    }

    public function testNormalLocalesAndStatusHelpRemainVisible(): void
    {
        require_once dirname(Hyyan_WPI_DIR) . '/src/Hyyan/WPI/Admin/StatusReport.php';
        ob_start();
        try {
            do_action('woocommerce_system_status_report');
            $output = ob_get_contents();
            self::assertStringContainsString('en_US', $output);
            self::assertStringContainsString('de_DE', $output);
            self::assertStringContainsString('woocommerce-de_DE.mo', $output);
            self::assertStringContainsString('woocommerce-help-tip', $output);
        } finally {
            ob_end_clean();
        }
    }
}
