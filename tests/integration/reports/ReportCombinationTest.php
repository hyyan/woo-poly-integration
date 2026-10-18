<?php
use Hyyan\WPI\Reports;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ReportCombinationTest extends TestCase
{
    #[DataProvider('reportContexts')]
    public function testLinkedReportRowsCombineWithoutRequiringLegacyUserPreference($current, string $preferred, string $field): void
    {
        $language = PLL()->curlang;
        $user = get_current_user_id();
        $saved = get_user_meta(1, 'user_lang', true);
        $products = array();
        try {
            wp_set_current_user(1);
            foreach (array('en','de') as $slug) {
                $product = new WC_Product_Simple();
                $product->set_name('Report combination regression '.$slug);
                $product->save();
                pll_set_post_language($product->get_id(), $slug);
                $products[$slug] = $product->get_id();
            }
            pll_save_post_translations($products);
            update_user_meta(1, 'user_lang', $preferred);
            PLL()->curlang = $current ? PLL()->model->get_language($current) : false;
            $report = (new ReflectionClass(Reports::class))->newInstanceWithoutConstructor();
            $result = $report->combineProductsByLanguage(array(
                (object)array('product_id'=>$products['en'], $field=>2),
                (object)array('product_id'=>$products['de'], $field=>3),
            ));
            self::assertCount(1, $result);
            self::assertEquals(5, $result[0]->$field);
            self::assertSame($products[$current ?: ($preferred ?: pll_default_language())], $result[0]->product_id);
        } finally {
            foreach ($products as $id) { wp_delete_post($id, true); }
            if ($saved === '') { delete_user_meta(1, 'user_lang'); }
            else { update_user_meta(1, 'user_lang', $saved); }
            wp_set_current_user($user);
            PLL()->curlang = $language;
        }
    }

    public static function reportContexts(): array
    {
        $cases = array();
        foreach (array('order_item_qty','order_item_total') as $field) {
            foreach (array(array('en',''),array('de',''),array(false,'de'),array(false,'')) as $context) {
                $cases[] = array($context[0], $context[1], $field);
            }
        }
        return $cases;
    }
}
