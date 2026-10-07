<?php
use Hyyan\WPI\Product\Variable;
use Hyyan\WPI\Product\Variation;
use PHPUnit\Framework\TestCase;

final class VariableProductImportTest extends TestCase
{
    #[PHPUnit\Framework\Attributes\RunInSeparateProcess]
    #[PHPUnit\Framework\Attributes\PreserveGlobalState(false)]
    public function testNativeImportSynchronizesMultipleVariationRowsAndPreservesTaxOverrides(): void
    {
        $page = $GLOBALS['pagenow'];
        $settings = get_option('wpi-metas-list');
        $language = PLL()->curlang;
        $products = array();
        $temporary = tempnam(sys_get_temp_dir(), 'woopoly-variable-import-');
        $csv = $temporary.'.csv';
        rename($temporary, $csv);
        try {
            $GLOBALS['pagenow'] = 'index.php';
            PLL()->curlang = PLL()->model->get_language('en');
            $modified = $settings;
            unset($modified['Taxes']['_tax_class']);
            update_option('wpi-metas-list', $modified);
            foreach (array('en', 'de') as $slug) {
                $parent = new WC_Product_Variable();
                $parent->set_name('Variable import regression '.$slug);
                $parent->set_status('publish');
                $parent->save();
                $products[$slug] = $parent->get_id();
                pll_set_post_language($parent->get_id(), $slug);
            }
            pll_save_post_translations($products);
            $source = array();
            for ($i = 0; $i < 2; $i++) {
                $child = new WC_Product_Variation();
                $child->set_parent_id($products['en']);
                $child->set_regular_price('20');
                $child->save();
                pll_set_post_language($child->get_id(), 'en');
                $source[] = $child->get_id();
            }
            $handler = (new ReflectionClass(Variable::class))->newInstanceWithoutConstructor();
            $GLOBALS['pagenow'] = 'post.php';
            $handler->duplicateVariations($products['en'], get_post($products['en']), true);
            $GLOBALS['pagenow'] = 'edit.php';
            $targets = wc_get_product($products['de'])->get_children();
            self::assertCount(2, $targets);
            foreach ($targets as $id) { update_post_meta($id, '_tax_class', 'zero-rate'); }
            file_put_contents($csv, "ID,Type,Regular price\n".$source[0].",variation,25\n".$source[1].",variation,30\n");
            require_once WC_ABSPATH.'includes/import/class-wc-product-csv-importer.php';
            $importer = new WC_Product_CSV_Importer($csv, array('mapping'=>array('ID'=>'id','Type'=>'type','Regular price'=>'regular_price'), 'parse'=>true, 'update_existing'=>true));
            $result = $importer->import();
            self::assertSame($source, $result['updated']);
            self::assertSame(array(), $result['failed']);
            foreach ($targets as $id) {
                $from = (int) get_post_meta($id, Variation::DUPLICATE_KEY, true);
                self::assertContains($from, $source);
                self::assertSame(wc_get_product($from)->get_regular_price(), wc_get_product($id)->get_regular_price());
                self::assertSame('zero-rate', wc_get_product($id)->get_tax_class('edit'));
            }
            self::assertSame($targets, wc_get_product($products['de'])->get_children());
        } finally {
            $GLOBALS['pagenow'] = 'index.php';
            foreach ($products as $id) {
                foreach (get_posts(array('post_type'=>'product_variation','post_parent'=>$id,'post_status'=>'any','numberposts'=>-1,'lang'=>'')) as $child) { wp_delete_post($child->ID, true); }
                wp_delete_post($id, true);
            }
            update_option('wpi-metas-list', $settings);
            PLL()->curlang = $language;
            $GLOBALS['pagenow'] = $page;
            unlink($csv);
        }
    }
}
