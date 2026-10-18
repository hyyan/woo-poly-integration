<?php
use Hyyan\WPI\Shipping;
use PHPUnit\Framework\TestCase;

final class ShippingZonesTest extends TestCase
{
    private Shipping $handler;
    private WC_Shipping_Zone $restOfWorld;
    private WC_Shipping_Zone $configured;
    private int $restMethod;
    private int $configuredMethod;

    protected function setUp(): void
    {
        $this->handler = (new ReflectionClass(Shipping::class))->newInstanceWithoutConstructor();
        $this->restOfWorld = new WC_Shipping_Zone(0);
        $this->restMethod = $this->restOfWorld->add_shipping_method('flat_rate');
        $this->configured = new WC_Shipping_Zone();
        $this->configured->set_zone_name('Shipping regression');
        $this->configured->add_location('DE', 'country');
        $this->configured->save();
        $this->configuredMethod = $this->configured->add_shipping_method('flat_rate');
    }

    protected function tearDown(): void
    {
        $this->restOfWorld->delete_shipping_method($this->restMethod);
        $this->configured->delete(true);
    }

    public function testShippingEnumerationDoesNotUseNullArrayKeys(): void
    {
        set_error_handler(function ($severity, $message, $file) {
            if ($severity === E_DEPRECATED && basename($file) === 'Shipping.php') {
                throw new RuntimeException($message);
            }
            return false;
        });
        try {
            $methods = $this->handler->getZonesShippingMethods();
            self::assertIsArray($methods);
        } finally {
            restore_error_handler();
        }
    }

    public function testRestOfWorldAndConfiguredMethodsKeepTheirInstanceKeys(): void
    {
        $methods = $this->handler->getZonesShippingMethods();
        $restKey = 'flat_rate_' . $this->restMethod;
        $configuredKey = 'flat_rate_' . $this->configuredMethod;
        self::assertArrayHasKey($restKey, $methods);
        self::assertArrayHasKey($configuredKey, $methods);
        self::assertSame($this->restMethod, $methods[$restKey]->get_instance_id());
        self::assertSame($this->configuredMethod, $methods[$configuredKey]->get_instance_id());
    }
}
