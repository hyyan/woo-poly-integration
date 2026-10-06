<?php
if (getenv('WP_ENVIRONMENT_TYPE') !== 'local') {
    throw new RuntimeException('Integration tests require the disposable local lab.');
}
define('DOING_AJAX', true);
define('WP_ADMIN', true);
require getenv('WP_ROOT') ?: '/var/www/html/wp-load.php';
if (!class_exists('Hyyan\WPI\Product\Variable') || !pll_default_language()) {
    throw new RuntimeException('Activate dependencies/plugin and configure test languages first.');
}
final class WooPolyTestDie extends RuntimeException {}
add_filter('wp_die_ajax_handler', function () {
    return function ($message) { throw new WooPolyTestDie((string) $message); };
});
