<?php

/**
 * This file is part of the hyyan/woo-poly-integration plugin.
 * (c) Hyyan Abo Fakher <hyyanaf@gmail.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This file was written by J.Moore to replace the existing Stock.php file which had
 * multiple issues: this version takes a different approach
 */

namespace Hyyan\WPI\Product;

use Hyyan\WPI\Utilities;
use Hyyan\WPI\Product\Variation;

class Stock {
	public function __construct() {

		//disable on product edit screen as this has its own synchronisation on save
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : false;
		if ( ($screen && $screen->post_type === 'product') || isset( $_POST[ 'product-type' ] ) ) {
			return;
		}

		// sync stock
		add_action(
		'woocommerce_product_set_stock', array( __CLASS__, 'SyncStockSimple' )
		);
		add_action(
		'woocommerce_variation_set_stock', array( __CLASS__, 'SyncStockVariation' )
		);
	}

	/*
	 * Unhook the action, synchronise the stock and rehook the action
	 *
	 * @param \WC_Product		$product   the product which has had stock updated
	 */
	public static function SyncStockSimple( \WC_Product $product ) {
		//first remove this action to avoid recusive call when setting translation stock
		remove_action( 'woocommerce_product_set_stock', array( __CLASS__, __FUNCTION__ ), 10 );
		static::SyncStock( $product );
		add_action( 'woocommerce_product_set_stock', array( __CLASS__, __FUNCTION__ ), 10 );
	}

	/*
	 * Unhook the action, synchronise the stock and rehook the action
	 *
	 * @param \WC_Product		$product   the product which has had stock updated
	 */
	public static function SyncStockVariation( \WC_Product $product ) {
		//first remove this action to avoid recusive call when setting translation stock
		remove_action( 'woocommerce_variation_set_stock', array( __CLASS__, __FUNCTION__ ), 10 );
		static::SyncStock( $product );
		add_action( 'woocommerce_variation_set_stock', array( __CLASS__, __FUNCTION__ ), 10 );
	}

	/*
	 * Synchronise stock levels across translations any time stock is updated
	 * through the product api [always now via wc_update_product_stock()]
	 *
	 * @param \WC_Product		$product   the product which has had stock updated
	 */
	public static function SyncStock( \WC_Product $product ) {
		//use same logic as wc_update_product_stock to get the product which is actually managing the stock
		$product_id_with_stock	 = $product->get_stock_managed_by_id();
		$product_with_stock		 = $product_id_with_stock !== $product->get_id() ? wc_get_product( $product_id_with_stock ) : $product;

		//skip if not a valid product
		if ( $product_with_stock && $product_with_stock->get_id() ) {
			$targetValue = $product_with_stock->get_stock_quantity();

			//update all the translations to the same stock level..
			$product_translations = [];
            if ($product_with_stock->is_type( 'variation' )) {
                $base_variation_id = Utilities::get_translated_variation($product_with_stock->get_id(),pll_default_language());               
    			$product_translations = Variation::getRelatedVariation( 
                    get_post_meta( $base_variation_id, Variation::DUPLICATE_KEY, true )
                    , true );
                if ($base_variation_id!=$product_id_with_stock){
                    if (($key = array_search($product_id_with_stock, $product_translations)) !== false) {
                        unset($product_translations[$key]);
                    }
                    $key = array_search($base_variation_id, $product_translations);
                    if ( $key === false ) {
                        $product_translations[]=$base_variation_id;
                    }
                }
            } else {
    			$product_translations = Utilities::getProductTranslationsArrayByObject( $product_with_stock );
            }

			if ( $product_translations ) {
        $target_status=$product_with_stock->get_stock_status();
				foreach ( $product_translations as $product_translation ) {
					if ( $product_translation != $product_with_stock->get_id() ) {
						$translation = wc_get_product( $product_translation );
						if ( $translation ) {
              //here the product stock is updated without saving then wc_update_product_stock_status will update and save status 
							wc_update_product_stock( $translation, $targetValue, 'set', true );
              wc_update_product_stock_status ($product_translation, $target_status);
              if ($translation->get_parent_id()) {
                $prodparent = wc_get_product($translation->get_parent_id());
                $parentstatus = $prodparent->get_stock_status();
                wc_update_product_stock_status ($translation->get_parent_id(), $target_status);
              }                                           
						}
					}
				}
			}
		}
	}

}
