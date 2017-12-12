<?php

/**
 * This file is part of the hyyan/woo-poly-integration plugin.
 * (c) Hyyan Abo Fakher <hyyanaf@gmail.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hyyan\WPI;

use Hyyan\WPI\Tools\FlashMessages;

/**
 * Plugin.
 *
 * @author Hyyan Abo Fakher <hyyanaf@gmail.com>
 */
class Plugin
{

    /** Required woocommerce version */
    const WOOCOMMERCE_VERSION = '3.0.0';

    /** Required polylang version */
    const POLYLANG_VERSION = '2.0.0';

    /**
     * Construct the plugin.
     */
    public function __construct()
    {
        FlashMessages::register();

        add_action('init', array($this, 'activate'));
        add_action('plugins_loaded', array($this, 'loadTextDomain'));
    }

    /**
     * Load plugin langauge file.
     */
    public function loadTextDomain()
    {
        load_plugin_textdomain(
                'woo-poly-integration', false, plugin_basename(dirname(Hyyan_WPI_DIR)) . '/languages'
        );
    }

    /**
     * Activate plugin.
     *
     * The plugin will register its core if the requirements are full filled , otherwise
     * it will show an admin error message
     *
     * @return bool false if plugin can not be activated
     */
    public function activate()
    {
        if (!static::canActivate()) {
            FlashMessages::remove(MessagesInterface::MSG_SUPPORT);
            FlashMessages::add(
                    MessagesInterface::MSG_ACTIVATE_ERROR, static::getView('Messages/activateError'), array('error'), true
            );

            return false;
        }

        FlashMessages::remove(MessagesInterface::MSG_ACTIVATE_ERROR);
        FlashMessages::add(
                MessagesInterface::MSG_SUPPORT, static::getView('Messages/support')
        );

        add_filter('plugin_action_links_woo-poly-integration/__init__.php', function ($links) {
            $baseURL = is_multisite() ? get_admin_url() : admin_url();
            $settingsLinks = array(
                '<a href="'
                . $baseURL
                . 'options-general.php?page=hyyan-wpi">'
                . __('Settings', ' woo-poly-integration')
                . '</a>',
                '<a target="_blank" href="https://github.com/hyyan/woo-poly-integration/wiki">'
                . __('Docs', 'woo-poly-integration')
                . '</a>',
            );

            return $settingsLinks + $links;
        });

        add_filter('plugin_row_meta', array(__CLASS__, 'plugin_row_meta'), 10, 2);
        
        $oldVersion = get_option('wpi_version');
        if (version_compare(self::getVersion(), $oldVersion, '<>')) {
            $this->onUpgrade(self::getVersion(), $oldVersion);
            update_option('wpi_version', self::getVersion());
        }

        $this->registerCore();
    }

    /**
     * Check if the plugin can be activated.
     *
     * @return bool true if can be activated , false otherwise
     */
    public static function canActivate()
    {
        $polylang = false;
        $woocommerce = false;

        /* check polylang plugin */
        if (
                (
                is_plugin_active('polylang/polylang.php') ||
                is_plugin_active('polylang-pro/polylang.php')
                ) ||
                (
                is_plugin_active_for_network('polylang/polylang.php') ||
                is_plugin_active_for_network('polylang-pro/polylang.php')
                )
        ) {
            if (isset($GLOBALS['polylang'], \PLL()->model, PLL()->links_model)) {
                if (pll_default_language()) {
                    $polylang = true;
                }
            }
        }

        /* check woocommerce plugin */
        if (
                is_plugin_active('woocommerce/woocommerce.php') ||
                is_plugin_active_for_network('woocommerce/woocommerce.php')
        ) {
            $woocommerce = true;
        }


        return ($polylang && Utilities::polylangVersionCheck(self::POLYLANG_VERSION)) &&
                ($woocommerce && Utilities::woocommerceVersionCheck(self::WOOCOMMERCE_VERSION));
    }

    /**
     * On Upgrade
     *
     * Run on the plugin updates only once
     *
     * @param num $newVersion
     * @param num $oldVersion
     */
    public function onUpgrade($newVersion, $oldVersion)
    {
        flush_rewrite_rules(true);
    }

    /**
     * Get current plugin version.
     *
     * @return int
     */
    public static function getVersion()
    {
        $data = get_plugin_data(Hyyan_WPI_DIR);

        return $data['Version'];
    }

    /**
     * Get plugin view.
     *
     * @param string $name view name
     * @param array  $vars array of vars to pass to the view
     *
     * @return string the view content
     */
    public static function getView($name, array $vars = array())
    {
        $result = '';
        $path = dirname(Hyyan_WPI_DIR) . '/src/Hyyan/WPI/Views/' . $name . '.php';
        if (file_exists($path)) {
            ob_start();
            include $path;
            $result = ob_get_clean();
        }

        return $result;
    }

    /**
     * Add plugin core classes.
     */
    protected function registerCore()
    {
        new Emails();
        new Admin\Settings();
        new Cart();
        //new Login();
        new Order();
        new Pages();
        new Endpoints();
        new Product\Product();
        new Taxonomies\Taxonomies();
        new Media();
        new Permalinks();
        new Language();
        new Coupon();
        new Reports();
        new Widgets\SearchWidget();
        new Widgets\LayeredNav();
        new Gateways();
        new Shipping();
        new Breadcrumb();
        new Tax();
        new LocaleNumbers();
        new Ajax();
    }

    /**
     * Show row meta on the plugin screen.
     * allows documentation link to be available even when plugin is deactivated
     *
     * @param	mixed $links Plugin Row Meta
     * @param	mixed $file  Plugin Base file
     * @return	array
     */
    public static function plugin_row_meta($links, $file)
    {
        if ('woo-poly-integration/__init__.php' == $file) {
            $row_meta = array(
                'docs' => '<a target="_blank" href="https://github.com/hyyan/woo-poly-integration/wiki"'
                . '" aria-label="' . esc_attr__('View WooCommerce-Polylang Integration documentation', 'woo-poly-integration') . '">'
                . esc_html__('Docs', 'woo-poly-integration') . '</a>',
                'support' => '<a target="_blank" href="https://github.com/hyyan/woo-poly-integration/issues"'
                . '" aria-label="' . esc_attr__('View Issue tracker on GitHub', 'woo-poly-integration') . '">'
                . esc_html__('Support', 'woo-poly-integration') . '</a>',
            );
            return array_merge($links, $row_meta);
        }

        return (array) $links;
    }
}
