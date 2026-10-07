<?php
use Hyyan\WPI\Tools\TranslationsDownloader;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class TranslationTransportSecurityTest extends TestCase
{
    public function testAvailabilityRequestRequiresCertificateVerification(): void
    {
        $observed = null;
        $filter = function ($response, $args, $url) use (&$observed) {
            if (strpos($url, '/translation/plugin/woocommerce/') !== false) {
                $observed = $args;
                return array('response' => array('code' => 200), 'body' => '');
            }
            return $response;
        };
        add_filter('pre_http_request', $filter, 10, 3);
        try {
            self::assertTrue(TranslationsDownloader::isAvaliable('zz_SECURITY'));
            self::assertTrue($observed['sslverify']);
        } finally {
            remove_filter('pre_http_request', $filter, 10);
        }
    }

    public function testDownloadRequestRequiresCertificateVerification(): void
    {
        $requests = array();
        $filter = function ($response, $args, $url) use (&$requests) {
            if (strpos($url, '/translation/plugin/woocommerce/') !== false) {
                $requests[] = $args;
                return count($requests) === 1
                    ? array('response' => array('code' => 200), 'body' => '')
                    : new WP_Error('http_request_failed', 'Certificate verification failed');
            }
            return $response;
        };
        add_filter('pre_http_request', $filter, 10, 3);
        try {
            try {
                TranslationsDownloader::download('zz_SECURITY', 'Transport test');
                self::fail('A transport error must stop the download.');
            } catch (RuntimeException $error) {
                self::assertCount(2, $requests);
                self::assertTrue($requests[1]['sslverify']);
            }
        } finally {
            remove_filter('pre_http_request', $filter, 10);
        }
    }

    #[DataProvider('availabilityCases')]
    public function testDownloadErrorsEscapeLanguageNamesAndRemoveUnsafeTranslationMarkup(bool $available): void
    {
        $requests = 0;
        $transport = function () use (&$requests, $available) {
            ++$requests;
            return $available && $requests === 1
                ? array('response' => array('code' => 200), 'body' => '')
                : new WP_Error('http_request_failed', 'Download failed');
        };
        $translation = function ($translated, $text, $domain) {
            if ($domain === 'woo-poly-integration' && strpos($text, 'WooCommerce translation %s') !== false) {
                return '<script>alert(1)</script><strong onclick="alert(1)">%1$s</strong> <a href="%2$s">Repository</a>';
            }
            return $translated;
        };
        add_filter('pre_http_request', $transport);
        add_filter('gettext', $translation, 10, 3);
        try {
            try {
                TranslationsDownloader::download('zz_SECURITY', '<img src=x onerror="alert(1)">Language');
                self::fail('A failed download must throw an exception.');
            } catch (RuntimeException $error) {
                $message = $error->getMessage();
                self::assertStringNotContainsString('<script>', $message);
                self::assertStringNotContainsString('onclick=', $message);
                self::assertStringNotContainsString('<img', $message);
                self::assertStringContainsString('&lt;img', $message);
                self::assertStringContainsString('zz_SECURITY', $message);
                self::assertStringContainsString('href="'.TranslationsDownloader::getRepoUrl().'"', $message);
                self::assertStringContainsString('</strong>', $message);
            }
        } finally {
            remove_filter('pre_http_request', $transport);
            remove_filter('gettext', $translation, 10);
        }
    }

    public static function availabilityCases(): array
    {
        return array('unavailable' => array(false), 'download failed' => array(true));
    }

    public function testFailedTransportIsNotReportedAsAvailable(): void
    {
        $filter = function () { return new WP_Error('http_request_failed', 'Certificate verification failed'); };
        add_filter('pre_http_request', $filter);
        try {
            self::assertFalse(TranslationsDownloader::isAvaliable('zz_SECURITY'));
        } finally {
            remove_filter('pre_http_request', $filter);
        }
    }
}
