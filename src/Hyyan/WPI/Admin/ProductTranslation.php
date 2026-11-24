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
        add_action('admin_post_woopoly_deepl_key', array($this, 'updateKey'));
        add_action('admin_post_woopoly_translate_product', array($this, 'submit'));
    }

    public function register($post)
    {
        if (current_user_can('manage_woocommerce') && $post->post_status !== 'auto-draft' && current_user_can('edit_post', $post->ID)
            && current_user_can(get_post_type_object('product')->cap->create_posts)) {
            add_meta_box('woopoly-translate-product', __('Translate with DeepL', 'woo-poly-integration'),
                array($this, 'render'), 'product', 'side');
        }
    }

    public function render($post)
    {
        $configured = (bool) DeepL::getKey();
        $personal = (bool) DeepL::personalKey();
        echo '<details' . ($configured ? '' : ' open') . '><summary>' . esc_html__('DeepL API key', 'woo-poly-integration') . '</summary>';
        if ($configured) {
            echo '<p>' . ($personal ? esc_html__('Your personal key is saved.', 'woo-poly-integration') : esc_html__('A shared store key is available.', 'woo-poly-integration')) . '</p>';
        }
        echo '<p><label for="woopoly-deepl-key">' . esc_html__('Your personal API key', 'woo-poly-integration') . '</label>';
        echo '<input class="widefat" type="password" id="woopoly-deepl-key" name="deepl_key" autocomplete="new-password" required form="woopoly-key-form"></p>';
        echo '<p><button type="submit" class="button" form="woopoly-key-form">' . esc_html__('Save key', 'woo-poly-integration') . '</button> ';
        if ($personal) {
            echo '<button type="submit" class="button-link" name="remove_key" value="1" formnovalidate form="woopoly-key-form">' . esc_html__('Remove personal key', 'woo-poly-integration') . '</button>';
        }
        echo '</p></details>';
        if (!$configured) {
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
        echo '<label for="woopoly-target-language">' . esc_html__('Target language', 'woo-poly-integration') . '</label> ';
        echo '<select id="woopoly-target-language" name="language" form="woopoly-translation-form">';
        foreach ($targets as $slug) {
            $language = PLL()->model->get_language($slug);
            echo '<option value="' . esc_attr($slug) . '">' . esc_html($language->name) . '</option>';
        }
        echo '</select><p>' . esc_html__('Send saved product text to DeepL and create a draft for review.', 'woo-poly-integration') . '</p>';
        echo '<button type="submit" class="button" form="woopoly-translation-form">' . esc_html__('Create translated draft', 'woo-poly-integration') . '</button>';
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
        echo '<form id="woopoly-key-form" method="post" action="' . esc_url(admin_url('admin-post.php')) . '">';
        echo '<input type="hidden" name="action" value="woopoly_deepl_key">';
        echo '<input type="hidden" name="product_id" value="' . esc_attr($post->ID) . '">';
        wp_nonce_field('woopoly_deepl_key_' . $post->ID, 'woopoly_key_nonce', false);
        echo '</form>';
    }

    public function updateKey()
    {
        $id = isset($_POST['product_id']) && is_string($_POST['product_id']) ? absint($_POST['product_id']) : 0;
        check_admin_referer('woopoly_deepl_key_' . $id, 'woopoly_key_nonce');
        if (get_post_type($id) !== 'product' || !current_user_can('manage_woocommerce') || !current_user_can('edit_post', $id)
            || !current_user_can(get_post_type_object('product')->cap->create_posts)) {
            wp_die(esc_html__('You cannot manage translation for this product.', 'woo-poly-integration'), '', array('response' => 403));
        }
        if (isset($_POST['remove_key']) && '1' === $_POST['remove_key']) {
            delete_user_meta(get_current_user_id(), DeepL::USER_KEY);
        } else {
            $key = isset($_POST['deepl_key']) && is_string($_POST['deepl_key']) ? trim(wp_unslash($_POST['deepl_key'])) : '';
            if (!DeepL::savePersonalKey($key)) {
                wp_die(esc_html__('Could not save the DeepL API key. Check the key and try again.', 'woo-poly-integration'), '', array('back_link' => true));
            }
        }
        wp_safe_redirect(get_edit_post_link($id, 'raw') . '#woopoly-translate-product');
        exit;
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
