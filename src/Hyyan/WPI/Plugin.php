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
        if (class_exists('Polylang')) {
            if (isset($GLOBALS['polylang'], \PLL()->model, PLL()->links_model)) {
                if (pll_default_language()) {
                    $polylang = true;
                }
            }
        }

        /* check woocommerce plugin */
        if (class_exists('WooCommerce')) {
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
	public function onUpgrade( $newVersion, $oldVersion ) 
	{
      $features = get_option( Admin\Features::getID() );
      if ( ! $features ) {
        $features = unserialize( 'a:12:{s:13:"fields-locker";s:2:"on";s:6:"emails";s:2:"on";s:7:"reports";s:2:"on";s:7:"coupons";s:2:"on";s:5:"stock";s:2:"on";s:10:"categories";s:2:"on";s:4:"tags";s:2:"on";s:10:"attributes";s:2:"on";s:24:"new-translation-defaults";s:1:"1";s:13:"localenumbers";s:3:"off";s:10:"importsync";s:2:"on";s:19:"language-downloader";s:2:"on";}' );
        update_option( Admin\Features::getID(), $features );
      }
      Taxonomies\Taxonomies::updatePolyLangFromWooPolyFeatures( $features, $features, Admin\Features::getID() );

      $metas = get_option( Admin\MetasList::getID() );
      if ( ! $metas ) {
        $metas = unserialize( 'a:9:{s:7:"general";a:10:{s:12:"product-type";s:12:"product-type";s:8:"_virtual";s:8:"_virtual";s:4:"_sku";s:4:"_sku";s:11:"_upsell_ids";s:11:"_upsell_ids";s:14:"_crosssell_ids";s:14:"_crosssell_ids";s:9:"_children";s:9:"_children";s:22:"_product_image_gallery";s:22:"_product_image_gallery";s:11:"total_sales";s:11:"total_sales";s:25:"_translation_porduct_type";s:25:"_translation_porduct_type";s:11:"_visibility";s:11:"_visibility";}s:8:"polylang";a:3:{s:10:"menu_order";s:10:"menu_order";s:13:"_thumbnail_id";s:13:"_thumbnail_id";s:14:"comment_status";s:14:"comment_status";}s:5:"stock";a:5:{s:13:"_manage_stock";s:13:"_manage_stock";s:6:"_stock";s:6:"_stock";s:11:"_backorders";s:11:"_backorders";s:13:"_stock_status";s:13:"_stock_status";s:18:"_sold_individually";s:18:"_sold_individually";}s:8:"shipping";a:5:{s:7:"_weight";s:7:"_weight";s:7:"_length";s:7:"_length";s:6:"_width";s:6:"_width";s:7:"_height";s:7:"_height";s:22:"product_shipping_class";s:22:"product_shipping_class";}s:10:"Attributes";a:3:{s:19:"_product_attributes";s:19:"_product_attributes";s:26:"_custom_product_attributes";s:26:"_custom_product_attributes";s:19:"_default_attributes";s:19:"_default_attributes";}s:12:"Downloadable";a:5:{s:13:"_downloadable";s:13:"_downloadable";s:19:"_downloadable_files";s:19:"_downloadable_files";s:15:"_download_limit";s:15:"_download_limit";s:16:"_download_expiry";s:16:"_download_expiry";s:14:"_download_type";s:14:"_download_type";}s:5:"Taxes";a:2:{s:11:"_tax_status";s:11:"_tax_status";s:10:"_tax_class";s:10:"_tax_class";}s:5:"price";a:5:{s:14:"_regular_price";s:14:"_regular_price";s:11:"_sale_price";s:11:"_sale_price";s:22:"_sale_price_dates_from";s:22:"_sale_price_dates_from";s:20:"_sale_price_dates_to";s:20:"_sale_price_dates_to";s:6:"_price";s:6:"_price";}s:9:"Variables";a:12:{s:20:"_min_variation_price";s:20:"_min_variation_price";s:20:"_max_variation_price";s:20:"_max_variation_price";s:23:"_min_price_variation_id";s:23:"_min_price_variation_id";s:23:"_max_price_variation_id";s:23:"_max_price_variation_id";s:28:"_min_variation_regular_price";s:28:"_min_variation_regular_price";s:28:"_max_variation_regular_price";s:28:"_max_variation_regular_price";s:31:"_min_regular_price_variation_id";s:31:"_min_regular_price_variation_id";s:31:"_max_regular_price_variation_id";s:31:"_max_regular_price_variation_id";s:25:"_min_variation_sale_price";s:25:"_min_variation_sale_price";s:25:"_max_variation_sale_price";s:25:"_max_variation_sale_price";s:28:"_min_sale_price_variation_id";s:28:"_min_sale_price_variation_id";s:28:"_max_sale_price_variation_id";s:28:"_max_sale_price_variation_id";}}' );
        update_option( Admin\MetasList::getID(), $metas );
        Taxonomies\Taxonomies::updatePolyLangFromWooPolyMetas( $metas, $metas, Admin\MetasList::getID() );
      }

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
        new Privacy();
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
