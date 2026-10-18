<?php
use Hyyan\WPI\Plugin;
use PHPUnit\Framework\TestCase;

final class ReleaseUpgradeContractTest extends TestCase
{
    public function testReleaseDeclaresConsistentMinimumRequirements(): void
    {
        $root = dirname(__DIR__, 3);
        $headers = array('wordpress' => 'Requires at least', 'woocommerce' => 'WC requires at least', 'php' => 'Requires PHP');
        $requirements = array('wordpress' => '5.4', 'woocommerce' => '3.0.0', 'php' => '8.3');
        self::assertSame($requirements, get_file_data($root . '/__init__.php', $headers));
        self::assertSame($requirements, get_file_data($root . '/readme.txt', $headers));
        self::assertSame($requirements, get_file_data($root . '/.github/release/readme.toml', $headers));
        self::assertSame($requirements['woocommerce'], Plugin::WOOCOMMERCE_VERSION);
    }
}
