<?php
if (getenv('WP_ENVIRONMENT_TYPE') !== 'local') {
    throw new RuntimeException('Frontend tests require the disposable local lab.');
}
require getenv('WP_ROOT') ?: '/var/www/html/wp-load.php';
if (is_admin() || !class_exists('Hyyan\\WPI\\Widgets\\LayeredNav') || !pll_default_language()) {
    throw new RuntimeException('Frontend tests require configured dependencies and frontend context.');
}
