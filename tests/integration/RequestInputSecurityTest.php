<?php
use Hyyan\WPI\Language;
use Hyyan\WPI\Reports;
use Hyyan\WPI\Utilities;
use Hyyan\WPI\Product\Product;
use Hyyan\WPI\Taxonomies\Categories;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class RequestInputSecurityTest extends TestCase
{
    private array $originalGet;
    private array $originalRequest;
    private array $originalPost;
    private int $originalUser;
    private $originalLanguage;
    private array $products = array();
    private array $categories = array();
    private array $requests = array();
    private $httpFilter;

    protected function setUp(): void
    {
        require_once ABSPATH.'wp-admin/includes/template.php';
        $this->originalGet = $_GET;
        $this->originalPost = $_POST;
        $this->originalRequest = $_REQUEST;
        $this->originalUser = get_current_user_id();
        $this->originalLanguage = PLL()->curlang;
        wp_set_current_user(1);
        $_GET = $_POST = $_REQUEST = array();
        $this->httpFilter = function ($response, $args, $url) {
            if (strpos($url, '/translation/plugin/woocommerce/') !== false) {
                $this->requests[] = $url;
                return new WP_Error('http_request_failed', 'Controlled transport failure');
            }
            return $response;
        };
        add_filter('pre_http_request', $this->httpFilter, 10, 3);
    }

    protected function tearDown(): void
    {
        remove_filter('pre_http_request', $this->httpFilter, 10);
        foreach ($this->products as $id) {
            wp_delete_post($id, true);
        }
        foreach ($this->categories as $id) {
            wp_delete_term($id, 'product_cat');
        }
        $_GET = $this->originalGet;
        $_POST = $this->originalPost;
        $_REQUEST = $this->originalRequest;
        wp_set_current_user($this->originalUser);
        PLL()->curlang = $this->originalLanguage;
    }

    private function handler(string $class)
    {
        return (new ReflectionClass($class))->newInstanceWithoutConstructor();
    }

    private function languageRequest(): array
    {
        return array('pll_action' => 'add', 'name' => 'Request test', 'locale' => 'zz_SECURITY', '_wpnonce_add-lang' => wp_create_nonce('add-lang'));
    }

    #[DataProvider('invalidLanguageRequests')]
    public function testInvalidLanguageRequestsDoNotStartDownloads(string $field, $value): void
    {
        $_REQUEST = $this->languageRequest();
        if ($value === null) {
            unset($_REQUEST[$field]);
        } else {
            $_REQUEST[$field] = $value;
        }
        self::assertFalse($this->handler(Language::class)->downlaodWhenPolylangAddLangauge());
        self::assertSame(array(), $this->requests);
    }

    public static function invalidLanguageRequests(): array
    {
        return array(
            'missing nonce' => array('_wpnonce_add-lang', null),
            'wrong nonce' => array('_wpnonce_add-lang', 'invalid'),
            'array nonce' => array('_wpnonce_add-lang', array('invalid')),
            'array action' => array('pll_action', array('add')),
            'missing name' => array('name', null),
            'array name' => array('name', array('name')),
            'missing locale' => array('locale', null),
            'array locale' => array('locale', array('de_DE')),
            'path locale' => array('locale', '../../woopoly-request-test'),
        );
    }

    #[DataProvider('deniedRoles')]
    public function testUnprivilegedLanguageRequestsDoNotStartDownloads(string $login): void
    {
        wp_set_current_user(get_user_by('login', $login)->ID);
        $_REQUEST = $this->languageRequest();
        self::assertFalse($this->handler(Language::class)->downlaodWhenPolylangAddLangauge());
        self::assertSame(array(), $this->requests);
    }

    public static function deniedRoles(): array
    {
        return array(array('lab-subscriber'), array('lab-customer'), array('lab-manager'));
    }

    public function testAuthorizedLanguageRequestPreservesTransportAndEscapesDisplayedError(): void
    {
        $_REQUEST = $this->languageRequest();
        $_REQUEST['name'] = '<script>window.woopolyRequestMarker=true</script>Request name';
        $original = $GLOBALS['wp_settings_errors'] ?? array();
        try {
            self::assertFalse($this->handler(Language::class)->downlaodWhenPolylangAddLangauge());
            self::assertCount(1, $this->requests);
            $errors = get_settings_errors('general');
            $message = end($errors)['message'];
            self::assertStringNotContainsString('<script>', $message);
            self::assertStringContainsString('Request name', $message);
            self::assertStringContainsString('<a href=', $message);
        } finally {
            $GLOBALS['wp_settings_errors'] = $original;
        }
    }

    public function testTranslatedDownloadErrorsPreserveLinksWithoutScripts(): void
    {
        $_REQUEST = $this->languageRequest();
        $filter = function ($translated, $text, $domain) {
            return $domain === 'woo-poly-integration' && strpos($text, 'WooCommerce translation %s') === 0
                ? '<script>window.woopolyErrorMarker=true</script><a href="%2$s">Translation help</a> %s'
                : $translated;
        };
        $original = $GLOBALS['wp_settings_errors'] ?? array();
        add_filter('gettext', $filter, 10, 3);
        try {
            self::assertFalse($this->handler(Language::class)->downlaodWhenPolylangAddLangauge());
            $errors = get_settings_errors('general');
            $message = end($errors)['message'];
            self::assertStringNotContainsString('<script>', $message);
            self::assertStringContainsString('Translation help</a>', $message);
        } finally {
            remove_filter('gettext', $filter, 10);
            $GLOBALS['wp_settings_errors'] = $original;
        }
    }

    #[DataProvider('editorFields')]
    public function testEditorDefaultsRespectSourceReadPermission(string $method, string $field): void
    {
        $source = wp_insert_post(array('post_type' => 'post', 'post_status' => 'private', 'post_title' => 'Private title', 'post_content' => 'Private content', 'post_excerpt' => 'Private excerpt', 'post_author' => 1));
        $this->products[] = $source;
        $_GET['from_post'] = (string) $source;
        $handler = $this->handler(Product::class);
        self::assertSame(get_post($source)->$field, $handler->$method('Default'));
        wp_set_current_user(get_user_by('login', 'lab-contributor')->ID);
        self::assertFalse(current_user_can('read_post', $source));
        self::assertSame('Default', $handler->$method('Default'));
    }

    public static function editorFields(): array
    {
        return array(array('wpi_editor_title', 'post_title'), array('wpi_editor_content', 'post_content'), array('wpi_editor_excerpt', 'post_excerpt'));
    }

    #[DataProvider('productEditorCreationRequests')]
    public function testProductEditorDefaultsRequireCreationNonce(string $method, string $field, string $nonce): void
    {
        $source = wp_insert_post(array('post_type' => 'product', 'post_status' => 'private',
            'post_title' => 'Product copy title', 'post_content' => 'Product copy content',
            'post_excerpt' => 'Product copy excerpt', 'post_author' => 1));
        $this->products[] = $source;
        $_GET = array('from_post' => ($nonce === 'padded' ? '000' : '') . $source, 'new_lang' => 'de');
        if ($nonce === 'valid' || $nonce === 'padded') {
            $_GET['_wpnonce'] = wp_create_nonce('new-post-translation');
        } elseif ($nonce === 'array') {
            $_GET['_wpnonce'] = array('invalid');
        } elseif ($nonce === 'wrong') {
            $_GET['_wpnonce'] = wp_create_nonce('unrelated-action');
        }
        $expected = $nonce === 'valid' || $nonce === 'padded' ? get_post($source)->$field : 'Default';
        self::assertSame($expected, $this->handler(Product::class)->$method('Default'));
    }

    public static function productEditorCreationRequests(): array
    {
        $cases = array();
        foreach (self::editorFields() as $editor) {
            foreach (array('missing', 'wrong', 'array', 'valid', 'padded') as $nonce) {
                $cases[$editor[0] . ' ' . $nonce] = array($editor[0], $editor[1], $nonce);
            }
        }
        return $cases;
    }

    #[DataProvider('productCreationLanguageNonces')]
    public function testProductCreationLanguageRequiresNonce(string $nonce, string $expected): void
    {
        $source = wp_insert_post(array('post_type' => 'product', 'post_status' => 'private',
            'post_title' => 'Language nonce source', 'post_author' => 1));
        $this->products[] = $source;
        $_GET = array('from_post' => (string) $source, 'new_lang' => 'de');
        if ($nonce === 'valid') {
            $_GET['_wpnonce'] = wp_create_nonce('new-post-translation');
        } elseif ($nonce === 'array') {
            $_GET['_wpnonce'] = array('invalid');
        } elseif ($nonce === 'wrong') {
            $_GET['_wpnonce'] = wp_create_nonce('unrelated-action');
        }
        self::assertSame($expected, $this->handler(Product::class)->getProductAttributesInLanguage(array())['lang']);
    }

    public static function productCreationLanguageNonces(): array
    {
        return array(array('missing', ''), array('wrong', ''), array('array', ''), array('valid', 'de'));
    }

    #[DataProvider('reportLanguageRequests')]
    public function testSharedLanguageGetterRecognizesOnlyTheNativeReportAllSelector(array $request, string $key, string $expected): void
    {
        $_GET = $request;
        self::assertSame($expected, Utilities::getRequestLanguage($key));
    }

    public static function reportLanguageRequests(): array
    {
        return array(
            'native all' => array(array('lang' => 'all'), 'lang', 'all'),
            'uppercase all' => array(array('lang' => 'ALL'), 'lang', 'all'),
            'spaced all' => array(array('lang' => ' all '), 'lang', 'all'),
            'readonly source context' => array(array('lang' => 'all', 'from_post' => '12', '_wpnonce' => 'wrong'), 'lang', 'all'),
            'creation rejects all' => array(array('new_lang' => 'all'), 'new_lang', ''),
            'array rejected' => array(array('lang' => array('all')), 'lang', ''),
            'unknown rejected' => array(array('lang' => 'unknown-language'), 'lang', ''),
            'registered retained' => array(array('lang' => 'de'), 'lang', 'de'),
            'absent retained' => array(array(), 'lang', ''),
        );
    }

    #[DataProvider('reportCombineSelectors')]
    public function testReportCombineSelectorsPreserveBothLanguageContexts($current, array $request, bool $expected): void
    {
        PLL()->curlang = $current ? PLL()->model->get_language($current) : false;
        $_GET = $request;
        self::assertSame($expected, Reports::isCombine());
    }

    public static function reportCombineSelectors(): array
    {
        $cases = array();
        foreach (array('de', false) as $current) {
            foreach (array(array(array(), false), array(array('lang' => array('all')), false), array(array('lang' => 'de'), false), array(array('lang' => 'all'), true), array(array('lang' => 'ALL'), true), array(array('lang' => ' all '), true), array(array('lang' => 'unknown-language'), false)) as $case) {
                $cases[] = array($current, $case[0], !$current || $case[1]);
            }
        }
        return $cases;
    }

    #[DataProvider('readOnlyLanguageRequests')]
    public function testReadOnlyLanguageSelectionDoesNotRequireCreationNonce(array $request, string $key): void
    {
        $_GET = $request;
        self::assertSame('de', Utilities::getRequestLanguage($key));
    }

    public static function readOnlyLanguageRequests(): array
    {
        return array(
            'blank editor' => array(array('new_lang' => 'de'), 'new_lang'),
            'term copy' => array(array('new_lang' => 'de', 'from_tag' => '12'), 'new_lang'),
            'report' => array(array('lang' => 'de', 'from_post' => '12', '_wpnonce' => 'unrelated'), 'lang'),
        );
    }

    #[DataProvider('languageFallbackRequests')]
    public function testLanguageGetterUsesFallbackOnlyForMissingInput(array $request, $fallback, $expected): void
    {
        $_GET = $request;
        self::assertSame($expected, Utilities::getRequestLanguage('new_lang', $fallback));
    }

    public static function languageFallbackRequests(): array
    {
        return array(
            'missing' => array(array(), 'en', 'en'),
            'null' => array(array('new_lang' => null), 'en', 'en'),
            'false native fallback' => array(array(), false, false),
            'array input' => array(array('new_lang' => array('de')), 'en', ''),
            'unknown language' => array(array('new_lang' => 'unknown-language'), 'en', ''),
            'registered language' => array(array('new_lang' => 'de'), 'en', 'de'),
        );
    }

    public function testAttributeLanguageFallbackRetainsPostAndPreferredLanguageObjects(): void
    {
        $postBefore = $GLOBALS['post'] ?? null;
        $source = wp_insert_post(array('post_type' => 'product', 'post_status' => 'publish', 'post_title' => 'Attribute language fallback'));
        $this->products[] = $source;
        pll_set_post_language($source, 'en');
        $handler = $this->handler(Product::class);
        try {
            $GLOBALS['post'] = get_post($source);
            $_GET = array();
            self::assertSame(array('hide_empty' => false, 'lang' => 'en'), $handler->getProductAttributesInLanguage(array('hide_empty' => false)));
            $_GET = array('new_lang' => null);
            self::assertSame('en', $handler->getProductAttributesInLanguage(array())['lang']);
            $_GET = array('new_lang' => 'unknown-language');
            self::assertSame('', $handler->getProductAttributesInLanguage(array())['lang']);
            $GLOBALS['post'] = null;
            $_GET = array();
            self::assertSame(PLL()->pref_lang, $handler->getProductAttributesInLanguage(array())['lang']);
        } finally {
            $GLOBALS['post'] = $postBefore;
        }
    }

    public function testProductLanguageRejectsArrayAndNormalizesSlashedText(): void
    {
        $handler = $this->handler(Product::class);
        $_GET['new_lang'] = array('de');
        self::assertSame('', $handler->getProductAttributesInLanguage(array())['lang']);
        $_GET['new_lang'] = wp_slash('<b>de</b>');
        self::assertSame('de', $handler->getProductAttributesInLanguage(array())['lang']);
    }

    public function testReportRouteRejectsArrayInput(): void
    {
        $_GET = array('tab' => array('orders'), 'report' => array('sales_by_product'));
        $reports = new Reports();
        try {
            self::assertFalse((new ReflectionProperty(Reports::class, 'tab'))->getValue($reports));
            self::assertFalse((new ReflectionProperty(Reports::class, 'report'))->getValue($reports));
        } finally {
            remove_filter('woocommerce_reports_get_order_report_data', array($reports, 'combineProductsByLanguage'));
            remove_filter('woocommerce_reports_get_order_report_query', array($reports, 'filterProductByLanguage'));
            foreach (array('woocommerce_report_most_stocked_query_from', 'woocommerce_report_out_of_stock_query_from', 'woocommerce_report_low_in_stock_query_from') as $hook) {
                remove_filter($hook, array($reports, 'filterStockByLanguage'));
            }
            remove_action('admin_init', array($reports, 'translateProductIDS'));
            remove_action('admin_init', array($reports, 'translateCategoryIDS'));
            remove_filter('woocommerce_report_sales_by_category_get_products_in_category', array($reports, 'addProductsInCategoryTranslations'));
        }
    }

    public function testReportIdsPreserveTranslationsAndRemoveInvalidShapes(): void
    {
        foreach (array('en', 'de') as $language) {
            $product = new WC_Product_Simple();
            $product->set_name('Report request '.$language);
            $product->save();
            $this->products[$language] = $product->get_id();
            pll_set_post_language($product->get_id(), $language);
            $term = wp_insert_term('Report request '.$language, 'product_cat');
            $this->categories[$language] = $term['term_id'];
            pll_set_term_language($term['term_id'], $language);
        }
        pll_save_post_translations($this->products);
        pll_save_term_translations($this->categories);
        $reports = $this->handler(Reports::class);
        PLL()->curlang = false;
        $_GET['product_ids'] = array((string) $this->products['en'], array($this->products['en']), '1 OR 1', '');
        $reports->translateProductIDS();
        self::assertEqualsCanonicalizing(array_values($this->products), array_values($_GET['product_ids']));
        PLL()->curlang = PLL()->model->get_language('de');
        $_GET = array('lang' => 'de', 'show_categories' => array((string) $this->categories['en'], array(1), '1 OR 1'));
        $reports->translateCategoryIDS();
        self::assertSame(array($this->categories['de']), $_GET['show_categories']);
        $_GET = array('lang' => 'd e', 'show_categories' => array((string) $this->categories['en']),
            'product_ids' => array((string) $this->products['en']));
        $reports->translateCategoryIDS();
        $reports->translateProductIDS();
        self::assertSame(array($this->categories['en']), $_GET['show_categories']);
        self::assertSame(array($this->products['en']), $_GET['product_ids']);
    }

    public function testReportCombiningRetainsEverySelectedProduct(): void
    {
        foreach (array('en', 'de') as $language) {
            $product = new WC_Product_Simple();
            $product->set_name('Combined request '.$language);
            $product->save();
            $this->products[$language] = $product->get_id();
            pll_set_post_language($product->get_id(), $language);
        }
        pll_save_post_translations($this->products);
        $other = new WC_Product_Simple();
        $other->set_name('Other selected product');
        $other->save();
        pll_set_post_language($other->get_id(), 'en');
        $this->products['other'] = $other->get_id();
        PLL()->curlang = false;
        $_GET['product_ids'] = array($this->products['en'], $other->get_id());
        $this->handler(Reports::class)->translateProductIDS();
        self::assertEqualsCanonicalizing(array_values($this->products), array_values($_GET['product_ids']));
    }

    public function testCategorySaveIgnoresMalformedFieldsAndPreservesNormalSynchronization(): void
    {
        foreach (array('en', 'de') as $language) {
            $term = wp_insert_term('Save request '.$language, 'product_cat');
            $this->categories[$language] = $term['term_id'];
            pll_set_term_language($term['term_id'], $language);
            update_term_meta($term['term_id'], 'display_type', 'products');
            update_term_meta($term['term_id'], 'thumbnail_id', 5);
        }
        pll_save_term_translations($this->categories);
        $handler = $this->handler(Categories::class);
        $_POST = array('display_type' => array('both'), 'product_cat_thumbnail_id' => array('9'), '_wpnonce' => wp_create_nonce('update-tag_'.$this->categories['en']));
        $handler->syncProductCatCustomFields($this->categories['en'], '', 'product_cat');
        foreach ($this->categories as $id) {
            self::assertSame('products', get_term_meta($id, 'display_type', true));
            self::assertSame('5', get_term_meta($id, 'thumbnail_id', true));
        }
        $_POST = array('display_type' => 'both', 'product_cat_thumbnail_id' => '9', '_wpnonce' => wp_create_nonce('update-tag_'.$this->categories['en']));
        $handler->syncProductCatCustomFields($this->categories['en'], '', 'product_cat');
        foreach ($this->categories as $id) {
            self::assertSame('both', get_term_meta($id, 'display_type', true));
            self::assertSame('9', get_term_meta($id, 'thumbnail_id', true));
        }
        $_POST = array('display_type' => '', 'product_cat_thumbnail_id' => '', '_wpnonce_add-tag' => wp_create_nonce('add-tag'));
        $handler->syncProductCatCustomFields($this->categories['en'], '', 'product_cat');
        foreach ($this->categories as $id) {
            self::assertSame('', get_term_meta($id, 'display_type', true));
            self::assertSame('0', get_term_meta($id, 'thumbnail_id', true));
        }
    }

    #[DataProvider('categoryRequestCases')]
    public function testCategorySynchronizationRequiresNativeNonceAndTermPermission(string $login, string $nonceCase): void
    {
        foreach (array('en', 'de') as $language) {
            $term = wp_insert_term('Category authorization '.$language, 'product_cat');
            $this->categories[$language] = $term['term_id'];
            pll_set_term_language($term['term_id'], $language);
            update_term_meta($term['term_id'], 'display_type', 'products');
            update_term_meta($term['term_id'], 'thumbnail_id', 5);
        }
        pll_save_term_translations($this->categories);
        wp_set_current_user(get_user_by('login', $login)->ID);
        $_POST = array('display_type' => 'both', 'product_cat_thumbnail_id' => '9');
        if ($nonceCase === 'valid') {
            $_POST['_wpnonce'] = wp_create_nonce('update-tag_'.$this->categories['en']);
        } elseif ($nonceCase === 'wrong term') {
            $_POST['_wpnonce'] = wp_create_nonce('update-tag_'.$this->categories['de']);
        } elseif ($nonceCase === 'array') {
            $_POST['_wpnonce'] = array('invalid');
        } elseif ($nonceCase === 'invalid') {
            $_POST['_wpnonce'] = 'invalid';
        }
        $hookCalls = 0;
        $observer = function () use (&$hookCalls) { ++$hookCalls; };
        add_action(\Hyyan\WPI\HooksInterface::PRODUCT_SYNC_CATEGORY_CUSTOM_FIELDS, $observer);
        try {
            $this->handler(Categories::class)->syncProductCatCustomFields($this->categories['en'], '', 'product_cat');
            $allowed = $nonceCase === 'valid' && in_array($login, array('lab-admin', 'lab-manager'), true);
            foreach ($this->categories as $id) {
                self::assertSame($allowed ? 'both' : 'products', get_term_meta($id, 'display_type', true));
                self::assertSame($allowed ? '9' : '5', get_term_meta($id, 'thumbnail_id', true));
            }
            self::assertSame(1, $hookCalls);
        } finally {
            remove_action(\Hyyan\WPI\HooksInterface::PRODUCT_SYNC_CATEGORY_CUSTOM_FIELDS, $observer);
        }
    }

    public static function categoryRequestCases(): array
    {
        return array(
            'missing nonce' => array('lab-admin', 'missing'),
            'invalid nonce' => array('lab-admin', 'invalid'),
            'array nonce' => array('lab-admin', 'array'),
            'other term nonce' => array('lab-admin', 'wrong term'),
            'subscriber with valid nonce' => array('lab-subscriber', 'valid'),
            'customer with valid nonce' => array('lab-customer', 'valid'),
            'administrator' => array('lab-admin', 'valid'),
            'shop manager' => array('lab-manager', 'valid'),
        );
    }

    public function testCombinedCategoryReportNormalizesIdsBeforeNativeReportReadsThem(): void
    {
        PLL()->curlang = false;
        $_GET = array('show_categories' => array('7', array('8'), '9 OR 1', -3, ''));
        $this->handler(Reports::class)->translateCategoryIDS();
        self::assertSame(array(7, 3), array_values($_GET['show_categories']));
    }

    public function testCategoryCopyRejectsArraySourceWithoutOutput(): void
    {
        $_GET = array('from_tag' => array('1'), 'new_lang' => 'de');
        $level = ob_get_level();
        ob_start();
        try {
            $result = $this->handler(Categories::class)->copyProductCatCustomFields();
            self::assertFalse($result);
            self::assertSame('', ob_get_contents());
        } finally {
            while (ob_get_level() > $level) {
                ob_end_clean();
            }
        }
    }

    #[DataProvider('attributeLanguageQueries')]
    public function testAttributeQueryAcceptsOnlyRegisteredRequestLanguages($language, string $expected): void
    {
        $_GET = array('new_lang' => $language);
        $args = array('taxonomy' => 'pa_size', 'hide_empty' => false);
        $result = $this->handler(Product::class)->getProductAttributesInLanguage($args);
        self::assertSame($expected, $result['lang']);
        unset($result['lang']);
        self::assertSame($args, $result);
    }

    public static function attributeLanguageQueries(): array
    {
        return array(
            'English' => array('en', 'en'),
            'German' => array('de', 'de'),
            'sanitized existing language' => array('<b>de</b>', 'de'),
            'unregistered language' => array('missing-language', ''),
            'array' => array(array('de'), ''),
            'boolean' => array(true, ''),
        );
    }

    public function testUnregisteredCategoryLanguageCannotRenderOrDispatchCopy(): void
    {
        $term = wp_insert_term('Category language request fixture', 'product_cat');
        self::assertFalse(is_wp_error($term));
        $this->categories['en'] = $term['term_id'];
        $_GET = array('new_lang' => 'missing-language', 'from_tag' => (string) $term['term_id']);
        $calls = 0;
        $observer = static function () use (&$calls) { ++$calls; };
        add_action(Hyyan\WPI\HooksInterface::PRODUCT_COPY_CATEGORY_CUSTOM_FIELDS, $observer);
        ob_start();
        try {
            $result = $this->handler(Categories::class)->copyProductCatCustomFields();
            self::assertFalse($result);
            self::assertSame('', ob_get_contents());
            self::assertSame(0, $calls);
        } finally {
            ob_end_clean();
            remove_action(Hyyan\WPI\HooksInterface::PRODUCT_COPY_CATEGORY_CUSTOM_FIELDS, $observer);
        }
    }

}
