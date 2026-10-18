<?php
use PHPUnit\Framework\TestCase;

final class PluginBootstrapContractTest extends TestCase
{
    public function testPluginCoexistsWithPreviouslyDeclaredGlobalCallbackNames(): void
    {
        $script = 'function onActivate() {} function wpi_status_report() {} require '.var_export(ABSPATH.'wp-load.php', true).'; Hyyan\\WPI\\Plugin::admin_activate(); echo "bootstrap complete";';
        $process = proc_open(array(PHP_BINARY, '-r', $script), array(1 => array('pipe', 'w'), 2 => array('pipe', 'w')), $pipes);
        self::assertIsResource($process);
        $output = stream_get_contents($pipes[1]);
        $errors = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        self::assertSame(0, proc_close($process), $errors);
        self::assertSame('bootstrap complete', $output);
    }

    public function testNativeActivationHookRetainsExistingStoredOptionBehavior(): void
    {
        $names = array('wpi_wcpagecheck_passed', 'hyyan-wpi-flash-messages');
        $original = array();
        foreach ($names as $name) { $original[$name] = get_option($name, null); }
        try {
            update_option('wpi_wcpagecheck_passed', true);
            update_option('hyyan-wpi-flash-messages', array('fixture' => 'notice'));
            do_action('activate_woo-poly-integration/__init__.php', false);
            self::assertFalse(get_option('wpi_wcpagecheck_passed'));
            self::assertSame('', get_option('hyyan-wpi-flash-messages'));
        } finally {
            foreach ($original as $name => $value) {
                if ($value === null) { delete_option($name); }
                else { update_option($name, $value); }
            }
        }
    }
}
