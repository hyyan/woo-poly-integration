<?php

/**
 * This file is part of the hyyan/woo-poly-integration plugin.
 * (c) Hyyan Abo Fakher <hyyanaf@gmail.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hyyan\WPI\Admin;

use Hyyan\WPI\Plugin;
use Hyyan\WPI\HooksInterface as Hyyan_WPI_Hooks;

/**
 * Handle the setup entry point before either dependency is configured.
 *
 * @author Hyyan Abo Fakher <hyyanaf@gmail.com>
 */
final class Setup
{
    /**
     * Register setup hooks independently of WooCommerce and Polylang readiness.
     */
    public function __construct()
    {
        add_action('admin_menu', array(__CLASS__, 'registerMenu'));
        add_action('admin_enqueue_scripts', array(__CLASS__, 'enqueueAssets'));
        add_action('admin_notices', array(__CLASS__, 'showSetupNotice'));
        add_action('admin_post_woopoly_setup_pages', array(__CLASS__, 'handlePageSetup'));
        add_action('init', array(__CLASS__, 'registerWooCommerceTask'), 20);
        add_filter('plugin_action_links_woo-poly-integration/__init__.php', array(__CLASS__, 'addPluginActionLinks'));
        add_filter('pll_wizard_steps', array(__CLASS__, 'addPolylangSetupStep'), 900);
    }

    /**
     * Get the local setup or advanced settings URL.
     *
     * @param bool $advanced whether to open advanced settings
     * @return string admin URL
     */
    public static function getSetupUrl($advanced = false)
    {
        return add_query_arg($advanced ? array('page' => 'hyyan-wpi', 'view' => 'advanced') : array('page' => 'hyyan-wpi'), admin_url('options-general.php'));
    }

    /**
     * Register the WooPoly entry point for site administrators.
     *
     * @return void
     */
    public static function registerMenu()
    {
        add_options_page(__('WooPoly setup', 'woo-poly-integration'), 'WooPoly', 'manage_options', 'hyyan-wpi', array(__CLASS__, 'outputPage'));
    }

    /**
     * Load setup styles only on the WooPoly management screen.
     *
     * @param string $screen current admin screen hook
     * @return void
     */
    public static function enqueueAssets($screen = '')
    {
		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		if ( in_array( $screen, array( 'plugins', 'settings_page_hyyan-wpi' ), true ) ) {
			wp_enqueue_style( 'woopoly-share', plugins_url( 'public/css/share' . $suffix . '.css', Hyyan_WPI_DIR ), array(), Plugin::getVersion() );
		}
        if ($screen === 'settings_page_hyyan-wpi') {
            $dependencies = wp_style_is('wp-theme', 'registered') ? array('wp-theme') : array();
            wp_enqueue_style('woopoly-setup', plugins_url('public/css/setup' . $suffix . '.css', Hyyan_WPI_DIR), $dependencies, Plugin::getVersion());
        }
    }

    /**
     * Check that two or more languages have published, linked store pages.
     *
     * @return bool true when the multilingual page setup is complete
     */
    public static function isReady()
    {
        return count(SetupPages::getLanguages()) >= 2 && SetupPages::isReady();
    }

    /**
     * Show one contextual setup link on the Plugins screen while setup is pending.
     *
     * @return void
     */
    public static function showSetupNotice()
    {
        $screen = get_current_screen();
        if (!current_user_can('manage_options') || !$screen || $screen->id !== 'plugins' || self::isReady()) {
            return;
        }
        echo '<div class="notice notice-info"><p>';
        esc_html_e('Welcome to WooPoly. Set up your languages and store pages to get your multilingual shop ready.', 'woo-poly-integration');
        echo ' <a href="' . esc_url(self::getSetupUrl()) . '">' . esc_html__('Start setup', 'woo-poly-integration') . '</a></p></div>';
    }

    /**
     * Expose setup before missing dependencies allow the integration to load.
     *
     * @param array $links existing plugin row actions
     * @return array plugin row actions including the setup link when needed
     */
    public static function addPluginActionLinks($links)
    {
        if (!Plugin::canActivate() && current_user_can('manage_options')) {
            array_unshift($links, '<a href="' . esc_url(self::getSetupUrl()) . '">' . esc_html__('Set up', 'woo-poly-integration') . '</a>');
        }
        return $links;
    }

    /**
     * Register the checklist with the native WooCommerce extended task list.
     *
     * @return void
     */
    public static function registerWooCommerceTask()
    {
        if (class_exists('Automattic\WooCommerce\Admin\Features\OnboardingTasks\TaskLists')) {
            \Automattic\WooCommerce\Admin\Features\OnboardingTasks\TaskLists::add_task('extended', new SetupTask());
        }
    }

    /**
     * Offer a direct continuation after Polylang's language and content setup.
     *
     * @param array $steps native Polylang wizard steps
     * @return array wizard steps including the WooPoly store-page entry point
     */
    public static function addPolylangSetupStep(array $steps)
    {
        if (class_exists('WooCommerce') && current_user_can('manage_options')) {
            $steps['woopoly'] = array(
                'name' => __('Store pages', 'woo-poly-integration'),
                'view' => array(__CLASS__, 'outputPolylangStep'),
                'scripts' => array(),
                'styles' => array(),
            );
        }
        return $steps;
    }

    /**
     * Render the WooPoly continuation within Polylang's native wizard.
     *
     * @return void
     */
    public static function outputPolylangStep()
    {
        echo '<h2>' . esc_html__('Prepare your multilingual store pages', 'woo-poly-integration') . '</h2>';
        echo '<p>' . esc_html__('Your language settings are saved. Continue in WooPoly to create or select your Shop, Cart, Checkout, and My account pages for each language.', 'woo-poly-integration') . '</p>';
        echo '<p>' . esc_html__('Existing pages and translations will be preserved. You can return to the checklist at any time under Settings → WooPoly.', 'woo-poly-integration') . '</p>';
        echo '<p class="wc-setup-actions step"><a class="button button-primary button-large" href="' . esc_url(self::getSetupUrl()) . '">' . esc_html__('Continue in WooPoly', 'woo-poly-integration') . '</a></p>';
    }

    /**
     * Describe required plugins, accepting either Polylang Free or Pro.
     *
     * @return array dependency names, plugin files, active flags and minimum versions
     */
    public static function getDependencies()
    {
        $plugins = get_plugins();
        $polylangFile = !isset($plugins['polylang/polylang.php']) && isset($plugins['polylang-pro/polylang.php'])
            ? 'polylang-pro/polylang.php' : 'polylang/polylang.php';
        foreach (array('polylang/polylang.php', 'polylang-pro/polylang.php') as $file) {
            if (is_plugin_active($file) || is_plugin_active_for_network($file)) {
                $polylangFile = $file;
                break;
            }
        }
        return array(
            array('name' => 'WooCommerce', 'slug' => 'woocommerce', 'file' => 'woocommerce/woocommerce.php',
                'active' => class_exists('WooCommerce'), 'minimum' => Plugin::WOOCOMMERCE_VERSION),
            array('name' => 'Polylang', 'slug' => 'polylang', 'file' => $polylangFile,
                'active' => class_exists('Polylang'), 'minimum' => Plugin::POLYLANG_VERSION),
        );
    }

    /**
     * Render setup status or the existing advanced settings screen.
     *
     * Rendering does not create pages or change language relationships.
     *
     * @return void
     */
    public static function outputPage()
    {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to set up WooPoly.', 'woo-poly-integration'), '', array('response' => 403));
        }
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Selects a read-only view; writes use handlePageSetup().
        $advanced = isset($_GET['view']) && is_string($_GET['view']) && sanitize_key(wp_unslash($_GET['view'])) === 'advanced';
        if ($advanced && Plugin::canActivate()) {
            echo '<p class="wpi-back"><a href="' . esc_url(self::getSetupUrl()) . '">&larr; ' . esc_html__('Back to setup', 'woo-poly-integration') . '</a></p>';
            do_action(Hyyan_WPI_Hooks::SETUP_ADVANCED_SETTINGS_ACTION);
            return;
        }
        $languages = SetupPages::getLanguages();
        $rows = SetupPages::getPageStatus();
        $existingPages = array();
        foreach ($languages as $slug => $language) {
            $existingPages[$slug] = self::getExistingPages($slug);
        }
        $plugins = get_plugins();
        $dependencies = self::getDependencies();
        $dependenciesReady = true;
        foreach ($dependencies as $dependency) {
            if (!$dependency['active'] || !isset($plugins[$dependency['file']]) ||
                    version_compare($plugins[$dependency['file']]['Version'], $dependency['minimum'], '<')) {
                $dependenciesReady = false;
            }
        }
        $languagesReady = count($languages) >= 2;
        $pagesReady = $languagesReady && SetupPages::isReady();
        $progress = (int) $dependenciesReady + (int) $languagesReady + (int) $pagesReady;
        $result = get_transient('wpi_setup_result_' . get_current_user_id());
        include dirname(__DIR__) . '/Views/Admin/setup.php';
    }

    /**
     * Verify an administrator request, run its page action, and return to setup.
     *
     * Only nonce-protected POST requests can create or assign pages.
     *
     * @return void
     */
    public static function handlePageSetup()
    {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to set up WooPoly.', 'woo-poly-integration'), '', array('response' => 403));
        }
        check_admin_referer('woopoly_setup_pages');
        $operation = isset($_POST['operation']) && is_string($_POST['operation']) ? sanitize_key(wp_unslash($_POST['operation'])) : '';
        if ($operation === 'create') {
            $result = SetupPages::createMissingPages();
        } elseif ($operation === 'assign') {
            $type = isset($_POST['page_type']) && is_string($_POST['page_type']) ? sanitize_key(wp_unslash($_POST['page_type'])) : '';
            $language = isset($_POST['language']) && is_string($_POST['language']) ? sanitize_key(wp_unslash($_POST['language'])) : '';
            $rawID = isset($_POST['page_id']) && is_string($_POST['page_id'])
                ? filter_var(wp_unslash($_POST['page_id']), FILTER_VALIDATE_REGEXP,
                    array('options' => array('regexp' => '/^[0-9]+$/D'))) : false;
            $id = $rawID !== false ? filter_var(ltrim($rawID, '0'), FILTER_VALIDATE_INT,
                array('options' => array('min_range' => 1))) : false;
            $result = $id ? SetupPages::assignPage($type, $language, $id) : new \WP_Error('invalid_page', __('Choose a valid page.', 'woo-poly-integration'));
        } else {
            $result = new \WP_Error('invalid_action', __('Choose a setup action and try again.', 'woo-poly-integration'));
        }
        $warnings = is_array($result) ? $result['warnings'] : array();
        $message = is_wp_error($result) ? $result->get_error_message() : __('Your page setup has been updated. Review the page status below.', 'woo-poly-integration');
        if ($warnings) {
            $message .= ' ' . implode(' ', $warnings);
        }
        set_transient('wpi_setup_result_' . get_current_user_id(), array('error' => is_wp_error($result), 'warning' => (bool) $warnings, 'message' => $message), 5 * MINUTE_IN_SECONDS);
        wp_safe_redirect(self::getSetupUrl() . '#store-pages');
        exit;
    }

    /**
     * Render an escaped, readable status badge.
     *
     * @param bool $ready whether this setup step is complete
     * @return void
     */
    public static function outputStatus($ready)
    {
        echo '<span class="wpi-status ' . ($ready ? 'is-ready' : 'is-pending') . '">' .
            ($ready ? esc_html__('Ready', 'woo-poly-integration') : esc_html__('Needs attention', 'woo-poly-integration')) . '</span>';
    }

    /**
     * List assignable pages in the requested language, including unassigned pages.
     *
     * @param string $language registered Polylang language slug
     * @return array page objects for the existing-page selector
     */
    public static function getExistingPages($language)
    {
        $pages = get_posts(array('post_type' => 'page', 'post_status' => array('publish', 'draft', 'private'),
            'numberposts' => -1, 'orderby' => 'title', 'order' => 'ASC', 'lang' => '', 'suppress_filters' => false));
        return array_filter($pages, static function ($page) use ($language) {
            $assigned = pll_get_post_language($page->ID);
            return !$assigned || $assigned === $language;
        });
    }
}
