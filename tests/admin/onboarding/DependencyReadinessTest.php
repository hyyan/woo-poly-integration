<?php

/**
 * This file is part of the hyyan/woo-poly-integration plugin.
 * (c) Hyyan Abo Fakher <hyyanaf@gmail.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @author Hyyan Abo Fakher <hyyanaf@gmail.com>
 */
final class DependencyReadinessTest extends TestCase
{
    /**
     * Keep the setup and Plugins screens available when a dependency is absent.
     *
     * @param array $missing plugin files excluded from the child request
     * @return void
     */
    #[DataProvider('missingDependencies')]
    public function testFreshActivationKeepsPluginsScreenAccessible(array $missing): void
    {
        $options = array('wpi_wcpagecheck_passed', 'hyyan-wpi-flash-messages');
        $original = array();
        foreach ($options as $name) {
            $original[$name] = get_option($name, null);
        }

        $script = 'define("WP_ADMIN", true);'
            . 'require_once ' . var_export(ABSPATH . 'wp-includes/plugin.php', true) . ';'
            . 'add_filter("option_active_plugins", static function ($plugins) {'
            . 'return array_values(array_diff($plugins, ' . var_export($missing, true) . ')); });'
            . 'add_filter("pre_option_wpi_wcpagecheck_passed", static function () { return "0"; });'
            . 'require ' . var_export(ABSPATH . 'wp-load.php', true) . ';'
            . 'require_once ' . var_export(ABSPATH . 'wp-admin/includes/admin.php', true) . ';'
            . 'wp_set_current_user(1);'
            . 'if (Hyyan\\WPI\\Plugin::canActivate()) { throw new RuntimeException("Dependency fixture is ready unexpectedly"); }'
            . 'set_current_screen("plugins"); Hyyan\\WPI\\Admin\\Setup::outputPage();'
            . 'echo "Plugins screen remains accessible";';

        try {
            $process = proc_open(array(PHP_BINARY, '-r', $script), array(
                1 => array('pipe', 'w'),
                2 => array('pipe', 'w'),
            ), $pipes);
            self::assertIsResource($process);
            $output = stream_get_contents($pipes[1]);
            $errors = stream_get_contents($pipes[2]);
            fclose($pipes[1]);
            fclose($pipes[2]);
            self::assertSame(0, proc_close($process), $errors);
            self::assertStringContainsString('Plugins screen remains accessible', $output);
        } finally {
            // The child bootstraps the real plugin, including its stored notices.
            wp_cache_delete('alloptions', 'options');
            foreach ($original as $name => $value) {
                wp_cache_delete($name, 'options');
                if ($value === null) {
                    delete_option($name);
                } else {
                    update_option($name, $value);
                }
            }
        }
    }

    /**
     * Get the supported missing-dependency installation scenarios.
     *
     * @return array plugin-file fixtures
     */
    public static function missingDependencies(): array
    {
        return array(
            'WooCommerce missing' => array(array('woocommerce/woocommerce.php')),
            'Polylang missing' => array(array('polylang/polylang.php')),
            'both missing' => array(array('woocommerce/woocommerce.php', 'polylang/polylang.php')),
        );
    }
}
