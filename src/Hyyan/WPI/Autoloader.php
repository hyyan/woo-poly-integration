<?php

/**
 * This file is part of the hyyan/woo-poly-integration plugin.
 * (c) Hyyan Abo Fakher <hyyanaf@gmail.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hyyan\WPI;

/**
 * Plugin Namespace Autoloader.
 *
 * @author Hyyan Abo Fakher <hyyanaf@gmail.com>
 */
final class Autoloader
{
    /**
     * @var string
     */
    private $base;

    /**
     * Construct the autoloader class.
     *
     * @param string $base the base path to use
     *
     * @throws \Exception when the autloader can not register itself
     */
    public function __construct($base)
    {
        $this->base = $base;
        spl_autoload_register(array($this, 'handle'), true, true);
    }

    /**
     * Handle autoloading.
     *
     * @param string $className class or inteface name
     *
     * @return bool true if class or interface exists , false otherwise
     */
    public function handle($className)
    {
        if (stripos($className, "Hyyan\WPI") === false) {
            return;
        }

        $filename = $this->base.str_replace('\\', '/', $className).'.php';
        if (file_exists($filename)) {
            require_once $filename;
            if (
                    class_exists($className) ||
                    interface_exists($className)
            ) {
                return true;
            }
        }

        return false;
    }
}
