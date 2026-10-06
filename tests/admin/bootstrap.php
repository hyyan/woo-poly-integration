<?php
if (getenv('WP_ENVIRONMENT_TYPE') !== 'local') {
    throw new RuntimeException('Admin tests require the disposable local lab.');
}
define('WP_ADMIN', true);
require getenv('WP_ROOT') ?: '/var/www/html/wp-load.php';
require_once ABSPATH.'wp-admin/includes/admin.php';
if (defined('DOING_AJAX') || !is_admin() || !pll_default_language()) {
    throw new RuntimeException('Admin maintenance tests require normal admin context.');
}
