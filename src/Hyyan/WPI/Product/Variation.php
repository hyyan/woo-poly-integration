<?php

/**
 * This file is part of the hyyan/woo-poly-integration plugin.
 * (c) Hyyan Abo Fakher <hyyanaf@gmail.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hyyan\WPI\Product;

/**
 * Variation.
 *
 * Handle Variation Duplicate
 *
 * @author Hyyan Abo Fakher <hyyanaf@gmail.com>
 */
class Variation
{
    const DUPLICATE_KEY = '_point_to_variation';

    /**
     * @var \WC_Product_Variable
     */
    protected $from;

    /**
     * @var \WC_Product
     */
    protected $to;

    /**
     * Construct object.
     *
     * @param \WC_Product_Variable $from the product which contains variations to
     *                                   copy from
     * @param \WC_Product          $to   the product which we will copy the variation to
     */
    public function __construct(\WC_Product_Variable $from, \WC_Product $to)
    {
        $this->from = $from;
        $this->to = $to;
    }

    /**
     * Handle variation duplicate.
     *
     * @return bool false if the from product conatins no variatoins
     */
    public function duplicate()
    {
        $fromVariation = $this->from->get_available_variations();

        if (empty($fromVariation)) {
            return false;
        }

        if ($this->to->get_id() === $this->from->get_id()) {

            /*
             * In such a case just add the duplicate meta
             */

            foreach ($fromVariation as $variation) {
                if (
                        !metadata_exists(
                                'post', $variation['variation_id'], self::DUPLICATE_KEY
                        )
                ) {
                    update_post_meta(
                            $variation['variation_id'], self::DUPLICATE_KEY, $variation['variation_id']
                    );
                }
            }
        } else {

            /* This could be a very long operation */
            set_time_limit(0);

            foreach ($fromVariation as $variation) {

                /*
                 * First we check if the "to" product contains the duplicate meta
                 * key to find out if we have to update or insert
                 */

                $posts = get_posts(array(
                    'meta_key' => self::DUPLICATE_KEY,
                    'meta_value' => $variation['variation_id'],
                    'post_type' => 'product_variation',
                    'post_parent' => $this->to->get_id(),
                ));

                switch (count($posts)) {
                    case 1:
                        // update
                        $this->update(
                                wc_get_product($variation['variation_id']), $posts[0], $variation
                        );
                        break;
                    case 0:
                        // insert
                        $this->insert(wc_get_product($variation['variation_id']), $variation);
                        break;
                    default:
                        // we can not handle , something wrong here
                        break;
                }
            }

            /* Restor original timeout */
            set_time_limit(ini_get('max_execution_time'));
        }
    }

    /**
     * Get array of variations IDS which point to the given variation ID.
     *
     * @param int $variatonID variation ID
     *
     * @return array array of posts
     */
    public static function getRelatedVariation($variatonID, $returnIDS = false)
    {
        $result = array();
        $poinTo = get_post_meta(
                $variatonID, self::DUPLICATE_KEY, true
        );

        if ($poinTo) {
            $result = get_posts(array(
                'meta_key' => self::DUPLICATE_KEY,
                'meta_value' => $poinTo,
                'post_type' => 'product_variation',
            ));

            if (true === $returnIDS) {
                $IDS = array();
                foreach ($result as $post) {
                    $IDS[] = $post->ID;
                }
                $result = $IDS;
            }
        }

        return $result;
    }

    /**
     * Delete all variation related to the given variation ID.
     *
     * @param int $variationID variation ID
     */
    public static function deleteRelatedVariation($variationID)
    {
        $posts = (array) static::getRelatedVariation($variationID);
        foreach ($posts as $post) {
            wp_delete_post($post->ID, true);
        }
    }

    /**
     * Create new variation.
     *
     * @param \WC_Product_Variation $variation the variation product
     * @param array                 $metas     variation array
     */
    protected function insert(\WC_Product_Variation $variation, array $metas)
    {
        // Add the duplicate meta to the default language product variation,
        // just in case the product was created before plugin acivation.
        $this->addDuplicateMeta($variation->get_id());

        $data = (array) get_post($variation->get_id());
        unset($data['ID']);
        $data['post_parent'] = $this->to->get_id();
        $ID = wp_insert_post($data);

        if ($ID) {
            update_post_meta(
                    $ID, self::DUPLICATE_KEY, $metas['variation_id']
            );

            $this->copyVariationMetas($variation->get_id(), $ID);
        }
    }

    /**
     * Update variation product from given post object.
     *
     * @param \WC_Product_Variation $variation
     * @param \WP_Post              $post
     * @param array                 $metas
     */
    protected function update(\WC_Product_Variation $variation, \WP_Post $post, array $metas)
    {
        $this->copyVariationMetas($variation->get_id(), $post->ID);
    }
    
    /**
     * Add duplicate meta key to products created before plugin activation.
     *
     * @param int $ID   Id of the product in the default language
     */
    public function addDuplicateMeta($ID)
    {
        if ($ID) {
            $meta = get_post_meta($ID, self::DUPLICATE_KEY);

            if (empty($meta)) {
                update_post_meta($ID, self::DUPLICATE_KEY, $ID);
            }
        }
    }

    /**
     * Sync Product Shipping Class.
     * 
     * Shipping Class translation is not supported after WooCommerce 2.6
     * but it is still implemented by WooCommerce as a taxonomy. Therefore,
     * Polylang will not copy the Shipping Class meta.
     *
     * @param int $from product variation ID
     * @param int $to   product variation ID
     */
    public function syncShippingClass($from, $to)
    {
			// Product edit - update shipping class of all product translations
			$variation_from = wc_get_product($from);
            
			if ($variation_from) {           
				$shipping_class = $variation_from->get_shipping_class();
				if ($shipping_class){
					$shipping_terms = get_term_by( 'slug', $shipping_class, 'product_shipping_class' );
					if ($shipping_terms) {
						wp_set_post_terms( $to, array( $shipping_terms->term_id ), 'product_shipping_class' );
					}
				}
			}    
		}

    /**
     * Copy variation meta.
     *
     * The method follow the same method polylang use to sync metas between
     * translations
     *
     * @param int $from product variation ID
     * @param int $to   product variation ID
     */
    protected function copyVariationMetas($from, $to)
    {
        /* copy or synchronize post metas and allow plugins to do the same */
        $metas_from     = get_post_custom($from);
        $metas_to       = get_post_custom($to);

        /* get public and protected meta keys */
        $keys           = array_unique(array_merge(array_keys($metas_from), array_keys($metas_to)));
        
        /* metas disabled for sync */
        $metas_nosync   = Meta::getDisabledProductMetaToCopy();

        /*
         * _variation_description meta is a text-based string and generally needs to be translated.
         * _variation_description meta is copied from product in default language to the translations
         * when the translation is first created. But the meta can be edited/changed and will not be
         * overwriten when product is saved or updated.
         */
        if (isset($metas_to['_variation_description'])) {
            $metas_nosync[] = '_variation_description';
        }
        
        /* synchronize */
        foreach ($keys as $key) {
            if (!in_array($key, $metas_nosync)) {
                /*
                 * the synchronization process of multiple values custom fields is
                 * easier if we delete all metas first
                 */
                delete_post_meta($to, $key);
                if (isset($metas_from[$key])) {
                    if (substr($key, 0, 10) == 'attribute_') {
                        $translated = array();
                        $tax = str_replace('attribute_', '', $key);
    
                        foreach ($metas_from[$key] as $termSlug) {
                            $term = get_term_by('slug', $termSlug, $tax);
                            if ($term) {
                                $lang = isset($_GET['new_lang']) ? esc_attr($_GET['new_lang']) : pll_get_post_language($this->to->get_id());
                                if ($translated_term = pll_get_term($term->term_id, $lang)) {
                                    $translated[] = get_term_by('id', $translated_term, $tax)->slug;
                                } else {
                                    $translated[] = '';     // Attribute term has no translation
                                }
                            } else {
                                $translated[] = $termSlug;
                            }
                        }
                        $metas_from[$key] = $translated;
                    }
                    foreach ($metas_from[$key] as $value) {
                        /*
                         * Important: always maybe_unserialize value coming from
                         *            get_post_custom. See codex.
                         */
                        $value = maybe_unserialize($value);
                        add_post_meta($to, $key, $value);
                    }
                }
            }
        }
				//add shipping class not included in metas as now a taxonomy
				$this->syncShippingClass($from, $to);
    }
}
