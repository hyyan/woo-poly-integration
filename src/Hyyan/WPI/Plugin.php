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

        add_action('init', array($this, 'activate'));
        add_action('plugins_loaded', array($this, 'loadTextDomain'));

        add_action( 'pll_add_language', array( __CLASS__, 'handleNewLanguage' ) );

        if ( is_admin() ) {
          if ( defined( 'DOING_AJAX' ) || (function_exists( 'is_ajax' ) && is_ajax()) ) {
            //skipping ajax
          } else {
            $wcpagecheck_passed = get_option( 'wpi_wcpagecheck_passed' );
            if ( Settings::getOption( 'checkpages', Features::getID(), 1 ) || get_option( 'wpi_wcpagecheck_passed' ) == '0' ) {
              add_action( 'pll_language_defined', array( __CLASS__, 'wpi_ensure_woocommerce_pages_translated' ) );
            }
          }
        }
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
        $wcpagecheck_passed	 = get_option( 'wpi_wcpagecheck_passed' );
        if ( ! $wcpagecheck_passed || version_compare( self::getVersion(), $oldVersion, '<>' ) ) {
            self::onUpgrade(self::getVersion(), $oldVersion);
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
	public static function onUpgrade( $newVersion, $oldVersion ) 
	{
  		update_option( 'wpi_version', self::getVersion() );
      $features = get_option( Admin\Features::getID() );
      if ( ! $features ) {
        $features = unserialize( 'a:13:{s:13:"fields-locker";s:2:"on";s:6:"emails";s:2:"on";s:7:"reports";s:2:"on";s:7:"coupons";s:2:"on";s:5:"stock";s:2:"on";s:10:"categories";s:3:"off";s:4:"tags";s:2:"on";s:10:"attributes";s:2:"on";s:24:"new-translation-defaults";s:1:"1";s:13:"localenumbers";s:3:"off";s:10:"importsync";s:2:"on";s:10:"checkpages";s:2:"on";s:19:"language-downloader";s:2:"on";}' );
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

	/*
	 * Ensure woocommerce pages exist, are translated and published
	 * Missing pages will be added in appropriate language
	 *
	 */
	public static function wpi_ensure_woocommerce_pages_translated() {

		//to avoid repetition, only do this when we are going to be alerted to the results
		if ( ! is_admin() || is_ajax() ) {
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

		//just in case, get and ensure we are in the default locale
		$default_lang	 = pll_default_language();
		$default_locale	 = pll_default_language( 'locale' );
		$start_locale	 = get_locale();
		if ( $default_locale != $start_locale ) {
			Utilities::switchLocale( $default_locale );
		}

		//get the current id of each woocommerce page
		foreach ( $page_types as $page_type ) {
			$pageid = wc_get_page_id( $page_type );
			if ( $pageid == -1 || ! get_post( $pageid ) ) {
				//if any of the pages is missing, rerun the woocommerce page creation
				//which will just fill in any missing page
				\WC_Install::create_pages();
				$pageid = wc_get_page_id( $page_type );
			}
			$pages[ $page_type ] = $pageid;
		}

		//check the page is published in each language
		//get the locales and the slugs
		$langs		 = pll_languages_list( array( 'fields' => 'locale' ) );
		$lang_slugs	 = pll_languages_list();

		//for each page, check all the translations and fill in and link where necessary
		foreach ( $pages as $page_type => $orig_page_id ) {
			$orig_page = get_post( $orig_page_id );
			if ( $orig_page ) {
				$orig_postlocale = pll_get_post_language( $orig_page_id, 'locale' );
				//default pages may not have language set correctly
				if ( ! $orig_postlocale || ($orig_postlocale != $default_locale) ) {
					$orig_postlocale = $default_locale;
					pll_set_post_language( $orig_page_id, $default_lang );
				}
				$translations[ $default_lang ]	 = $orig_page_id;
				$changed						 = false;
				foreach ( $langs as $langId => $langLocale ) {
					$translation_id	 = $orig_page_id;
					$langSlug		 = $lang_slugs[ $langId ];
					$isNewPost		 = false;


					//if this is not the original language
					if ( $langLocale != $orig_postlocale ) {

						// and there is no translation in target language
						$translation_id = pll_get_post( $orig_page_id, $langLocale );
						if ( $translation_id == 0 || $translation_id == $orig_page_id ) {

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
									$post_name		 = _x( 'shop', 'Page slug', 'woocommerce' );
									$post_title		 = _x( 'Shop', 'Page title', 'woocommerce' );
									$post_content	 = '';
									break;
								case 'cart':
									$post_name		 = _x( 'cart', 'Page slug', 'woocommerce' );
									$post_title		 = _x( 'Cart', 'Page title', 'woocommerce' );
									$post_content	 = '<!-- wp:shortcode -->[' . apply_filters( 'woocommerce_cart_shortcode_tag', 'woocommerce_cart' ) . ']<!-- /wp:shortcode -->';
									break;
								case 'checkout':
									$post_name		 = _x( 'checkout', 'Page slug', 'woocommerce' );
									$post_title		 = _x( 'Checkout', 'Page title', 'woocommerce' );
									$post_content	 = '<!-- wp:shortcode -->[' . apply_filters( 'woocommerce_checkout_shortcode_tag', 'woocommerce_checkout' ) . ']<!-- /wp:shortcode -->';
									break;
								case 'myaccount':
									$post_name		 = _x( 'my-account', 'Page slug', 'woocommerce' );
									$post_title		 = _x( 'My account', 'Page title', 'woocommerce' );
									$post_content	 = '<!-- wp:shortcode -->[' . apply_filters( 'woocommerce_my_account_shortcode_tag', 'woocommerce_my_account' ) . ']<!-- /wp:shortcode -->';
									break;
							}


							$page_data		 = array(
								'post_status'	 => 'publish',
								'post_type'		 => 'page',
								'post_author'	 => 1,
								'post_name'		 => $post_name,
								'post_title'	 => $post_title,
								'post_content'	 => $post_content,
								//'post_parent'	 => $post_parent,
								'comment_status' => 'closed',
							);
							$translation_id	 = wp_insert_post( $page_data );

							pll_set_post_language( $translation_id, $langSlug );
							$changed = true;
						}
						//always add the existing translations back into the translations array
						$translations [ $langSlug ] = $translation_id;
					}
					//if this woocommerce page is an existing post, check post status
					if ( $translation_id && ! $isNewPost ) {
						$thisPost = get_post( $translation_id );
						if ( $thisPost ) {
							$postStatus = $thisPost->post_status;
							if ( $postStatus != 'publish' ) {
								$warnings[ $page_type . '::' . $langSlug ] = sprintf(
								__( '%1$s page in language %2$s is in status %3$s and needs to be published for the shop to work properly, check page id %4$s', 'woo-poly-integration' ), $page_type, $langLocale, $postStatus, $translation_id );
							}
						}
					}
				}
				if ( $changed ) {
					pll_save_post_translations( $translations );
				}
			}
		}

		if ( $warnings ) {
			FlashMessages::add(
			'pagechecks', implode( '<br/>', $warnings )
			, array( 'updated' ), true
			);
			update_option( 'wpi_wcpagecheck_passed', false );
		} else {
			FlashMessages::remove( 'pagechecks' );
			update_option( 'wpi_wcpagecheck_passed', true );
		}
		$locale = get_locale();
		if ( $locale != $start_locale ) {
			Utilities::switchLocale( $start_locale );
		}
	}

}
