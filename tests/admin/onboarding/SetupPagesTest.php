<?php

/**
 * This file is part of the hyyan/woo-poly-integration plugin.
 * (c) Hyyan Abo Fakher <hyyanaf@gmail.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Hyyan\WPI\Admin\Setup;
use Hyyan\WPI\Admin\SetupPages;
use Hyyan\WPI\Admin\SetupTask;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Exercise onboarding against real WordPress pages and Polylang relationships.
 *
 * @author Hyyan Abo Fakher <hyyanaf@gmail.com>
 */
final class SetupPagesTest extends TestCase
{
    private array $options = array();
    private array $ids = array();
    private int $user;
    private $language;
    private array $get;

    /** {@inheritdoc} */
    protected function setUp(): void
    {
        $this->user = get_current_user_id();
        $this->language = PLL()->curlang;
        $this->get = $_GET;
        wp_set_current_user(1);
        PLL()->curlang = PLL()->model->get_language('en');
        foreach (array_keys(SetupPages::getPageTypes()) as $type) {
            $key = 'woocommerce_' . $type . '_page_id';
            $this->options[$key] = get_option($key);
            $id = $this->createPage('en', '<!-- wp:shortcode -->[woocommerce_' . $type . ']<!-- /wp:shortcode -->');
            update_option($key, $id);
        }
        $this->options['wpi_wcpagecheck_passed'] = get_option('wpi_wcpagecheck_passed');
    }

    /** {@inheritdoc} */
    protected function tearDown(): void
    {
        wp_set_current_user(1);
        foreach (SetupPages::getPageStatus() as $row) {
            foreach ($row['cells'] as $cell) {
                if ($cell['id'] && !in_array($cell['id'], $this->ids, true)) {
                    $this->ids[] = $cell['id'];
                }
            }
        }
        foreach (array_reverse($this->ids) as $id) {
            wp_delete_post($id, true);
        }
        foreach ($this->options as $name => $value) {
            if ($value === false) {
                delete_option($name);
            } else {
                update_option($name, $value);
            }
        }
        $_GET = $this->get;
        PLL()->curlang = $this->language;
        wp_set_current_user($this->user);
    }

    /**
     * Create one isolated page fixture.
     *
     * @param string $language language slug
     * @param string $content stored page content
     * @param string $status publication status
     * @return int fixture page ID
     */
    private function createPage(string $language, string $content = '', string $status = 'publish'): int
    {
        $id = wp_insert_post(wp_slash(array('post_type' => 'page', 'post_status' => $status,
            'post_title' => 'Onboarding fixture ' . wp_generate_password(6, false), 'post_content' => $content)), true);
        self::assertIsInt($id);
        $this->ids[] = $id;
        if ($language !== '') {
            pll_set_post_language($id, $language);
        }
        return $id;
    }

    /**
     * Verify that inspecting and rendering the checklist does not create pages.
     *
     * @return void
     */
    public function testChecklistIsReadOnly(): void
    {
        global $wpdb;
        $before = $wpdb->get_results("SELECT ID, post_content, post_status FROM {$wpdb->posts} WHERE post_type = 'page' ORDER BY ID", ARRAY_A);
        $language = PLL()->curlang;
        $rows = SetupPages::getPageStatus();
        self::assertSame('missing', $rows['cart']['cells']['de']['status']);
        $_GET = array('page' => 'hyyan-wpi');
        ob_start();
        try {
            Setup::outputPage();
            $html = ob_get_contents();
        } finally {
            ob_end_clean();
        }
        self::assertStringContainsString('Create missing pages and translations', $html);
        self::assertStringContainsString('Use an existing page', $html);
        self::assertStringContainsString('value="2" max="3"', $html);
        self::assertFalse(Setup::isReady());
        // A second render represents returning after leaving or closing setup.
        ob_start();
        try {
            Setup::outputPage();
            self::assertStringContainsString('value="2" max="3"', ob_get_contents());
        } finally {
            ob_end_clean();
        }
        self::assertFalse(Setup::isReady());
        self::assertSame($language, PLL()->curlang);
        self::assertEquals($before, $wpdb->get_results("SELECT ID, post_content, post_status FROM {$wpdb->posts} WHERE post_type = 'page' ORDER BY ID", ARRAY_A));
    }

    /**
     * Opening setup or recording a native task action cannot finish missing pages.
     *
     * @return void
     */
    public function testNativeTaskRequiresCurrentConfigurationRatherThanInteractionFlags(): void
    {
        $task = new SetupTask();
        foreach (array(SetupTask::ACTIONED_OPTION, SetupTask::DISMISSED_OPTION, SetupTask::COMPLETED_OPTION) as $option) {
            $this->options[$option] = get_option($option);
            update_option($option, array($task->get_id()));
        }
        ob_start();
        try {
            Setup::outputPage();
        } finally {
            ob_end_clean();
        }
        self::assertFalse($task->is_complete());
        self::assertFalse($task->is_dismissable());
        self::assertFalse($task->dismiss());
        self::assertFalse(Setup::isReady());

        self::assertIsArray(SetupPages::createMissingPages());
        self::assertTrue($task->is_complete());

        $translation = pll_get_post(SetupPages::getDefaultPage('checkout')->ID, 'de');
        wp_update_post(array('ID' => $translation, 'post_status' => 'draft'));
        self::assertFalse($task->is_complete());
        self::assertFalse(Setup::isReady());
    }

    /**
     * Offer only unassigned pages and pages in the selected language, escaping titles.
     *
     * @return void
     */
    public function testExistingPageChoicesRespectLanguageAndEscapeTitles(): void
    {
        $german = $this->createPage('de');
        $english = $this->createPage('en');
        $title = '<img src=x onerror="alert(1)"> & choose';
        wp_update_post(wp_slash(array('ID' => $german, 'post_title' => $title)));
        $choices = wp_list_pluck(Setup::getExistingPages('de'), 'ID');
        self::assertContains($german, $choices);
        self::assertNotContains($english, $choices);
        ob_start();
        try {
            Setup::outputPage();
            $html = ob_get_contents();
        } finally {
            ob_end_clean();
        }
        self::assertStringContainsString(esc_html($title), $html);
        self::assertStringNotContainsString($title, $html);
    }

    /**
     * Create missing originals in the default language regardless of admin filters.
     *
     * @return void
     */
    public function testNativeInstallerUsesDefaultLanguageAndRestoresAdminLanguage(): void
    {
        foreach (array_keys(SetupPages::getPageTypes()) as $type) {
            update_option('woocommerce_' . $type . '_page_id', 0);
        }
        $suffix = wp_generate_password(12, false);
        $filter = static function ($pages) use ($suffix) {
            foreach ($pages as $type => &$page) {
                $page['name'] = 'woopoly-fixture-' . $type . '-' . $suffix;
                $page['content'] .= '<!-- ' . $suffix . '-' . $type . ' -->';
            }
            return $pages;
        };
        add_filter('woocommerce_create_pages', $filter);
        PLL()->curlang = PLL()->model->get_language('de');
        try {
            self::assertIsArray(SetupPages::createMissingPages());
            self::assertSame('de', PLL()->curlang->slug);
            foreach (SetupPages::getPageStatus() as $row) {
                self::assertSame('en', pll_get_post_language($row['base']->ID));
                self::assertSame('publish', $row['cells']['de']['status']);
                self::assertStringContainsString($suffix, $row['base']->post_content);
            }
        } finally {
            remove_filter('woocommerce_create_pages', $filter);
        }
    }

    /**
     * Exercise the registered admin-post route with real authentication and nonces.
     *
     * @param string $login local fixture account or guest
     * @param string $nonce nonce fixture type
     * @param int $status expected HTTP response
     * @return void
     */
    #[DataProvider('pageRequests')]
    public function testRealPageSetupRequest(string $login, string $nonce, int $status): void
    {
        global $wpdb;
        $before = $wpdb->get_results("SELECT ID, post_content, post_status FROM {$wpdb->posts} WHERE post_type = 'page' ORDER BY ID", ARRAY_A);
        $user = $login === 'guest' ? 0 : get_user_by('login', $login)->ID;
        wp_set_current_user($user);
        $originalCookies = $_COOKIE;
        $token = $user ? WP_Session_Tokens::get_instance($user)->create(time() + 300) : '';
        $cookies = array();
        $body = array('action' => 'woopoly_setup_pages', 'operation' => 'create');
        if ($user) {
            $cookies[AUTH_COOKIE] = wp_generate_auth_cookie($user, time() + 300, 'auth', $token);
            $cookies[LOGGED_IN_COOKIE] = wp_generate_auth_cookie($user, time() + 300, 'logged_in', $token);
            $_COOKIE[LOGGED_IN_COOKIE] = $cookies[LOGGED_IN_COOKIE];
        }
        if ($nonce !== 'missing') {
            $body['_wpnonce'] = $nonce === 'valid' ? wp_create_nonce('woopoly_setup_pages') : 'invalid';
        }
        try {
            $response = wp_remote_post('http://127.0.0.1/wp-admin/admin-post.php', array(
                'body' => $body, 'cookies' => $cookies, 'timeout' => 30, 'redirection' => 0));
            self::assertFalse(is_wp_error($response));
            self::assertSame($status, wp_remote_retrieve_response_code($response));
            wp_cache_flush();
            if ($status === 302) {
                self::assertStringContainsString('page=hyyan-wpi#store-pages', wp_remote_retrieve_header($response, 'location'));
                self::assertTrue(Setup::isReady());
            } else {
                self::assertEquals($before, $wpdb->get_results("SELECT ID, post_content, post_status FROM {$wpdb->posts} WHERE post_type = 'page' ORDER BY ID", ARRAY_A));
                self::assertFalse(Setup::isReady());
            }
        } finally {
            $_COOKIE = $originalCookies;
            if ($user) {
                WP_Session_Tokens::get_instance($user)->destroy($token);
                delete_transient('wpi_setup_result_' . $user);
            }
        }
    }

    /**
     * Get authentication and nonce scenarios for the real page-creation route.
     *
     * @return array request fixtures
     */
    public static function pageRequests(): array
    {
        return array(
            'guest' => array('guest', 'missing', 400),
            'subscriber' => array('lab-subscriber', 'valid', 403),
            'manager' => array('lab-manager', 'valid', 403),
            'missing nonce' => array('lab-admin', 'missing', 403),
            'invalid nonce' => array('lab-admin', 'invalid', 403),
            'authorized' => array('lab-admin', 'valid', 302),
        );
    }

    /**
     * Preserve custom content and linked drafts, and make repeated creation safe.
     *
     * @return void
     */
    public function testCreationPreservesExistingTranslationsAndIsRepeatable(): void
    {
        $rows = SetupPages::getPageStatus();
        $source = $rows['cart']['base']->ID;
        $content = '<!-- wp:woocommerce/cart {"className":"quoted \\"label\\""} --><div>Custom \\ text</div><!-- /wp:woocommerce/cart -->';
        wp_update_post(wp_slash(array('ID' => $source, 'post_content' => $content)));
        $checkout = $rows['checkout']['base']->ID;
        $draft = $this->createPage('de', 'Keep this custom checkout', 'draft');
        pll_save_post_translations(array('en' => $checkout, 'de' => $draft));
        $result = SetupPages::createMissingPages();
        self::assertIsArray($result);
        self::assertSame(3, $result['created']);
        $translated = pll_get_post($source, 'de');
        self::assertSame($content, get_post($source)->post_content);
        self::assertSame($content, get_post($translated)->post_content);
        self::assertSame('de', pll_get_post_language($translated));
        self::assertSame('draft', get_post_status($draft));
        self::assertSame('Keep this custom checkout', get_post($draft)->post_content);
        self::assertSame($draft, pll_get_post($checkout, 'de'));
        self::assertSame(0, SetupPages::createMissingPages()['created']);
        self::assertSame($translated, pll_get_post($source, 'de'));
        self::assertFalse(SetupPages::isReady());
    }

    /**
     * Verify every page group remains independent after creation.
     *
     * @return void
     */
    public function testCreationLinksEveryLanguageToTheCorrectStoreFunction(): void
    {
        self::assertSame(4, SetupPages::createMissingPages()['created']);
        self::assertTrue(Setup::isReady());
        $ids = array();
        foreach (SetupPages::getPageStatus() as $row) {
            foreach ($row['cells'] as $slug => $cell) {
                self::assertSame('publish', $cell['status']);
                self::assertSame($slug, pll_get_post_language($cell['id']));
                $ids[] = $cell['id'];
            }
        }
        self::assertCount(8, array_unique($ids));
    }

    /**
     * Assign an existing translation and refuse to steal it for another function.
     *
     * @return void
     */
    public function testExistingPageAssignmentPreservesOtherRelationships(): void
    {
        $id = $this->createPage('de', 'A designed cart page');
        self::assertTrue(SetupPages::assignPage('cart', 'de', $id));
        self::assertSame('A designed cart page', get_post($id)->post_content);
        self::assertSame($id, pll_get_post(SetupPages::getDefaultPage('cart')->ID, 'de'));
        self::assertSame('page_in_use', SetupPages::assignPage('checkout', 'de', $id)->get_error_code());
        self::assertSame('wrong_language', SetupPages::assignPage('cart', 'en', $id)->get_error_code());
    }

    /**
     * Prevent reassigning a page already in another product-independent page group.
     *
     * @return void
     */
    public function testExistingTranslationGroupCannotBeStolen(): void
    {
        $source = $this->createPage('en');
        $translation = $this->createPage('de');
        pll_save_post_translations(array('en' => $source, 'de' => $translation));
        self::assertSame('already_linked', SetupPages::assignPage('cart', 'de', $translation)->get_error_code());
        self::assertSame($source, pll_get_post($translation, 'en'));
    }

    /**
     * Deny page changes for non-administrative WordPress roles.
     *
     * @param string $login local fixture account
     * @return void
     */
    #[DataProvider('restrictedUsers')]
    public function testRestrictedUsersCannotChangePages(string $login): void
    {
        $id = $this->createPage('de', 'Unchanged');
        wp_set_current_user(get_user_by('login', $login)->ID);
        self::assertSame('forbidden', SetupPages::createMissingPages()->get_error_code());
        self::assertSame('forbidden', SetupPages::assignPage('cart', 'de', $id)->get_error_code());
        self::assertSame('Unchanged', get_post($id)->post_content);
    }

    /**
     * Get roles that cannot manage site setup.
     *
     * @return array account names
     */
    public static function restrictedUsers(): array
    {
        return array(array('lab-subscriber'), array('lab-contributor'), array('lab-manager'));
    }
}
