<?php
if (!defined('ABSPATH')) {
    exit('restricted access');
}
?>
<h3>
    <span>
        <?php _e('Support The Plugin', 'woo-poly-integration'); ?>
    </span>
</h3>
<div class="inside">
    <?php
    _e('<strong>I will never ask you for donation , now or in the future</strong> ,
        but the plugin stills need your support.
        please support by rating this plugin On
        <a href="https://wordpress.org/support/view/plugin-reviews/woo-poly-integration">Wordpress Repository</a> ,
        or by giving the plugin a star on  <a href="https://github.com/hyyan/woo-poly-integration">Github</a>.
        <br><br>
        If you speak langauge other than English ,
        you can support the plugin by extending the
        trasnlation list. and your name will be added
        to translators list', 'woo-poly-integration'
    );
    ?>
    <br><br>
    <?php echo \Hyyan\WPI\Plugin::getView('social') ?>
</div>
