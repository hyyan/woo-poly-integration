<?php

/**
 * This file is part of the hyyan/woo-poly-integration plugin.
 * (c) Hyyan Abo Fakher <hyyanaf@gmail.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hyyan\WPI\Admin;

use Hyyan\WPI\Product\Meta;

/**
 * MetasList.
 *
 * @author Hyyan Abo Fakher <hyyanaf@gmail.com>
 */
class MetasList extends AbstractSettings
{
    /**
     * {@inheritdoc}
     */
    public static function getID()
    {
        return 'wpi-metas-list';
    }

    /**
     * {@inheritdoc}
     */
    protected function doGetSections()
    {
        return array(
            array(
                'title' => __('Metas List', 'woo-poly-integration'),
                'desc' => __(
                        'The section will allow you to control which metas should be
                         synced between products and their translations. The default
                         values are appropriate for the large majority of the users.
                         It is safe to ignore these settings if you do not understand
                         their meaning.Please ignore this section if you do not
                         understand the meaning of this.
                        ', 'woo-poly-integration'
                ) . ' ' . __(
                        'For more information please see:', 'woo-poly-integration'
                ) . ' <a target="_blank" href="https://github.com/hyyan/woo-poly-integration/wiki/Settings-Metas">' .
                    __('documentation pages', 'woo-poly-integration') . '</a>.'
                ,
            ),
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function doGetFields()
    {

        /* Metas list */
        $metas = Meta::getProductMetaToCopy(array(), false);
        $fields = array();
        foreach ($metas as $ID => $value) {
            $fields[] = array(
                'name' => $ID,
                'label' => $value['name'],
                'desc' => $value['desc'],
                'type' => 'multicheck',
                'default' => array_combine($value['metas'], $value['metas']),
                'options' => array_combine(
                        $value['metas'], array_map(array(__CLASS__, 'normalize'), $value['metas'])
                ),
            );
        }

        return $fields;
    }

    /**
     * Normalize string by removing "_", and leading and trailing spaces from string.
     *
     * @param string $string
     *
     * @return string
     */
    public static function normalize($string)
    {
        return ucwords(trim(str_replace('_', ' ', $string)));
    }
}
