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
 * Plugin Hooks Interface.
 *
 * @author Hyyan Abo Fakher <hyyanaf@gmail.com>
 */
interface HooksInterface
{
    /**
     * Product Meta Sync Filter.
     *
     * The filter is fired before product meta array is passed to polylang
     * to handle sync.
     *
     * The filter receive one parameter which is the meta array
     *
     * for instance :
     * <code>
     * add_filter(Hyyan\WPI\HooksInterface::PRODUCT_META_SYNC_FILTER,function($meta=array()) {
     *
     *      // do whatever you want
     *
     *      return $meta;
     * });
     * </code>
     */
    const PRODUCT_META_SYNC_FILTER = 'woo-poly.product.metaSync';

    /**
     * Fields Locker Selectors Filter.
     *
     * The filter will be fired when the fields locker builds its selectors list
     * allowing other plugins to extend this list
     *
     * for instance :
     * <code>
     * add_filter(HooksInterface::FIELDS_LOCKER_SELECTORS_FILTER,function($selectors=array()) {
     *
     *      $selectors[] = '.my_field_to_lock';
     *
     *      return $selectors;
     * });
     */
    const FIELDS_LOCKER_SELECTORS_FILTER = 'woo-poly.fieldsLockerSelectors';

    /**
     * Fields Locker Variable Exclude Selectors Filter.
     *
     * This filter may be used to exclude some variation attributes from being locked.
     */
    const FIELDS_LOCKER_VARIABLE_EXCLUDE_SELECTORS_FILTER = 'woo-poly.fieldsLockerVariableExcludeSelectors';

    /**
     * Product Sync Category Custom Fields Action.
     *
     * The action will be fired when the plugin attemps to sync default product
     * category custom fields (dispplay_type,thumbinal_id)
     *
     * The action can be used to update extra custom fields if they exist
     *
     * for instance :
     * <code>
     *
     * add_action(
     *      HooksInterface::PRODUCT_SYNC_CATEGORY_CUSTOM_FIELDS,
     *      function (\Hyyan\WPI\Taxonomies $tax , $termID) {
     *
     *        if (isset($_POST['my_field_name'])) {
     *            $tax->doSyncProductCatCustomFields(
     *                      $termID
     *                     , 'my_field_name'
     *                     , esc_attr($_POST['my_field_name'])
     *             );
     *        }
     *
     *      }
     * );
     * </code>
     */
    const PRODUCT_SYNC_CATEGORY_CUSTOM_FIELDS = 'woo-poly.product.syncCategoryCustomFields';

    /**
     * Product Copy Category Custom Fields.
     *
     * The action is fired when new translatin is being added for product category
     *
     * The action can be used to copy catefory custom fields from give category
     * ID to its transation
     *
     * for instance :
     *
     * <code>
     * add_action(HooksInterface::PRODUCT_COPY_CATEGORY_CUSTOM_FIELDS,function ($categoryID) {
     *
     *        // do whatever you want here
     * });
     * </code>
     */
    const PRODUCT_COPY_CATEGORY_CUSTOM_FIELDS = 'woo-poly.product.copyCategoryCustomFields';

    /**
     * Pages List.
     *
     * The filter id fired before the list of woocommerce page names are passed
     * to ploylang in order to handle their translation
     *
     * for instance :
     * <code>
     * add_filter(Hyyan\WPI\HooksInterface::PAGES_LIST,function (array $pages) {
     *
     *      // do whatever you want
     *      $pages [] = 'shop';
     *
     *      return $pages;
     * });
     * </code>
     */
    const PAGES_LIST = 'woo-poly.pages.list';

    /**
     * Settings Sections Filter.
     *
     * The filter is fired when settings section are being built, to ler other
     * plugins add their own sections
     *
     * for instance :
     * <code>
     * add_filter(HooksInterface::SETTINGS_SECTIONS_FILTER,function (array $sections) {
     *
     *      // Add your section
     *
     *      return $sections;
     * });
     * </code>
     */
    const SETTINGS_SECTIONS_FILTER = 'woo-poly.settings.sections';

    /**
     * Settings Fields Filter.
     *
     * The filter is fired when settings fields are being built, to ler other
     * plugins add their own fields
     *
     * for instance :
     * <code>
     * add_filter(HooksInterface::SETTINGS_FIELDS_FILTER,function (array $fields) {
     *
     *      // Add your fields
     *
     *      return $fields;
     * });
     * </code>
     */
    const SETTINGS_FIELDS_FILTER = 'woo-poly.settings.fields';

    /**
     * Language Repo URL Filter.
     *
     * The filter is fired before using the default language repo url.
     */
    const LANGUAGE_REPO_URL_FILTER = 'woo-poly.language.repoUrl';

    /**
     * Load Payment Gateway Extention.
     *
     * The action is fired when this plugin is initialised and allows other plugins
     * to load payment gateways extentions or change the gateway object to
     * enable Polylang support.
     *
     * The action can be used to load a class extention for a given payment gateway
     *
     * for instance :
     *
     * <code>
     * add_action(HooksInterface::GATEWAY_LOAD_EXTENTION . $gateway->id,function ($gateway, $available_gateways) {
     *
     *        // do whatever you want here
     * });
     * </code>
     */
    const GATEWAY_LOAD_EXTENTION = 'woo-poly.gateway.loadClassExtention.';

    /**
     * Product Variation Copy Meta Action.
     *
     * This action gets fired as soon as variation meta is being copied.
     */
    const PRODUCT_VARIATION_COPY_META_ACTION = 'woo-poly.product.variation.copyMeta';

    /**
     * Product Disabled Meta Sync Filter.
     *
     * The filter is fired while excluding certain meta from being handled by PolyLang.
     *
     * The filter receives one parameter which is the meta array
     *
     * for instance :
     * <code>
     * add_filter(Hyyan\WPI\HooksInterface::PRODUCT_DISABLED_META_SYNC_FILTER,function($meta=array()) {
     *
     *      // do whatever you want
     *
     *      return $meta;
     * });
     * </code>
     */
    const PRODUCT_DISABLED_META_SYNC_FILTER = 'woo-poly.product.disabledMetaSync';

    /**
     * Emails Translatable Filter.
     *
     * This filter may be used to support translation for custom email templates (ids).
     */
    const EMAILS_TRANSLATABLE_FILTER = 'woo-poly.Emails.translatableEmails';

    /**
     * Emails Default Settings Filter.
     *
     * This filter may be used to adjust default settings for email templates.
     */
    const EMAILS_DEFAULT_SETTINGS_FILTER = 'woo-poly.Emails.defaultSettings';

    /**
     * Emails Translatable Action.
     *
     * This action fires after our plugin has added filters to translate subjects and headings for email.
     */
    const EMAILS_TRANSLATION_ACTION = 'woo-poly.Emails.translation';

    /**
     * Emails Switch Language Action.
     *
     * This filter fires before the language is being switched so that plugins may unload their language file.
     */
    const EMAILS_SWITCH_LANGUAGE_ACTION = 'woo-poly.Emails.switchLanguage';

    /**
     * Emails After Switch Language Action.
     *
     * This filter fires after the language has been switched so that plugins may reload their language file.
     */
    const EMAILS_AFTER_SWITCH_LANGUAGE_ACTION = 'woo-poly.Emails.afterSwitchLanguage';

    /**
     * Emails Order Find Replace Find Filter.
     *
     * This filter may be used to support custom variables in subjects/headings (find).
     */
    const EMAILS_ORDER_FIND_REPLACE_FIND_FILTER = 'woo-poly.Emails.orderFindReplaceFind';

    /**
     * Emails Order Find Replace Replace Filter.
     *
     * This filter may be used to support custom variables in subjects/headings (replace).
     */
    const EMAILS_ORDER_FIND_REPLACE_REPLACE_FILTER = 'woo-poly.Emails.orderFindReplaceReplace';
}
