<?php // phpcs:ignore WordPress.Files.FileName -- The plugin autoloader resolves the class name to this filename.
/**
 * Bundled WooPoly documentation.
 *
 * @package WooPoly
 */

namespace Hyyan\WPI\Admin;

/** Read-only documentation for administrators and shop managers. */
final class Guide {

	/** Register the guide independently of the integration dependencies. */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'register' ) );
	}

	/** Register the reader under Tools and enqueue its stylesheet on that screen. */
	public function register() {
		$capability = current_user_can( 'manage_options' ) ? 'manage_options' : 'manage_woocommerce';
		$screen     = add_management_page( __( 'WooPoly Documentation', 'woo-poly-integration' ), __( 'WooPoly Documentation', 'woo-poly-integration' ), $capability, 'woopoly-guide', array( $this, 'render' ) );
		if ( $screen ) {
			add_action( 'admin_print_styles-' . $screen, array( $this, 'styles' ) );
		}
	}

	/** Load only the reader's styles. */
	public function styles() {
		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		wp_enqueue_style( 'woopoly-guide', plugins_url( 'public/css/guide' . $suffix . '.css', Hyyan_WPI_DIR ), array(), \Hyyan\WPI\Plugin::getVersion() );
	}

	/**
	 * Get the documentation URL for this WordPress installation.
	 *
	 * @param string $topic Article slug.
	 * @return string Admin URL.
	 */
	public static function url( $topic = 'index' ) {
		return add_query_arg(
			array(
				'page'  => 'woopoly-guide',
				'topic' => $topic,
			),
			admin_url( 'tools.php' )
		);
	}

	/** Display a bundled article selected from the guide index. */
	public function render() {
		if ( ! current_user_can( 'manage_options' ) && ! current_user_can( 'manage_woocommerce' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- WooCommerce registers manage_woocommerce.
			wp_die( esc_html__( 'You do not have permission to read this guide.', 'woo-poly-integration' ), '', array( 'response' => 403 ) );
		}
		$guide = require dirname( Hyyan_WPI_DIR ) . '/public/guide/articles.php';
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only selection from the bundled article index.
		$topic = isset( $_GET['topic'] ) && is_string( $_GET['topic'] ) ? sanitize_text_field( wp_unslash( $_GET['topic'] ) ) : 'index';
		if ( ! isset( $guide['articles'][ $topic ] ) ) {
			$topic = 'index';
		}
		$image_url = plugins_url( 'public/guide/images/', Hyyan_WPI_DIR );
		?>
		<div class="wrap wpiguide">
			<h1><?php esc_html_e( 'WooPoly Documentation', 'woo-poly-integration' ); ?></h1>
			<?php if ( current_user_can( 'manage_options' ) ) : ?>
				<p><a href="<?php echo esc_url( Setup::getSetupUrl() ); ?>"><?php esc_html_e( 'WooPoly settings', 'woo-poly-integration' ); ?></a></p>
			<?php endif; ?>
			<form class="wpiguide-jump" method="get" action="<?php echo esc_url( admin_url( 'tools.php' ) ); ?>">
				<input type="hidden" name="page" value="woopoly-guide">
				<label for="wpiguide-topic"><?php esc_html_e( 'Guide topic', 'woo-poly-integration' ); ?></label>
				<select id="wpiguide-topic" name="topic">
					<?php foreach ( $guide['navigation'] as $group => $topics ) : ?>
						<optgroup label="<?php echo esc_attr( $group ); ?>">
							<?php foreach ( $topics as $slug => $label ) : ?>
								<option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $slug, $topic ); ?>><?php echo esc_html( $label ); ?></option>
							<?php endforeach; ?>
						</optgroup>
					<?php endforeach; ?>
				</select>
				<button class="button" type="submit"><?php esc_html_e( 'Open', 'woo-poly-integration' ); ?></button>
			</form>
			<div class="wpiguide-layout">
				<nav aria-label="<?php esc_attr_e( 'Guide topics', 'woo-poly-integration' ); ?>">
					<?php foreach ( $guide['navigation'] as $group => $topics ) : ?>
						<details <?php echo isset( $topics[ $topic ] ) ? 'open' : ''; ?>>
						<summary><?php echo esc_html( $group ); ?></summary>
						<ul>
							<?php foreach ( $topics as $slug => $label ) : ?>
								<li><a href="<?php echo esc_url( self::url( $slug ) ); ?>" <?php echo $slug === $topic ? 'aria-current="page"' : ''; ?>><?php echo esc_html( $label ); ?>
									<?php if ( 'translations/deepl' === $slug ) : ?>
										<span class="wpiguide-new"><?php esc_html_e( 'New', 'woo-poly-integration' ); ?></span>
									<?php endif; ?>
								</a></li>
							<?php endforeach; ?>
						</ul>
						</details>
					<?php endforeach; ?>
				</nav>
				<article><?php echo wp_kses_post( str_replace( 'WOOPOLY_GUIDE_IMAGES/', esc_url( $image_url ), $guide['articles'][ $topic ] ) ); ?></article>
			</div>
		</div>
		<?php
	}
}
