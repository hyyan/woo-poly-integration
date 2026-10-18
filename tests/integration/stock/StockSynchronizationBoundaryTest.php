<?php
use Hyyan\WPI\Product\Stock;
use Hyyan\WPI\Product\Variation;
use PHPUnit\Framework\TestCase;

final class StockSynchronizationBoundaryTest extends TestCase
{
    public function testOneExhaustedVariationDoesNotHideAvailableSiblingOrParent(): void
    {
        $parents=array();$variants=array();
        try {
            foreach(array('en','de') as $lang){
                $p=new WC_Product_Variable();$p->set_name('Stock boundary '.$lang);$p->set_status('publish');
                $a=new WC_Product_Attribute();$a->set_name('Size');$a->set_options(array('Small','Large'));$a->set_variation(true);$p->set_attributes(array($a));$p->save();pll_set_post_language($p->get_id(),$lang);$parents[$lang]=$p->get_id();
                foreach(array('Small'=>3,'Large'=>7) as $size=>$stock){
                    $v=new WC_Product_Variation();$v->set_parent_id($p->get_id());$v->set_attributes(array('size'=>$size));$v->set_status('publish');$v->set_regular_price('10');$v->set_manage_stock(true);$v->set_stock_quantity($stock);$v->save();$variants[$lang][$size]=$v->get_id();
                }
                WC_Product_Variable::sync($p->get_id());
            }
            pll_save_post_translations($parents);
            foreach($variants as $lang=>$items){foreach($items as $size=>$id){update_post_meta($id,Variation::DUPLICATE_KEY,$variants['en'][$size]);}}
            wc_update_product_stock(wc_get_product($variants['de']['Small']),0,'set');
            foreach(array('en','de') as $lang){
                self::assertSame(0,wc_get_product($variants[$lang]['Small'])->get_stock_quantity());
                self::assertSame(7,wc_get_product($variants[$lang]['Large'])->get_stock_quantity());
                self::assertSame('instock',wc_get_product($parents[$lang])->get_stock_status());
            }
        } finally {
            foreach($variants as $items){foreach($items as $id){wc_get_product($id)->delete(true);}}
            foreach($parents as $id){wc_get_product($id)->delete(true);}
        }
    }

    #[PHPUnit\Framework\Attributes\DataProvider('stockProductTypes')]
    public function testStockCallbackRestoresItsHookWhenAReceiverThrows(string $type): void
    {
        $products=array();$parents=array();$hooks=array_map(static function($hook){return clone $hook;},$GLOBALS['wp_filter']);
        $tag=$type==='variation'?'woocommerce_variation_set_stock':'woocommerce_product_set_stock';
        $callback=array(Stock::class,$type==='variation'?'SyncStockVariation':'SyncStockSimple');
        try {
            foreach(array('en','de') as $lang){
                if($type==='variation'){$parent=new WC_Product_Variable();$parent->set_name('Stock receiver parent '.$lang);$parent->set_status('publish');$parent->save();pll_set_post_language($parent->get_id(),$lang);$parents[$lang]=$parent->get_id();$p=new WC_Product_Variation();$p->set_parent_id($parent->get_id());}
                else{$p=new WC_Product_Simple();}
                $p->set_name('Stock exception '.$lang);$p->set_status('publish');$p->set_regular_price('10');$p->set_manage_stock(true);$p->set_stock_quantity(5);$p->save();
                if($type==='simple'){pll_set_post_language($p->get_id(),$lang);}
                $products[$lang]=$p->get_id();
            }
            if($type==='variation'){pll_save_post_translations($parents);foreach($products as $id){update_post_meta($id,Variation::DUPLICATE_KEY,$products['en']);}}
            else{pll_save_post_translations($products);}
            $receiver=static function($product) use($products){if($product->get_id()===$products['de']){throw new RuntimeException('Controlled receiver failure');}};
            add_action($tag,$receiver,20);
            try {wc_update_product_stock(wc_get_product($products['en']),4,'set');self::fail('Expected the controlled receiver exception');}
            catch(RuntimeException $exception){self::assertSame('Controlled receiver failure',$exception->getMessage());}
            self::assertSame(10,has_action($tag,$callback));
            remove_action($tag,$receiver,20);
            wc_update_product_stock(wc_get_product($products['en']),3,'set');
            self::assertSame(3,wc_get_product($products['en'])->get_stock_quantity());
            self::assertSame(3,wc_get_product($products['de'])->get_stock_quantity());
        } finally {
            $GLOBALS['wp_filter']=$hooks;
            foreach($products as $id){wc_get_product($id)->delete(true);}
            foreach($parents as $id){wc_get_product($id)->delete(true);}
        }
    }
    public static function stockProductTypes(): array
    {
        return array('simple'=>array('simple'),'variation'=>array('variation'));
    }

    #[PHPUnit\Framework\Attributes\DataProvider('invalidSourceRelationships')]
    public function testNativeVariationLinkSurvivesInvalidSourceRelationship(?string $source): void
    {
        $products = array();
        $parents = array();
        try {
            foreach (array('en', 'de') as $lang) {
                $parent = new WC_Product_Variable();
                $parent->set_name('Invalid source stock ' . $lang);
                $parent->set_status('publish');
                $parent->save();
                pll_set_post_language($parent->get_id(), $lang);
                $parents[$lang] = $parent->get_id();
                $child = new WC_Product_Variation();
                $child->set_parent_id($parent->get_id());
                $child->set_status('publish');
                $child->set_regular_price('12');
                $child->set_manage_stock(true);
                $child->set_stock_quantity(5);
                $child->save();
                pll_set_post_language($child->get_id(), $lang);
                $products[$lang] = $child->get_id();
            }
            pll_save_post_translations($parents);
            pll_save_post_translations($products);
            update_post_meta($products['de'], Variation::DUPLICATE_KEY, '000' . $products['en']);
            if ($source !== null) { update_post_meta($products['en'], Variation::DUPLICATE_KEY, $source); }
            self::assertSame($products['en'], pll_get_post($products['de'], 'en'));
            wc_update_product_stock(wc_get_product($products['de']), 4, 'set');
            self::assertSame(4, wc_get_product($products['de'])->get_stock_quantity());
            self::assertSame(4, wc_get_product($products['en'])->get_stock_quantity());
            self::assertSame(10, has_action('woocommerce_variation_set_stock', array(Stock::class, 'SyncStockVariation')));
        } finally {
            foreach ($products as $id) { wp_delete_post($id, true); }
            foreach ($parents as $id) { wp_delete_post($id, true); }
        }
    }

    public static function invalidSourceRelationships(): array
    {
        return array('missing' => array(null), 'malformed' => array('not-a-variation'),
            'overflow' => array((string) PHP_INT_MAX . '0'));
    }

}
