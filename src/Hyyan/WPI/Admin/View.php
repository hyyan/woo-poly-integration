<div class="wrap">
    <h2> <?php _e('WooPoly Advanced Options', 'woo-poly-integration'); ?></h2>
    <div id="poststuff">
        <div id="post-body" class="metabox-holder columns-2">

            <!-- main content -->
            <div id="post-body-content">
                <?php $this->show_navigation(); ?>
                <div class="postbox" style="padding: 10px;">
                    <?php $this->show_forms(); ?>
                </div>
            </div>

            <!-- sidebar -->
            <div id="postbox-container-1" class="postbox-container">

                <!-- About the plugin -->
                <div class="postbox">
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
                                        and
                                        <a href="https://wordpress.org/plugins/polylang/">Polylang</a>'
                                    , 'woo-poly-integration'
                            )
                            ?>
                            <br><br>
                            <p>
                                <?php _e('Author : ', 'woo-poly-integration') ?>
                                <a href="https://github.com/hyyan">Hyyan Abo Fakher</a>
                            </p>
                            <a href="https://wordpress.org/plugins/woo-poly-integration" target="_blank">
                                <img src="https://poser.pugx.org/hyyan/woo-poly-integration/v/stable.svg" />
                            </a>
                            <a href="https://github.com/hyyan/woo-poly-integration/blob/master/LICENSE" target="_blank">
                                <img src="https://poser.pugx.org/hyyan/woo-poly-integration/license.svg"/>
                            </a>
                            <a href="http://stillmaintained.com/hyyan/woo-poly-integration" target="_blank">
                                <img src="http://stillmaintained.com/hyyan/woo-poly-integration.png" />
                            </a>
                        </div>
                    </div>

                </div>

                <!-- Support plugin -->
                <div class="postbox">
                    <h3>
                        <span>
                            <?php _e('Support The Plugin', 'woo-poly-integration'); ?>
                        </span>
                    </h3>
                    <div class="inside">
                        <?php
                        _e('<strong>I will never ask you for donation , now or the future</strong> ,
                                    but the plugin stills need your support.
                                    please support by rating this plugin On
                                    <a href="https://wordpress.org/support/view/plugin-reviews/woo-poly-integration">Wordpress Repository</a> ,
                                    or by giving the plugin a star on  <a href="https://github.com/hyyan/woo-poly-integration">Github</a>.
                                    <br><br>
                                    If you speak langauge other than English ,
                                    you can support the plugin by extending the
                                    trasnlation list. and your name will be added
                                    to translators list
                                    '
                                , 'woo-poly-integration'
                        );
                        ?>
                        <br><br>
                        <iframe src="https://ghbtns.com/github-btn.html?user=hyyan&repo=woo-poly-integration&type=star&count=true" frameborder="0" scrolling="0" width="170px" height="20px"></iframe>
                        <iframe src="https://ghbtns.com/github-btn.html?user=hyyan&repo=woo-poly-integration&type=watch&count=true&v=2" frameborder="0" scrolling="0" width="170px" height="20px"></iframe>
                    </div>

                </div>

                <!-- Need help -->
                <div class = "postbox">

                    <h3><span><?php _e('Need Help ?', 'woo-poly-integration');
                        ?></span></h3>

                    <div class="inside">
                        <p>
                            <?php
                            _e('Need help , Want to ask for new feature ?
                                     please contact using one of the following methods'
                                    , 'woo-poly-integration'
                            )
                            ?>
                        </p>
                        <ul>
                            <li>
                                <a href="https://github.com/hyyan/woo-poly-integration/issues" target="_blank">
                                    <?php _e('On Github', 'woo-poly-integration'); ?>
                                </a>
                            </li>
                            <li>
                                <a href="https://wordpress.org/support/plugin/woo-poly-integration" target="_blank">
                                    <?php _e('On Wordpress Support Froum', 'woo-poly-integration'); ?>
                                </a>
                            </li>
                            <li>
                                <a href="mailto:tiribthea4hyyan@gmail.com" target="_blank">
                                    <?php _e('On My Email', 'woo-poly-integration'); ?>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
