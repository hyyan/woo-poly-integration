<?php

/**
 * This file is part of the hyyan/woo-poly-integration plugin.
 * (c) Hyyan Abo Fakher <hyyanaf@gmail.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hyyan\WPI;

/**
 * Media.
 *
 * Handle products media translation
 *
 * @author Hyyan Abo Fakher <hyyanaf@gmail.com>
 */
class Media
{

    /**
     * Construct object.
     */
    public function __construct()
    {
        if (static::isMediaTranslationEnabled()) {
            add_filter(
                'woocommerce_product_get_gallery_image_ids',
                 array($this, 'translateGallery')
            );
        }
    }

    /**
     * Check if media translation is enable in polylang settings.
     *
     * @return bool true if enabled , false otherwise
     */
    public static function isMediaTranslationEnabled()
    {
        $options = get_option('polylang');

        return $options['media_support'];
    }

    /**
     * Translate product gallery.
     *
     * @param array $IDS current attachment IDS
     *
     * @return array translated attachment IDS
     */
    public function translateGallery(array $IDS)
    {
        $translations = array();
        foreach ($IDS as $ID) {
            $tr = pll_get_post($ID);
            if ($tr) {
                $translations [] = $tr;
                continue;
            }
            $translations [] = $ID;
        }

        return $translations;
    }
}
