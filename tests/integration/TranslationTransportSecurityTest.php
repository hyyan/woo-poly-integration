<?php
use Hyyan\WPI\Tools\TranslationsDownloader;
use PHPUnit\Framework\TestCase;

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
