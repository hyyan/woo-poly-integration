<?php

if (!defined('ABSPATH')) {
    exit('restricted access');
}
?>

<?php

printf(
    __( 'You can translate woocommerce endpoints, email strings, shipping methods from polylang strings tab. <a target="_blank" href="%s">%s</a>', 'woo-poly-integration'), 
    add_query_arg(
        array(
          'page'	 => 'mlang_strings',
          'group' => \Hyyan\WPI\Endpoints::getPolylangStringSection(),
        ), 
        admin_url( 'admin.php' ) 
    ), 
    __( 'Translate', 'woo-poly-integration' )
)
?>
