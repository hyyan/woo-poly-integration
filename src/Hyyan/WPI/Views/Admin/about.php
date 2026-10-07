<?php
if (!defined('ABSPATH')) {
    exit('restricted access');
}
?>
<h3>
    <span>
        <?php esc_html_e('About The Plugin', 'woo-poly-integration'); ?>
    </span>
</h3>
<div class="inside">
    <div>
        <?php
        echo wp_kses_post(__('The plugin is an open source project
            which aims to fill the gap between
            <a href="https://wordpress.org/plugins/woocommerce/">WooCommerce</a>
            and <a href="https://wordpress.org/plugins/polylang/">Polylang</a>', 'woo-poly-integration'
        ));
        echo('. ');
        esc_html_e('For more information please see:', 'woo-poly-integration');
        echo(' <a target="_blank" href="https://github.com/hyyan/woo-poly-integration/wiki">');
        esc_html_e('documentation pages', 'woo-poly-integration');
        echo('</a>.');
        ?>
        <br><br>

        <p>
            <?php esc_html_e('Author : ', 'woo-poly-integration') ?>
            <a href="https://github.com/hyyan">Hyyan Abo Fakher</a>
        </p>

        <?php include dirname(__DIR__).'/badges.php'; ?>

    </div>
</div>
