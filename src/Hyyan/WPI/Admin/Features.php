<?php

/**
 * This file is part of the hyyan/woo-poly-integration plugin.
 * (c) Hyyan Abo Fakher <tiribthea4hyyan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hyyan\WPI\Admin;

/**
 * Features
 *
 * @author Hyyan
 */
class Features extends AbstractSettings
{

    /**
     * {@inheritdocs}
     */
    public static function getID()
    {
        return 'wpi-features';
    }

    /**
     * {@inheritdocs}
     */
    protected function doGetSections()
    {
        return array(
            array(
                'title' => __('Features', 'woo-poly-integration'),
                'desc' => __(
                        ' The section will allow you to Enable/Disable
                          Plugin Features.'
                        , 'woo-poly-integration'
                )
            )
        );
    }

    /**
     * {@inheritdocs}
     */
    protected function doGetFields()
    {
        return array(
            array(
                'name' => 'fields-locker',
                'type' => 'checkbox',
                'default' => 'on',
                'label' => __('Fields Locker', 'woo-poly-integration'),
                'desc' => __(
                        'Fields locker makes it easy for user to know which
                         field to translate and which to ignore '
                        , 'woo-poly-integration'
                )
            ),
            array(
                'name' => 'emails',
                'type' => 'checkbox',
                'default' => 'on',
                'label' => __('Emails', 'woo-poly-integration'),
                'desc' => __(
                        'Use order language whenever woocommerce sends order emails'
                        , 'woo-poly-integration'
                )
            ),
            array(
                'name' => 'reports',
                'type' => 'checkbox',
                'default' => 'on',
                'label' => __('Reports', 'woo-poly-integration'),
                'desc' => __(
                        'Enable reports langauge filtering and combining'
                        , 'woo-poly-integration'
                )
            ),
            array(
                'name' => 'coupons',
                'type' => 'checkbox',
                'default' => 'on',
                'label' => __('Coupons Sync', 'woo-poly-integration'),
                'desc' => __(
                        'Apply coupons rules for product and its translations'
                        , 'woo-poly-integration'
                )
            ),
            array(
                'name' => 'stock',
                'type' => 'checkbox',
                'default' => 'on',
                'label' => __('Stock Sync', 'woo-poly-integration'),
                'desc' => __(
                        'Sync stock for product and its translations'
                        , 'woo-poly-integration'
                )
            ),
            array(
                'name' => 'categories',
                'type' => 'checkbox',
                'default' => 'on',
                'label' => __('Translate Categories', 'woo-poly-integration'),
                'desc' => __(
                        'Enable categories translations'
                        , 'woo-poly-integration'
                )
            ),
            array(
                'name' => 'tags',
                'type' => 'checkbox',
                'default' => 'on',
                'label' => __('Translate Tags', 'woo-poly-integration'),
                'desc' => __(
                        'Enable tags translations'
                        , 'woo-poly-integration'
                )
            ),
            array(
                'name' => 'attributes',
                'type' => 'checkbox',
                'default' => 'on',
                'label' => __('Translate Attributes', 'woo-poly-integration'),
                'desc' => __(
                        'Enable Attributes translations'
                        , 'woo-poly-integration'
                )
            ),
            array(
                'name' => 'shipping-class',
                'type' => 'checkbox',
                'default' => 'off',
                'label' => __('Translate ShippingClass', 'woo-poly-integration'),
                'desc' => __(
                        'Enable ShippingClass translations'
                        , 'woo-poly-integration'
                )
            )
        );
    }

}
