<?php

if (!defined('ABSPATH')) {
    exit('restricted access');
}
?>

<?php

echo wp_kses_post(sprintf(
    __( 'You can translate woocommerce endpoints, email strings, shipping methods from polylang strings tab. <a target="_blank" href="%s">%s</a>', 'woo-poly-integration'), 
    esc_url(add_query_arg(
        array(
          'page'	 => 'mlang_strings',
          'group' => \Hyyan\WPI\Endpoints::getPolylangStringSection(),
        ), 
        admin_url( 'admin.php' ) 
    )),
    esc_html__( 'Translate', 'woo-poly-integration' )
));
?>
