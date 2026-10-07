<?php
if (!defined('ABSPATH')) {
    exit('restricted access');
}
?>
<div class="wrap">
    <h2> <?php esc_html_e('WooPoly Advanced Options', 'woo-poly-integration'); ?></h2>
    <div id="poststuff">
        <div id="post-body" class="metabox-holder columns-2">

            <!-- main content -->
            <div id="post-body-content">
                <?php include __DIR__.'/Admin/main.php'; ?>
            </div>

            <!-- sidebar -->
            <div id="postbox-container-1" class="postbox-container">

                <!-- About the plugin -->
                <div class="postbox">
                    <?php include __DIR__.'/Admin/about.php'; ?>
                </div>

                <!-- Support plugin -->
                <div class="postbox">
                    <?php include __DIR__.'/Admin/support.php'; ?>
                </div>

                <!-- Need help -->
                <div class = "postbox">
                    <?php include __DIR__.'/Admin/getHelp.php'; ?>
                </div>
            </div>
        </div>
    </div>
</div>
