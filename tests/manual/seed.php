<?php

use Hyyan\WPI\Product\Variation;
use Automattic\WooCommerce\Internal\ProductDownloads\ApprovedDirectories\Register;

if (wp_get_environment_type() !== 'local' || $GLOBALS['wpdb']->prefix !== 'wpi_manual_') {
    throw new RuntimeException('Dedicated manual store required');
}

wp_set_current_user(1);
$fixture = get_option('wpi_manual_fixture');

if ($fixture) {
    $format = getenv('WPI_MANUAL_MODE');
    if ($format) {
        if (!in_array($format, array('classic', 'block'), true)) {
            throw new RuntimeException('Unknown checkout format');
        }
        foreach (array('cart', 'checkout') as $page) {
            update_option('woocommerce_' . $page . '_page_id', $fixture['pages'][$format][$page]['en']);
        }
        flush_rewrite_rules();
    }
    echo "Existing manual fixtures retained.\n";
    return;
}

$fixture = array('schema' => 1, 'pages' => array(), 'products' => array(), 'terms' => array());

// Create accounts for each permission level.
$accounts = array(
    'manual-manager' => 'shop_manager',
    'manual-customer' => 'customer',
    'manual-subscriber' => 'subscriber',
);
foreach ($accounts as $login => $role) {
    $user = wp_insert_user(array(
        'user_login' => $login,
        'user_pass' => 'manual-password',
        'user_email' => $login . '@example.invalid',
        'role' => $role,
    ));
    if (is_wp_error($user)) {
        throw new RuntimeException($user->get_error_message());
    }
    $address = array(
        'first_name' => 'Manual',
        'last_name' => 'Customer',
        'address_1' => '10 Test Street',
        'city' => 'London',
        'postcode' => 'SW1A 1AA',
        'country' => 'GB',
    );
    foreach ($address as $field => $value) {
        update_user_meta($user, 'billing_' . $field, $value);
        update_user_meta($user, 'shipping_' . $field, $value);
    }
}

// Preserve native block markup before selecting the classic checkout pages.
$blockContent = array();
foreach (array('cart', 'checkout') as $page) {
    $blockContent[$page] = get_post_field('post_content', wc_get_page_id($page));
    if (!has_block('woocommerce/' . $page, $blockContent[$page])) {
        throw new RuntimeException('Native WooCommerce block page required');
    }
}

foreach (array('classic', 'block') as $format) {
    $pages = $format === 'classic' ? array('shop', 'cart', 'checkout', 'myaccount') : array('cart', 'checkout');
    foreach ($pages as $page) {
        foreach (array('en', 'de') as $language) {
            $shortcode = $page === 'myaccount' ? 'my_account' : $page;
            $content = $page === 'shop' ? '' : '[woocommerce_' . $shortcode . ']';
            if ($format === 'block') {
                $content = $blockContent[$page];
            }
            $id = wp_insert_post(array(
                'post_type' => 'page',
                'post_status' => 'publish',
                'post_title' => ucfirst($page) . ' ' . $format . ' ' . strtoupper($language),
                'post_content' => $content,
            ), true);
            if (is_wp_error($id)) {
                throw new RuntimeException($id->get_error_message());
            }
            pll_set_post_language($id, $language);
            $fixture['pages'][$format][$page][$language] = $id;
        }
        pll_save_post_translations($fixture['pages'][$format][$page]);
    }
}

// Create linked categories, tags and global colour terms.
$attribute = wc_create_attribute(array(
    'name' => 'Colour',
    'slug' => 'manual_colour',
    'type' => 'select',
    'order_by' => 'menu_order',
    'has_archives' => true,
));
if (is_wp_error($attribute)) {
    throw new RuntimeException($attribute->get_error_message());
}
delete_transient('wc_attribute_taxonomies');
register_taxonomy('pa_manual_colour', array('product'), array('hierarchical' => false));

$termNames = array(
    'product_cat' => array('en' => 'Clothing', 'de' => 'Kleidung'),
    'product_tag' => array('en' => 'Featured', 'de' => 'Empfohlen'),
    'red' => array('en' => 'Red', 'de' => 'Rot'),
    'blue' => array('en' => 'Blue', 'de' => 'Blau'),
);
foreach ($termNames as $kind => $names) {
    $taxonomy = in_array($kind, array('red', 'blue'), true) ? 'pa_manual_colour' : $kind;
    foreach ($names as $language => $name) {
        $term = wp_insert_term($name, $taxonomy);
        if (is_wp_error($term)) {
            throw new RuntimeException($term->get_error_message());
        }
        $fixture['terms'][$kind][$language] = (int) $term['term_id'];
        pll_set_term_language($term['term_id'], $language);
    }
    pll_save_term_translations($fixture['terms'][$kind]);
}

// Each product has an English and German version with the same SKU.
$productKinds = array('simple', 'variable', 'parent-stock', 'wildcard', 'backorder', 'download');
foreach ($productKinds as $kind) {
    foreach (array('en', 'de') as $language) {
        $simple = in_array($kind, array('simple', 'download'), true);
        $product = $simple ? new WC_Product_Simple() : new WC_Product_Variable();
        $product->set_name('Manual ' . $kind . ' ' . strtoupper($language));
        $product->set_status('publish');
        $product->set_description($language === 'de'
            ? 'Unabhaengige deutsche Beschreibung.'
            : 'Independent English description.');
        $product->set_category_ids(array($fixture['terms']['product_cat'][$language]));
        $product->set_tag_ids(array($fixture['terms']['product_tag'][$language]));
        $product->set_weight('1');
        $product->set_length('1');
        $product->set_width('1');
        $product->set_height('1');

        if ($simple) {
            $product->set_regular_price('20');
            $product->set_manage_stock(true);
            $product->set_stock_quantity(10);
        } else {
            $colour = new WC_Product_Attribute();
            $colour->set_id($attribute);
            $colour->set_name('pa_manual_colour');
            $colour->set_options(array(
                $fixture['terms']['red'][$language],
                $fixture['terms']['blue'][$language],
            ));
            $colour->set_visible(true);
            $colour->set_variation(true);
            $product->set_attributes(array($colour));
            if ($kind === 'parent-stock') {
                $product->set_manage_stock(true);
                $product->set_stock_quantity(10);
            }
        }

        if ($kind === 'download') {
            $uploads = wp_upload_dir();
            $directory = $uploads['basedir'] . '/manual-downloads';
            if (!wp_mkdir_p($directory)) {
                throw new RuntimeException('Download directory creation failed');
            }
            $url = $uploads['baseurl'] . '/manual-downloads/';
            wc_get_container()->get(Register::class)->add_approved_directory($url);
            $filename = 'manual-' . $language . '.txt';
            if (file_put_contents($directory . '/' . $filename, 'Manual download ' . $language) === false) {
                throw new RuntimeException('Download file creation failed');
            }
            $download = new WC_Product_Download();
            $download->set_id('manual-file');
            $download->set_name('Manual file ' . strtoupper($language));
            $download->set_file($url . $filename);
            $product->set_virtual(true);
            $product->set_downloadable(true);
            $product->set_downloads(array($download));
        }

        $product->save();
        pll_set_post_language($product->get_id(), $language);
        $product->set_sku('manual-' . $kind);
        $product->save();
        $fixture['products'][$kind][$language] = array(
            'parent' => $product->get_id(),
            'variations' => array(),
        );

        if (!$simple) {
            $colours = $kind === 'wildcard' ? array('any') : array('red', 'blue');
            foreach ($colours as $colour) {
                $variation = new WC_Product_Variation();
                $variation->set_parent_id($product->get_id());
                $variation->set_status('publish');
                $variation->set_regular_price($colour === 'blue' ? '25' : '20');
                $slug = $colour === 'any' ? '' : get_term($fixture['terms'][$colour][$language], 'pa_manual_colour')->slug;
                $variation->set_attributes(array('pa_manual_colour' => $slug));
                $variation->set_manage_stock($kind !== 'parent-stock');
                if ($kind !== 'parent-stock') {
                    $variation->set_stock_quantity($kind === 'backorder' ? 0 : 10);
                }
                if ($kind === 'backorder') {
                    $variation->set_backorders('notify');
                }
                $variation->set_weight('1');
                $variation->set_length('1');
                $variation->set_width('1');
                $variation->set_height('1');
                $variation->save();
                pll_set_post_language($variation->get_id(), $language);
                $fixture['products'][$kind][$language]['variations'][$colour] = $variation->get_id();
            }
            WC_Product_Variable::sync($product->get_id());
        }
    }
    pll_save_post_translations(array(
        'en' => $fixture['products'][$kind]['en']['parent'],
        'de' => $fixture['products'][$kind]['de']['parent'],
    ));
    foreach ($fixture['products'][$kind] as $product) {
        foreach ($product['variations'] as $colour => $id) {
            update_post_meta($id, Variation::DUPLICATE_KEY, $fixture['products'][$kind]['en']['variations'][$colour]);
        }
    }
}

// Coupons cover fixed, percentage and translated category restrictions.
$coupons = array(
    'manual5' => array('fixed_cart', '5', '10'),
    'manual10' => array('percent', '10', ''),
    'manual-category' => array('fixed_product', '3', ''),
);
foreach ($coupons as $code => $settings) {
    $coupon = new WC_Coupon();
    $coupon->set_code($code);
    $coupon->set_discount_type($settings[0]);
    $coupon->set_amount($settings[1]);
    if ($settings[2]) {
        $coupon->set_minimum_amount($settings[2]);
    }
    if ($code === 'manual-category') {
        $coupon->set_product_categories(array($fixture['terms']['product_cat']['en']));
    }
    $coupon->save();
}

// Offline payment, UK shipping and VAT allow complete local purchases.
$zone = new WC_Shipping_Zone();
$zone->set_zone_name('Manual UK');
$zone->add_location('GB', 'country');
$zone->save();
$shippingMethods = array(
    'flat_rate' => array('title' => 'Manual delivery', 'cost' => '5'),
    'local_pickup' => array('title' => 'Manual pickup', 'cost' => '0'),
);
foreach ($shippingMethods as $method => $settings) {
    $id = $zone->add_shipping_method($method);
    update_option('woocommerce_' . $method . '_' . $id . '_settings', array_merge(
        array('enabled' => 'yes', 'tax_status' => 'taxable'),
        $settings
    ));
}
WC_Tax::_insert_tax_rate(array(
    'tax_rate_country' => 'GB',
    'tax_rate_state' => '',
    'tax_rate' => '20.0000',
    'tax_rate_name' => 'Manual VAT',
    'tax_rate_priority' => 1,
    'tax_rate_compound' => 0,
    'tax_rate_shipping' => 1,
    'tax_rate_order' => 0,
    'tax_rate_class' => '',
));
$options = array(
    'woocommerce_default_country' => 'GB',
    'woocommerce_store_city' => 'London',
    'woocommerce_store_postcode' => 'SW1A 1AA',
    'woocommerce_currency' => 'GBP',
    'woocommerce_enable_guest_checkout' => 'yes',
    'woocommerce_enable_myaccount_registration' => 'yes',
    'woocommerce_calc_taxes' => 'yes',
    'woocommerce_prices_include_tax' => 'no',
    'woocommerce_custom_orders_table_enabled' => 'no',
    'woocommerce_custom_orders_table_data_sync_enabled' => 'no',
);
foreach ($options as $name => $value) {
    update_option($name, $value);
}
update_option('woocommerce_cheque_settings', array(
    'enabled' => 'yes',
    'title' => 'Manual offline payment',
    'description' => 'No payment is taken.',
    'instructions' => 'Manual fixture order.',
));

$language = PLL()->model->get_language('de');
$strings = new PLL_MO();
$strings->import_from_db($language);
$translations = array(
    'Manual delivery' => 'Testlieferung',
    'Manual pickup' => 'Abholung',
    'Manual offline payment' => 'Testzahlung',
    'Manual VAT' => 'Test MwSt',
);
foreach ($translations as $source => $target) {
    $strings->add_entry(new Translation_Entry(array(
        'singular' => $source,
        'translations' => array($target),
    )));
}
$strings->export_to_db($language);

foreach (array('shop', 'cart', 'checkout', 'myaccount') as $page) {
    update_option('woocommerce_' . $page . '_page_id', $fixture['pages']['classic'][$page]['en']);
}
$GLOBALS['wp_rewrite']->set_permalink_structure('/%postname%/');
flush_rewrite_rules(true);
update_option('wpi_manual_fixture', $fixture, false);
echo "Manual fixtures created.\n";
