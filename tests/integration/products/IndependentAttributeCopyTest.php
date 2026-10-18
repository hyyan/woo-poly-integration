<?php
use Hyyan\WPI\Product\Meta;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class IndependentAttributeCopyTest extends TestCase
{
    #[PHPUnit\Framework\Attributes\RunInSeparateProcess]
    #[PHPUnit\Framework\Attributes\PreserveGlobalState(false)]
    #[DataProvider('copyModes')]
    public function testNativeCopyRespectsCustomAttributeSetting(bool $sync, bool $custom): void
    {
        $settings = get_option('wpi-metas-list');
        $modified = $settings;
        if (!$custom) { unset($modified['Attributes']['_custom_product_attributes']); }
        else { $modified['Attributes']['_custom_product_attributes'] = '_custom_product_attributes'; }
        update_option('wpi-metas-list', $modified);
        $products = array();
        try {
            foreach (array('en', 'de') as $slug) {
                $p = new WC_Product_Simple(); $p->set_name('Independent attribute regression '.$slug); $p->save();
                pll_set_post_language($p->get_id(), $slug); $products[$slug] = $p->get_id();
            }
            pll_save_post_translations($products);
            $global = array('name'=>'pa_copy-regression','value'=>'','position'=>0,'is_visible'=>1,'is_variation'=>0,'is_taxonomy'=>1);
            $source = array('name'=>'Custom note','value'=>'Source note','position'=>1,'is_visible'=>1,'is_variation'=>0,'is_taxonomy'=>0);
            $target = $source; $target['value'] = 'Independent target note';
            update_post_meta($products['en'], '_product_attributes', array('pa_copy-regression'=>$global,'custom-note'=>$source));
            update_post_meta($products['de'], '_product_attributes', array('custom-note'=>$target));
            add_filter('pll_copy_post_metas', array(Meta::class, 'wpi_filter_pll_metas'), 10, 5);
            if ($sync && !$custom) {
                $source['value'] = 'Updated source note';
                update_post_meta($products['en'], '_product_attributes', array('pa_copy-regression'=>$global, 'custom-note'=>$source));
                self::assertSame($target, get_post_meta($products['de'], '_product_attributes', true)['custom-note']);
            }
            $handler = (new ReflectionClass(Meta::class))->newInstanceWithoutConstructor();
            $handler->syncProductMeta($products['en'], $products['de'], 'de', $sync);
            $copied = get_post_meta($products['de'], '_product_attributes', true);
            self::assertSame($global, $copied['pa_copy-regression']);
            self::assertSame($sync && !$custom ? $target : $source, $copied['custom-note']);
            self::assertSame(array('pa_copy-regression'=>$global,'custom-note'=>$source), get_post_meta($products['en'], '_product_attributes', true));
        } finally {
            update_option('wpi-metas-list', $settings);
            foreach ($products as $id) { wp_delete_post($id, true); }
        }
    }

    #[PHPUnit\Framework\Attributes\RunInSeparateProcess]
    #[PHPUnit\Framework\Attributes\PreserveGlobalState(false)]
    public function testCopyFailureRestoresNativeMetadataFilters(): void
    {
        $settings = get_option('wpi-metas-list');
        $modified = $settings; unset($modified['Attributes']['_custom_product_attributes']);
        update_option('wpi-metas-list', $modified);
        $receiver = PLL()->sync;
        $callbacks = $GLOBALS['wp_filter']['pll_translate_post_meta']->callbacks;
        $copyCallbacks = isset($GLOBALS['wp_filter']['pll_copy_post_metas']) ? $GLOBALS['wp_filter']['pll_copy_post_metas']->callbacks : array();
        PLL()->sync = new class {
            public function copy_post_metas($from, $to, $lang, $sync): void
            {
                throw new RuntimeException('Receiver regression');
            }
        };
        try {
            $handler = (new ReflectionClass(Meta::class))->newInstanceWithoutConstructor();
            try { $handler->syncProductMeta(11, 12, 'de', true); self::fail('Receiver should throw'); }
            catch (RuntimeException $error) { self::assertSame('Receiver regression', $error->getMessage()); }
            self::assertSame($callbacks, $GLOBALS['wp_filter']['pll_translate_post_meta']->callbacks);
            self::assertSame($copyCallbacks, isset($GLOBALS['wp_filter']['pll_copy_post_metas']) ? $GLOBALS['wp_filter']['pll_copy_post_metas']->callbacks : array());
        } finally {
            PLL()->sync = $receiver;
            update_option('wpi-metas-list', $settings);
        }
    }

    public static function copyModes(): array
    {
        return array('independent'=>array(true,false),'synchronized'=>array(true,true),'new translation'=>array(false,false));
    }
}
