<?php
if (!defined('ABSPATH')) {
    exit('restricted access');
}
?>
<h3>
    <?php
    _e('Hyyan WooCommerce Polylang Integration Plugin', 'woo-poly-integration');
    ?>
</h3>
<p>
    <?php
    _e('The plugin can not function correctly , the plugin requires
        minimum plugin versions WooCommerce version 3 or higher and Polylang 2 or higher.
        Please configure Polylang by adding a language before activating WooCommerce Polylang Integration.', 'woo-poly-integration'
    );
    _e('See also', 'woo-poly-integration');
    echo('<a href="https://github.com/hyyan/woo-poly-integration/wiki/Installation">');
    _e('Installation Guide', 'woo-poly-integration');
    echo('</a>.');
    ?>
<p>
<hr>
<?php _e('Plugins Sites : ', 'woo-poly-integration'); ?>
<a href="https://wordpress.org/plugins/woocommerce/">
    <?php _e('WooCommerce', 'woo-poly-integration'); ?>
</a>
|
<a href="https://wordpress.org/plugins/polylang/">
    <?php _e('Polylang', 'woo-poly-integration'); ?>
</a>
