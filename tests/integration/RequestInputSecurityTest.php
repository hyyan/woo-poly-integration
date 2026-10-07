<?php
use Hyyan\WPI\Language;
use Hyyan\WPI\Reports;
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
}
