<?php
use Hyyan\WPI\Gateways\GatewayBACS;
use Hyyan\WPI\Gateways\GatewayCOD;
use Hyyan\WPI\Gateways\GatewayCheque;
use Hyyan\WPI\Tools\FlashMessages;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class GatewayOutputSecurityTest extends TestCase
{
    #[DataProvider('gateways')]
    public function testCustomerInstructionsRemoveScriptsAndPreserveRichText(string $class, string $method, string $output): void
    {
        $gateway = new $class();
        $gateway->instructions = '<script>window.woopolyGatewayMarker=true</script><p><strong>Pay securely</strong> <a href="https://example.org/pay">Details</a></p>';
        if ($gateway instanceof GatewayBACS) {
            $gateway->account_details = array();
        }
        $order = wc_create_order();
        $order->set_payment_method($method);
        $order->set_status('on-hold');
        $bufferLevel = ob_get_level();
        ob_start();
        try {
            if ($output === 'email') {
                $gateway->email_instructions($order, false);
            } else {
                $gateway->thankyou_page($order->get_id());
            }
            $html = ob_get_clean();
            self::assertStringNotContainsString('<script>', $html);
            self::assertStringContainsString('<strong>Pay securely</strong>', $html);
            self::assertStringContainsString('href="https://example.org/pay"', $html);
        } finally {
            while (ob_get_level() > $bufferLevel) {
                ob_end_clean();
            }
            remove_action('woocommerce_thankyou_'.$method, array($gateway, 'thankyou_page'));
            remove_action('woocommerce_email_before_order_table', array($gateway, 'email_instructions'));
            $order->delete(true);
        }
    }

    public static function gateways(): array
    {
        $cases = array();
        foreach (array('bacs' => GatewayBACS::class, 'cod' => GatewayCOD::class, 'cheque' => GatewayCheque::class) as $method => $class) {
            foreach (array('email', 'thankyou') as $output) {
                $cases[$method.' '.$output] = array($class, $method, $output);
            }
        }
        return $cases;
    }

    public function testBankHeadingAndFilteredAccountDetailsCannotCreateScripts(): void
    {
        $gateway = new GatewayBACS();
        $gateway->instructions = '';
        $gateway->account_details = array(array('account_name' => 'Example bank', 'bank_name' => 'Example', 'account_number' => '123456', 'sort_code' => '', 'iban' => '', 'bic' => ''));
        $order = wc_create_order();
        $order->set_billing_country('GB');
        $order->save();
        $filter = function ($translated, $text, $domain) {
            return $domain === 'woocommerce' && $text === 'Our bank details'
                ? '<script>window.woopolyBankMarker=true</script>Bank heading'
                : $translated;
        };
        add_filter('gettext', $filter, 10, 3);
        $bufferLevel = ob_get_level();
        ob_start();
        try {
            $gateway->thankyou_page($order->get_id());
            $html = ob_get_clean();
            self::assertStringNotContainsString('<script>', $html);
            self::assertStringContainsString('Bank heading', $html);
            self::assertStringContainsString('123456', $html);
            self::assertStringContainsString('wc-bacs-bank-details-account-name', $html);
        } finally {
            while (ob_get_level() > $bufferLevel) {
                ob_end_clean();
            }
            remove_filter('gettext', $filter, 10);
            remove_action('woocommerce_thankyou_bacs', array($gateway, 'thankyou_page'));
            remove_action('woocommerce_email_before_order_table', array($gateway, 'email_instructions'));
            $order->delete(true);
        }
    }

    public function testNoticeClassesCannotInjectAttributesAndRichMessageRemainsIntact(): void
    {
        $original = get_option(FlashMessages::getOptionName());
        FlashMessages::clearMessages();
        FlashMessages::add('output-test', '<script>window.woopolyNoticeMarker=true</script><b onclick="alert(1)">Notice</b><a href="https://example.org/help">Help</a><a href="javascript:alert(1)">Unsafe</a>', array('updated" data-marker="unsafe'));
        $bufferLevel = ob_get_level();
        ob_start();
        try {
            FlashMessages::display();
            $html = ob_get_clean();
            self::assertStringNotContainsString(' data-marker="unsafe', $html);
            self::assertStringNotContainsString('<script>', $html);
            self::assertStringNotContainsString('onclick=', $html);
            self::assertStringNotContainsString('javascript:', $html);
            self::assertStringContainsString('<b>Notice</b>', $html);
            self::assertStringContainsString('href="https://example.org/help"', $html);
        } finally {
            while (ob_get_level() > $bufferLevel) {
                ob_end_clean();
            }
            if ($original === false) {
                delete_option(FlashMessages::getOptionName());
            } else {
                update_option(FlashMessages::getOptionName(), $original);
            }
        }
    }
}
