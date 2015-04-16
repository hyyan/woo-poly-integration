<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Hyyan\WPI;

/**
 * Plugin Namespace Autoloader
 *
 * @author Hyyan
 */
class Autoloader
{
    /**
     * @var String
     */
    private $base;

    /**
     * Constrcut the autoloader class
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
     * Handle autoloading
     *
     * @param string $className class or inteface name
     *
     * @return boolean true if class or interface exists , false otherwise
     */
    public function handle($className)
    {
        if (stripos($className, "Hyyan\WPI") === false) {
            return;
        }

        $filename = $this->base . str_replace('\\', '/', $className) . ".php";
        var_dump($filename);
        if (file_exists($filename)) {
            require_once($filename);
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
