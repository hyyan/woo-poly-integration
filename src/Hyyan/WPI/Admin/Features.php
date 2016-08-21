<?php

/**
 * This file is part of the hyyan/woo-poly-integration plugin.
 * (c) Hyyan Abo Fakher <hyyanaf@gmail.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hyyan\WPI\Admin;

use Hyyan\WPI\Utilities;

/**
 * Features.
 *
 * @author Hyyan Abo Fakher <hyyanaf@gmail.com>
 */
class Features extends AbstractSettings
{
    /**
     * {@inheritdoc}
     */
    public static function getID()
    {
        return 'wpi-features';
    }

    /**
     * {@inheritdoc}
     */
    protected function doGetSections()
    {
        return array(
            array(
                'title' => __('Features', 'woo-poly-integration'),
                'desc' => __(
                        ' The section will allow you to Enable/Disable
                          Plugin Features.', 'woo-poly-integration'
                ),
            ),
        );
    }

    /**
     * {@inheritdoc}
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
                         field to translate and which to ignore ', 'woo-poly-integration'
                ),
            ),
            array(
                'name' => 'emails',
                'type' => 'checkbox',
                'default' => 'on',
                'label' => __('Emails', 'woo-poly-integration'),
                'desc' => __(
                        'Use order language whenever woocommerce sends order emails', 'woo-poly-integration'
                ),
            ),
            array(
                'name' => 'reports',
                'type' => 'checkbox',
                'default' => 'on',
                'label' => __('Reports', 'woo-poly-integration'),
                'desc' => __(
                        'Enable reports language filtering and combining', 'woo-poly-integration'
                ),
            ),
            array(
                'name' => 'coupons',
                'type' => 'checkbox',
                'default' => 'on',
                'label' => __('Coupons Sync', 'woo-poly-integration'),
                'desc' => __(
                        'Apply coupons rules for product and its translations', 'woo-poly-integration'
                ),
            ),
            array(
                'name' => 'stock',
                'type' => 'checkbox',
                'default' => 'on',
                'label' => __('Stock Sync', 'woo-poly-integration'),
                'desc' => __(
                        'Sync stock for product and its translations', 'woo-poly-integration'
                ),
            ),
            array(
                'name' => 'categories',
                'type' => 'checkbox',
                'default' => 'on',
                'label' => __('Translate Categories', 'woo-poly-integration'),
                'desc' => __(
                        'Enable categories translations', 'woo-poly-integration'
                ),
            ),
            array(
                'name' => 'tags',
                'type' => 'checkbox',
                'default' => 'on',
                'label' => __('Translate Tags', 'woo-poly-integration'),
                'desc' => __(
                        'Enable tags translations', 'woo-poly-integration'
                ),
            ),
            array(
                'name' => 'attributes',
                'type' => 'checkbox',
                'default' => 'on',
                'label' => __('Translate Attributes', 'woo-poly-integration'),
                'desc' => __(
                        'Enable attributes translations', 'woo-poly-integration'
                ),
            ),
            array(
                'name' => 'shipping-class',
                'type' => 'checkbox',
                'default' => 'off',
                'label' => __('Translate Shipping Classes', 'woo-poly-integration'),
                'desc' => __(
                        'Enable shipping classes translations'.(Utilities::woocommerceVersionCheck('2.6') ? ' (not supported for WooCommerce versions >= 2.6)' : ''), 'woo-poly-integration'
                ),
            ),
        );
    }
}
