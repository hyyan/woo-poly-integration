<?php

/**
 * This file is part of the hyyan/woo-poly-integration plugin.
 * (c) Hyyan Abo Fakher <hyyanaf@gmail.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hyyan\WPI\Taxonomies;

use Hyyan\WPI\Admin\Settings;
use Hyyan\WPI\Admin\Features;

/**
 * Taxonomies.
 *
 * @author Hyyan Abo Fakher <hyyanaf@gmail.com>
 */
class Taxonomies
{
    /**
     * Managed taxonomies.
     *
     * @var array
     */
    protected $managed = array();

    /**
     * Construct object.
     */
    public function __construct()
    {
        /* Just to prepare taxonomies  */
        $this->prepareAndGet();

        /* Manage taxonomies translation */
        add_filter(
                'pll_get_taxonomies', array($this, 'manageTaxonomiesTranslation')
        );
    }

    /**
     * Notifty polylang about product taxonomies.
     *
     * @param array $taxonomies array of cutoms taxonomies managed by polylang
     *
     * @return array
     */
    public function manageTaxonomiesTranslation($taxonomies)
    {
        $supported = $this->prepareAndGet();
        $add = $supported[0];
        $remove = $supported[1];
        $options = get_option('polylang');

        $taxs = $options['taxonomies'];
        $update = false;

        foreach ($add as $tax) {
            if (!in_array($tax, $taxs)) {
                $options['taxonomies'][] = $tax;
                $update = true;
            }
        }
        foreach ($remove as $tax) {
            if (in_array($tax, $taxs)) {
                $options['taxonomies'] = array_flip($options['taxonomies']);
                unset($options['taxonomies'][$tax]);
                $options['taxonomies'] = array_flip($options['taxonomies']);
                $update = true;
            }
        }

        if ($update) {
            update_option('polylang', $options);
        }

        return array_merge($taxonomies, $add);
    }

    /**
     * Get managed taxonomies.
     *
     * @return array taxonomies that must be added and removed to polylang
     */
    protected function prepareAndGet()
    {
        $add = array();
        $remove = array();
        $supported = array(
            'attributes' => 'Hyyan\WPI\Taxonomies\Attributes',
            'categories' => 'Hyyan\WPI\Taxonomies\Categories',
            'tags' => 'Hyyan\WPI\Taxonomies\Tags',
            'shipping-class' => 'Hyyan\WPI\Taxonomies\ShippingCalss',
        );

        foreach ($supported as $option => $class) {
            $names = $class::getNames();
            if ('on' === Settings::getOption($option, Features::getID(), 'on')) {
                $add = array_merge($add, $names);
                if (!isset($this->managed[$class])) {
                    $this->managed[$class] = new $class();
                }
            } else {
                $remove = array_merge($remove, $names);
            }
        }

        return array($add, $remove);
    }
}
