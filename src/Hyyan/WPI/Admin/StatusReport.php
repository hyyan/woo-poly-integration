<?php

use Hyyan\WPI\Tools\TranslationsDownloader;

/*
 *
 */
add_action( 'woocommerce_system_status_report', 'wpi_status_report' );
function wpi_status_report() {
	?>

	<table class="wc_status_table widefat" cellspacing="0">
		<thead>
			<tr>
				<th colspan="3" data-export-label="WooCommerce Polylang Integration"><h2><?php esc_html_e( 'WooCommerce Polylang Integration', 'woo-poly-integration' ); ?></h2></th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td data-export-label="Language Locale"><?php esc_html_e( 'Language Locale', 'woo-poly-integration' ); ?>:</td>
				<td class="help"><?php echo wc_help_tip( esc_html__( 'The current language used by WordPress. Default = English', 'woocommerce' ) ); /* phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped */ ?></td>
				<td><?php echo esc_html( get_locale() ); ?></td>
			</tr>
			<tr>
				<td data-export-label="Polylang Language Locale"><?php esc_html_e( 'Polylang Default Language Locale', 'woo-poly-integration' ); ?>:</td>
				<td class="help"><?php echo wc_help_tip( esc_html__( 'The default language set in Polylang', 'woo-poly-integration' ) ); /* phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped */ ?></td>
				<td><?php echo esc_html( pll_default_language( 'locale' ) ); ?></td>
			</tr>
			<tr>
				<td data-export-label="Polylang Available Languages"><?php esc_html_e( 'Polylang Available Languages', 'woo-poly-integration' ); ?>:</td>
				<td class="help"><?php echo wc_help_tip( esc_html__( 'The available languages in Polylang', 'woo-poly-integration' ) ); /* phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped */ ?></td>
				<td><?php
					$langs = pll_languages_list( array( 'fields' => 'locale' ) );

					foreach ( $langs as $langId => $langLocale ) {
						echo($langLocale);
						$downloaded	 = TranslationsDownloader::isDownloaded( $langLocale );
						$location	 = sprintf(
						trailingslashit( WP_LANG_DIR )
						. 'plugins/woocommerce-%s.mo', $langLocale
						);
						if ( $downloaded ) {
							echo(' (WooCommerce translation file found OK at ' . $location . ') ');
						} else {
							echo(' Warning - missing WooCommerce translation file NOT found at ' . $location . ' ');
						}
						echo '<br/>';
					}
					?></td>
			</tr>
		</tbody>
	</table>

	<?php
}
?>