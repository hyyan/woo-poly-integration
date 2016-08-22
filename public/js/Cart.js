/*
 * This file is part of the hyyan/woo-poly-integration plugin.
 * (c) Hyyan Abo Fakher <hyyanaf@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Modified WooCommerce cart-fragments.js script to break HTML5 fragment caching. 
 * Useful when switching languages. Adds support new Cart page ajax.
 **/

/* global wc_cart_fragments_params */
jQuery(function ($) {

    // wc_cart_fragments_params is required to continue, ensure the object exists
    if (typeof wc_cart_fragments_params === 'undefined') {
        return false;
    }

    /* Storage Handling */
    var $supports_html5_storage;
    var cart_hash_key = wc_cart_fragments_params.ajax_url.toString() + '-wc_cart_hash';

    try {
        $supports_html5_storage = ('sessionStorage' in window && window.sessionStorage !== null);
        window.sessionStorage.setItem('wc', 'test');
        window.sessionStorage.removeItem('wc');
        window.localStorage.setItem('wc', 'test');
        window.localStorage.removeItem('wc');
    } catch (err) {
        $supports_html5_storage = false;
    }

    /* Cart session creation time to base expiration on */
    function set_cart_creation_timestamp() {
        if ($supports_html5_storage) {
            sessionStorage.setItem('wc_cart_created', (new Date()).getTime());
        }
    }

    /* Set the cart hash in both session and local storage */
    function set_cart_hash(cart_hash) {
        if ($supports_html5_storage) {
            localStorage.setItem(cart_hash_key, cart_hash);
            sessionStorage.setItem(cart_hash_key, cart_hash);
        }
    }

    /* Get current Polylang language */
    function get_pll_language() {
        var pll_lang = $.cookie('pll_language');

        if (pll_lang === null || pll_lang === undefined || pll_lang === '') {
            pll_lang = '';
        }

        return pll_lang;
    }

    var $fragment_refresh = {
        url: wc_cart_fragments_params.wc_ajax_url.toString().replace('%%endpoint%%', 'get_refreshed_fragments'),
        type: 'POST',
        success: function (data) {
            if (data && data.fragments) {

                $.each(data.fragments, function (key, value) {
                    $(key).replaceWith(value);
                });

                if ($supports_html5_storage) {
                    sessionStorage.setItem(wc_cart_fragments_params.fragment_name, JSON.stringify(data.fragments));
                    set_cart_hash(data.cart_hash);

                    if (data.cart_hash) {
                        set_cart_creation_timestamp();
                    }
                }

                $(document.body).trigger('wc_fragments_refreshed');
            }
        }
    };

    /* Named callback for refreshing cart fragment */
    function refresh_cart_fragment() {
        $.ajax($fragment_refresh);
    }

    /* Cart Handling */
    if ($supports_html5_storage) {

        var cart_timeout = null,
                day_in_ms = (24 * 60 * 60 * 1000);

        $(document.body).bind('wc_fragment_refresh updated_wc_div', function () {
            refresh_cart_fragment();
        });

        $(document.body).bind('added_to_cart', function (event, fragments, cart_hash) {
            var prev_cart_hash = sessionStorage.getItem(cart_hash_key);

            if (prev_cart_hash === null || prev_cart_hash === undefined || prev_cart_hash === '') {
                set_cart_creation_timestamp();
            }

            sessionStorage.setItem(wc_cart_fragments_params.fragment_name, JSON.stringify(fragments));
            set_cart_hash(cart_hash);
        });

        $(document.body).bind('wc_fragments_refreshed', function () {
            clearTimeout(cart_timeout);
            cart_timeout = setTimeout(refresh_cart_fragment, day_in_ms);
        });

        // Refresh when storage changes in another tab
        $(window).on('storage onstorage', function (e) {
            if (cart_hash_key === e.originalEvent.key && localStorage.getItem(cart_hash_key) !== sessionStorage.getItem(cart_hash_key)) {
                refresh_cart_fragment();
            }
        });

        try {
            var wc_fragments = $.parseJSON(sessionStorage.getItem(wc_cart_fragments_params.fragment_name)),
                    cart_hash = sessionStorage.getItem(cart_hash_key),
                    cookie_hash = $.cookie('woocommerce_cart_hash'),
                    cart_created = sessionStorage.getItem('wc_cart_created');

            if (cart_hash === null || cart_hash === undefined || cart_hash === '') {
                cart_hash = '';
            }

            if (cookie_hash === null || cookie_hash === undefined || cookie_hash === '') {
                cookie_hash = '';
            }

            if (cart_hash && (cart_created === null || cart_created === undefined || cart_created === '')) {
                throw 'No cart_created';
            }

            if (cart_created) {
                var cart_expiration = ((1 * cart_created) + day_in_ms),
                        timestamp_now = (new Date()).getTime();
                if (cart_expiration < timestamp_now) {
                    throw 'Fragment expired';
                }
                cart_timeout = setTimeout(refresh_cart_fragment, (cart_expiration - timestamp_now));
            }

            if (wc_fragments && wc_fragments['div.widget_shopping_cart_content'] && cart_hash === cookie_hash) {

                $.each(wc_fragments, function (key, value) {
                    $(key).replaceWith(value);
                });

                $(document.body).trigger('wc_fragments_loaded');
            } else {
                throw 'No fragment';
            }

            // Refresh when the display language changes
            var prev_pll_lang = sessionStorage.getItem('pll_language'),
                    pll_lang = get_pll_language();

            if (prev_pll_lang === null || prev_pll_lang === undefined || prev_pll_lang === '') {
                prev_pll_lang = '';
            }

            if (pll_lang) {
                if (!prev_pll_lang || prev_pll_lang !== pll_lang) {
                    sessionStorage.setItem('pll_language', pll_lang);
                    throw 'Language changed';
                }
            } else {
                throw 'Language not found';
            }

        } catch (err) {
            refresh_cart_fragment();
        }

    } else {
        refresh_cart_fragment();
    }

    /* Cart Hiding */
    if ($.cookie('woocommerce_items_in_cart') > 0) {
        $('.hide_cart_widget_if_empty').closest('.widget_shopping_cart').show();
    } else {
        $('.hide_cart_widget_if_empty').closest('.widget_shopping_cart').hide();
    }

    $(document.body).bind('adding_to_cart', function () {
        $('.hide_cart_widget_if_empty').closest('.widget_shopping_cart').show();
    });
});
