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
 * Handle woocommerce emails
 *
 * @author Hyyan Abo Fakher <hyyanaf@gmail.com>
 */
class Emails
{

    /** @var array Array of email types */
    public $emails;

    /** @var array Array of email default settings */
    protected $default_settings;

    /**
     * Construct object.
     */
    public function __construct()
    {
        if ('on' === Settings::getOption('emails', Features::getID(), 'on')) {
            add_filter('plugin_locale', array($this, 'correctLocal'), 100);

            // Register woocommerce email subjects and headings in polylang strings translations table
            $this->registerEmailStringsForTranslation(); // called only after all plugins are loaded
            // Translate Woocommerce email subjects and headings to the order language
            // new order
            add_filter('woocommerce_email_subject_new_order', array($this, 'translateEmailSubjectNewOrder'), 10, 2);
            add_filter('woocommerce_email_heading_new_order', array($this, 'translateEmailHeadingNewOrder'), 10, 2);
            add_filter('woocommerce_email_recipient_new_order', array($this, 'translateEmailRecipientNewOrder'), 10, 2);

            // processing order
            add_filter('woocommerce_email_subject_customer_processing_order', array($this, 'translateEmailSubjectCustomerProcessingOrder'), 10, 2);
            add_filter('woocommerce_email_heading_customer_processing_order', array($this, 'translateEmailHeadingCustomerProcessingOrder'), 10, 2);
            // refunded order
            add_filter('woocommerce_email_subject_customer_refunded_order', array($this, 'translateEmailSubjectCustomerRefundedOrder'), 10, 2);
            add_filter('woocommerce_email_heading_customer_refunded_order', array($this, 'translateEmailHeadingCustomerRefundedOrder'), 10, 2);
            // customer note
            add_filter('woocommerce_email_subject_customer_note', array($this, 'translateEmailSubjectCustomerNote'), 10, 2);
            add_filter('woocommerce_email_heading_customer_note', array($this, 'translateEmailHeadingCustomerNote'), 10, 2);
            // customer invoice
            add_filter('woocommerce_email_subject_customer_invoice', array($this, 'translateEmailSubjectCustomerInvoice'), 10, 2);
            add_filter('woocommerce_email_heading_customer_invoice', array($this, 'translateEmailHeadingCustomerInvoice'), 10, 2);
            // customer invoice paid
            add_filter('woocommerce_email_subject_customer_invoice_paid', array($this, 'translateEmailSubjectCustomerInvoicePaid'), 10, 2);
            add_filter('woocommerce_email_heading_customer_invoice_paid', array($this, 'translateEmailHeadingCustomerInvoicePaid'), 10, 2);
            // completed order
            add_filter('woocommerce_email_subject_customer_completed_order', array($this, 'translateEmailSubjectCustomerCompletedOrder'), 10, 2);
            add_filter('woocommerce_email_heading_customer_completed_order', array($this, 'translateEmailHeadingCustomerCompletedOrder'), 10, 2);
            // new account
            add_filter('woocommerce_email_subject_customer_new_account', array($this, 'translateEmailSubjectCustomerNewAccount'), 10, 2);
            add_filter('woocommerce_email_heading_customer_new_account', array($this, 'translateEmailHeadingCustomerNewAccount'), 10, 2);
            // reset password
            add_filter('woocommerce_email_subject_customer_reset_password', array($this, 'translateEmailSubjectCustomerResetPassword'), 10, 2);
            add_filter('woocommerce_email_heading_customer_reset_password', array($this, 'translateEmailHeadingCustomerResetPassword'), 10, 2);

            // On Hold Order
            add_filter('woocommerce_email_subject_customer_on_hold_order', array($this, 'translateEmailSubjectCustomerOnHoldOrder'), 10, 2);
            add_filter('woocommerce_email_heading_customer_on_hold_order', array($this, 'translateEmailHeadingCustomerOnHoldOrder'), 10, 2);

            // Cancelled Order
            add_filter('woocommerce_email_subject_cancelled_order', array($this, 'translateEmailSubjectCancelOrder'), 10, 2);
            add_filter('woocommerce_email_heading_cancelled_order', array($this, 'translateEmailHeadingCancelOrder'), 10, 2);
            add_filter('woocommerce_email_recipient_cancelled_order', array($this, 'translateEmailRecipientCancelOrder'), 10, 2);

            // Failed Order
            add_filter('woocommerce_email_subject_failed_order', array($this, 'translateEmailSubjectFailedOrder'), 10, 2);
            add_filter('woocommerce_email_heading_failed_order', array($this, 'translateEmailHeadingFailedOrder'), 10, 2);
            add_filter('woocommerce_email_recipient_failed_order', array($this, 'translateEmailRecipientFailedOrder'), 10, 2);

            // strings for all emails
            add_filter('woocommerce_email_footer_text', array($this, 'translateCommonString'));
            add_filter('woocommerce_email_from_address', array($this, 'translateCommonString'));
            add_filter('woocommerce_email_from_name', array($this, 'translateCommonString'));

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
            'customer_processing_order',
            'customer_refunded_order',
            'customer_note',
            'customer_invoice',
            'customer_completed_order',
            'customer_new_account',
            'customer_reset_password',
            'customer_on_hold_order',
            'cancelled_order',
            'failed_order',
        ), $this);

        $this->default_settings = apply_filters(HooksInterface::EMAILS_DEFAULT_SETTINGS_FILTER, array(
            'new_order_subject'                             => __('[{site_title}] New customer order ({order_number}) - {order_date}', 'woocommerce'),
            'new_order_heading'                             => __('New customer order', 'woocommerce'),
            'customer_processing_order_subject'             => __('Your {site_title} order receipt from {order_date}', 'woocommerce'),
            'customer_processing_order_heading'             => __('Thank you for your order', 'woocommerce'),
            'customer_refunded_order_subject_partial'       => __('Your {site_title} order from {order_date} has been partially refunded', 'woocommerce'),
            'customer_refunded_order_heading_partial'       => __('Your order has been partially refunded', 'woocommerce'),
            'customer_refunded_order_subject_full'          => __('Your {site_title} order from {order_date} has been refunded', 'woocommerce'),
            'customer_refunded_order_heading_full'          => __('Your order has been fully refunded', 'woocommerce'),
            'customer_note_subject'                         => __('Note added to your {site_title} order from {order_date}', 'woocommerce'),
            'customer_note_heading'                         => __('A note has been added to your order', 'woocommerce'),
            'customer_invoice_subject_paid'                 => __('Your {site_title} order from {order_date}', 'woocommerce'),
            'customer_invoice_heading_paid'                 => __('Order {order_number} details', 'woocommerce'),
            'customer_invoice_subject'                      => __('Invoice for order {order_number} from {order_date}', 'woocommerce'),
            'customer_invoice_heading'                      => __('Invoice for order {order_number}', 'woocommerce'),
            'customer_completed_order_subject'              => __('Your {site_title} order from {order_date} is complete', 'woocommerce'),
            'customer_completed_order_heading'              => __('Your order is complete', 'woocommerce'),
            'customer_completed_order_subject_downloadable' => __('Your {site_title} order from {order_date} is complete - download your files', 'woocommerce'),
            'customer_completed_order_heading_downloadable' => __('Your order is complete - download your files', 'woocommerce'),
            'customer_new_account_subject'                  => __('Your account on {site_title}', 'woocommerce'),
            'customer_new_account_heading'                  => __('Welcome to {site_title}', 'woocommerce'),
            'customer_reset_password_subject'               => __('Password Reset for {site_title}', 'woocommerce'),
            'customer_reset_password_heading'               => __('Password Reset Instructions', 'woocommerce'),
            'customer_on_hold_order_subject'                => __('Your {site_title} order receipt from {order_date}', 'woocommerce'),
            'customer_on_hold_order_heading'                => __('Thank you for your order', 'woocommerce'),
            'cancelled_order_subject'                       => __('[{site_title}] Cancelled order ({order_number})', 'woocommerce'),
            'cancelled_order_heading'                       => __('Cancelled order', 'woocommerce'),
            'failed_order_subject'                          => __('[{site_title}] Failed order ({order_number})', 'woocommerce'),
            'failed_order_heading'                          => __('Failed order', 'woocommerce'),
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

                case 'customer_completed_order':
                    $this->registerString($email, '_downloadable');
                    $this->registerString($email);
                    break;

                case 'new_order':
                case 'cancelled_order':
                case 'failed_order':
                case 'customer_processing_order':
                case 'customer_note':
                case 'customer_new_account':
                case 'customer_reset_password':
                case 'customer_on_hold_order':
                default:
                    // Register strings
                    $this->registerString($email);
                    break;
            }
        }

        //Register global email strings for translation
        $this->registerCommonString('woocommerce_email_footer_text', sprintf(__('%s - Powered by WooCommerce', 'woocommerce'), get_bloginfo('name', 'display'))
        );
        $this->registerCommonString('woocommerce_email_from_name', esc_attr(get_bloginfo('name', 'display')));
        $this->registerCommonString('woocommerce_email_from_address', get_option('admin_email'));
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
    public function registerString($email_type, $sufix = '')
    {
        if (function_exists('pll_register_string')) {
            $settings = get_option('woocommerce_' . $email_type . '_settings');
            if ($settings) {
                if (isset($settings['subject' . $sufix]) && isset($settings['heading' . $sufix])) {
                    pll_register_string('woocommerce_' . $email_type . '_subject' . $sufix, $settings['subject' . $sufix], __('Woocommerce Emails', 'woo-poly-integration'));
                    pll_register_string('woocommerce_' . $email_type . '_heading' . $sufix, $settings['heading' . $sufix], __('Woocommerce Emails', 'woo-poly-integration'));
                }
                //recipient applies to shop emails New, Cancel and Failed order types
                if (isset($settings['recipient' . $sufix])) {
                    pll_register_string('woocommerce_' . $email_type . '_recipient' . $sufix, $settings['recipient' . $sufix], __('Woocommerce Emails', 'woo-poly-integration'));
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
     * @param string $sufix      Additional string variation, e.g. invoice paid vs invoice
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
     * Translate to the order language, the email subject of new order email notifications to the admin.
     *
     * @param string   $subject Email subject in default language
     * @param WC_Order $order   Order object
     *
     * @return string Translated subject
     */
    public function translateCommonString($email_string)
    {
        if (function_exists('pll_register_string')) {
            $lang  = pll_current_language('locale');
            $trans = pll__($email_string);
            if ($trans) {
                return $trans;
            } else {
                return $email_string;
            }
        }
    }

    /**
     * Translate to the order language, the email subject of processing order email notifications to the customer.
     *
     * @param string   $subject Email subject in default language
     * @param WC_Order $order   Order object
     *
     * @return string Translated subject
     */
    public function translateEmailSubjectCustomerOnHoldOrder($subject, $order)
    {
        return $this->translateEmailStringToOrderLanguage($subject, $order, 'subject', 'customer_on_hold_order');
    }

    /**
     * Translate to the order language, the email heading of processing order email notifications to the customer.
     *
     * @param string   $heading Email heading in default language
     * @param WC_Order $order   Order object
     *
     * @return string Translated heading
     */
    public function translateEmailHeadingCustomerOnHoldOrder($heading, $order)
    {
        return $this->translateEmailStringToOrderLanguage($heading, $order, 'heading', 'customer_on_hold_order');
    }

    /**
     * Translate to the order language, the email subject of Cancel order email notifications to the admin.
     *
     * @param string   $subject Email subject in default language
     * @param WC_Order $order   Order object
     *
     * @return string Translated subject
     */
    public function translateEmailRecipientFailedOrder($subject, $order)
    {
        return $this->translateEmailStringToOrderLanguage($subject, $order, 'recipient', 'failed_order');
    }

    /**
     * Translate to the order language, the email subject of Failed order email notifications to the admin.
     *
     * @param string   $subject Email subject in default language
     * @param WC_Order $order   Order object
     *
     * @return string Translated subject
     */
    public function translateEmailSubjectFailedOrder($subject, $order)
    {
        return $this->translateEmailStringToOrderLanguage($subject, $order, 'subject', 'failed_order');
    }

    /**
     * Translate to the order language, the email heading of Failed order email notifications to the admin.
     *
     * @param string   $heading Email heading in default language
     * @param WC_Order $order   Order object
     *
     * @return string Translated heading
     */
    public function translateEmailHeadingFailedOrder($heading, $order)
    {
        return $this->translateEmailStringToOrderLanguage($heading, $order, 'heading', 'failed_order');
    }

    /**
     * Translate to the order language, the email subject of Cancel order email notifications to the admin.
     *
     * @param string   $subject Email subject in default language
     * @param WC_Order $order   Order object
     *
     * @return string Translated subject
     */
    public function translateEmailRecipientCancelOrder($subject, $order)
    {
        return $this->translateEmailStringToOrderLanguage($subject, $order, 'recipient', 'cancelled_order');
    }

    /**
     * Translate to the order language, the email subject of Cancel order email notifications to the admin.
     *
     * @param string   $subject Email subject in default language
     * @param WC_Order $order   Order object
     *
     * @return string Translated subject
     */
    public function translateEmailSubjectCancelOrder($subject, $order)
    {
        return $this->translateEmailStringToOrderLanguage($subject, $order, 'subject', 'cancelled_order');
    }

    /**
     * Translate to the order language, the email heading of Cancel order email notifications to the admin.
     *
     * @param string   $heading Email heading in default language
     * @param WC_Order $order   Order object
     *
     * @return string Translated heading
     */
    public function translateEmailHeadingCancelOrder($heading, $order)
    {
        return $this->translateEmailStringToOrderLanguage($heading, $order, 'heading', 'cancelled_order');
    }

    /**
     * Translate to the order language, the email subject of Cancel order email notifications to the admin.
     *
     * @param string   $subject Email subject in default language
     * @param WC_Order $order   Order object
     *
     * @return string Translated subject
     */
    public function translateEmailRecipientNewOrder($subject, $order)
    {
        return $this->translateEmailStringToOrderLanguage($subject, $order, 'recipient', 'new_order');
    }

    /**
     * Translate to the order language, the email subject of new order email notifications to the admin.
     *
     * @param string   $subject Email subject in default language
     * @param WC_Order $order   Order object
     *
     * @return string Translated subject
     */
    public function translateEmailSubjectNewOrder($subject, $order)
    {
        return $this->translateEmailStringToOrderLanguage($subject, $order, 'subject', 'new_order');
    }

    /**
     * Translate to the order language, the email heading of new order email notifications to the admin.
     *
     * @param string   $heading Email heading in default language
     * @param WC_Order $order   Order object
     *
     * @return string Translated heading
     */
    public function translateEmailHeadingNewOrder($heading, $order)
    {
        return $this->translateEmailStringToOrderLanguage($heading, $order, 'heading', 'new_order');
    }

    /**
     * Translate to the order language, the email subject of processing order email notifications to the customer.
     *
     * @param string   $subject Email subject in default language
     * @param WC_Order $order   Order object
     *
     * @return string Translated subject
     */
    public function translateEmailSubjectCustomerProcessingOrder($subject, $order)
    {
        return $this->translateEmailStringToOrderLanguage($subject, $order, 'subject', 'customer_processing_order');
    }

    /**
     * Translate to the order language, the email heading of processing order email notifications to the customer.
     *
     * @param string   $heading Email heading in default language
     * @param WC_Order $order   Order object
     *
     * @return string Translated heading
     */
    public function translateEmailHeadingCustomerProcessingOrder($heading, $order)
    {
        return $this->translateEmailStringToOrderLanguage($heading, $order, 'heading', 'customer_processing_order');
    }

    /**
     * Translate to the order language, the email subject of refunded order email notifications to the customer.
     *
     * @param string   $subject Email subject in default language
     * @param WC_Order $order   Order object
     *
     * @return string Translated subject
     */
    public function translateEmailSubjectCustomerRefundedOrder($subject, $order)
    {
        if (!empty($order) && $this->isFullyRefunded($order)) {
            return $this->translateEmailStringToOrderLanguage($subject, $order, 'subject_full', 'customer_refunded_order');
        } else {
            return $this->translateEmailStringToOrderLanguage($subject, $order, 'subject_partial', 'customer_refunded_order');
        }
    }

    /**
     * Translate to the order language, the email heading of refunded order email notifications to the customer.
     *
     * @param string   $heading Email heading in default language
     * @param WC_Order $order   Order object
     *
     * @return string Translated heading
     */
    public function translateEmailHeadingCustomerRefundedOrder($subject, $order)
    {
        if (!empty($order) && $this->isFullyRefunded($order)) {
            return $this->translateEmailStringToOrderLanguage($subject, $order, 'heading_full', 'customer_refunded_order');
        } else {
            return $this->translateEmailStringToOrderLanguage($subject, $order, 'heading_partial', 'customer_refunded_order');
        }
    }

    /**
     * Translate to the order language, the email subject of customer note emails.
     *
     * @param string   $subject Email subject in default language
     * @param WC_Order $order   Order object
     *
     * @return string Translated subject
     */
    public function translateEmailSubjectCustomerNote($subject, $order)
    {
        return $this->translateEmailStringToOrderLanguage($subject, $order, 'subject', 'customer_note');
    }

    /**
     * Translate to the order language, the email heading of customer note emails.
     *
     * @param string   $heading Email heading in default language
     * @param WC_Order $order   Order object
     *
     * @return string Translated heading
     */
    public function translateEmailHeadingCustomerNote($heading, $order)
    {
        return $this->translateEmailStringToOrderLanguage($heading, $order, 'heading', 'customer_note');
    }

    /**
     * Translate to the order language, the email subject of order invoice email notifications to the customer.
     *
     * @param string   $subject Email subject in default language
     * @param WC_Order $order   Order object
     *
     * @return string Translated subject
     */
    public function translateEmailSubjectCustomerInvoice($subject, $order)
    {
        return $this->translateEmailStringToOrderLanguage($subject, $order, 'subject', 'customer_invoice');
    }

    /**
     * Translate to the order language, the email heading of of order invoice email notifications to the customer.
     *
     * @param string   $heading Email heading in default language
     * @param WC_Order $order   Order object
     *
     * @return string Translated heading
     */
    public function translateEmailHeadingCustomerInvoice($heading, $order)
    {
        return $this->translateEmailStringToOrderLanguage($heading, $order, 'heading', 'customer_invoice');
    }

    /**
     * Translate to the order language, the email subject of order invoice paid email notifications to the customer.
     *
     * @param string   $subject Email subject in default language
     * @param WC_Order $order   Order object
     *
     * @return string Translated subject
     */
    public function translateEmailSubjectCustomerInvoicePaid($subject, $order)
    {
        return $this->translateEmailStringToOrderLanguage($subject, $order, 'subject_paid', 'customer_invoice');
    }

    /**
     * Translate to the order language, the email heading of of order invoice paid email notifications to the customer.
     *
     * @param string   $heading Email heading in default language
     * @param WC_Order $order   Order object
     *
     * @return string Translated heading
     */
    public function translateEmailHeadingCustomerInvoicePaid($heading, $order)
    {
        return $this->translateEmailStringToOrderLanguage($heading, $order, 'heading_paid', 'customer_invoice');
    }

    /**
     * Translate to the order language, the email subject of completed order email notifications to the customer.
     *
     * @param string   $subject Email subject in default language
     * @param WC_Order $order   Order object
     *
     * @return string Translated subject
     */
    public function translateEmailSubjectCustomerCompletedOrder($subject, $order)
    {
        if (!empty($order) && $order->has_downloadable_item()) {
            return $this->translateEmailStringToOrderLanguage($subject, $order, 'subject_downloadable', 'customer_completed_order');
        } else {
            return $this->translateEmailStringToOrderLanguage($subject, $order, 'subject', 'customer_completed_order');
        }
    }

    /**
     * Translate to the order language, the email heading of completed order email notifications to the customer.
     *
     * @param string   $heading Email heading in default language
     * @param WC_Order $order   Order object
     *
     * @return string Translated heading
     */
    public function translateEmailHeadingCustomerCompletedOrder($heading, $order)
    {
        if (!empty($order) && $order->has_downloadable_item()) {
            return $this->translateEmailStringToOrderLanguage($subject, $order, 'heading_downloadable', 'customer_completed_order');
        } else {
            return $this->translateEmailStringToOrderLanguage($heading, $order, 'heading', 'customer_completed_order');
        }
    }

    /**
     * Translate the email subject of new account email notifications to the customer.
     *
     * @param string   $subject Email subject in default language
     * @param WC_Order $order   Order object
     *
     * @return string Translated subject
     */
    public function translateEmailSubjectCustomerNewAccount($subject, $order)
    {
        return $this->translateEmailString($subject, 'subject', 'customer_new_account');
    }

    /**
     * Translate the email heading of new account email notifications to the customer.
     *
     * @param string   $heading Email heading in default language
     * @param WC_Order $order   Order object
     *
     * @return string Translated heading
     */
    public function translateEmailHeadingCustomerNewAccount($heading, $order)
    {
        return $this->translateEmailString($heading, 'heading', 'customer_new_account');
    }

    /**
     * Translate the email subject of password reset email notifications to the customer.
     *
     * @param string   $subject Email subject in default language
     * @param WC_Order $order   Order object
     *
     * @return string Translated subject
     */
    public function translateEmailSubjectCustomerResetPassword($subject, $order)
    {
        return $this->translateEmailString($subject, 'subject', 'customer_reset_password');
    }

    /**
     * Translate the email heading of password reset email notifications to the customer.
     *
     * @param string   $heading Email heading in default language
     * @param WC_Order $order   Order object
     *
     * @return string Translated heading
     */
    public function translateEmailHeadingCustomerResetPassword($heading, $order)
    {
        return $this->translateEmailString($heading, 'heading', 'customer_reset_password');
    }

    /**
     * Translates Woocommerce email subjects and headings content.
     *
     * @param string $string      Subject or heading not translated
     * @param string $string_type Type of string to translate <subject | heading>
     * @param string $email_type  Email template
     *
     * @return string Translated string, returns the original $string if a user translation is not found
     */
    public function translateEmailString($string, $string_type, $email_type)
    {
        $_string = $string; // Store original string to return in case of error
        if (false == ($string  = $this->getEmailSetting($string_type, $email_type))) {
            return $_string; // Use default, it should be already in the user current language
        }

        // Retrieve translation from Polylang Strings Translations table
        $string = pll__($string);

        $find    = '{site_title}';
        $replace = get_bloginfo('name');

        $string = str_replace($find, $replace, $string);

        return $string;
    }

    /**
     * Translates Woocommerce email subjects and headings content to the order language.
     *
     * @param string   $string      Subject or heading not translated
     * @param WC_Order $order       Order object
     * @param string   $string_type Type of string to translate <subject | heading>
     * @param string   $email_type  Email template
     *
     * @return string Translated string, returns the original $string on error
     */
    public function translateEmailStringToOrderLanguage($string, $order, $string_type, $email_type)
    {
        //allow function to be called with no order to try to pick up pll locale for footer, from address and name
        $order_language = ($order) ? pll_get_post_language(Utilities::get_orderid($order), 'locale') : '';
        if ($order_language == '') {
            $order_language = pll_current_language('locale');
            if (!($order_language)) {
                return $string;
            }
        }

        // Get setting used to register string in the Polylang strings translation table
        $_string = $string; // Store original string to return in case of error
        if (false == ($string  = $this->getEmailSetting($string_type, $email_type)) && !isset($this->default_settings[$email_type . '_' . $string_type])) {
            return $_string; // No setting in Polylang strings translations table nor default string found to translate
        }

        // Switch language
        $this->switchLanguage($order_language);

        if ($string) {
            // Retrieve translation from Polylang Strings Translations table
            $string = pll__($string);
        } else {
            // If no user translation found in Polylang Strings Translations table, use WooCommerce default translation
            $string = __($this->default_settings[$email_type . '_' . $string_type], 'woocommerce');
        }

        if ($order) {
            $find    = array();
            $replace = array();

            $find['order-date']   = '{order_date}';
            $find['order-number'] = '{order_number}';
            $find['site_title']   = '{site_title}';

            $replace['order-date']   = date_i18n(wc_date_format(), strtotime($order->get_date_created()));
            $replace['order-number'] = $order->get_order_number();
            $replace['site_title']   = get_bloginfo('name');

            $string = str_replace(apply_filters(HooksInterface::EMAILS_ORDER_FIND_REPLACE_FIND_FILTER, $find, $order), apply_filters(HooksInterface::EMAILS_ORDER_FIND_REPLACE_REPLACE_FILTER, $replace, $order), $string);
        }
        return $string;
    }

    /**
     * Get setting used to register string in the Polylang strings translation table.
     *
     * @param string $string_type <subject | heading> of $email_type, e.g. subject, subject_paid
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
            return false; // Setting not registered for translation (admin have changed woocommerce default)
        }
    }

    /**
     * Check whether a refund is made in full.
     *
     * @param WC_Order $order Order object
     *
     * @return bool True if order is fully refunded, False otherwise
     */
    public function isFullyRefunded($order)
    {
        if ((!empty($order) && $order->get_remaining_refund_amount() > 0) || (!empty($order) && $order->has_free_item() && $order->get_remaining_refund_items() > 0)) {
            // Order partially refunded
            return false;
        } else {
            // Order fully refunded
            return true;
        }
    }

    /**
     * Reload text domains with order locale.
     *
     * @param string $language Language slug (e.g. en, de )
     */
    public function switchLanguage($language)
    {
        if (class_exists('Polylang')) {
            global $locale, $polylang, $woocommerce;
            static $cache; // Polylang string translations cache object to avoid loading the same translations object several times
            // Cache object not found. Create one...
            if (empty($cache)) {
                $cache = new \PLL_Cache();
            }

            //$current_language = pll_current_language( 'locale' );
            // unload plugin's textdomains
            unload_textdomain('default');
            unload_textdomain('woocommerce');#

            do_action(HooksInterface::EMAILS_SWITCH_LANGUAGE_ACTION);

            // set locale to order locale
            $locale                    = apply_filters('locale', $language);
            $polylang->curlang->locale = $language;

            // Cache miss
            if (false === $mo = $cache->get($language)) {
                $mo                            = new \PLL_MO();
                $mo->import_from_db($GLOBALS['polylang']->model->get_language($language));
                $GLOBALS['l10n']['pll_string'] = &$mo;

                // Add to cache
                $cache->set($language, $mo);
            }

            // (re-)load plugin's textdomain with order locale
            load_default_textdomain($language);

            $woocommerce->load_plugin_textdomain();
            do_action(HooksInterface::EMAILS_AFTER_SWITCH_LANGUAGE_ACTION);

            $wp_locale = new \WP_Locale();
        }
    }

    /**
     * Correct the locale for orders emails.
     *
     * @global \Polylang $polylang
     * @global \WooCommerce $woocommerce
     *
     * @param string $locale current locale
     *
     * @return string locale
     */
    public function correctLocal($locale)
    {
        global $polylang, $woocommerce;
        if (!$polylang || !$woocommerce) {
            return $locale;
        }

        $refer = isset($_GET['action']) &&
                esc_attr($_GET['action'] === 'woocommerce_mark_order_status'); // Should use sanitize_text_field() instead of esc_attr?

        /*         * *****add-on to have multilanguage on note and refund mails ********* */
        if (isset($_POST['note_type']) && $_POST['note_type'] == 'customer') {
            $refer = true;
        }
        if (isset($_POST['refund_amount']) && ($_POST['refund_amount'] > 0)) {
            $refer = true;
        }
        /*         * *****add-on to have multilanguage on note and refund mails ********* */

        if ((!is_admin() && !isset($_REQUEST['ipn_track_id'])) || (defined('DOING_AJAX') && !$refer)) {
            return $locale;
        }

        if ('GET' === filter_input(INPUT_SERVER, 'REQUEST_METHOD') && !$refer) {
            return $locale;
        }

        $ID = false;

        if (!isset($_REQUEST['ipn_track_id'])) {
            $search = array('post', 'post_ID', 'pll_post_id', 'order_id');

            foreach ($search as $value) {
                if (isset($_REQUEST[$value])) {
                    $ID = esc_attr($_REQUEST[$value]);
                    break;
                }
            }
        } else {
            $ID = $this->getOrderIDFromIPNRequest();
        }

        if ((get_post_type($ID) !== 'shop_order') && !$refer) {
            return $locale;
        }

        $orderLanguage = Order::getOrderLangauge($ID);

        if ($orderLanguage) {
            $entity = Utilities::getLanguageEntity($orderLanguage);

            if ($entity) {
                $polylang->curlang         = $polylang->model->get_language(
                        $entity->locale
                );
                $GLOBALS['text_direction'] = $entity->is_rtl ? 'rtl' : 'ltr';
                if (class_exists('WP_Locale')) {
                    $GLOBALS['wp_locale'] = new \WP_Locale();
                }

                return $entity->locale;
            }
        }

        return $locale;
    }

    /**
     * Return the order id associated with the current IPN request.
     *
     * @return int the order id if one was found or false
     */
    public function getOrderIDFromIPNRequest()
    {
        if (!empty($_REQUEST)) {
            $posted = wp_unslash($_REQUEST);

            if (empty($posted['custom'])) {
                return false;
            }

            $custom = maybe_unserialize($posted['custom']);

            if (!is_array($custom)) {
                return false;
            }

            list($order_id, $order_key) = $custom;

            return $order_id;
        }

        return false;
    }
}
