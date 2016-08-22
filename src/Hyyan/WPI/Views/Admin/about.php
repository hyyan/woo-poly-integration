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
        )
        ?>
        <br><br>

        <p>
            <?php _e('Author : ', 'woo-poly-integration') ?>
            <a href="https://github.com/hyyan">Hyyan Abo Fakher</a>
        </p>

        <?php echo \Hyyan\WPI\Plugin::getView('badges'); ?>

    </div>
</div>
