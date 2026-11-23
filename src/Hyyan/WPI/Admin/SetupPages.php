<?php

/**
 * This file is part of the hyyan/woo-poly-integration plugin.
 * (c) Hyyan Abo Fakher <hyyanaf@gmail.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hyyan\WPI\Admin;

use Hyyan\WPI\Plugin;

/**
 * Inspect and prepare store pages without replacing existing content.
 *
 * @author Hyyan Abo Fakher <hyyanaf@gmail.com>
 */
final class SetupPages
{
    /**
     * Get the four functional WooCommerce page types.
     *
     * @return array page type keys mapped to localized WooCommerce titles and slugs
     */
    public static function getPageTypes()
    {
        // phpcs:disable WordPress.WP.I18n.TextDomainMismatch -- These canonical page labels belong to WooCommerce's language packs.
        return array(
            'shop' => array(_x('Shop', 'Page title', 'woocommerce'), _x('shop', 'Page slug', 'woocommerce')),
            'cart' => array(_x('Cart', 'Page title', 'woocommerce'), _x('cart', 'Page slug', 'woocommerce')),
            'checkout' => array(_x('Checkout', 'Page title', 'woocommerce'), _x('checkout', 'Page slug', 'woocommerce')),
            'myaccount' => array(_x('My account', 'Page title', 'woocommerce'), _x('my-account', 'Page slug', 'woocommerce')),
        );
        // phpcs:enable WordPress.WP.I18n.TextDomainMismatch
    }

    /**
     * Get configured store languages when both dependencies are ready.
     *
     * @return array language slugs mapped to display names and locales
     */
    public static function getLanguages()
    {
        if (!Plugin::canActivate()) {
            return array();
        }
        $languages = array();
        foreach (pll_languages_list() as $slug) {
            $language = PLL()->model->get_language($slug);
            $languages[$slug] = array('name' => $language->name, 'locale' => $language->locale);
        }
        return $languages;
    }

    /**
     * Resolve a WooCommerce assignment to its default-language page.
     *
     * @param string $type WooCommerce page type key
     * @return \WP_Post|null assigned page, or null when it does not exist
     */
    public static function getDefaultPage($type)
    {
        $id = (int) get_option('woocommerce_' . $type . '_page_id');
        if ($id > 0 && function_exists('pll_get_post') && pll_default_language()) {
            $id = pll_get_post($id, pll_default_language()) ?: $id;
        }
        return $id > 0 ? get_post($id) : null;
    }

    /**
     * Inspect page language relationships and publication state without writing data.
     *
     * @return array page labels, source pages, and per-language status cells
     */
    public static function getPageStatus()
    {
        $rows = array();
        $languages = self::getLanguages();
        foreach (self::getPageTypes() as $type => $definition) {
            $base = self::getDefaultPage($type);
            $cells = array();
            foreach ($languages as $slug => $language) {
                $id = $base ? pll_get_post($base->ID, $slug) : 0;
                if (!$id && $base && !pll_get_post_language($base->ID) && $slug === pll_default_language()) {
                    $id = $base->ID;
                }
                $post = $id ? get_post($id) : null;
                $status = 'missing';
                if ($post && $post->post_type === 'page') {
                    $status = !pll_get_post_language($post->ID) ? 'unassigned' : $post->post_status;
                }
                $cells[$slug] = array('id' => $post ? $post->ID : 0, 'status' => $status);
            }
            $rows[$type] = array('label' => $definition[0], 'base' => $base, 'cells' => $cells);
        }
        return $rows;
    }

    /**
     * Check that each configured language has all four published store pages.
     *
     * @return bool true when every page relationship is ready
     */
    public static function isReady()
    {
        if (!Plugin::canActivate()) {
            return false;
        }
        foreach (self::getPageStatus() as $row) {
            foreach ($row['cells'] as $cell) {
                if ($cell['status'] !== 'publish') {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * Assign a selected page without stealing another language or relationship.
     *
     * @param string $type WooCommerce page type key
     * @param string $language registered Polylang language slug
     * @param int $id existing page ID
     * @return true|\WP_Error true on assignment, or a validation or permission error
     */
    public static function assignPage($type, $language, $id)
    {
        if (!current_user_can('manage_options') || !current_user_can('edit_post', $id)) {
            return new \WP_Error('forbidden', __('You do not have permission to assign this page.', 'woo-poly-integration'));
        }
        if (!isset(self::getPageTypes()[$type], self::getLanguages()[$language]) || get_post_type($id) !== 'page' ||
                in_array(get_post_status($id), array('trash', 'auto-draft'), true)) {
            return new \WP_Error('invalid_page', __('Choose an existing page in the selected language.', 'woo-poly-integration'));
        }
        $assignedLanguage = pll_get_post_language($id);
        if ($assignedLanguage && $assignedLanguage !== $language) {
            return new \WP_Error('wrong_language', __('This page belongs to a different language.', 'woo-poly-integration'));
        }
        $base = self::getDefaultPage($type);
        if ($language !== pll_default_language() && (!$base || !current_user_can('edit_post', $base->ID))) {
            return new \WP_Error('missing_source', __('Choose the default-language page first.', 'woo-poly-integration'));
        }
        foreach (self::getPageStatus() as $otherType => $row) {
            if ($otherType === $type) {
                continue;
            }
            foreach ($row['cells'] as $cell) {
                if ($cell['id'] === $id) {
                    return new \WP_Error('page_in_use', __('Use a separate page for each store function.', 'woo-poly-integration'));
                }
            }
        }
        $existing = pll_get_post_translations($id);
        $sourceId = $language === pll_default_language() ? $id : $base->ID;
        if (isset($existing[pll_default_language()]) && (int) $existing[pll_default_language()] !== $sourceId) {
            return new \WP_Error('already_linked', __('This page is already linked to a different translation. Review it in Pages first.', 'woo-poly-integration'));
        }
        $translations = pll_get_post_translations($sourceId);
        if (!empty($translations[$language]) && (int) $translations[$language] !== $id) {
            return new \WP_Error('already_linked', __('A translation is already linked. Edit that page in Pages instead.', 'woo-poly-integration'));
        }
        if ($language === pll_default_language()) {
            update_option('woocommerce_' . $type . '_page_id', $id);
        }
        pll_set_post_language($id, $language);
        $translations[$language] = $id;
        pll_save_post_translations($translations);
        return true;
    }

    /**
     * Create missing functional pages and translations after explicit authorization.
     *
     * Existing page content and translation links are preserved. Repeated calls
     * reuse linked pages, including after a partially completed request.
     *
     * @return array|\WP_Error created translation count and warnings, or a setup error
     */
    public static function createMissingPages()
    {
        if (!Plugin::canActivate() || !current_user_can('manage_options') ||
                !current_user_can('edit_pages') || !current_user_can('publish_pages')) {
            return new \WP_Error('forbidden', __('Finish the required plugins and languages steps first.', 'woo-poly-integration'));
        }
        $missing = array();
        foreach (self::getPageStatus() as $type => $row) {
            if ($row['base'] && ($row['base']->post_type !== 'page' ||
                    !current_user_can('edit_post', $row['base']->ID) ||
                    (pll_get_post_language($row['base']->ID) && pll_get_post_language($row['base']->ID) !== pll_default_language()))) {
                return new \WP_Error('invalid_source', __('Review the assigned default-language pages before creating translations.', 'woo-poly-integration'));
            }
            foreach ($row['cells'] as $cell) {
                if ($cell['id'] && !current_user_can('edit_post', $cell['id'])) {
                    return new \WP_Error('forbidden', __('You cannot edit one of the linked pages.', 'woo-poly-integration'));
                }
            }
            if (!$row['base']) {
                $missing[$type] = true;
            }
        }
        $warnings = self::installLanguagePacks();
        if ($missing) {
            // Native WooCommerce creation handles blocks, page assignments and duplicate detection.
            $filter = static function ($pages) use ($missing) {
                return array_intersect_key($pages, $missing);
            };
            add_filter('woocommerce_create_pages', $filter, PHP_INT_MAX);
            try {
                \WC_Install::create_pages();
            } finally {
                remove_filter('woocommerce_create_pages', $filter, PHP_INT_MAX);
            }
        }
        $created = 0;
        foreach (self::getPageTypes() as $type => $definition) {
            $base = self::getDefaultPage($type);
            if (!$base || $base->post_status !== 'publish') {
                continue;
            }
            if (!pll_get_post_language($base->ID)) {
                pll_set_post_language($base->ID, pll_default_language());
            }
            $translations = pll_get_post_translations($base->ID);
            $translations[pll_default_language()] = $base->ID;
            foreach (self::getLanguages() as $slug => $language) {
                if ($slug === pll_default_language() || !empty($translations[$slug])) {
                    continue;
                }
                switch_to_locale($language['locale']);
                try {
                    $localized = self::getPageTypes()[$type];
                    $title = $localized[0];
                    $name = $localized[1];
                } finally {
                    restore_previous_locale();
                }
                $id = wp_insert_post(wp_slash(array(
                    'post_type' => 'page',
                    'post_status' => 'publish',
                    'post_author' => get_current_user_id(),
                    'post_title' => $title,
                    'post_name' => $name,
                    'post_content' => $base->post_content,
                    'comment_status' => 'closed',
                )), true);
                if (is_wp_error($id)) {
                    return $id;
                }
                pll_set_post_language($id, $slug);
                $translations[$slug] = $id;
                // Save after each page so a retry cannot create a second translation.
                pll_save_post_translations($translations);
                ++$created;
            }
        }
        update_option('wpi_wcpagecheck_passed', self::isReady());
        return array('created' => $created, 'warnings' => $warnings);
    }

    /**
     * Install available WooCommerce language packs using the WordPress upgrader.
     *
     * Missing packs do not prevent functional pages from being created. The setup
     * result explains that their titles and custom text still need review.
     *
     * @return array translated warnings for unavailable language packs
     */
    private static function installLanguagePacks()
    {
        $missing = array();
        foreach (self::getLanguages() as $language) {
            $locale = $language['locale'];
            if ($locale !== 'en_US' && !is_readable(WP_LANG_DIR . '/plugins/woocommerce-' . $locale . '.mo') &&
                    !is_readable(WP_LANG_DIR . '/plugins/woocommerce-' . $locale . '.l10n.php')) {
                $missing[$locale] = $language['name'];
            }
        }
        if (!$missing) {
            return array();
        }
        require_once ABSPATH . 'wp-admin/includes/translation-install.php';
        require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
        $available = translations_api('plugins', array('slug' => 'woocommerce', 'version' => WC()->version));
        foreach ($missing as $locale => $name) {
            if (!is_wp_error($available) && current_user_can('install_languages') && wp_is_file_mod_allowed('download_language_pack')) {
                foreach ($available['translations'] ?? array() as $translation) {
                    if ($translation['language'] !== $locale) {
                        continue;
                    }
                    $translation['type'] = 'plugin';
                    $translation['slug'] = 'woocommerce';
                    $upgrader = new \Language_Pack_Upgrader(new \Automatic_Upgrader_Skin());
                    $result = $upgrader->upgrade((object) $translation, array('clear_update_cache' => false));
                    if ($result && !is_wp_error($result)) {
                        unset($missing[$locale]);
                    }
                    break;
                }
            }
        }
        $warnings = array();
        foreach ($missing as $name) {
            $warnings[] = sprintf(
                /* translators: %s: language display name. */
                __('The WooCommerce language pack for %s could not be installed. Update translations in Dashboard → Updates and review the page titles.', 'woo-poly-integration'), $name
            );
        }
        return $warnings;
    }
}
