jQuery(document).ready(function ($) {
    var i;
    var ids = [
        '_virtual'
                , '_downloadable'
                , '_backorders'
                , 'product-type'
                , '_manage_stock'
                , '_stock'
                , '_sold_individually'
                , 'comment_status'
                , '_tax_status'
                , '_tax_class'
                , 'parent_id'
                , '_stock_status'
                //, 'crosssell_ids'
                //, 'upsell_ids'
    ];

    for (i = 0; i < ids.length; i++) {
        $('#' + ids[i]).attr('disabled', 'disabled');
    }

    var inpt_names = [
        '_width'
                , '_height'
                , '_sku'
                , '_length'
                , '_weight'
                , 'product_length'
                , '_regular_price'
                , '_sale_price'
                , '_sale_price_dates_from'
                , '_sale_price_dates_to'
                , 'menu_order'
    ];
    for (i = 0; i < inpt_names.length; i++) {
        $('input[name="' + inpt_names[i] + '"]').attr('readonly', 'readonly');

        //variation fields
        $('input[name^="variable' + inpt_names[i] + '"]').each(function () {
            $(this).attr('readonly', 'readonly');
        });
    }

    var var_checkboxes = [
        '_enabled'
                , '_is_downloadable'
                , '_is_virtual'
                , '_manage_stock'
    ];
    for (i = 0; i < var_checkboxes.length; i++) {
        $('input[name^="variable' + var_checkboxes[i] + '"]').each(function () {
            $(this).attr('readonly', 'readonly');
        });
    }


    var var_selectboxes = ['_stock_status', '_shipping_class', '_tax_class'];
    for (i = 0; i < var_selectboxes.length; i++) {
        $('select[name^="variable' + var_selectboxes[i] + '"]').each(function () {
            $(this).attr('disabled', 'disabled');
        });
    }


    $('.woocommerce_attribute_data td textarea,.attribute_values').each(function () {
        $(this).attr('readonly', 'readonly');
    });


    $('.woocommerce_variation>h3 select, #variable_product_options .toolbar select, .woocommerce_attribute_data input[type="checkbox"]').each(function () {
        $(this).attr('disabled', 'disabled');
    });

    $('form#post input[type="submit"]').click(function () {
        for (i = 0; i < ids.length; i++) {
            $('#' + ids[i]).removeAttr('disabled');
        }
        $('.woocommerce_variation select,#variable_product_options .toolbar select,.woocommerce_variation input[type="checkbox"],.woocommerce_attribute_data input[type="checkbox"]').each(function () {
            $(this).removeAttr('disabled');
        });
    });


    //quick edit fields
    for (i = 0; i < ids.length; i++) {
        $('.inline-edit-product [name="' + ids[i] + '"]').attr('disabled', 'disabled');
    }

    for (i = 0; i < inpt_names.length; i++) {
        $('.inline-edit-product [name="' + inpt_names[i] + '"]').attr('readonly', 'readonly');
    }

});
