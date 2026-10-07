<?php
use Automattic\WooCommerce\Utilities\FeaturesUtil;
use PHPUnit\Framework\TestCase;

final class HposCompatibilityDeclarationTest extends TestCase
{
    public function testNativeFeatureControllerRecognizesThePluginDeclaration(): void
    {
        $features = FeaturesUtil::get_compatible_features_for_plugin('woo-poly-integration/__init__.php');
        self::assertContains('custom_order_tables', $features['compatible']);
        self::assertNotContains('custom_order_tables', $features['incompatible']);
    }
}
