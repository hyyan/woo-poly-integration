<?php

use Hyyan\WPI\Product\TranslatedDraft;
use PHPUnit\Framework\TestCase;

final class DeepLProductTest extends TestCase
{
    public function testCreatesLinkedDraftAndProtectsReviewedContent(): void
    {
        $user = get_current_user_id();
        $key = getenv('DEEPL_AUTH_KEY');
        $ids = array();
        $requests = 0;
        $api = static function ($pre, $args, $url) use (&$requests) {
            if ($url !== 'https://api-free.deepl.com/v2/translate') {
                return $pre;
            }
            ++$requests;
            $body = json_decode($args['body'], true);
            self::assertSame('EN', $body['source_lang']);
            self::assertSame('DE', $body['target_lang']);
            $items = array_map(static function ($s) { return array('text' => 'DE ' . $s); }, $body['text']);
            return array('response' => array('code' => 200), 'headers' => array(), 'body' => wp_json_encode(array('translations' => $items)));
        };
        $keys = static function () { return array('seo_title', '_sku', '_price'); };
        try {
            wp_set_current_user(1);
            putenv('DEEPL_AUTH_KEY=test:fx');
            add_filter('pre_http_request', $api, 10, 3);
            add_filter(\Hyyan\WPI\HooksInterface::PRODUCT_TRANSLATION_META_KEYS, $keys);
            $product = new WC_Product_Simple();
            $product->set_name('Blue cup');
            $product->set_description('<p>Stoneware</p>');
            $product->set_short_description('Handmade');
            $product->set_purchase_note('Wash by hand');
            $product->set_regular_price('19.99');
            $product->set_sku(wp_unique_id('cup-'));
            $product->set_manage_stock(true);
            $product->set_stock_quantity(7);
            $source = $product->save();
            $ids[] = $source;
            pll_set_post_language($source, 'en');
            update_post_meta($source, 'seo_title', 'Blue cup');
            wp_set_current_user(0);
            self::assertTrue(is_wp_error(TranslatedDraft::create($source, 'de')));
            self::assertSame(0, $requests);
            wp_set_current_user(1);
            $failure = static function () { return new WP_Error('connection_failed'); };
            add_filter('pre_http_request', $failure, 100);
            try {
                self::assertTrue(is_wp_error(TranslatedDraft::create($source, 'de')));
                self::assertEmpty(pll_get_post($source, 'de'));
            } finally {
                remove_filter('pre_http_request', $failure, 100);
            }
            $draft = TranslatedDraft::create($source, 'de');
            self::assertIsInt($draft, is_wp_error($draft) ? $draft->get_error_message() : '');
            $ids[] = $draft;
            self::assertSame('draft', get_post_status($draft));
            self::assertSame($draft, pll_get_post($source, 'de'));
            $copy = wc_get_product($draft);
            self::assertSame('DE Blue cup', $copy->get_name());
            self::assertSame('DE <p>Stoneware</p>', $copy->get_description());
            self::assertSame('DE Handmade', $copy->get_short_description());
            self::assertSame('DE Wash by hand', $copy->get_purchase_note());
            self::assertSame('DE Blue cup', get_post_meta($draft, 'seo_title', true));
            self::assertSame($product->get_sku(), $copy->get_sku());
            self::assertEquals(19.99, $copy->get_price());
            self::assertSame(7, $copy->get_stock_quantity());
            wp_update_post(array('ID' => $draft, 'post_title' => 'Reviewed'));
            $before = $requests;
            self::assertTrue(is_wp_error(TranslatedDraft::create($source, 'de')));
            self::assertSame($before, $requests);
            self::assertSame('Reviewed', get_post($draft)->post_title);
        } finally {
            remove_filter('pre_http_request', $api, 10);
            remove_filter(\Hyyan\WPI\HooksInterface::PRODUCT_TRANSLATION_META_KEYS, $keys);
            foreach (array_reverse($ids) as $id) {
                wp_delete_post($id, true);
            }
            putenv($key === false ? 'DEEPL_AUTH_KEY' : 'DEEPL_AUTH_KEY=' . $key);
            wp_set_current_user($user);
        }
    }
}
