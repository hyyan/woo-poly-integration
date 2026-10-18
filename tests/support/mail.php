<?php
// Exercise native email generation without delivering mail from the disposable lab.
if (!defined('ABSPATH') || wp_get_environment_type() !== 'local') {
    return;
}
add_filter('pre_wp_mail', '__return_true');
