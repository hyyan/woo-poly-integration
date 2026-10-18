<?php
use Hyyan\WPI\Gateways;
use Hyyan\WPI\Gateways\GatewayBACS;
use Hyyan\WPI\Gateways\GatewayCOD;
use Hyyan\WPI\Gateways\GatewayCheque;
use Hyyan\WPI\Shipping;
use Hyyan\WPI\Utilities;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class StoredTranslationContractTest extends TestCase
{
    #[DataProvider('storedTextPaths')]
    public function testConfiguredTextUsesPolylangTranslationWithoutChangingStoredValue(string $language, string $path): void
    {
        $previous = PLL()->curlang;
        $strings = $GLOBALS['l10n']['pll_string'] ?? null;
        $direction = $GLOBALS['text_direction'] ?? null;
        $source = 'Stored translation contract ' . $path . ' ' . $language;
        $target = $language === 'de' ? 'Gespeicherte Uebersetzung ' . $path : $source;
        $order = null;
        $gateway = null;
        $bufferLevel = ob_get_level();
        try {
            Utilities::switch_pll_locale($language === 'de' ? 'de_DE' : 'en_US');
            $mo = new PLL_MO();
            $mo->add_entry(new Translation_Entry(array('singular' => $source, 'translations' => array($target))));
            $GLOBALS['l10n']['pll_string'] = $mo;
            self::assertSame($target, pll__($source));
            $payments = (new ReflectionClass(Gateways::class))->newInstanceWithoutConstructor();
            $shipping = (new ReflectionClass(Shipping::class))->newInstanceWithoutConstructor();
            switch ($path) {
                case 'payment title':
                    $result = $payments->translatePaymentGatewayTitle($source, 'bacs');
                    break;
                case 'payment description':
                    $result = $payments->translatePaymentGatewayDescription($source, 'bacs');
                    break;
                case 'order payment method':
                    $rows = array('payment_method' => array('label' => 'Payment', 'value' => $source));
                    $translated = $payments->translateWoocommerceOrderPaymentMethod($rows, new WC_Order());
                    self::assertSame('Payment', $translated['payment_method']['label']);
                    self::assertSame($source, $rows['payment_method']['value']);
                    $result = $translated['payment_method']['value'];
                    break;
                case 'shipping label':
                    $result = $shipping->translateShippingLabel($source);
                    break;
                case 'order shipping method':
                    $result = $shipping->translateOrderShippingMethod($source . ', ' . $source, new WC_Order());
                    self::assertSame($target . ', ' . $target, $result);
                    return;
                default:
                    list($method, $output) = explode(' ', $path);
                    $classes = array('bacs' => GatewayBACS::class, 'cod' => GatewayCOD::class, 'cheque' => GatewayCheque::class);
                    $gateway = new $classes[$method]();
                    $gateway->instructions = $source;
                    if ($method === 'bacs') {
                        $gateway->account_details = array();
                    }
                    $order = wc_create_order();
                    $order->set_payment_method($method);
                    $order->set_status('on-hold');
                    ob_start();
                    if ($output === 'email') {
                        $gateway->email_instructions($order, false);
                    } else {
                        $gateway->thankyou_page($order->get_id());
                    }
                    $result = trim(strip_tags(ob_get_clean()));
                    self::assertSame($source, $gateway->instructions);
            }
            self::assertSame($target, $result);
        } finally {
            while (ob_get_level() > $bufferLevel) {
                ob_end_clean();
            }
            if ($gateway) {
                remove_action('woocommerce_thankyou_' . $method, array($gateway, 'thankyou_page'));
                remove_action('woocommerce_email_before_order_table', array($gateway, 'email_instructions'));
            }
            if ($order) {
                $order->delete(true);
            }
            PLL()->curlang = $previous;
            if ($strings === null) { unset($GLOBALS['l10n']['pll_string']); }
            else { $GLOBALS['l10n']['pll_string'] = $strings; }
            if ($direction === null) { unset($GLOBALS['text_direction']); }
            else { $GLOBALS['text_direction'] = $direction; }
        }
    }

    public static function storedTextPaths(): array
    {
        $paths = array('payment title', 'payment description', 'order payment method', 'shipping label', 'order shipping method',
            'bacs thankyou', 'bacs email', 'cod thankyou', 'cod email', 'cheque thankyou', 'cheque email');
        $cases = array();
        foreach (array('en', 'de') as $language) {
            foreach ($paths as $path) {
                $cases[$language . ' ' . $path] = array($language, $path);
            }
        }
        return $cases;
    }
}
