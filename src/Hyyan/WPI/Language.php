<?php

/**
 * This file is part of the hyyan/woo-poly-integration plugin.
 * (c) Hyyan Abo Fakher <hyyanaf@gmail.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hyyan\WPI;

use Hyyan\WPI\Admin\Settings;
use Hyyan\WPI\Admin\Features;
use Hyyan\WPI\Tools\TranslationsDownloader;

/**
 * Language.
 *
 * @author Hyyan Abo Fakher <hyyanaf@gmail.com>
 */
class Language
{
    /**
     * Construct object.
     */
    public function __construct()
    {
        add_action('load-settings_page_mlang', array(
            $this, 'downlaodWhenPolylangAddLangauge',
        ));

        add_action('woo-poly.settings.wpi-features_fields', array(
            $this, 'addSettingFields',
        ));
    }

    /**
     * Add setting fields.
     *
     * Add langauge setting fields
     *
     * @param array $fields
     *
     * @return array
     */
    public function addSettingFields(array $fields)
    {
        $fields [] = array(
            'name' => 'language-downloader',
            'type' => 'checkbox',
            'default' => 'on',
            'label' => __('Translation Downloader', 'woo-poly-integration'),
            'desc' => __(
                    'Download Woocommerce translations when a new polylang language is added', 'woo-poly-integration'
            ),
        );

        return $fields;
    }

    /**
     * Download Translation.
     *
     * Download woocommerce translation when polylang add new langauge
     *
     * @return bool true if action executed successfully , false otherwise
     */
    public function downlaodWhenPolylangAddLangauge()
    {
        if ('off' === Settings::getOption('language-downloader', Features::getID(), 'on')) {
            return false;
        }

        if (
                !isset($_REQUEST['pll_action']) ||
                'add' !== esc_attr($_REQUEST['pll_action'])
        ) {
            return false;
        }

        $name = esc_attr($_REQUEST['name']);
        $locale = esc_attr($_REQUEST['locale']);

        if ('en_us' === strtolower($locale)) {
            return true;
        }

        try {
            return TranslationsDownloader::download($locale, $name);
        } catch (\RuntimeException $ex) {
            add_settings_error(
                    'general', $ex->getCode(), $ex->getMessage()
            );

            return false;
        }
    }
}
