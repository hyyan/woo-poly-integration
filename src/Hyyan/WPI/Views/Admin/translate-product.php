<?php
/**
 * Product translation language selector.
 *
 * @package WooPoly
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit( 'restricted access' );
}
?>

<label for="woopoly-target-language"><?php esc_html_e( 'Target language', 'woo-poly-integration' ); ?></label>
<select id="woopoly-target-language" name="language" form="woopoly-translation-form">
	<?php foreach ( $targets as $slug ) : ?>
		<option value="<?php echo esc_attr( $slug ); ?>">
			<?php echo esc_html( PLL()->model->get_language( $slug )->name ); ?>
		</option>
	<?php endforeach; ?>
</select>

<p><?php esc_html_e( 'Send saved product text to DeepL and create a draft for review.', 'woo-poly-integration' ); ?></p>

<button type="submit" class="button" form="woopoly-translation-form">
	<?php esc_html_e( 'Create translated draft', 'woo-poly-integration' ); ?>
</button>
