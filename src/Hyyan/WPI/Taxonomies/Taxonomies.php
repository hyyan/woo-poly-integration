<?php

/**
 * This file is part of the hyyan/woo-poly-integration plugin.
 * (c) Hyyan Abo Fakher <tiribthea4hyyan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hyyan\WPI\Taxonomies;

/**
 * Taxonomies
 *
 * @author Hyyan
 */
class Taxonomies implements TaxonomiesInterface
{
    /**
     * Managed taxonomies instances
     *
     * @var array
     */
    public $managed = array();

    /**
     * Construct object
     */
    public function __construct()
    {

        $this->managed = array(
            new Attributes(),
            new Categories(),
            new Tags(),
            new ShippingCalss()
        );

        /* Manage taxonomies translation */
        add_filter(
                'pll_get_taxonomies'
                , array($this, 'manageTaxonomiesTranslation')
        );
    }

    /**
     * Notifty polylang about product taxonomies
     *
     * @param array $taxonomies array of cutoms taxonomies managed by polylang
     *
     * @return array
     */
    public function manageTaxonomiesTranslation($taxonomies)
    {

        $new = $this->getNames();
        $options = get_option('polylang');
        $taxs = $options['taxonomies'];
        $update = false;

        foreach ($new as $tax) {
            if (!in_array($tax, $taxs)) {
                $options['taxonomies'][] = $tax;
                $update = true;
            }
        }

        if ($update) {
            update_option('polylang', $options);
        }

        return array_merge($taxonomies, $new);
    }

    /**
     * @{inheritdoc}
     */
    public function getNames()
    {
        $taxonomies = array();

        foreach ($this->managed as $tax) {
            $taxonomies = array_merge($taxonomies, $tax->getNames());
        }

        return $taxonomies;
    }

}
