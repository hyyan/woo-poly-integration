<?php
use Hyyan\WPI\Plugin;
use PHPUnit\Framework\TestCase;

final class ReleaseUpgradeContractTest extends TestCase
{
    public function testReleaseRetainsPublishedMinimumRequirements(): void
    {
        $root = dirname(__DIR__, 3);
        $headers = array('wordpress' => 'Requires at least', 'woocommerce' => 'WC requires at least', 'php' => 'Requires PHP');
        $published = array('wordpress' => '5.4', 'woocommerce' => '3.0.0', 'php' => '7.0');
        self::assertSame($published, get_file_data($root . '/__init__.php', $headers));
        self::assertSame($published, get_file_data($root . '/readme.txt', $headers));
        self::assertSame($published['woocommerce'], Plugin::WOOCOMMERCE_VERSION);
    }
}
