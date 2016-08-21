<?php

/**
 * This file is part of the hyyan/woo-poly-integration plugin.
 * (c) Hyyan Abo Fakher <hyyanaf@gmail.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hyyan\WPI\Admin;

/**
 * SettingsInterface.
 *
 * @author Hyyan Abo Fakher <hyyanaf@gmail.com>
 */
interface SettingsInterface
{
    /**
     * Get sections array.
     *
     * @param array $sections current registered sections
     *
     * @return array array of sections
     */
    public function getSections(array $sections);

    /**
     * Get fields array.
     *
     * @param array $fields current registered fields
     *
     * @return array
     */
    public function getFields(array $fields);

    /**
     * Get settings ID.
     *
     * @return string setting ID
     */
    public static function getID();
}
