<?php
use Automattic\WooCommerce\Blocks\Assets\AssetDataRegistry;
use Automattic\WooCommerce\Blocks\Package;
use Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry;
use Hyyan\WPI\Utilities;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class BlockGatewayTranslationTest extends TestCase
{
    #[DataProvider('languagesAndBlocks')]
    #[PHPUnit\Framework\Attributes\RunInSeparateProcess]
    #[PHPUnit\Framework\Attributes\PreserveGlobalState(false)]
    public function testNativePaymentDataTranslatesConfiguredFieldsAndPreservesSettings(string $language, string $block): void
    {
        $settings = array(); $translations = array();
        $previous = PLL()->curlang;
        $registry = Package::container()->get(PaymentMethodRegistry::class);
        $assets = Package::container()->get(AssetDataRegistry::class);
        try {
            foreach (array('en', 'de') as $slug) {
                $lang = PLL()->model->get_language($slug);
                $mo = new PLL_MO(); $mo->import_from_db($lang);
                $translations[$slug] = clone $mo;
                foreach (array('cheque', 'bacs', 'cod') as $id) {
                    foreach (array('title', 'description') as $field) {
                        $source = 'Gateway regression '.$id.' '.$field;
                        $mo->add_entry(new Translation_Entry(array('singular' => $source,
                            'translations' => array($slug === 'de' ? 'Deutsch '.$source : $source))));
                    }
                }
                $mo->export_to_db($lang);
            }
            foreach (array('cheque', 'bacs', 'cod') as $id) {
                $key = 'woocommerce_'.$id.'_settings'; $settings[$key] = get_option($key);
                update_option($key, array('enabled' => 'yes', 'title' => 'Gateway regression '.$id.' title',
                    'description' => 'Gateway regression '.$id.' description'));
                $registry->get_registered($id)->initialize();
            }
            Utilities::switchLocale($language === 'de' ? 'de_DE' : 'en_US');
            $raw = $registry->get_all_registered_script_data()['paymentMethodData'];
            self::assertFalse($assets->exists('paymentMethodData'));
            do_action('woocommerce_blocks_'.$block.'_enqueue_data');
            $getter = new ReflectionMethod($assets, 'get');
            $data = $getter->invoke($assets)['paymentMethodData'];
            foreach (array('cheque', 'bacs', 'cod') as $id) {
                $expected = $raw[$id];
                foreach (array('title', 'description') as $field) {
                    $expected[$field] = ($language === 'de' ? 'Deutsch ' : '').$raw[$id][$field];
                }
                self::assertSame($expected, $data[$id]);
                $order = new WC_Order();
                $order->set_payment_method($id);
                $order->set_payment_method_title($raw[$id]['title']);
                self::assertSame($expected['title'], $order->get_payment_method_title());
                $order->set_payment_method_title($expected['title']);
                self::assertSame($expected['title'], $order->get_payment_method_title());
                self::assertSame('Gateway regression '.$id.' title', get_option('woocommerce_'.$id.'_settings')['title']);
            }
            do_action('woocommerce_blocks_'.$block.'_enqueue_data');
            self::assertSame($data, $getter->invoke($assets)['paymentMethodData']);
        } finally {
            foreach ($settings as $key => $value) {
                if ($value === false) { delete_option($key); } else { update_option($key, $value); }
            }
            foreach ($translations as $slug => $mo) { $mo->export_to_db(PLL()->model->get_language($slug)); }
            PLL()->curlang = $previous; restore_previous_locale();
        }
    }

    public static function languagesAndBlocks(): array
    {
        return array('German cart' => array('de', 'cart'), 'German checkout' => array('de', 'checkout'),
            'English cart' => array('en', 'cart'), 'English checkout' => array('en', 'checkout'));
    }
}
