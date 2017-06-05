<?php
if (!defined('ABSPATH')) {
    exit('restricted access');
}
?>
<h3>
    <span>
        <?php _e('About The Plugin', 'woo-poly-integration'); ?>
    </span>
</h3>
<div class="inside">
    <div>
        <?php
        _e('The plugin is an open source project
            which aims to fill the gap between
            <a href="https://wordpress.org/plugins/woocommerce/">Woocommerce</a>
            and <a href="https://wordpress.org/plugins/polylang/">Polylang</a>', 'woo-poly-integration'
        );
        echo('. ');
        _e('For more information please see:', 'woo-poly-integration');
        echo(' <a target="_blank" href="https://github.com/hyyan/woo-poly-integration/wiki">');
        _e('documentation pages', 'woo-poly-integration');
        echo('</a>.');
        ?>
        <br><br>

        <p>
            <?php _e('Author : ', 'woo-poly-integration') ?>
            <a href="https://github.com/hyyan">Hyyan Abo Fakher</a>
        </p>

        <?php echo \Hyyan\WPI\Plugin::getView('badges'); ?>

    </div>
</div>
