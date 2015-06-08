<?php
if (!defined('ABSPATH')) {
    exit('restricted access');
}
?>
<div class="wrap">
    <h2> <?php _e('WooPoly Advanced Options', 'woo-poly-integration'); ?></h2>
    <div id="poststuff">
        <div id="post-body" class="metabox-holder columns-2">

            <!-- main content -->
            <div id="post-body-content">
                <?php echo \Hyyan\WPI\Plugin::getView('Admin/main', $vars) ?>
            </div>

            <!-- sidebar -->
            <div id="postbox-container-1" class="postbox-container">

                <!-- About the plugin -->
                <div class="postbox">
                    <?php echo \Hyyan\WPI\Plugin::getView('Admin/about') ?>
                </div>

                <!-- Support plugin -->
                <div class="postbox">
                    <?php echo \Hyyan\WPI\Plugin::getView('Admin/support') ?>
                </div>

                <!-- Need help -->
                <div class = "postbox">
                    <?php echo \Hyyan\WPI\Plugin::getView('Admin/getHelp') ?>
                </div>
            </div>
        </div>
    </div>
</div>
