<?php

/**
 * This file is part of the hyyan/woo-poly-integration plugin.
 * (c) Hyyan Abo Fakher <hyyanaf@gmail.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hyyan\WPI\Admin;

use Hyyan\WPI\Product\DeepL;
use Hyyan\WPI\Utilities;

/**
 * Features.
 *
 * @author Hyyan Abo Fakher <hyyanaf@gmail.com>
 */
class Features extends AbstractSettings
{
	const DEEPL_KEY_MASK = '********';

    /**
     * {@inheritdoc}
     */
    public static function getID()
    {
        return 'wpi-features';
    }

	/**
	 * Keep the credential out of the password field and the public features array.
	 *
	 * @param mixed $value Submitted password field.
	 * @return string Mask for the stored credential, or an empty field.
	 */
	public function sanitizeDeepLKey( $value ) {
		$value = is_string( $value ) ? trim( $value ) : null;

		if ( '' === $value ) {
			delete_option( DeepL::STORE_KEY );

			return '';
		}

		if ( self::DEEPL_KEY_MASK !== $value && ! DeepL::saveStoreKey( $value ) ) {
			add_settings_error( self::getID(), 'deepl-key', __( 'Could not save the DeepL API key.', 'woo-poly-integration' ) );
		}

		return DeepL::storeKey() ? self::DEEPL_KEY_MASK : '';
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
                ) . ' ' . __(
                        'For more information please see:', 'woo-poly-integration'
				) . ' <a href="' . esc_url( \Hyyan\WPI\Admin\Guide::url( 'settings/features' ) ) . '"> ' .
                    __('documentation pages', 'woo-poly-integration') . '</a>.',
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
				'name'              => 'deepl-api-key',
				'type'              => 'password',
				'default'           => DeepL::storeKey() ? self::DEEPL_KEY_MASK : '',
				'label'             => __( 'DeepL API key', 'woo-poly-integration' ),
				'desc'              => __( 'Store key for product translation. Enter a new key to replace it or clear the field to remove it.', 'woo-poly-integration' ) . ' <a href="' . esc_url( Guide::url( 'translations/deepl' ) . '#connect-a-deepl-api-key' ) . '">' . esc_html__( 'DeepL setup guide', 'woo-poly-integration' ) . '</a>',
				'sanitize_callback' => array( $this, 'sanitizeDeepLKey' ),
			),
			array(
				'name'  => 'deepl-meta-keys',
				'type'  => 'text',
				'label' => __( 'DeepL product text fields', 'woo-poly-integration' ),
				'desc'  => __( 'Additional custom field keys containing translatable product text, separated by commas.', 'woo-poly-integration' ) . ' <a href="' . esc_url( Guide::url( 'translations/deepl' ) . '#include-custom-text-fields' ) . '">' . esc_html__( 'Custom field example', 'woo-poly-integration' ) . '</a>',
			),
            array(
                'name' => 'fields-locker',
                'type' => 'checkbox',
                'default' => 'on',
                'label' => __('Fields Locker', 'woo-poly-integration'),
                'desc' => __(
                        'Locks Meta fields which are set to be synchronized.', 'woo-poly-integration'
                ),
            ),
            array(
                'name' => 'emails',
                'type' => 'checkbox',
                'default' => 'on',
                'label' => __('Emails', 'woo-poly-integration'),
                'desc' => __(
                        'Use order language whenever WooCommerce sends order emails', 'woo-poly-integration'
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
				'desc'    => ' <a href="' . esc_url( \Hyyan\WPI\Admin\Guide::url( 'products/stock' ) ) . '"> ' .
                            __(
                        'Sync stock for product and its translations', 'woo-poly-integration'
                ) . '.</a> <strong>' . __(
                        'Note: this setting affects user actions on stock, to control synchronisation when editing products check the settings for Metas List, Stock Metas.', 'woo-poly-integration'
                ) . '</strong>',
            ),
            array(
                'name' => 'categories',
                'type' => 'checkbox',
                'default' => 'on',
                'label' => __('Translate Categories', 'woo-poly-integration'),
				'desc'    => ' <a href="' . esc_url( \Hyyan\WPI\Admin\Guide::url( 'products/categories' ) ) . '"> ' .
                            __(
                        'Enable categories translations', 'woo-poly-integration'
                )   . '</a>.',
            ),
            array(
                'name' => 'tags',
                'type' => 'checkbox',
                'default' => 'on',
                'label' => __('Translate Tags', 'woo-poly-integration'),
				'desc'    => ' <a href="' . esc_url( \Hyyan\WPI\Admin\Guide::url( 'products/tags' ) ) . '"> ' .
                            __(
                        'Enable tags translations', 'woo-poly-integration'
                )   . '</a>.',
            ),
            array(
                'name' => 'attributes',
                'type' => 'checkbox',
                'default' => 'on',
                'label' => __('Translate Attributes', 'woo-poly-integration'),
				'desc'    => ' <a href="' . esc_url( \Hyyan\WPI\Admin\Guide::url( 'products/attributes' ) ) . '"> ' .
                            __(
                        'Enable attributes translations', 'woo-poly-integration'
                )  . '</a>.',
            ),

            array(
                'name' => 'new-translation-defaults',
                'type' => 'radio',
                'default' => '0',   //starting this off for backwards compatibility, users should test before turning on
                'label' => __('New Translation Behaviour', 'woo-poly-integration'),
                'desc' => __(
                        'When creating new translations, start with blank text, copy or machine translation? (You may want to turn this off if using Polylang Pro, Lingotek or other automatic copy-or-translation solution.) ', 'woo-poly-integration'
                ),
                'options' => array(
                    '0' => 'Blank Text',
                    '1' => __('Copy Source', 'woo-poly-integration'),
                    '2' => __('Translate Source', 'woo-poly-integration') . ' (coming soon..  until available will use Copy Source) ',
                )
            ),
            array(
                'name' => 'localenumbers',
                'type' => 'checkbox',
                'default' => 'on',
                'label' => __('Use locale number formats', 'woo-poly-integration'),
				'desc'    => ' <a href="' . esc_url( \Hyyan\WPI\Admin\Guide::url( 'products/price-localization' ) ) . '"> ' .
                   __(
                        'Format numbers according to the convention for current language', 'woo-poly-integration')
                 . '</a>.'
                ,
            ),
            array(
                'name' => 'importsync',
                'type' => 'checkbox',
                'default' => 'on',
                'label' => __('Synchronize on Import', 'woo-poly-integration'),
				'desc'    => ' <a href="' . esc_url( \Hyyan\WPI\Admin\Guide::url( 'products/import-and-bulk-edit' ) ) . '"> ' .
                   __(
                        'When using WooCommerce 3.1 importer to importing updates to existing items, apply synchronization rules to update any existing translations.', 'woo-poly-integration')
                 . '</a>.'
                ,
            ),
            array(
              'name'		 => 'checkpages',
              'type'		 => 'checkbox',
              'default'	 => 'off',
              'label'		 => __( 'Check WooCommerce Pages', 'woo-poly-integration' ),
              'desc'		 => '' .
              __(
              'Check if WooCommerce pages are present and correctly translated and published. Especially helpul on initial setup', 'woo-poly-integration' )
              . '.'
            ,
            ),
        );
    }
}
