<?php // phpcs:ignore WordPress.Files.FileName -- The plugin autoloader maps class names directly to paths.
/**
 * This file is part of the hyyan/woo-poly-integration plugin.
 * (c) Hyyan Abo Fakher <hyyanaf@gmail.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package WooPoly
 */

namespace Hyyan\WPI\Product;

use Hyyan\WPI\Media;
use Hyyan\WPI\Admin\Settings;
use Hyyan\WPI\Admin\Features;

/** Create and link translated WooCommerce product drafts. */
final class TranslatedDraft {

	/**
	 * Create a linked draft under a language-specific database lock.
	 *
	 * @param int    $id Source product ID.
	 * @param string $target Target language slug.
	 */
	public static function create( $id, $target ) {
		global $wpdb;

		if ( ! current_user_can( 'manage_woocommerce' ) || 'product' !== get_post_type( $id ) || ! current_user_can( 'edit_post', $id ) // phpcs:ignore WordPress.WP.Capabilities.Unknown -- WooCommerce registers this capability.
			|| ! current_user_can( get_post_type_object( 'product' )->cap->create_posts ) ) {
			return new \WP_Error( 'forbidden', __( 'You cannot translate this product.', 'woo-poly-integration' ) );
		}

		$source = pll_get_post_language( $id );
		if ( ! $source || $source === $target || ! in_array( $target, pll_languages_list(), true ) ) {
			return new \WP_Error( 'language', __( 'Choose another configured language.', 'woo-poly-integration' ) );
		}

		// Serialize writes to the same language's shared term, media and attribute translations.
		$lock = 'woopoly_' . md5( DB_NAME . $wpdb->prefix . $target );
		if ( '1' !== (string) $wpdb->get_var( $wpdb->prepare( 'SELECT GET_LOCK(%s, 0)', $lock ) ) ) { // phpcs:ignore WordPress.DB.DirectDatabaseQuery -- Advisory locks require an uncached query on this connection.
			return new \WP_Error( 'busy', __( 'Another translation is running. Try again shortly.', 'woo-poly-integration' ) );
		}

		try {
			if ( pll_get_post( $id, $target ) ) {
				return new \WP_Error( 'exists', __( 'This product already has a translation in that language.', 'woo-poly-integration' ) );
			}

			return self::translate( wc_get_product( $id ), $target );
		} finally {
			$wpdb->get_var( $wpdb->prepare( 'SELECT RELEASE_LOCK(%s)', $lock ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery -- Advisory locks require an uncached query on this connection.
		}
	}

	/**
	 * Collect and translate the product text before creating the draft.
	 *
	 * @param WC_Product $product Source product.
	 * @param string     $target Target language slug.
	 */
	private static function translate( $product, $target ) {
		$id    = $product->get_id();
		$post  = get_post( $id );
		$texts = array( $post->post_title, $post->post_content, $post->post_excerpt, $product->get_purchase_note( 'edit' ) );
		$meta  = self::custom_text_fields( $product );

		foreach ( $meta as $values ) {
			$texts = array_merge( $texts, $values );
		}

		$taxonomies = array( 'product_cat', 'product_tag' );
		$labels     = array();

		foreach ( $product->get_attributes( 'edit' ) as $attribute ) {
			if ( $attribute->is_taxonomy() ) {
				$taxonomies[] = $attribute->get_name();
				$global       = wc_get_attribute( $attribute->get_id() );
				if ( $global ) {
					$labels[] = $global->name;
				}
			} else {
				$texts[] = $attribute->get_name();
				$texts   = array_merge( $texts, $attribute->get_options() );
			}
		}

		$terms = array();

		foreach ( $taxonomies as $taxonomy ) {
			if ( ! pll_is_translated_taxonomy( $taxonomy ) ) {
				continue;
			}

			$assigned = wp_get_object_terms( $id, $taxonomy );
			if ( is_wp_error( $assigned ) ) {
				return $assigned;
			}

			foreach ( $assigned as $term ) {
				while ( $term && ! is_wp_error( $term ) && pll_get_term_language( $term->term_id ) && ! isset( $terms[ $term->term_id ] ) && ! pll_get_term( $term->term_id, $target ) ) {
					$terms[ $term->term_id ] = $term;
					$texts[]                 = $term->name;
					$texts[]                 = $term->description;
					$term                    = $term->parent ? get_term( $term->parent, $taxonomy ) : null;
				}
			}
		}

		$children = $product->is_type( 'variable' ) ? $product->get_children() : array();
		$images   = array_merge( array( $product->get_image_id( 'edit' ) ), $product->get_gallery_image_ids( 'edit' ) );

		foreach ( $children as $child ) {
			$variation = wc_get_product( $child );
			$texts[]   = $variation->get_description( 'edit' );
			$images[]  = $variation->get_image_id( 'edit' );
		}

		$media = array();
		if ( Media::isMediaTranslationEnabled() ) {
			foreach ( array_unique( array_filter( $images ) ) as $image ) {
				if ( pll_get_post( $image, $target ) || ! current_user_can( 'edit_post', $image ) ) {
					continue;
				}

				$attachment = get_post( $image );
				if ( $attachment && 'attachment' === $attachment->post_type ) {
					$media[ $image ] = array( $attachment->post_excerpt, get_post_meta( $image, '_wp_attachment_image_alt', true ) );
					$texts           = array_merge( $texts, $media[ $image ] );
				}
			}
		}

		$language = PLL()->model->get_language( $target );
		$catalog  = new \PLL_MO();
		$catalog->import_from_db( $language );
		$labels       = array_filter(
			array_unique( $labels ),
			static function ( $label ) use ( $catalog ) {
				return ! isset( $catalog->entries[ $label ] );
			}
		);
		$translations = DeepL::translate( array_merge( $texts, $labels ), pll_get_post_language( $id, 'locale' ), $language->locale );
		if ( is_wp_error( $translations ) ) {
			return $translations;
		}

		$text = static function ( $value ) use ( $translations ) {
			return isset( $translations[ $value ] ) ? wp_kses_post( $translations[ $value ] ) : $value;
		};

		if ( pll_get_post( $id, $target ) ) {
			return new \WP_Error( 'exists', __( 'This product already has a translation in that language.', 'woo-poly-integration' ) );
		}

		$draft = wp_insert_post(
			wp_slash(
				array(
					'post_type'    => 'product',
					'post_status'  => 'draft',
					'post_title'   => $text( $post->post_title ),
					'post_excerpt' => $text( $post->post_excerpt ),
					'post_content' => $text( $post->post_content ),
				)
			),
			true
		);
		if ( is_wp_error( $draft ) ) {
			return $draft;
		}

		try {
			pll_set_post_language( $draft, $target );

			foreach ( $terms as $term ) {
				self::translate_term( $term, $target, $terms, $text );
			}

			self::translate_media( $media, $target, $text );

			if ( $labels ) {
				$catalog->import_from_db( $language );

				foreach ( $labels as $label ) {
					if ( ! isset( $catalog->entries[ $label ] ) ) {
						$catalog->add_entry(
							new \Translation_Entry(
								array(
									'singular'     => $label,
									'translations' => array( $text( $label ) ),
								)
							)
						);
					}
				}

				$catalog->export_to_db( $language );
			}

			self::copy_product( $product, $draft, $target, $taxonomies, $children, $text );

			foreach ( $meta as $key => $values ) {
				delete_post_meta( $draft, $key );

				foreach ( $values as $value ) {
					add_post_meta( $draft, $key, wp_slash( $text( $value ) ) );
				}
			}

			$links            = pll_get_post_translations( $id );
			$links[ $target ] = $draft;
			pll_save_post_translations( $links );

			return $draft;
		} catch ( \Throwable $error ) {
			wp_delete_post( $draft, true );

			return new \WP_Error( 'translation_failed', $error->getMessage() );
		}
	}

	/**
	 * Collect translatable custom metadata.
	 *
	 * @param WC_Product $product Source product.
	 */
	private static function custom_text_fields( $product ) {
		$meta       = array();
		$configured = Settings::getOption( 'deepl-meta-keys', Features::getID(), '' );
		$keys       = is_string( $configured ) ? array_map( 'trim', explode( ',', $configured ) ) : array();
		$keys       = apply_filters( \Hyyan\WPI\HooksInterface::PRODUCT_TRANSLATION_META_KEYS, $keys, $product );
		$internal   = $product->get_data_store()->get_internal_meta_keys();

		foreach ( array_diff( array_unique( array_filter( $keys, 'is_string' ) ), $internal ) as $key ) {
			if ( '' === $key || strpos( $key, '_wp_' ) === 0 || strpos( $key, '_edit_' ) === 0 ) {
				continue;
			}

			$values = get_post_meta( $product->get_id(), $key, false );
			if ( $values && count( array_filter( $values, 'is_string' ) ) === count( $values ) ) {
				$meta[ $key ] = $values;
			}
		}

		return $meta;
	}

	/**
	 * Create missing attachment translations and apply translated text.
	 *
	 * @param array    $media Source attachment fields.
	 * @param string   $target Target language slug.
	 * @param callable $text Text translation lookup.
	 * @throws \RuntimeException When an attachment cannot be translated.
	 */
	private static function translate_media( array $media, $target, $text ) {
		foreach ( $media as $image => $fields ) {
			if ( pll_get_post( $image, $target ) ) {
				continue;
			}

			$model   = PLL()->model->post;
			$manager = is_callable( array( $model, 'create_media_translation' ) ) ? $model : PLL()->posts;
			$copy    = $manager->create_media_translation( $image, $target );
			if ( ! $copy ) {
				throw new \RuntimeException( __( 'The translated attachment could not be created.', 'woo-poly-integration' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- The admin handler escapes this error when rendering it.
			}

			$result = wp_update_post(
				wp_slash(
					array(
						'ID'           => $copy,
						'post_excerpt' => $text( $fields[0] ),
					)
				),
				true
			);
			if ( is_wp_error( $result ) ) {
				throw new \RuntimeException( $result->get_error_message() ); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- The admin handler escapes this error when rendering it.
			}

			update_post_meta( $copy, '_wp_attachment_image_alt', wp_slash( $text( $fields[1] ) ) );
		}
	}

	/**
	 * Create or reuse a translated term and its parent.
	 *
	 * @param WP_Term  $term Source term.
	 * @param string   $target Target language slug.
	 * @param array    $terms Source terms indexed by ID.
	 * @param callable $text Text translation lookup.
	 * @throws \RuntimeException When term insertion fails.
	 */
	private static function translate_term( $term, $target, array $terms, $text ) {
		$existing = pll_get_term( $term->term_id, $target );
		if ( $existing ) {
			return $existing;
		}

		$parent = 0;
		if ( isset( $terms[ $term->parent ] ) ) {
			$parent = self::translate_term( $terms[ $term->parent ], $target, $terms, $text );
		} elseif ( $term->parent ) {
			$parent = pll_get_term( $term->parent, $target );
		}

		$result = wp_insert_term(
			$text( $term->name ),
			$term->taxonomy,
			array(
				'parent'      => (int) $parent,
				'slug'        => $term->slug . '-' . $target,
				'description' => $text( $term->description ),
			)
		);
		if ( is_wp_error( $result ) ) {
			throw new \RuntimeException( $result->get_error_message() ); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- The admin handler escapes this error when rendering it.
		}

		$new = (int) $result['term_id'];
		pll_set_term_language( $new, $target );
		$links            = pll_get_term_translations( $term->term_id );
		$links[ $target ] = $new;
		pll_save_term_translations( $links );

		return $new;
	}

	/**
	 * Copy product data, variations and translated terms to the draft.
	 *
	 * @param WC_Product $product Source product.
	 * @param int        $draft Draft product ID.
	 * @param string     $target Target language slug.
	 * @param array      $taxonomies Product taxonomies.
	 * @param array      $children Source variation IDs.
	 * @param callable   $text Text translation lookup.
	 * @throws \RuntimeException When a taxonomy assignment fails.
	 */
	private static function copy_product( $product, $draft, $target, $taxonomies, $children, $text ) {
		$id    = $product->get_id();
		$types = wp_set_object_terms( $draft, $product->get_type(), 'product_type' );
		if ( is_wp_error( $types ) ) {
			throw new \RuntimeException( $types->get_error_message() ); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- The admin handler escapes this error when rendering it.
		}

		foreach ( Meta::getProductMetaToCopy() as $key ) {
			foreach ( get_post_meta( $id, $key, false ) as $value ) {
				add_post_meta( $draft, $key, wp_slash( $value ) );
			}
		}

		foreach ( array_merge( $taxonomies, array( 'product_shipping_class' ) ) as $taxonomy ) {
			$values = wp_get_object_terms( $id, $taxonomy, array( 'fields' => 'ids' ) );
			if ( is_wp_error( $values ) ) {
				throw new \RuntimeException( $values->get_error_message() ); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- The admin handler escapes this error when rendering it.
			}

			$values = array_map(
				static function ( $value ) use ( $target, $taxonomy ) {
					return (int) ( pll_is_translated_taxonomy( $taxonomy ) ? pll_get_term( $value, $target ) : $value );
				},
				$values
			);
			$set    = wp_set_object_terms( $draft, array_filter( $values ), $taxonomy );
			if ( is_wp_error( $set ) ) {
				throw new \RuntimeException( $set->get_error_message() ); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- The admin handler escapes this error when rendering it.
			}
		}

		$image = static function ( $id ) use ( $target ) {
			$translated = $id && Media::isMediaTranslationEnabled() ? pll_get_post( $id, $target ) : 0;
			return $translated ? $translated : $id;
		};

		$copy = wc_get_product( $draft );
		$copy->set_image_id( $image( $product->get_image_id( 'edit' ) ) );
		$copy->set_gallery_image_ids( array_map( $image, $product->get_gallery_image_ids( 'edit' ) ) );
		$copy->set_purchase_note( $text( $product->get_purchase_note( 'edit' ) ) );
		$attributes = array();
		$local      = array();

		foreach ( $product->get_attributes( 'edit' ) as $key => $original ) {
			$attribute = clone $original;
			if ( $attribute->is_taxonomy() ) {
				$values = wp_get_object_terms( $draft, $attribute->get_name(), array( 'fields' => 'ids' ) );
				$attribute->set_options( $values );
			} else {
				$attribute->set_name( $text( $original->get_name() ) );
				$attribute->set_options( array_map( $text, $original->get_options() ) );
				$local[ $key ] = $attribute;
			}

			$attributes[] = $attribute;
		}

		$copy->set_attributes( $attributes );
		$selected = static function ( $values ) use ( $target, $local, $text ) {
			$translated = array();

			foreach ( $values as $key => $value ) {
				if ( isset( $local[ $key ] ) ) {
					$translated[ sanitize_title( $local[ $key ]->get_name() ) ] = $text( $value );
				} else {
					$term               = $value && taxonomy_exists( $key ) ? get_term_by( 'slug', $value, $key ) : null;
					$term_id            = $term ? pll_get_term( $term->term_id, $target ) : 0;
					$translated[ $key ] = $term_id ? get_term( $term_id, $key )->slug : $value;
				}
			}

			return $translated;
		};

		$copy->set_default_attributes( $selected( $product->get_default_attributes( 'edit' ) ) );
		$copy->save();
		if ( $children ) {
			$include_hidden = '__return_false';
			add_filter( 'woocommerce_hide_invisible_variations', $include_hidden );

			try {
				( new Variation( $product, $copy ) )->duplicate();
			} finally {
				remove_filter( 'woocommerce_hide_invisible_variations', $include_hidden );
			}

			foreach ( $children as $child ) {
				$variation = wc_get_product( $child );

				foreach ( (array) Variation::getRelatedVariation( $child, true ) as $related ) {
					if ( (int) wp_get_post_parent_id( $related ) !== $draft ) {
						continue;
					}

					$translated = wc_get_product( $related );
					$translated->set_attributes( $selected( $variation->get_attributes( 'edit' ) ) );
					$translated->set_description( $text( $variation->get_description( 'edit' ) ) );
					$translated->set_image_id( $image( $variation->get_image_id( 'edit' ) ) );
					$translated->save();
				}
			}
		}
	}
}
