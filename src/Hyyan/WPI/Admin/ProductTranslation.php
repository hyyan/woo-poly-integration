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
use Hyyan\WPI\Product\TranslatedDraft;

final class ProductTranslation
{
    public function __construct()
    {
        add_action('add_meta_boxes_product', array($this, 'register'));
        add_action('admin_footer-post.php', array($this, 'form'));
        add_action('admin_post_woopoly_translate_product', array($this, 'submit'));
    }

    public function register($post)
    {
        if (current_user_can('manage_woocommerce') && $post->post_status !== 'auto-draft' && current_user_can('edit_post', $post->ID)
            && current_user_can(get_post_type_object('product')->cap->create_posts)) {
            add_meta_box(
                'woopoly-translate-product',
                __('Translate with DeepL', 'woo-poly-integration'),
                array($this, 'render'),
                'product',
                'side'
            );
        }
    }

    public function render($post)
    {
        if (!DeepL::getKey()) {
            if (current_user_can('manage_options')) {
                echo '<a href="' . esc_url(Setup::getSetupUrl(true) . '#wpi-features') . '">';
                esc_html_e('Connect DeepL in WooPoly settings', 'woo-poly-integration');
                echo '</a>';
            } else {
                esc_html_e('The store needs a DeepL API key before products can be translated.', 'woo-poly-integration');
            }

            return;
        }

        $source = pll_get_post_language($post->ID);
        if (!$source) {
            echo '<p>' . esc_html__('Assign a language and save this product first.', 'woo-poly-integration') . '</p>';
            return;
        }

        $targets = array_diff(pll_languages_list(), array_keys(pll_get_post_translations($post->ID)));
        if (!$targets) {
            echo '<p>' . esc_html__('This product already has translations in every configured language.', 'woo-poly-integration') . '</p>';
            return;
        }

        include dirname(__DIR__) . '/Views/Admin/translate-product.php';
    }

    /** Keep the translation request independent of the product editor's save form. */
    public function form()
    {
        $post = get_post();
        if (!$post || $post->post_type !== 'product' || !current_user_can('edit_post', $post->ID)) {
            return;
        }

        echo '<form id="woopoly-translation-form" method="post" action="' . esc_url(admin_url('admin-post.php')) . '">';
        echo '<input type="hidden" name="action" value="woopoly_translate_product">';
        echo '<input type="hidden" name="product_id" value="' . esc_attr($post->ID) . '">';
        wp_nonce_field('woopoly_translate_product_' . $post->ID);
        echo '</form>';
    }

    public function submit()
    {
        $id = isset($_POST['product_id']) && is_string($_POST['product_id']) ? absint($_POST['product_id']) : 0;

        check_admin_referer('woopoly_translate_product_' . $id);
        $target = isset($_POST['language']) && is_string($_POST['language']) ? sanitize_key(wp_unslash($_POST['language'])) : '';
        $draft = TranslatedDraft::create($id, $target);
        if (is_wp_error($draft)) {
            wp_die(esc_html($draft->get_error_message()), '', array('back_link' => true));
        }

        wp_safe_redirect(get_edit_post_link($draft, 'raw'));
        exit;
    }
}
