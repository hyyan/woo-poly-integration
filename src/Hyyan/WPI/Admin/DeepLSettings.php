<?php

/**
 * This file is part of the hyyan/woo-poly-integration plugin.
 * (c) Hyyan Abo Fakher <hyyanaf@gmail.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hyyan\WPI\Admin;

use Hyyan\WPI\Product\DeepL;

final class DeepLSettings
{
    public function __construct()
    {
        add_action('admin_menu', array($this, 'registerMenu'));
        add_action('admin_post_woopoly_deepl_key', array($this, 'save'));
    }

    public function registerMenu()
    {
        if (!current_user_can('manage_options')) {
            add_submenu_page(
                'woocommerce',
                __('WooPoly settings', 'woo-poly-integration'),
                'WooPoly',
                'manage_woocommerce',
                'woopoly-deepl',
                array($this, 'managerPage')
            );
        }
    }

    public static function url()
    {
        if (current_user_can('manage_options')) {
            return Setup::getSetupUrl(true) . '#woopoly-deepl';
        }

        return admin_url('admin.php?page=woopoly-deepl');
    }

    public function managerPage()
    {
        echo '<div class="wrap">';
        self::render();
        echo '</div>';
    }

    public static function render()
    {
        if (!current_user_can('manage_woocommerce')) {
            return;
        }

        include dirname(__DIR__) . '/Views/Admin/deepl-settings.php';
    }

    public function save()
    {
        if (!current_user_can('manage_woocommerce')) {
            wp_die(esc_html__('You cannot change WooPoly translation settings.', 'woo-poly-integration'), '', array('response' => 403));
        }

        check_admin_referer('woopoly_deepl_key');
        if (isset($_POST['remove_key']) && '1' === $_POST['remove_key']) {
            delete_option(DeepL::STORE_KEY);
        } else {
            $key = isset($_POST['deepl_key']) && is_string($_POST['deepl_key']) ? trim(wp_unslash($_POST['deepl_key'])) : '';
            if (!DeepL::saveStoreKey($key)) {
                wp_die(esc_html__('Could not save the DeepL API key. Check the key and try again.', 'woo-poly-integration'), '', array('back_link' => true));
            }
        }

        wp_safe_redirect(self::url());
        exit;
    }
}
