<?php
if (!defined('ABSPATH')) {
    exit;
}
$wpi_script_suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
wp_enqueue_script( 'woopoly-share', plugins_url( 'public/js/Share' . $wpi_script_suffix . '.js', Hyyan_WPI_DIR ), array( 'clipboard' ), \Hyyan\WPI\Plugin::getVersion(), true );
// Share only the public project information supplied here.
$wpi_share_text = __(
	'@Hyyan Abo Fakher created WooPoly in 2015 to connect WooCommerce with Polylang. With WooPoly, a shop can list the same product in several languages and keep one stock count across all its translations.

Hyyan made WooPoly free and open source from day one and has pledged to keep it that way. If you know someone building a multilingual store, pass this along.

https://wordpress.org/plugins/woo-poly-integration/',
	'woo-poly-integration'
);
?>
<p><?php esc_html_e( 'I started WooPoly in 2015 to help shop owners sell in more than one language. WooPoly has been free from day one. My commitment is to keep it free and open source.', 'woo-poly-integration' ); ?></p>
<p><?php esc_html_e( 'If WooPoly has helped you run your shop, would you star it on GitHub? It lets me know the work is useful. If you prefer WordPress.org, a review about what worked for your store helps the next shop owner decide. Thank you for giving WooPoly a place in your shop.', 'woo-poly-integration' ); ?></p>
<p>— <a href="https://www.linkedin.com/in/hyyan/" target="_blank" rel="noopener noreferrer">Hyyan Abo Fakher</a> · <a href="https://github.com/hyyan" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'GitHub profile', 'woo-poly-integration' ); ?></a></p>
<p>
	<a class="button button-primary" href="https://github.com/hyyan/woo-poly-integration" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Star WooPoly on GitHub', 'woo-poly-integration' ); ?></a>
	<a class="button" href="https://wordpress.org/support/plugin/woo-poly-integration/reviews/#new-post" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Rate on WordPress.org', 'woo-poly-integration' ); ?></a>
	<a class="button" href="https://www.linkedin.com/feed/?shareActive=true&amp;text=<?php echo esc_attr( rawurlencode( $wpi_share_text ) ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Share on LinkedIn', 'woo-poly-integration' ); ?></a>
</p>
<details>
	<summary><?php esc_html_e( 'Preview the post and tag Hyyan', 'woo-poly-integration' ); ?></summary>
	<div class="woopoly-post-preview">
		<blockquote><?php echo esc_html( $wpi_share_text ); ?></blockquote>
		<button type="button" class="woopoly-copy-post" title="<?php esc_attr_e( 'Copy post text', 'woo-poly-integration' ); ?>" data-clipboard-text="<?php echo esc_attr( $wpi_share_text ); ?>" data-success="<?php esc_attr_e( 'Post copied.', 'woo-poly-integration' ); ?>" data-error="<?php esc_attr_e( 'Select the post text and copy it manually.', 'woo-poly-integration' ); ?>">
			<span class="dashicons dashicons-admin-page" aria-hidden="true"></span>
			<span class="screen-reader-text"><?php esc_html_e( 'Copy post text', 'woo-poly-integration' ); ?></span>
		</button>
		<span role="status" class="woopoly-copy-status"></span>
	</div>
	<p><?php esc_html_e( 'To tag Hyyan on LinkedIn, select his profile from the mention suggestions.', 'woo-poly-integration' ); ?></p>
</details>
