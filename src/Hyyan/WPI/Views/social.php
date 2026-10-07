<?php
if (!defined('ABSPATH')) {
    exit;
}
$woo_poly_integration_tweet_url = add_query_arg(array(
    'url' => rawurlencode('https://wordpress.org/plugins/woo-poly-integration/'),
    'text' => rawurlencode('Hyyan WooCommerce Polylang Integration ,makes you run multilingual store easily.'),
    'via' => 'HyyanAF',
), 'https://twitter.com/intent/tweet');
$woo_poly_integration_facebook_url = add_query_arg('u', rawurlencode('https://wordpress.org/plugins/woo-poly-integration/'), 'https://www.facebook.com/sharer/sharer.php');
?>
<p>
    <a class="button" href="<?php echo esc_url($woo_poly_integration_tweet_url); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Tweet', 'woo-poly-integration'); ?></a>
    <a class="button" href="<?php echo esc_url($woo_poly_integration_facebook_url); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Share on Facebook', 'woo-poly-integration'); ?></a>
    <a class="button" href="https://github.com/hyyan/woo-poly-integration" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Star on GitHub', 'woo-poly-integration'); ?></a>
</p>
