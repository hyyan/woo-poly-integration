<?php

/**
 * This file is part of the hyyan/woo-poly-integration plugin.
 * (c) Hyyan Abo Fakher <hyyanaf@gmail.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hyyan\WPI\Admin;

use Hyyan\WPI\HooksInterface;

/**
 * Settings.
 *
 * Admin settings page
 *
 * @author Hyyan Abo Fakher <hyyanaf@gmail.com>
 */
class Settings extends \WeDevs_Settings_API
{
    /**
     * Construct object.
     */
    public function __construct()
    {
        parent::__construct();
        add_action('admin_init', array($this, 'init'));
        add_action('admin_menu', array($this, 'registerMenu'));

        new Features();
        new MetasList();
    }

    /**
     * Initialize settings.
     */
    public function init()
    {
        /* Set the settings */
        $this->set_sections($this->getSections());
        $this->set_fields($this->getFields());

        /* Initialize settings */
        $this->admin_init();
    }

    /**
     * Add plugin menu.
     */
    public function registerMenu()
    {
        add_options_page(
                __('Hyyan WooCommerce Polylang Integration', 'woo-poly-integration'), __('WooPoly', 'woo-poly-integration'), 'delete_posts', 'hyyan-wpi', array($this, 'outputPage')
        );
    }

    /**
     * Get sections.
     *
     * Get setting sections array to register
     *
     * @return array
     */
    public function getSections()
    {
        return apply_filters(HooksInterface::SETTINGS_SECTIONS_FILTER, array());
    }

    /**
     * Returns all the settings fields.
     *
     * @return array settings fields
     */
    public function getFields()
    {
        return apply_filters(HooksInterface::SETTINGS_FIELDS_FILTER, array());
    }

    /**
     * Output page content.
     */
    public function outputPage()
    {
        echo \Hyyan\WPI\Plugin::getView('admin', array(
            'self' => $this,
        ));
    }

    /**
     * Get the value of a settings field.
     *
     * @param string $option  settings field name
     * @param string $section the section name this field belongs to
     * @param string $default default text if it's not found
     *
     * @return mixed
     */
    public static function getOption($option, $section, $default = '')
    {
        $options = get_option($section);

        if (!empty($options[$option])) {  // equivalent to: isset($options[$option]) && $options[$option]
            return $options[$option];
        }   // when all settings are disabled
        elseif (isset($options[$option])) {
            return array();
        } else {
            return $default;
        }
    }
}
