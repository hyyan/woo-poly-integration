<?php

/**
 * This file is part of the hyyan/woo-poly-integration plugin.
 * (c) Hyyan Abo Fakher <hyyanaf@gmail.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hyyan\WPI\Admin;

use Hyyan\WPI\HooksInterface;

/**
 * AbstractSettings.
 *
 * @author Hyyan
 * @author Hyyan Abo Fakher <hyyanaf@gmail.com>
 */
abstract class AbstractSettings implements SettingsInterface
{
    /**
     * Construct object.
     */
    public function __construct()
    {
        add_filter(
                HooksInterface::SETTINGS_SECTIONS_FILTER, array($this, 'getSections')
        );
        add_filter(
                HooksInterface::SETTINGS_FIELDS_FILTER, array($this, 'getFields')
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getSections(array $sections)
    {
        $new = array();
        $id = static::getID();
        $current = apply_filters(
                "woo_poly_integration_settings_{$id}_sections", (array) $this->doGetSections()
        );

        foreach ($current as $def) {
            $def['id'] = static::getID();
            $new[static::getID()] = $def;
        }

        return array_merge($sections, $new);
    }

    /**
     * {@inheritdoc}
     */
    public function getFields(array $fields)
    {
        $id = static::getID();
        return array_merge($fields, array(
            static::getID() => apply_filters(
                    "woo_poly_integration_settings_{$id}_fields", (array) $this->doGetFields()
            ),
        ));
    }

    /**
     * @see SettingsInterface::getSections()
     */
    abstract protected function doGetSections();

    /**
     * @see SettingsInterface::getFields()
     */
    abstract protected function doGetFields();
}
