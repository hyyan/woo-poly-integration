<?php
use PHPUnit\Framework\TestCase;
use Hyyan\WPI\Utilities;
use Hyyan\WPI\Product\Variation;

final class CartVariationContinuityTest extends TestCase
{
    public function testLinkedVariationAddsMergeButDifferentSelectionsAndItemDataStaySeparate(): void
    {
        $originalCart = WC()->cart;
        $originalLanguage = PLL()->curlang;
        $originalStrings = $GLOBALS['l10n']['pll_string'] ?? null;
        $originalDirection = $GLOBALS['text_direction'] ?? null;
        $originalNotices = wc_get_notices();
        $parents = array(); $variants = array();
        try {
            wc_load_cart(); WC()->cart = new WC_Cart();
            foreach (array('en','de') as $lang) {
                $p = new WC_Product_Variable(); $p->set_name('Cart continuity '.$lang); $p->set_status('publish');
                $a = new WC_Product_Attribute(); $a->set_name('Size'); $a->set_options(array('Small','Large')); $a->set_variation(true);
                $p->set_attributes(array($a)); $p->save(); pll_set_post_language($p->get_id(),$lang); $parents[$lang]=$p->get_id();
                foreach (array('Small'=>'9.95','Large'=>'14.95') as $size=>$price) {
                    $v=new WC_Product_Variation(); $v->set_parent_id($p->get_id()); $v->set_attributes(array('size'=>$size)); $v->set_regular_price($price); $v->set_virtual(true); $v->set_status('publish'); $v->save(); $variants[$lang][$size]=$v->get_id();
                }
                WC_Product_Variable::sync($p->get_id());
            }
            pll_save_post_translations($parents);
            foreach($variants as $lang=>$items){foreach($items as $size=>$id){update_post_meta($id,Variation::DUPLICATE_KEY,$variants['en'][$size]);}}
            Utilities::switch_pll_locale('de_DE');
            $first=WC()->cart->add_to_cart(apply_filters('woocommerce_add_to_cart_product_id',$parents['de']),2,$variants['de']['Large'],array('attribute_size'=>'Large'));
            self::assertNotFalse($first);
            Utilities::switch_pll_locale('en_US');
            $second=WC()->cart->add_to_cart(apply_filters('woocommerce_add_to_cart_product_id',$parents['en']),1,$variants['en']['Large'],array('attribute_size'=>'Large'));
            self::assertSame($first,$second);
            self::assertCount(1,WC()->cart->get_cart());
            self::assertSame(3,WC()->cart->get_cart()[$first]['quantity']);
            self::assertSame($variants['de']['Large'],WC()->cart->get_cart()[$first]['variation_id']);
            WC()->cart->calculate_totals();
            self::assertEquals(44.85,WC()->cart->get_cart()[$first]['line_total']);
            $small=WC()->cart->add_to_cart($parents['en'],1,$variants['en']['Small'],array('attribute_size'=>'Small'));
            self::assertNotFalse($small); self::assertNotSame($first,$small);
            $custom=WC()->cart->add_to_cart($parents['en'],1,$variants['en']['Large'],array('attribute_size'=>'Large'),array('engraving'=>'different'));
            self::assertNotFalse($custom); self::assertNotSame($first,$custom);
            self::assertCount(3,WC()->cart->get_cart());
            WC()->cart->remove_cart_item($custom);
            foreach (array('en','de') as $lang) {
                $v=wc_get_product($variants[$lang]['Large']); $v->set_manage_stock(true); $v->set_stock_quantity(3); $v->save();
            }
            WC()->cart->cart_contents[$first]['data']=wc_get_product($variants['de']['Large']);
            self::assertFalse(WC()->cart->add_to_cart($parents['en'],1,$variants['en']['Large'],array('attribute_size'=>'Large')));
            self::assertSame(3,WC()->cart->get_cart()[$first]['quantity']);
        } finally {
            foreach($variants as $items){foreach($items as $id){wc_get_product($id)->delete(true);}}
            foreach($parents as $id){wc_get_product($id)->delete(true);}
            WC()->cart=$originalCart; PLL()->curlang=$originalLanguage;
            wc_set_notices($originalNotices);
            if ($originalStrings) { $GLOBALS['l10n']['pll_string']=$originalStrings; } else { unset($GLOBALS['l10n']['pll_string']); }
            if ($originalDirection !== null) { $GLOBALS['text_direction']=$originalDirection; } else { unset($GLOBALS['text_direction']); }
        }
    }

    #[PHPUnit\Framework\Attributes\DataProvider('stockModes')]
    public function testNativeStockModesRetainTranslatedCartRules(string $mode): void
    {
        $originalCart=WC()->cart; $originalLanguage=PLL()->curlang;
        $originalStrings=$GLOBALS['l10n']['pll_string'] ?? null;
        $originalDirection=$GLOBALS['text_direction'] ?? null; $originalNotices=wc_get_notices();
        $parents=array(); $variants=array();
        try {
            wc_load_cart(); WC()->cart=new WC_Cart();
            foreach(array('en','de') as $lang){
                $p=new WC_Product_Variable(); $p->set_name('Stock mode '.$mode.' '.$lang); $p->set_status('publish');
                $a=new WC_Product_Attribute(); $a->set_name('Size'); $a->set_options(array('Small','Large')); $a->set_variation(true);
                $p->set_attributes(array($a)); $p->set_sold_individually($mode==='sold');
                if($mode!=='sold'){ $p->set_manage_stock(true); $p->set_stock_quantity($mode==='backorders' ? 1 : 3); $p->set_backorders($mode==='backorders' ? 'yes' : 'no'); }
                $p->save(); pll_set_post_language($p->get_id(),$lang); $parents[$lang]=$p->get_id();
                foreach(array('Small','Large') as $size){
                    $v=new WC_Product_Variation(); $v->set_parent_id($p->get_id()); $v->set_attributes(array('size'=>$size)); $v->set_regular_price('14.95'); $v->set_status('publish'); $v->set_virtual(true);
                    $v->set_sold_individually($mode==='sold'); $v->save(); $variants[$lang][$size]=$v->get_id();
                }
                WC_Product_Variable::sync($p->get_id());
            }
            pll_save_post_translations($parents);
            foreach($variants as $lang=>$items){foreach($items as $size=>$id){update_post_meta($id,Variation::DUPLICATE_KEY,$variants['en'][$size]);}}
            Utilities::switch_pll_locale('de_DE');
            $first=WC()->cart->add_to_cart($parents['de'],2,$variants['de']['Large'],array('attribute_size'=>'Large'));
            self::assertNotFalse($first);
            Utilities::switch_pll_locale('en_US');
            if($mode==='parent'){
                $small=WC()->cart->add_to_cart($parents['en'],1,$variants['en']['Small'],array('attribute_size'=>'Small'));
                self::assertNotFalse($small);
                self::assertFalse(WC()->cart->add_to_cart($parents['en'],1,$variants['en']['Large'],array('attribute_size'=>'Large')));
                self::assertSame(3,WC()->cart->get_cart_contents_count());
            } elseif($mode==='backorders'){
                self::assertSame($first,WC()->cart->add_to_cart($parents['en'],1,$variants['en']['Large'],array('attribute_size'=>'Large')));
                self::assertSame(3,WC()->cart->get_cart_contents_count());
            } else {
                self::assertSame(1,WC()->cart->get_cart()[$first]['quantity']);
                self::assertFalse(WC()->cart->add_to_cart($parents['en'],1,$variants['en']['Large'],array('attribute_size'=>'Large')));
                self::assertSame(1,WC()->cart->get_cart_contents_count());
            }
        } finally {
            foreach($variants as $items){foreach($items as $id){wc_get_product($id)->delete(true);}}
            foreach($parents as $id){wc_get_product($id)->delete(true);}
            WC()->cart=$originalCart; PLL()->curlang=$originalLanguage; wc_set_notices($originalNotices);
            if($originalStrings){$GLOBALS['l10n']['pll_string']=$originalStrings;}else{unset($GLOBALS['l10n']['pll_string']);}
            if($originalDirection!==null){$GLOBALS['text_direction']=$originalDirection;}else{unset($GLOBALS['text_direction']);}
        }
    }

    public static function stockModes(): array
    {
        return array('parent'=>array('parent'),'backorders'=>array('backorders'),'sold individually'=>array('sold'));
    }

}
