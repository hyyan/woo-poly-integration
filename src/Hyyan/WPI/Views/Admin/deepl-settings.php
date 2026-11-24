<?php
use Hyyan\WPI\Product\DeepL;

if (!defined('ABSPATH')) {
    exit('restricted access');
}
?>

<h2 id="woopoly-deepl"><?php esc_html_e('DeepL product translation', 'woo-poly-integration'); ?></h2>

<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
    <?php wp_nonce_field('woopoly_deepl_key'); ?>
    <input type="hidden" name="action" value="woopoly_deepl_key">

    <label for="woopoly-deepl-key"><?php esc_html_e('DeepL API key', 'woo-poly-integration'); ?></label>
    <input type="password" class="regular-text" id="woopoly-deepl-key" name="deepl_key" autocomplete="new-password" required>

    <p class="description">
        <?php esc_html_e('This key is shared by everyone who can translate products in this store.', 'woo-poly-integration'); ?>
    </p>

    <?php if (DeepL::getKey()) : ?>
        <p><?php esc_html_e('A DeepL key is configured.', 'woo-poly-integration'); ?></p>
    <?php endif; ?>

    <?php submit_button(__('Save key', 'woo-poly-integration'), 'primary', 'save_key', false); ?>

    <?php if (DeepL::storeKey()) : ?>
        <button class="button" name="remove_key" value="1" formnovalidate>
            <?php esc_html_e('Remove saved key', 'woo-poly-integration'); ?>
        </button>
    <?php endif; ?>
</form>
