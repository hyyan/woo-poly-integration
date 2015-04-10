/*
 * This file is part of the hyyan/woo-poly-integration plugin.
 * (c) Hyyan Abo Fakher <tiribthea4hyyan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/*
 * Modified cart-fragments.js script to break HTML5 fragment caching. 
 * Useful with WPML when switching languages 
 * 
 * https://gist.github.com/khromov/7223963
 **/

jQuery(document).ready(function ($) {

    /** Cart Handling */
    $supports_html5_storage = ('sessionStorage' in window && window['sessionStorage'] !== null);

    $fragment_refresh = {
        url: woocommerce_params.ajax_url,
        type: 'POST',
        data: {action: 'woocommerce_get_refreshed_fragments'},
        success: function (data) {
            if (data && data.fragments) {

                $.each(data.fragments, function (key, value) {
                    $(key).replaceWith(value);
                });

                if ($supports_html5_storage) {
                    sessionStorage.setItem("wc_fragments", JSON.stringify(data.fragments));
                    sessionStorage.setItem("wc_cart_hash", data.cart_hash);
                }

                $('body').trigger('wc_fragments_refreshed');
            }
        }
    };

    //Always perform fragment refresh
    $.ajax($fragment_refresh);

    /* Cart hiding */
    if ($.cookie("woocommerce_items_in_cart") > 0)
        $('.hide_cart_widget_if_empty').closest('.widget_shopping_cart').show();
    else
        $('.hide_cart_widget_if_empty').closest('.widget_shopping_cart').hide();

    $('body').bind('adding_to_cart', function () {
        $('.hide_cart_widget_if_empty').closest('.widget_shopping_cart').show();
    });

});