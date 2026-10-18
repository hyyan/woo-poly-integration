<?php
use Hyyan\WPI\Product\Variable;
use Hyyan\WPI\Product\Variation;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
final class VariationSecurityTest extends TestCase
{
    private array $ids = array();
    private int $source;
    private int $translated;
    private int $unrelated;
    private int $sibling;
    protected function setUp(): void
    {
        wp_set_current_user(1);
        foreach (array('en', 'de') as $language) {
            $parent = new WC_Product_Variable(); $parent->set_name('PHPUnit variable ' . $language); $parent->save();
            $this->ids[] = $parent->get_id(); pll_set_post_language($parent->get_id(), $language);
            $variation = new WC_Product_Variation(); $variation->set_parent_id($parent->get_id()); $variation->set_regular_price('10'); $variation->save();
            $id = $variation->get_id(); $this->ids[] = $id;
            if ($language === 'en') { $this->source = $id; } else { $this->translated = $id; }
        }
        foreach (array($this->source, $this->translated) as $id) { update_post_meta($id, Variation::DUPLICATE_KEY, $this->source); }
        $other = new WC_Product_Simple(); $other->set_name('Unrelated simple product'); $other->save();
        $this->unrelated = $other->get_id(); $this->ids[] = $this->unrelated;
        $sibling = new WC_Product_Variation();
        $sibling->set_parent_id(wp_get_post_parent_id($this->source));
        $sibling->set_regular_price('15'); $sibling->save();
        $this->sibling = $sibling->get_id(); $this->ids[] = $this->sibling;
        $_POST = $_REQUEST = array();
    }
    protected function tearDown(): void
    {
        wp_set_current_user(1);
        foreach (array_reverse($this->ids) as $id) { wp_delete_post($id, true); }
        $_POST = $_REQUEST = array();
    }
    private function remove($values, ?string $nonce): string
    {
        $_POST = $values === null ? array() : array('variation_ids' => $values);
        $_REQUEST = $_POST;
        if ($nonce !== null) { $_REQUEST['security'] = $_POST['security'] = $nonce; }
        ob_start();
        try { do_action('wp_ajax_woocommerce_remove_variations'); } catch (WooPolyTestDie $e) {}
        finally { $output = ob_get_clean(); }
        return $output;
    }

    #[DataProvider('rejectedRequests')]
    public function testInvalidRequestStopsWholeHookBeforeLookup(string $form, string $code): void
    {
        $values = array(
            'missing' => null, 'scalar' => (string) $this->source, 'empty' => array(),
            'zero' => array($this->source, 0), 'missing variation' => array($this->source, PHP_INT_MAX),
            'wrong post type' => array($this->source, $this->unrelated),
            'object' => array($this->source, (object) array('id' => $this->source)),
            'negative' => array($this->source, -1), 'text' => array($this->source, 'invalid'),
            'suffix' => array($this->source, $this->source . 'suffix'),
            'overflow' => array($this->source, (string) PHP_INT_MAX . '0'),
            'nested' => array($this->source, array($this->source)),
            'boolean' => array($this->source, true), 'float' => array($this->source, 1.0),
        );
        $before = array();
        foreach ($this->ids as $id) { $before[$id] = array(get_post($id), get_post_meta($id)); }
        $lookups = array();
        $observe = function ($sql) use (&$lookups) {
            if (strpos($sql, 'TRIM(LEADING') !== false) { $lookups[] = $sql; }
            return $sql;
        };
        add_filter('query', $observe);
        try { $output = $this->remove($values[$form], wp_create_nonce('delete-variations')); }
        finally { remove_filter('query', $observe); }
        self::assertSame(array(), $lookups);
        foreach ($before as $id => $snapshot) {
            self::assertEquals($snapshot, array(get_post($id), get_post_meta($id)));
        }
        self::assertSame($code, json_decode($output, true)['data']['code'] ?? $output);
    }

    public static function rejectedRequests(): array
    {
        $cases = array();
        foreach (array('missing', 'scalar', 'empty', 'zero', 'missing variation', 'wrong post type',
            'object', 'negative', 'text', 'suffix', 'overflow', 'nested', 'boolean', 'float') as $form) {
            $cases[$form] = array($form, in_array($form, array('missing variation', 'wrong post type'), true)
                ? 'invalid_variation' : 'invalid_variation_ids');
        }
        return $cases;
    }

    public function testUnauthenticatedActionHasNoDeletionCallback(): void
    {
        wp_set_current_user(0);
        self::assertFalse(has_action('wp_ajax_nopriv_woocommerce_remove_variations'));
        do_action('wp_ajax_nopriv_woocommerce_remove_variations');
        self::assertNotNull(get_post($this->source));
        self::assertNotNull(get_post($this->translated));
    }

    public function testQueryBindsIntegerAndPreservesExactMetadataMatching(): void
    {
        global $wpdb;
        update_post_meta($this->translated, Variation::DUPLICATE_KEY, '000' . $this->source);
        update_post_meta($this->sibling, Variation::DUPLICATE_KEY, $this->source . 'suffix');
        $queries = array();
        $observe = function ($sql) use (&$queries) {
            if (strpos($sql, 'TRIM(LEADING') !== false) { $queries[] = $sql; }
            return $sql;
        };
        add_filter('query', $observe);
        try { $ids = array_map('intval', Variation::getRelatedVariation($this->source, true)); }
        finally { remove_filter('query', $observe); }
        self::assertEqualsCanonicalizing(array($this->source, $this->translated), $ids);
        self::assertCount(1, $queries);
        self::assertStringContainsString("meta_key = '_point_to_variation'", $queries[0]);
        self::assertStringContainsString('= CAST(' . $this->source . ' AS BINARY)', $queries[0]);
        self::assertStringContainsString($wpdb->postmeta, $queries[0]);
    }

    #[DataProvider('validIdForms')]
    public function testAuthorizedRemovalPreservesSiblingAndParentMetadata(string $form): void
    {
        $parent = wp_get_post_parent_id($this->source);
        update_post_meta($parent, '_cve_regression_marker', 'retained');
        $value = $form === 'integer' ? $this->source : ($form === 'padded' ? '000' . $this->source : (string) $this->source);
        $this->remove(array($value), wp_create_nonce('delete-variations'));
        self::assertNull(get_post($this->source));
        self::assertNull(get_post($this->translated));
        self::assertSame(array(), get_post_meta($this->source));
        self::assertSame(array(), get_post_meta($this->translated));
        self::assertNotNull(get_post($this->sibling));
        self::assertSame('15', wc_get_product($this->sibling)->get_regular_price());
        self::assertSame('retained', get_post_meta($parent, '_cve_regression_marker', true));
        $product = wc_get_product($parent);
        $product->set_name('Edited after variation removal'); $product->save();
        self::assertSame('Edited after variation removal', wc_get_product($parent)->get_name());
    }

    public static function validIdForms(): array
    {
        return array(array('integer'), array('string'), array('padded'));
    }

    #[DataProvider('httpRequests')]
    public function testRealAdminAjaxRequest(string $login, string $input, string $nonce, int $status, bool $deleted): void
    {
        $before = array();
        foreach ($this->ids as $id) { $before[$id] = array(get_post($id), get_post_meta($id)); }
        $user = $login === 'guest' ? 0 : get_user_by('login', $login)->ID;
        wp_set_current_user($user);
        $originalCookies = $_COOKIE;
        $token = $user ? WP_Session_Tokens::get_instance($user)->create(time() + 300) : '';
        $cookies = array();
        if ($user) {
            $cookies[AUTH_COOKIE] = wp_generate_auth_cookie($user, time() + 300, 'auth', $token);
            $cookies[LOGGED_IN_COOKIE] = wp_generate_auth_cookie($user, time() + 300, 'logged_in', $token);
            $_COOKIE[LOGGED_IN_COOKIE] = $cookies[LOGGED_IN_COOKIE];
        }
        $body = array('action' => 'woocommerce_remove_variations');
        if ($input !== 'missing') {
            $body['variation_ids'] = $input === 'valid' ? array($this->source)
                : ($input === 'scalar' ? (string) $this->source : array($this->source, 'invalid'));
        }
        if ($nonce !== 'missing') { $body['security'] = $nonce === 'valid' ? wp_create_nonce('delete-variations') : 'invalid'; }
        try {
            $response = wp_remote_post('http://127.0.0.1/wp-admin/admin-ajax.php', array(
                'body' => $body, 'cookies' => $cookies, 'timeout' => 30));
            self::assertFalse(is_wp_error($response), is_wp_error($response) ? $response->get_error_message() : '');
            self::assertSame($status, wp_remote_retrieve_response_code($response));
            foreach (array($this->source, $this->translated) as $id) {
                clean_post_cache($id);
                self::assertSame($deleted, get_post($id) === null);
                if ($deleted) { self::assertSame(array(), get_post_meta($id)); }
            }
            clean_post_cache($this->sibling);
            self::assertNotNull(get_post($this->sibling));
            if (!$deleted) {
                foreach ($before as $id => $snapshot) {
                    clean_post_cache($id);
                    self::assertEquals($snapshot, array(get_post($id), get_post_meta($id)));
                }
            }
        } finally {
            $_COOKIE = $originalCookies;
            if ($user) { WP_Session_Tokens::get_instance($user)->destroy($token); }
        }
    }

    public static function httpRequests(): array
    {
        return array(
            'guest' => array('guest', 'valid', 'valid', 400, false),
            'subscriber' => array('lab-subscriber', 'valid', 'valid', 403, false),
            'contributor' => array('lab-contributor', 'valid', 'valid', 403, false),
            'missing nonce' => array('lab-admin', 'valid', 'missing', 403, false),
            'invalid nonce' => array('lab-admin', 'valid', 'invalid', 403, false),
            'missing IDs' => array('lab-admin', 'missing', 'valid', 400, false),
            'scalar IDs' => array('lab-admin', 'scalar', 'valid', 400, false),
            'mixed IDs' => array('lab-admin', 'mixed', 'valid', 400, false),
            'administrator' => array('lab-admin', 'valid', 'valid', 200, true),
            'shop manager' => array('lab-manager', 'valid', 'valid', 200, true),
        );
    }

    public function testHttpRemovalSynchronizesBothParentsPriceLookupTables(): void
    {
        global $wpdb;
        $parents = array(wp_get_post_parent_id($this->source), wp_get_post_parent_id($this->translated));
        $translatedSibling = new WC_Product_Variation();
        $translatedSibling->set_parent_id($parents[1]);
        $translatedSibling->set_regular_price('15'); $translatedSibling->save();
        $this->ids[] = $translatedSibling->get_id();
        update_post_meta($this->sibling, Variation::DUPLICATE_KEY, $this->sibling);
        update_post_meta($translatedSibling->get_id(), Variation::DUPLICATE_KEY, $this->sibling);
        foreach ($parents as $parent) {
            WC_Product_Variable::sync($parent);
            wc_get_product($parent)->get_variation_prices();
            self::assertSame(10.0, (float) wc_get_product($parent)->get_variation_price('min'));
        }
        $this->testRealAdminAjaxRequest('lab-admin', 'valid', 'valid', 200, true);
        // The HTTP request invalidated transients in another PHP process.
        wp_cache_flush();
        foreach ($parents as $parent) {
            clean_post_cache($parent);
            $lookup = $wpdb->get_row($wpdb->prepare(
                "SELECT min_price, max_price FROM {$wpdb->wc_product_meta_lookup} WHERE product_id = %d", $parent), ARRAY_A);
            self::assertSame(15.0, (float) $lookup['min_price']);
            self::assertSame(15.0, (float) $lookup['max_price']);
            self::assertCount(1, wc_get_product($parent)->get_children());
            self::assertSame(15.0, (float) wc_get_product($parent)->get_variation_price('min'));
        }
    }

    public function testNativeWooCommerceClientSuppliesDeletionNonce(): void
    {
        $script = file_get_contents(WC_ABSPATH . 'assets/js/admin/meta-boxes-product-variation.js');
        self::assertMatchesRegularExpression('/action:\\s*[\'"]woocommerce_remove_variations[\'"]/', $script);
        self::assertMatchesRegularExpression('/data\\.security\\s*=\\s*woocommerce_admin_meta_boxes_variations\\.delete_variations_nonce/', $script);
        $localization = file_get_contents(WC_ABSPATH . 'includes/admin/class-wc-admin-assets.php');
        self::assertMatchesRegularExpression('/[\'"]delete_variations_nonce[\'"]\\s*=>\\s*wp_create_nonce\\(\\s*[\'"]delete-variations[\'"]/', $localization);
    }
    public function testSubscriberCannotDeleteEvenWithValidNonce(): void
    {
        $subscriber = get_user_by('login', 'lab-subscriber'); wp_set_current_user($subscriber->ID);
        $this->remove(array($this->source), wp_create_nonce('delete-variations'));
        self::assertNotNull(get_post($this->source)); self::assertNotNull(get_post($this->translated));
    }
    #[DataProvider('invalidNonces')]
    public function testAdministratorCannotDeleteWithoutValidNonce($nonce): void
    {
        $this->remove(array($this->source), $nonce);
        self::assertNotNull(get_post($this->source)); self::assertNotNull(get_post($this->translated));
    }
    public static function invalidNonces(): array { return array('missing' => array(null), 'invalid' => array('invalid')); }
    public function testMalformedIdDoesNotDeleteValidPrefixOrExecuteSql(): void
    {
        $this->remove(array($this->source . ' OR 1=1'), wp_create_nonce('delete-variations'));
        self::assertNotNull(get_post($this->source)); self::assertNotNull(get_post($this->translated));
        self::assertEmpty(Variation::getRelatedVariation($this->source . ' OR 1=1', true));
    }
    public function testValidBatchDoesNotPartiallyDeleteBeforeMalformedId(): void
    {
        $this->remove(array($this->source, array('nested')), wp_create_nonce('delete-variations'));
        self::assertNotNull(get_post($this->source)); self::assertNotNull(get_post($this->translated));
    }
    public function testOverflowingIdRejectsWholeRemovalBatch(): void
    {
        $this->remove(array($this->source, (string) PHP_INT_MAX . '0'), wp_create_nonce('delete-variations'));
        self::assertNotNull(get_post($this->source));
        self::assertNotNull(get_post($this->translated));
    }

    public function testShopManagerRemovalKeepsOriginalTranslationSynchronization(): void
    {
        $manager = get_user_by('login', 'lab-manager'); wp_set_current_user($manager->ID);
        $this->remove(array((string) $this->source), wp_create_nonce('delete-variations'));
        self::assertNull(get_post($this->source)); self::assertNull(get_post($this->translated));
        self::assertNotNull(get_post($this->unrelated));
    }
    public function testTranslatedVariationRemovalDeletesItsLinkedSource(): void
    {
        $this->remove(array($this->translated), wp_create_nonce('delete-variations'));
        self::assertNull(get_post($this->source));
        self::assertNull(get_post($this->translated));
        self::assertNotNull(get_post($this->unrelated));
    }

    public function testAdministratorRemovalKeepsOriginalTranslationSynchronization(): void
    {
        $this->remove(array($this->source), wp_create_nonce('delete-variations'));
        self::assertNull(get_post($this->source));
        self::assertNull(get_post($this->translated));
        self::assertNotNull(get_post($this->unrelated));
    }
    public function testCustomerCannotDeleteEvenWithValidNonce(): void
    {
        $customer = get_user_by('login', 'lab-customer');
        wp_set_current_user($customer->ID);
        $this->remove(array($this->source), wp_create_nonce('delete-variations'));
        self::assertNotNull(get_post($this->source));
        self::assertNotNull(get_post($this->translated));
    }
    public function testCorruptRelationshipCannotDeleteSimpleProduct(): void
    {
        update_post_meta($this->unrelated, Variation::DUPLICATE_KEY, $this->source);
        $this->remove(array($this->source), wp_create_nonce('delete-variations'));
        self::assertNotNull(get_post($this->source));
        self::assertNotNull(get_post($this->translated));
        self::assertNotNull(get_post($this->unrelated));
    }
    public function testZeroAndNonexistentIdsAreHarmless(): void
    {
        $this->remove(array(0, 999999999), wp_create_nonce('delete-variations'));
        self::assertNotNull(get_post($this->source)); self::assertNotNull(get_post($this->translated));
    }
    public function testLookupPreservesBothLanguages(): void
    {
        $ids = array_map('intval', Variation::getRelatedVariation($this->source, true)); sort($ids);
        $expected = array($this->source, $this->translated); sort($expected);
        self::assertSame($expected, $ids);
    }
    public function testContributorCannotDeleteEvenWithValidNonce(): void
    {
        $contributor = get_user_by('login', 'lab-contributor');
        wp_set_current_user($contributor->ID);
        $this->remove(array($this->source), wp_create_nonce('delete-variations'));
        self::assertNotNull(get_post($this->source));
        self::assertNotNull(get_post($this->translated));
    }

    #[DataProvider('invalidVariationIds')]
    public function testInvalidIdTypesCannotReachTheRelatedVariationQuery($value): void
    {
        global $wpdb;
        $queries = $wpdb->num_queries;
        self::assertFalse(Variation::getRelatedVariation($value, true));
        self::assertSame($queries, $wpdb->num_queries);
        $this->remove(array($this->source, $value), wp_create_nonce('delete-variations'));
        self::assertNotNull(get_post($this->source));
        self::assertNotNull(get_post($this->translated));
    }

    public static function invalidVariationIds(): array
    {
        return array(
            'sql expression' => array('1 OR 1=1'),
            'decimal string' => array('1.5'),
            'negative string' => array('-1'),
            'scientific notation' => array('1e2'),
            'empty string' => array(''),
            'nested array' => array(array('1')),
            'boolean' => array(true),
            'floating point' => array(1.5),
            'null' => array(null),
        );
    }

    #[DataProvider('deniedParents')]
    public function testRemovalRequiresPermissionForBothProductParents($language, $selected = 'en'): void
    {
        $manager = get_user_by('login', 'lab-manager');
        wp_set_current_user($manager->ID);
        $variation = $language === 'en' ? $this->source : $this->translated;
        $deniedParent = wp_get_post_parent_id($variation);
        $deny = function ($caps, $cap, $user, $args) use ($deniedParent) {
            if ($cap === 'edit_post' && isset($args[0]) && (int) $args[0] === $deniedParent) {
                return array('do_not_allow');
            }
            return $caps;
        };
        add_filter('map_meta_cap', $deny, 100, 4);
        try {
            self::assertTrue(current_user_can('edit_products'));
            $this->remove(array($selected === 'en' ? $this->source : $this->translated), wp_create_nonce('delete-variations'));
            self::assertNotNull(get_post($this->source));
            self::assertNotNull(get_post($this->translated));
        } finally {
            remove_filter('map_meta_cap', $deny, 100);
        }
    }

    public static function deniedParents(): array
    {
        return array('source' => array('en'), 'translation' => array('de'),
            'source from translated selection' => array('en', 'de'),
            'translation from translated selection' => array('de', 'de'));
    }

}
