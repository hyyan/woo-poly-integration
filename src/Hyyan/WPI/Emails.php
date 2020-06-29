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
use Hyyan\WPI\Utilities;

/**
 * Emails.
 *
 * Handle WooCommerce emails
 *
 * @author Hyyan Abo Fakher <hyyanaf@gmail.com>
 */
class Emails
{

    /** @var array Array of email types */
    public $emails;
    public $switched_lang = '';

    /*
     * gets the woocommerce default value for the string type:
     * woocommerce should be switched to order language before calling
     *
     * @param string $string_type <subject | heading | additional_content> of $email_type, e.g. subject, subject_paid
     * @param string $email_type  Email type, e.g. new_order, customer_invoice
     *
     * @return string	WooCommerce default value for current language
     */
    public function get_default_setting($string_type, $email_type)
    {
        $wc_emails = \WC_Emails::instance();
        $emails = $wc_emails->get_emails();
        $emailobj = new \stdClass();
        $return = '';

        switch ($email_type) {
            case 'new_order':
                $emailobj = $emails['WC_Email_New_Order'];
                break;
            case 'cancelled_order':
                $emailobj = $emails['WC_Email_Cancelled_Order'];
                break;
            case 'failed_order':
                $emailobj = $emails['WC_Email_Failed_Order'];
                break;
            case 'customer_on_hold_order':
                $emailobj = $emails['WC_Email_Customer_On_Hold_Order'];
                break;
            case 'customer_processing_order':
                $emailobj = $emails['WC_Email_Customer_Processing_Order'];
                break;
            case 'customer_completed_order':
                $emailobj = $emails['WC_Email_Customer_Completed_Order'];
                break;
            case 'customer_refunded_order':
                $emailobj = $emails['WC_Email_Customer_Refunded_Order'];
                break;
            case 'customer_invoice':
                $emailobj = $emails['WC_Email_Customer_Invoice'];
                break;
            case 'customer_note':
                $emailobj = $emails['WC_Email_Customer_Note'];
                break;
            case 'customer_reset_password':
                $emailobj = $emails['WC_Email_Customer_Reset_Password'];
                break;
            case 'customer_new_account':
                $emailobj = $emails['WC_Email_Customer_New_Account'];
                break;
            default:
            //no action possible
        }

        if (is_a($emailobj, 'WC_Email')) {
            switch ($string_type) {
                case 'subject_full': //variant for refunded order
                case 'subject':
                    $return = $emailobj->get_default_subject();
                    break;
                case 'heading_full': //variant for refunded order
                case 'heading':
                    $return = $emailobj->get_default_heading();
                    break;
                case 'additional_content':
                    $return = $emailobj->get_default_additional_content();
                    break;
                case 'subject_partial':  //variant for refunded order
                case 'subject_paid':    //variant for customer invoice
                    $return = $emailobj->get_default_subject(true);
                    break;
                case 'heading_partial': //variant for refunded order
                case 'heading_paid':    //variant for customer invoice
                    $return = $emailobj->get_default_heading(true);
                    break;
                case 'subject':
                    $return = $emailobj->get_default_subject();
                    break;
            }
        }
        return apply_filters(HooksInterface::EMAILS_DEFAULT_SETTING_FILTER, $return, $string_type, $email_type);
    }
    /**
     * Construct object.
     */
    public function __construct()
    {
        if ('on' === Settings::getOption('emails', Features::getID(), 'on')) {

            // Register WooCommerce email subjects and headings in polylang strings translations table
            $this->registerEmailStringsForTranslation(); // called only after all plugins are loaded
            // Translate WooCommerce email subjects and headings to the order language
            // new order
            add_filter('woocommerce_email_subject_new_order', array($this, 'filter_email_subject'), 10, 3);
            add_filter('woocommerce_email_heading_new_order', array($this, 'filter_email_heading'), 10, 3);
            add_filter('woocommerce_email_recipient_new_order', array($this, 'filter_email_recipient'), 10, 3);
            add_filter('woocommerce_email_additional_content_new_order', array($this, 'filter_email_additional_content'), 10, 3);

            // processing order
            add_filter('woocommerce_email_subject_customer_processing_order', array($this, 'filter_email_subject'), 10, 3);
            add_filter('woocommerce_email_heading_customer_processing_order', array($this, 'filter_email_heading'), 10, 3);
            add_filter('woocommerce_email_additional_content_customer_processing_order', array($this, 'filter_email_additional_content'), 10, 3);
            // refunded order
            add_filter('woocommerce_email_subject_customer_refunded_order', array($this, 'filter_email_subject'), 10, 3);
            add_filter('woocommerce_email_heading_customer_refunded_order', array($this, 'filter_email_heading'), 10, 3);
            add_filter('woocommerce_email_additional_content_customer_refunded_order', array($this, 'filter_email_additional_content'), 10, 3);
            // special case partially refunded order
            add_filter('woocommerce_email_additional_content_customer_partially_refunded_order', array($this, 'filter_email_additional_content'), 10, 3);
            // customer note
            add_filter('woocommerce_email_subject_customer_note', array($this, 'filter_email_subject'), 10, 3);
            add_filter('woocommerce_email_heading_customer_note', array($this, 'filter_email_heading'), 10, 3);
            add_filter('woocommerce_email_additional_content_customer_note', array($this, 'filter_email_additional_content'), 10, 3);
            // customer invoice
            add_filter('woocommerce_email_subject_customer_invoice', array($this, 'filter_email_subject'), 10, 3);
            add_filter('woocommerce_email_heading_customer_invoice', array($this, 'filter_email_heading'), 10, 3);
            add_filter('woocommerce_email_additional_content_customer_invoice', array($this, 'filter_email_additional_content'), 10, 3);
            // customer invoice paid
            add_filter('woocommerce_email_subject_customer_invoice_paid', array($this, 'filter_email_subject'), 10, 3);
            add_filter('woocommerce_email_heading_customer_invoice_paid', array($this, 'filter_email_heading'), 10, 3);
            add_filter('woocommerce_email_additional_content_customer_invoice_paid', array($this, 'filter_email_additional_content'), 10, 3);
            // completed order
            add_filter('woocommerce_email_subject_customer_completed_order', array($this, 'filter_email_subject'), 10, 3);
            add_filter('woocommerce_email_heading_customer_completed_order', array($this, 'filter_email_heading'), 10, 3);
            add_filter('woocommerce_email_additional_content_customer_completed_order', array($this, 'filter_email_additional_content'), 10, 3);
            // new account
            add_filter('woocommerce_email_subject_customer_new_account', array($this, 'filter_email_subject'), 10, 3);
            add_filter('woocommerce_email_heading_customer_new_account', array($this, 'filter_email_heading'), 10, 3);
            add_filter('woocommerce_email_additional_content_customer_new_account', array($this, 'filter_email_additional_content'), 10, 3);
            // reset password
            add_filter('woocommerce_email_subject_customer_reset_password', array($this, 'filter_email_subject'), 10, 3);
            add_filter('woocommerce_email_heading_customer_reset_password', array($this, 'filter_email_heading'), 10, 3);
            add_filter('woocommerce_email_additional_content_customer_reset_password', array($this, 'filter_email_additional_content'), 10, 3);

            // On Hold Order
            add_filter('woocommerce_email_subject_customer_on_hold_order', array($this, 'filter_email_subject'), 10, 3);
            add_filter('woocommerce_email_heading_customer_on_hold_order', array($this, 'filter_email_heading'), 10, 3);
            add_filter('woocommerce_email_additional_content_customer_on_hold_order', array($this, 'filter_email_additional_content'), 10, 3);

            // Cancelled Order
            add_filter('woocommerce_email_subject_cancelled_order', array($this, 'filter_email_subject'), 10, 3);
            add_filter('woocommerce_email_heading_cancelled_order', array($this, 'filter_email_heading'), 10, 3);
            add_filter('woocommerce_email_recipient_cancelled_order', array($this, 'filter_email_recipient'), 10, 3);
            add_filter('woocommerce_email_additional_content_cancelled_order', array($this, 'filter_email_additional_content'), 10, 3);

            // Failed Order
            add_filter('woocommerce_email_subject_failed_order', array($this, 'filter_email_subject'), 10, 3);
            add_filter('woocommerce_email_heading_failed_order', array($this, 'filter_email_heading'), 10, 3);
            add_filter('woocommerce_email_recipient_failed_order', array($this, 'filter_email_recipient'), 10, 3);
            add_filter('woocommerce_email_additional_content_failed_order', array($this, 'filter_email_additional_content'), 10, 3);

            // strings for all emails
            add_filter('woocommerce_email_footer_text', array($this, 'translateCommonString'));
            add_filter('woocommerce_email_from_address', array($this, 'translateCommonString'));
            add_filter('woocommerce_email_from_name', array($this, 'translateCommonString'));

            //reset language switch on woocommerce events
            add_filter('woocommerce_email_setup_locale', array($this, 'reset_lang_switch'));
            add_filter('woocommerce_email_restore_locale', array($this, 'reset_lang_switch'));

            do_action(HooksInterface::EMAILS_TRANSLATION_ACTION, $this);
        }
    }
    /**
     * Register woocommerce email subjects and headings in polylang strings
     * translations table.
     */
    public function registerEmailStringsForTranslation()
    {
        $this->emails = apply_filters(HooksInterface::EMAILS_TRANSLATABLE_FILTER, array(
            'new_order',
            'cancelled_order',
            'failed_order',
            'customer_on_hold_order',
            'customer_processing_order',
            'customer_completed_order',
            'customer_refunded_order',
            'customer_invoice',
            'customer_note',
            'customer_reset_password',
            'customer_new_account',
            ), $this);

        // Register strings for translation and hook filters
        foreach ($this->emails as $email) {
            switch ($email) {
                case 'customer_refunded_order':
                    // Register strings
                    $this->registerString($email, '_partial');
                    $this->registerString($email, '_full');
                    break;

                case 'customer_invoice':
                    // Register strings
                    $this->registerString($email, '_paid');
                    $this->registerString($email);
                    break;

                case 'new_order':
                case 'cancelled_order':
                case 'failed_order':
                case 'customer_on_hold_order':
                case 'customer_processing_order':
                case 'customer_completed_order':
                case 'customer_note':
                case 'customer_reset_password':
                case 'customer_new_account':
                default:
                    // Register strings
                    $this->registerString($email);
                    break;
            }
        }

        //Register global email strings for translation
        $wc_emails = \WC_Emails::instance();
        $this->registerCommonString('woocommerce_email_footer_text', apply_filters('woocommerce_email_footer_text', get_option('woocommerce_email_footer_text')));
        $this->registerCommonString('woocommerce_email_from_name', $wc_emails->get_from_name());
        $this->registerCommonString('woocommerce_email_from_address', $wc_emails->get_from_address());
    }
    /**
     * Register email subjects and headings strings for translation in Polylang
     * Strings Translations table.
     *
     * Note: This function uses get_option to retrive the subject and heading
     * string from the WooCommerce Admin Settings page. get_option will return false
     * if the Admin user has not changed (nor saved) the default settings.
     *
     * @param string $email_type Email type
     * @param string $sufix      Additional string variation, e.g. invoice paid vs invoice
     */
    public function registerString($email_type, $suffix = '')
    {
        if (function_exists('pll_register_string')) {
            $settings = get_option('woocommerce_' . $email_type . '_settings');
            if ($settings) {
                if (isset($settings['subject' . $suffix])) {
                    pll_register_string('woocommerce_' . $email_type . '_subject' . $suffix, $settings['subject' . $suffix], __('WooCommerce Emails', 'woo-poly-integration'));
                }
                if (isset($settings['heading' . $suffix])) {
                    pll_register_string('woocommerce_' . $email_type . '_heading' . $suffix, $settings['heading' . $suffix], __('WooCommerce Emails', 'woo-poly-integration'));
                }
                if (isset($settings['additional_content'])) {
                    pll_register_string('woocommerce_' . $email_type . '_additional_content', $settings['additional_content'], __('WooCommerce Emails', 'woo-poly-integration'));
                }
                //recipient applies to shop emails New, Cancel and Failed order types
                if (isset($settings['recipient'])) {
                    pll_register_string('woocommerce_' . $email_type . '_recipient', $settings['recipient'], __('WooCommerce Emails', 'woo-poly-integration'));
                }
            }
        }
    }
    /**
     * Register common strings for all wooCommerce emails for translation in Polylang
     * Strings Translations table.
     *
     * Note: This function uses get_option to retrive the
     * string from the WooCommerce Admin Settings page. get_option will return false
     * if the Admin user has not changed (nor saved) the default settings.
     *
     *
     * @param string $email_type Email type
     * @param string $sufix      Additional string common to different email types
     */
    public function registerCommonString($setting, $default = '')
    {
        if (function_exists('pll_register_string')) {
            $value = get_option($setting);

            if (!($value)) {
                $value = $default;
            }
            if ($value) {
                pll_register_string($setting, $value, __('Woocommerce Emails', 'woo-poly-integration'));
            }
        }
    }
    /**
     * Filter Woocommerce email texts to the order or user language.
     *
     * @param string   $formatted_string   Candidate Subject or heading not translated but already with token replacements
     * @param WC_Order $object       Order object or User depending on email type
     * @param string   $string_type Type of string to translate <subject | heading>
     * @param string   $email_type  Email template
     *
     * @return string Translated string, returns the original $string on error
     */
    public function filter_email_additional_content($formatted_string, $object, $email)
    {
        return $this->translateEmailStringToObjectLanguage($formatted_string, $object, 'additional_content', $email);
    }
    /**
     * Translates Woocommerce email texts to the order language.
     *
     * @param string   $formatted_string      Subject or heading not translated but already with token replacements
     * @param WC_Order $object       Order object or User depending on email type
     * @param string   $string_type Type of string to translate <subject | heading>
     * @param string   $email_type  Email template
     *
     * @return string Translated string, returns the original $string on error
     */
    public function filter_email_heading($formatted_string, $object, $email)
    {
        return $this->translateEmailStringToObjectLanguage($formatted_string, $object, 'heading', $email);
    }
    /**
     * Translates Woocommerce email texts to the order language.
     *
     * @param string   $formatted_string      Subject or heading not translated but already with token replacements
     * @param WC_Order $object       Order object or User depending on email type
     * @param string   $string_type Type of string to translate <subject | heading>
     * @param string   $email_type  Email template
     *
     * @return string Translated string, returns the original $string on error
     */
    public function filter_email_subject($formatted_string, $object, $email)
    {
        return $this->translateEmailStringToObjectLanguage($formatted_string, $object, 'subject', $email);
    }
    /**
     * Translates Woocommerce email texts to the order language.
     *
     * @param string   $formatted_string      Subject or heading not translated but already with token replacements
     * @param WC_Order $object       Order object or User depending on email type
     * @param string   $string_type Type of string to translate <subject | heading>
     * @param string   $email_type  Email template
     *
     * @return string Translated string, returns the original $string on error
     */
    public function filter_email_recipient($formatted_string, $object, $email)
    {
        return $this->translateEmailStringToObjectLanguage($formatted_string, $object, 'recipient', $email);
    }

    /**
     * Potentially translate strings which are not email specific.
     * Assumes language will already have been switched using previous filters
     *
     * @param string   $subject Email footer text, from name or email in default language
     * @param WC_Order $order   Order object
     *
     * @return string Translated string
     */
    public function translateCommonString($email_string)
    {
        if (!function_exists('pll_register_string')) {
            return $email_string;
        }
        $trans = pll__($email_string);
        if ($trans) {
            return $trans;
        }
        return $email_string;
    }
    /**
     * Translates Woocommerce email texts to the order language.
     *
     * @param string   $formatted_string      Subject or heading not translated but already with token replacements
     * @param WC_Order $object       Order object or User depending on email type
     * @param string   $string_type Type of string to translate <subject | heading>
     * @param string   $email_type  Email template
     *
     * @return string Translated string, returns the original $string on error
     */
    public function maybeSwitchLanguage($target_object)
    {
        if ($this->switched_lang != '') {
            return $this->switched_lang;
        }
        //locale is filtered so if language is already switched
        //locale may already be in the correct language
        $locale = get_locale();
        $target_language = $locale;

        if (is_a($target_object, 'WC_Order')) {
            $target_language = pll_get_post_language($target_object->get_id(), 'locale');
        } else if (is_a($target_object, 'WP_User')) {
            $target_language = get_user_locale($target_object->ID);
        }
        Utilities::switchLocale($target_language);
        $this->switched_lang = $target_language;
        return $target_language;
    }
    /**
     * hooked to woocommerce email start/stop events to reset switched status
     */
    public function reset_lang_switch()
    {
        $this->switched_lang = '';
        return false;
    }
    /**
     * Translates Woocommerce email subjects and headings content to the order language.
     *
     * @param string   $formatted_string      Subject/heading/text not translated but already with token replacements
     * @param WC_Order $target_object       Order object or user object
     * @param string   $string_type Type of string to translate <subject | heading>
     * @param WC_Email $email_obj  Email template
     *
     * @return string Translated string, returns the original $string on error
     */
    public function translateEmailStringToObjectLanguage($formatted_string, $target_object, $string_type, $email_obj)
    {
        $target_language = $this->maybeSwitchLanguage($target_object);
        $email_type = $email_obj->id;

        //check for special cases for partial refunds and fully paid invoices
        switch ($email_type){
            case 'customer_partially_refunded_order':
                switch ($string_type){
                    case 'subject':
                    case 'heading':
                        $string_type .= '_partial';
                        break;
                }
                //email class for refunds changes id but this plugin never worked that way,
                //and saved settings for other fields should also be in common with customer_refunded_order
                $email_type = 'customer_refunded_order';  
                break;
            case 'customer_refunded_order':
                switch ($string_type){
                    case 'subject':
                    case 'heading':
                        $string_type .= '_full';
                        break;
                }
                break;
            case 'customer_invoice':
                switch ($string_type){
                    case 'subject':
                    case 'heading':
                        if ($target_object->has_status( wc_get_is_paid_statuses())){
                            $string_type .= '_paid';
                        }
                        break;
                }
                break;
        }
        
        //if custom string is set in WooCommerce admin settings
        $string_template = $this->getEmailSetting($string_type, $email_type);
        if ($string_template) {
            //check for a translation in polylang
            $string_template_translated = pll_translate_string($string_template, $target_language);
            //if there is translation, use it
            if ($string_template_translated != $string_template) {
                $string_template = $string_template_translated;
            }
        } else {
            $string_template = $this->get_default_setting($string_type, $email_type);
        }

        // No setting in Polylang strings translations table nor default string
        if (!$string_template) {
            return $formatted_string;
        }

        //perform standard replacements on template
        if (is_a($target_object, 'WC_Order')) {
            //for legacy compatibility
            $find = array();
            $replace = array();
            $find['order-date'] = '{order_date}';
            $replace['order-date'] = date_i18n(wc_date_format(), strtotime($target_object->get_date_created()));
            $formatted_string = str_replace(apply_filters(HooksInterface::EMAILS_ORDER_FIND_REPLACE_FIND_FILTER, $find, $target_object), 
                apply_filters(HooksInterface::EMAILS_ORDER_FIND_REPLACE_REPLACE_FILTER, $replace, $target_object), $string_template);
            //better solution, native WooCommerce call
            $formatted_string = $email_obj->format_string($formatted_string);
        }
        return $formatted_string;
    }
    /**
     * Get setting used to register string in the Polylang strings translation table.
     *
     * @param string $string_type <subject | heading | additional_content> of $email_type, e.g. subject, subject_paid
     * @param string $email_type  Email type, e.g. new_order, customer_invoice
     *
     * return $string|boolean Email setting from database if one is found, false otherwise
     */
    public function getEmailSetting($string_type, $email_type)
    {
        $settings = get_option('woocommerce_' . $email_type . '_settings');

        if ($settings && isset($settings[$string_type])) {
            return $settings[$string_type];
        } else {
            return false; // Setting not registered in string table (admin have NOT changed woocommerce default)
        }
    }
}
