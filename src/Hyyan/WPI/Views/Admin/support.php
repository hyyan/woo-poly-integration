<?php
if (!defined('ABSPATH')) {
    exit('restricted access');
}
?>
<h3><span><?php esc_html_e( 'A note from Hyyan', 'woo-poly-integration' ); ?></span></h3>
<div class="inside">
	<?php require dirname( __DIR__ ) . '/social.php'; ?>
</div>
