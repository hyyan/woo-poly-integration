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

		if ( $product_with_stock ) {
			$targetValue = $product_with_stock->get_stock_quantity();

			//update all the translations to the same stock level..
			$product_translations = ($product_with_stock->is_type( 'variation' )) ?
			Variation::getRelatedVariation( get_post_meta( $product_with_stock->get_id(), Variation::DUPLICATE_KEY, true ), true ) :
			Utilities::getProductTranslationsArrayByObject( $product_with_stock );

			foreach ( $product_translations as $product_translation ) {
				if ( $product_translation != $product_with_stock->get_id() ) {
					$translation = wc_get_product( $product_translation );
					if ( $translation ) {
						wc_update_product_stock( $translation, $targetValue );
					}
				}
			}
		}
	}

}
