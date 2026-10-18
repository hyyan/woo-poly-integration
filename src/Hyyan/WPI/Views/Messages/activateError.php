<?php
if (!defined('ABSPATH')) {
    exit('restricted access');
}
?>
<h3>
    <?php
    esc_html_e('Hyyan WooCommerce Polylang Integration Plugin', 'woo-poly-integration');
    ?>
</h3>
<p>
    <?php
    esc_html_e('The plugin can not function correctly , the plugin requires
        minimum plugin versions WooCommerce version 3 or higher and Polylang 2 or higher.
        Please configure Polylang by adding a language before activating WooCommerce Polylang Integration.', 'woo-poly-integration'
    );
    esc_html_e('See also', 'woo-poly-integration');
    echo('<a href="https://hyyan.github.io/woo-poly-integration/getting-started/installation.html">');
    esc_html_e('Installation Guide', 'woo-poly-integration');
    echo('</a>.');
    ?>
<p>
<hr>
<?php esc_html_e('Plugins : ', 'woo-poly-integration'); ?>
<a href="https://wordpress.org/plugins/woocommerce/">
    <?php printf('%s V%s', esc_html__('WooCommerce', 'woo-poly-integration'), esc_html(Hyyan\WPI\Plugin::WOOCOMMERCE_VERSION)); ?>
</a>
|
<a href="https://wordpress.org/plugins/polylang/">
    <?php printf('%s V%s', esc_html__('Polylang', 'woo-poly-integration'), esc_html(Hyyan\WPI\Plugin::POLYLANG_VERSION)); ?>
</a>
