<?php

/**
 * This file is part of the hyyan/woo-poly-integration plugin.
 * (c) Hyyan Abo Fakher <hyyanaf@gmail.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hyyan\WPI;

use Hyyan\WPI\HooksInterface as Hyyan_WPI_Hooks;
use Hyyan\WPI\Tools\FlashMessages;
use Hyyan\WPI\Admin\Settings;
use Hyyan\WPI\Admin\Features;

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
        add_action('admin_notices', array($this, 'showWelcomeNotice'));

        add_action('init', array($this, 'activate'));
        add_action('plugins_loaded', array($this, 'loadTextDomain'));
        add_filter(Hyyan_WPI_Hooks::WORDPRESS_GETTEXT_FILTER, array($this, 'translateLegacyFormat'), PHP_INT_MAX, 3);
        add_filter(Hyyan_WPI_Hooks::WORDPRESS_GETTEXT_CONTEXT_FILTER, array($this, 'translateWooCommerceContext'), PHP_INT_MAX, 4);
		add_action( 'admin_init', array( __CLASS__, 'admin_activate' ) );

        add_action( 'pll_add_language', array( __CLASS__, 'handleNewLanguage' ) );

        if ( is_admin() ) {
          if ( defined( 'DOING_AJAX' ) || (function_exists( 'is_ajax' ) && is_ajax()) ) {
            //skipping ajax
          } else {
            $wcpagecheck_passed = get_option( 'wpi_wcpagecheck_passed' );
            $check_pages		 = Settings::getOption( 'checkpages', Features::getID(), 0 );
            if ( ($check_pages && $check_pages != 'off') || ! ($wcpagecheck_passed) ) {
                add_action( 'current_screen', array( __CLASS__, 'wpi_ensure_woocommerce_pages_translated' ) );
            }
          }
        }
    }

    /*
     * enable admin features in admin mode
     */
	public static function admin_activate() {
		include_once( plugin_dir_path( __FILE__ ) . 'Admin/StatusReport.php');
	}

    /*
     * when new language is added in polylang, flag that default pages should be rechecked
     * (try not to download immediately as translation files will not be downloaded yet)
     */
    public static function handleNewLanguage( $args ) {
        update_option( 'wpi_wcpagecheck_passed', false );
    }

    /**
     * Load plugin langauge file.
     */
    public function loadTextDomain()
    {
        global $wp_version, $wp_textdomain_registry, $l10n;
        $domain = 'woo-poly-integration';
        $path = WP_PLUGIN_DIR . '/' . plugin_basename(dirname(Hyyan_WPI_DIR)) . '/languages';
        if (version_compare($wp_version, '6.7', '>=')) {
            $wp_textdomain_registry->set_custom_path($domain, $path);
            if (isset($l10n[$domain]) && $l10n[$domain] instanceof \NOOP_Translations) {
                unset($l10n[$domain]);
            }
            return;
        }

        $locale = apply_filters(Hyyan_WPI_Hooks::WORDPRESS_PLUGIN_LOCALE_FILTER, determine_locale(), $domain);
        $mofile = $domain . '-' . $locale . '.mo';
        if (load_textdomain($domain, WP_LANG_DIR . '/plugins/' . $mofile, $locale)) {
            return;
        }
        if (version_compare($wp_version, '6.1', '>=')) {
            $wp_textdomain_registry->set_custom_path($domain, $path);
        }
        load_textdomain($domain, $path . '/' . $mofile, $locale);
    }

    /**
     * Retain catalog translations for earlier message formats.
     *
     * @param string $translation translated text
     * @param string $text source text
     * @param string $domain text domain
     * @return string
     */
    public function translateLegacyFormat($translation, $text, $domain)
    {
        if ($domain !== 'woo-poly-integration' || $translation !== $text) {
            return $translation;
        }
        $legacy_formats = array(
            'WooCommerce translation %1$s can not be found in : <a href="%2$s">%2$s</a>' =>
                'WooCommerce translation %s can not be found in : <a href="%2$s">%2$s</a>',
            'Unable to download WooCommerce translation %1$s from : <a href="%2$s">%2$s</a>' =>
                'Unable to download WooCommerce translation %s from : <a href="%2$s">%2$s</a>',
            'You can translate woocommerce endpoints, email strings, shipping methods from polylang strings tab. <a target="_blank" href="%1$s">%2$s</a>' =>
                'You can translate woocommerce endpoints, email strings, shipping methods from polylang strings tab. <a target="_blank" href="%s">%s</a>',
        );
        if (isset($legacy_formats[$text])) {
            return $this->getCompatibleTranslation($translation, $text, $legacy_formats[$text], 'woo-poly-integration');
        }
        $woocommerce_messages = array(
            'Sort code',
            'Bank',
            'Account number',
            'IBAN',
            'BIC',
            'Our bank details',
            'Your personal data will be used to process your order, support your experience throughout this website, and for other purposes described in our %s.',
            'Your personal data will be used to support your experience throughout this website, to manage access to your account, and for other purposes described in our %s.',
            'You cannot add that amount of &quot;%1$s&quot; to the cart because there is not enough stock (%2$s remaining).',
            'The current language used by WordPress. Default = English',
            'Coupon: %s',
        );
        return in_array($text, $woocommerce_messages, true)
            ? $this->getCompatibleTranslation($translation, $text, $text, 'woocommerce') : $translation;
    }

    /**
     * Retain WooCommerce page translations for plugin messages.
     *
     * @param string $translation translated text
     * @param string $text source text
     * @param string $context translation context
     * @param string $domain text domain
     * @return string
     */
    public function translateWooCommerceContext($translation, $text, $context, $domain)
    {
        if ($domain !== 'woo-poly-integration' || $translation !== $text) {
            return $translation;
        }
        $page_messages = array(
            'Page slug' => array('shop', 'cart', 'checkout', 'my-account'),
            'Page title' => array('Shop', 'Cart', 'Checkout', 'My account'),
        );
        return isset($page_messages[$context]) && in_array($text, $page_messages[$context], true)
            ? $this->getCompatibleTranslation($translation, $text, $text, 'woocommerce', $context) : $translation;
    }

    /**
     * Get an earlier catalog translation when the current message is untranslated.
     *
     * @param string $translation current translation
     * @param string $text current source text
     * @param string $legacy earlier source text
     * @param string $legacy_domain earlier text domain
     * @param string $context translation context
     * @return string
     */
    private function getCompatibleTranslation($translation, $text, $legacy, $legacy_domain, $context = '')
    {
        $catalog = get_translations_for_domain('woo-poly-integration');
        $key = $context !== '' ? $context . "\x04" . $text : $text;
        if (($catalog instanceof \WP_Translations &&
                \WP_Translation_Controller::get_instance()->has_translation($key, 'woo-poly-integration')) ||
                (!($catalog instanceof \WP_Translations) && isset($catalog->entries[$key]))) {
            return $translation;
        }
        $translated = get_translations_for_domain($legacy_domain)->translate($legacy, $context);
        if ($context !== '') {
            $translated = apply_filters(Hyyan_WPI_Hooks::WORDPRESS_GETTEXT_CONTEXT_FILTER,
                $translated, $legacy, $context, $legacy_domain);
            if (version_compare(get_bloginfo('version'), '5.5', '>=')) {
                $translated = apply_filters(Hyyan_WPI_Hooks::WORDPRESS_WOOCOMMERCE_GETTEXT_CONTEXT_FILTER,
                    $translated, $legacy, $context, $legacy_domain);
            }
        } else {
            $translated = apply_filters(Hyyan_WPI_Hooks::WORDPRESS_GETTEXT_FILTER, $translated, $legacy, $legacy_domain);
            if (version_compare(get_bloginfo('version'), '5.5', '>=')) {
                $translated = $legacy_domain === 'woocommerce'
                    ? apply_filters(Hyyan_WPI_Hooks::WORDPRESS_WOOCOMMERCE_GETTEXT_FILTER, $translated, $legacy, $legacy_domain)
                    : apply_filters(Hyyan_WPI_Hooks::WORDPRESS_PLUGIN_GETTEXT_FILTER, $translated, $legacy, $legacy_domain);
            }
        }
        return $translated === $legacy ? $translation : $translated;
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
        // Retire the old site-wide support message in favor of a scoped welcome notice.
        FlashMessages::remove(MessagesInterface::MSG_SUPPORT);

        add_filter('plugin_action_links_woo-poly-integration/__init__.php', function ($links) {
            $baseURL = is_multisite() ? get_admin_url() : admin_url();
            $settingsLinks = array(
                static::settingsLinkHTML(),
                '<a target="_blank" href="https://hyyan.github.io/woo-poly-integration/">'
                . esc_html__('Docs', 'woo-poly-integration')
                . '</a>',
            );

            return $settingsLinks + $links;
        });

        add_filter('plugin_row_meta', array(__CLASS__, 'plugin_row_meta'), 10, 2);
        
        $oldVersion = get_option('wpi_version');
        $wcpagecheck_passed	 = get_option( 'wpi_wcpagecheck_passed' );
        if ( ! $wcpagecheck_passed || version_compare( self::getVersion(), $oldVersion, '<>' ) ) {
            self::onUpgrade(self::getVersion(), $oldVersion);
        }

        $this->registerCore();
    }

    /**
     * Welcome administrators once per release on the plugin's management screens.
     */
    public function showWelcomeNotice()
    {
        if (!current_user_can('manage_options') || !static::canActivate()) {
            return;
        }
        $screen = get_current_screen();
        if (!$screen || !in_array($screen->id, array('plugins', 'settings_page_hyyan-wpi'), true)) {
            return;
        }
        $version = static::getVersion();
        if (get_user_meta(get_current_user_id(), 'wpi_welcome_version', true) === $version) {
            return;
        }

        echo '<div class="notice notice-info is-dismissible">';
        echo wp_kses_post(static::getView('Messages/support'));
        echo '</div>';
        update_user_meta(get_current_user_id(), 'wpi_welcome_version', $version);
    }

	/*
	 * make settings page link easily available from multiple messages
	 */
	public static function settingsLinkHTML() 
	{
		$baseURL = is_multisite() ? get_admin_url() : admin_url();
        return '<a href="'.esc_url($baseURL.'options-general.php?page=hyyan-wpi').'">'
            .esc_html__('Settings', 'woo-poly-integration').'</a>';
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
	public static function onUpgrade( $newVersion, $oldVersion ) 
	{
  		update_option( 'wpi_version', self::getVersion() );
      $features = get_option( Admin\Features::getID() );
      if ( ! $features ) {
			$features = unserialize( 'a:13:{s:13:"fields-locker";s:2:"on";s:6:"emails";s:2:"on";s:7:"reports";s:2:"on";s:7:"coupons";s:2:"on";s:5:"stock";s:2:"on";s:10:"categories";s:2:"on";s:4:"tags";s:2:"on";s:10:"attributes";s:2:"on";s:24:"new-translation-defaults";s:1:"1";s:13:"localenumbers";s:2:"on";s:10:"importsync";s:2:"on";s:10:"checkpages";s:3:"off";s:19:"language-downloader";s:2:"on";}' );
        update_option( Admin\Features::getID(), $features );
      }
      Taxonomies\Taxonomies::updatePolyLangFromWooPolyFeatures( $features, $features, Admin\Features::getID() );

      $metas = get_option( Admin\MetasList::getID() );
      if ( ! $metas ) {
        $metas = unserialize( 'a:9:{s:7:"general";a:10:{s:12:"product-type";s:12:"product-type";s:8:"_virtual";s:8:"_virtual";s:4:"_sku";s:4:"_sku";s:11:"_upsell_ids";s:11:"_upsell_ids";s:14:"_crosssell_ids";s:14:"_crosssell_ids";s:9:"_children";s:9:"_children";s:22:"_product_image_gallery";s:22:"_product_image_gallery";s:11:"total_sales";s:11:"total_sales";s:25:"_translation_porduct_type";s:25:"_translation_porduct_type";s:11:"_visibility";s:11:"_visibility";}s:8:"polylang";a:3:{s:10:"menu_order";s:10:"menu_order";s:13:"_thumbnail_id";s:13:"_thumbnail_id";s:14:"comment_status";s:14:"comment_status";}s:5:"stock";a:6:{s:13:"_manage_stock";s:13:"_manage_stock";s:6:"_stock";s:6:"_stock";s:11:"_backorders";s:11:"_backorders";s:13:"_stock_status";s:13:"_stock_status";s:17:"_low_stock_amount";s:17:"_low_stock_amount";s:18:"_sold_individually";s:18:"_sold_individually";}s:8:"shipping";a:5:{s:7:"_weight";s:7:"_weight";s:7:"_length";s:7:"_length";s:6:"_width";s:6:"_width";s:7:"_height";s:7:"_height";s:22:"product_shipping_class";s:22:"product_shipping_class";}s:10:"Attributes";a:3:{s:19:"_product_attributes";s:19:"_product_attributes";s:26:"_custom_product_attributes";s:26:"_custom_product_attributes";s:19:"_default_attributes";s:19:"_default_attributes";}s:12:"Downloadable";a:5:{s:13:"_downloadable";s:13:"_downloadable";s:19:"_downloadable_files";s:19:"_downloadable_files";s:15:"_download_limit";s:15:"_download_limit";s:16:"_download_expiry";s:16:"_download_expiry";s:14:"_download_type";s:14:"_download_type";}s:5:"Taxes";a:2:{s:11:"_tax_status";s:11:"_tax_status";s:10:"_tax_class";s:10:"_tax_class";}s:5:"price";a:5:{s:14:"_regular_price";s:14:"_regular_price";s:11:"_sale_price";s:11:"_sale_price";s:22:"_sale_price_dates_from";s:22:"_sale_price_dates_from";s:20:"_sale_price_dates_to";s:20:"_sale_price_dates_to";s:6:"_price";s:6:"_price";}s:9:"Variables";a:12:{s:20:"_min_variation_price";s:20:"_min_variation_price";s:20:"_max_variation_price";s:20:"_max_variation_price";s:23:"_min_price_variation_id";s:23:"_min_price_variation_id";s:23:"_max_price_variation_id";s:23:"_max_price_variation_id";s:28:"_min_variation_regular_price";s:28:"_min_variation_regular_price";s:28:"_max_variation_regular_price";s:28:"_max_variation_regular_price";s:31:"_min_regular_price_variation_id";s:31:"_min_regular_price_variation_id";s:31:"_max_regular_price_variation_id";s:31:"_max_regular_price_variation_id";s:25:"_min_variation_sale_price";s:25:"_min_variation_sale_price";s:25:"_max_variation_sale_price";s:25:"_max_variation_sale_price";s:28:"_min_sale_price_variation_id";s:28:"_min_sale_price_variation_id";s:28:"_max_sale_price_variation_id";s:28:"_max_sale_price_variation_id";}}' );
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
                'docs' => '<a target="_blank" href="https://hyyan.github.io/woo-poly-integration/"'
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

	/*
	 * Ensure woocommerce pages exist, are translated and published
	 * Missing pages will be added in appropriate language
	 *
	 */
	public static function wpi_ensure_woocommerce_pages_translated() {
        if (!current_user_can('manage_woocommerce') && !current_user_can('manage_options')) {
            return;
        }

		//to avoid repetition, only do this when we are going to be alerted to the results
		if ( ! is_admin() || defined( 'DOING_AJAX' ) || (function_exists( 'is_ajax' ) && is_ajax()) ) {
			return;
		}

		/*
		 * only recheck this when on relevant pages such as pages list,
		 * and settings pages for the main plugins Polylang, WooCommerce and woopoly
		 * which might affect the woocommerce pages or transations
		 */
		$allowedPages	 = array(
			'woocommerce_page_wc-settings',
			'languages_page_mlang_settings',
			'toplevel_page_mlang',
			'settings_page_hyyan-wpi',
			'edit-page',
			'page',
			'plugins',
		);
		$screen			 = function_exists( 'get_current_screen' ) ? get_current_screen() : false;
		if ( ! $screen || ! in_array( $screen->id, $allowedPages ) ) {
			return;
		}
		//avoid any re-entrance
		if ( get_option( 'wpi_wcpagecheck_passed' ) == 'checking' ) {
			return;
		}
		update_option( 'wpi_wcpagecheck_passed', 'checking' );

		//each of the main pages to create
		$page_types	 = array( 'cart', 'checkout', 'myaccount', 'shop' );
		$pages		 = array();
		$warnings	 = array();
		$failure		 = false;
		//only create pages if the setting is on, otherwise only warnings will be shown
		$create_pages	 = Settings::getOption( 'checkpages', Features::getID(), 0 );
		if ( $create_pages && $create_pages == 'off' ) {
			$create_pages = false;
		}

		//get status of current language environment
		$default_lang	 = pll_default_language();
		$default_locale	 = pll_default_language( 'locale' );
		$start_locale		 = ( is_admin() ) ? get_user_locale() : get_locale();
		//important: in admin mode and 'Show all language' there is no polylang current language
		$pll_start_locale	 = pll_current_language( 'locale' );

		/*
		 * important, we must be in the base language before doing the check
		 * because otherwise the posts will be pll filtered into other language
		 * and appear to be missing if not translated
		 */
		if ( $pll_start_locale ) {
			if ( $default_locale != $pll_start_locale ) {
				Utilities::switchLocale( $default_locale );
			}
		} elseif ( $default_locale != $start_locale ) {
			Utilities::switchLocale( $default_locale );
		}

		/*
		 * get the current id of each woocommerce page in the base language
		 * using the native woocommerce function to fill any missing page
		 */
		foreach ( $page_types as $page_type ) {
			$pageid = wc_get_page_id( $page_type );
			if ( $pageid == -1 || ! get_post( $pageid ) ) {
				if ( $create_pages ) {
				    //if any of the pages is missing, rerun the woocommerce page creation
				    //which will just fill in any missing page
				    \WC_Install::create_pages();
				    $pageid = wc_get_page_id( $page_type );
					$warnings[ $page_type . '::' . $default_locale ] = sprintf(
					/* translators: 1: Page type, 2: Locale, 3: Page edit URL, 4: Page ID. */
					__( '%1$s page in base language %2$s was not found and was created using woocommerce create_pages() as page <a href="%3$s">%4$s</a>', 'woo-poly-integration' ), esc_html( $page_type ), esc_html( $default_locale ), esc_url( get_edit_post_link( $pageid, 'raw' ) ), absint( $pageid ) );
				} else {
					$warnings[ $page_type . '::' . $default_locale ] = sprintf(
					/* translators: 1: Page type, 2: Locale, 3: Settings link HTML. */
					__( '%1$s page in language %2$s was not found and must be created for the shop to work: this will be done automatically if Check WooCommerce Pages option is enabled in %3$s.  Translations for this page may also be missing.', 'woo-poly-integration' ), esc_html( $page_type ), esc_html( $default_locale ), static::settingsLinkHTML() );
					$failure										 = true;
				}
			}
			$pages[ $page_type ] = $pageid;
		}

		//check the page is published in each language
		//get the locales and the slugs
		$langs		 = pll_languages_list( array( 'fields' => 'locale' ) );
		$lang_slugs	 = pll_languages_list();

		//for each page, check all the translations and fill in and link where necessary
		foreach ( $pages as $page_type => $orig_page_id ) {
			$changed	 = false;
			$orig_page = get_post( $orig_page_id );
			if ( $orig_page ) {
				$orig_postlocale = pll_get_post_language( $orig_page_id, 'locale' );
				$orig_postlang	 = pll_get_post_language( $orig_page_id, 'slug' );
				//default pages may not have language set correctly
				if ( ! $orig_postlocale ) {
					$orig_postlocale = $default_locale;
					$orig_postlang									 = $default_lang;
					pll_set_post_language( $orig_page_id, $orig_postlang );
					$warnings[ $page_type . '::' . $default_locale ] = sprintf(
					/* translators: 1: Page type, 2: Locale, 3: Page edit URL, 4: Page ID. */
					__( '%1$s page did not have language - language set to %2$s on page <a href="%3$s">%4$s</a>', 'woo-poly-integration' ), esc_html( $page_type ), esc_html( $default_locale ), esc_url( get_edit_post_link( $orig_page_id, 'raw' ) ), absint( $orig_page_id ) );
				}
				$translations[ $orig_postlang ] = $orig_page_id;
				foreach ( $langs as $langId => $langLocale ) {
					$translation_id	 = $orig_page_id;
					$langSlug		 = $lang_slugs[ $langId ];
					$isNewPost		 = false;


					//if this is not the original language
					if ( $langLocale != $orig_postlocale ) {

						// and there is no translation in target language
						$translation_id = pll_get_post( $orig_page_id, $langLocale );
						if ( $translation_id == 0 || $translation_id == $orig_page_id ) {
							if ( $create_pages ) {
                                //then create new post in target language
                                $isNewPost = true;
                                Utilities::switchLocale( $langLocale );

                                //default to copy source page
                                $post_name		 = $orig_page->post_name;
                                $post_title		 = $orig_page->post_title;
                                $post_content	 = $orig_page->post_content;

                                //ideally, get correct translation
                                switch ( $page_type ) {
                                    case 'shop':
                                        $post_name		 = _x( 'shop', 'Page slug', 'woo-poly-integration' );
                                        $post_title		 = _x( 'Shop', 'Page title', 'woo-poly-integration' );
                                        $post_content	 = '';
                                        break;
                                    case 'cart':
                                        $post_name		 = _x( 'cart', 'Page slug', 'woo-poly-integration' );
                                        $post_title		 = _x( 'Cart', 'Page title', 'woo-poly-integration' );
                                        $post_content	 = has_block( 'woocommerce/cart', $orig_page->post_content )
                                        ? $orig_page->post_content
                                        : '<!-- wp:shortcode -->[' . apply_filters( 'woocommerce_cart_shortcode_tag', 'woocommerce_cart' ) . ']<!-- /wp:shortcode -->';
                                        break;
                                    case 'checkout':
                                        $post_name		 = _x( 'checkout', 'Page slug', 'woo-poly-integration' );
                                        $post_title		 = _x( 'Checkout', 'Page title', 'woo-poly-integration' );
                                        $post_content	 = has_block( 'woocommerce/checkout', $orig_page->post_content )
                                        ? $orig_page->post_content
                                        : '<!-- wp:shortcode -->[' . apply_filters( 'woocommerce_checkout_shortcode_tag', 'woocommerce_checkout' ) . ']<!-- /wp:shortcode -->';
                                        break;
                                    case 'myaccount':
                                        $post_name		 = _x( 'my-account', 'Page slug', 'woo-poly-integration' );
                                        $post_title		 = _x( 'My account', 'Page title', 'woo-poly-integration' );
                                        $post_content	 = '<!-- wp:shortcode -->[' . apply_filters( 'woocommerce_my_account_shortcode_tag', 'woocommerce_my_account' ) . ']<!-- /wp:shortcode -->';
                                        break;
                                }


                                $page_data		 = array(
                                    'post_status'	 => 'publish',
                                    'post_type'		 => 'page',
                                    'post_author'	 => 1,
                                    'post_name'		 => $post_name,
                                    'post_title'	 => $post_title,
                                    'post_content'	 => wp_slash( $post_content ),
                                    //'post_parent'	 => $post_parent,
                                    'comment_status' => 'closed',
                                );
                                $translation_id	 = wp_insert_post( $page_data );
							}
							//if there now is a translation is where there was not before, creation must have been successful
							if ( $translation_id ) {
							    pll_set_post_language( $translation_id, $langSlug );
							    $changed = true;
								$warnings[ $page_type . '::' . $langLocale ] = sprintf(
								/* translators: 1: Page type, 2: Locale, 3: Page edit URL, 4: Page ID. */
								__( '%1$s page in language %2$s was not found and was created as page <a href="%3$s">%4$s</a>', 'woo-poly-integration' ), esc_html( $page_type ), esc_html( $langLocale ), esc_url( get_edit_post_link( $translation_id, 'raw' ) ), absint( $translation_id ) );
							} else {
								$warnings[ $page_type . '::' . $langLocale ] = sprintf(
								/* translators: 1: Page type, 2: Locale, 3: Settings link HTML. */
								__( '%1$s page in language %2$s was not found and must be created for the shop to work in this language: this will be done automatically if Check WooCommerce Pages option is enabled in %3$s.', 'woo-poly-integration' ), esc_html( $page_type ), esc_html( $langLocale ), static::settingsLinkHTML() );
								$failure									 = true;
							}
						}
						//always add the existing translations back into the translations array
						if ( $translation_id ) {
						    $translations [ $langSlug ] = $translation_id;
					    }
					}
					//if this woocommerce page is an existing post, check post status
					if ( $translation_id && ! $isNewPost ) {
						$thisPost = get_post( $translation_id );
						if ( $thisPost ) {
							$postStatus = $thisPost->post_status;
							if ( $postStatus != 'publish' ) {
								$baseURL = is_multisite() ? get_admin_url() : admin_url();
								if ( $postStatus == 'trash' ) {
									$warnings[ $page_type . '::' . $langSlug ] = sprintf(
									/* translators: 1: Page type, 2: Locale, 3: Trash URL, 4: Page ID. */
									__( '%1$s page in language %2$s has been deleted, please check the <a href="%3$s">trash</a>, and restore page %4$s', 'woo-poly-integration' ), esc_html( $page_type ), esc_html( $langLocale ), esc_url( add_query_arg( array( 'post_status' => 'trash', 'post_type' => 'page', 'lang' => $langSlug ), $baseURL.'edit.php' ) ), absint( $translation_id ) );
								} else {
									$warnings[ $page_type . '::' . $langSlug ] = sprintf(
									/* translators: 1: Page type, 2: Locale, 3: Post status, 4: Page edit URL, 5: Page ID. */
									__( '%1$s page in language %2$s is in status %3$s and needs to be published for the shop to work properly, check page <a href="%4$s">%5$s</a>', 'woo-poly-integration' ), esc_html( $page_type ), esc_html( $langLocale ), esc_html( $postStatus ), esc_url( get_edit_post_link( $translation_id, 'raw' ) ), absint( $translation_id ) );
								}
								$failure = true;
							}
						} else {
								$warnings[ $page_type . '::' . $langSlug ] = sprintf(
							/* translators: 1: Page type, 2: Locale, 3: Missing page ID. */
							__( '%1$s page in language %2$s was linked in polylang but cannot be found, link will be removed to missing page %3$s', 'woo-poly-integration' ), esc_html( $page_type ), esc_html( $langLocale ), absint( $translation_id ) );
							unset( $translations[ $langSlug ] );
							$failure									 = true;
							$changed									 = true;
							}
						}
					}
				}
				if ( $changed ) {
					pll_save_post_translations( $translations );
				}
			}

		/*
		 * update result of page checks
		 */
		if ( $warnings ) {
			FlashMessages::add(
			'pagechecks', wp_kses_post( implode( '<br/>', $warnings ) )
			, array( 'updated' ), true
			);
		} else {
			FlashMessages::remove( 'pagechecks' );
		}
		if ( $failure ) {
			update_option( 'wpi_wcpagecheck_passed', '0' );
		} else {
			update_option( 'wpi_wcpagecheck_passed', '1' );
		}

		/*
		 * check current locale and reset it if changed
		 */
		$locale = get_locale();
		if ( $locale != $start_locale ) {
			Utilities::switch_wp_locale( $start_locale );
		}
		if ( $locale != $pll_start_locale ) {
			Utilities::switch_pll_locale( $pll_start_locale );
		}
	}

}
