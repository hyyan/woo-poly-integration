/*
 * This file is part of the hyyan/woo-poly-integration plugin.
 * (c) Hyyan Abo Fakher <tiribthea4hyyan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

(function ($, document) {

    /**
     * Constrcut the class
     * 
     * @param {jQuery} $ jquery object
     * @param {document} document
     * @returns {FieldsLocker_L9.FieldsLocker}
     */
    var FieldsLocker = function ($, document) {
        this.$ = $;
        this.document = document;
    };

    FieldsLocker.prototype = {
        /**
         * Constrcutor
         */
        constructor: FieldsLocker,
        /**
         * Do lock all fields
         */
        init: function () {

            // handle disabled items
            var disabled = this.getDisabledItems();
            for (var i = 0; i < disabled.length; i++) {
                $(disabled[i])
                        .attr('onclick', 'event.preventDefault();') // for checkboxes;
                        .css({
                            'opacity': .5,
                            'pointer-events': 'none',
                            'cursor': 'not-allowed'
                        });
            }

            // handle deleted items
            var deleted = this.getDeletedItems();
            for (var i = 0; i < deleted.length; i++) {
                $(deleted[i]).css({visibility: 'hidden'});
            }
            
        }

        /**
         * Get array of selectors to disable
         * 
         * @returns Array
         */
        , getDisabledItems: function () {
            // default ids
            var items = [
                // quick edit
                '#woocommerce-fields :input',
                // product types
                '.type_box :input',
                // inputs : general
                '#general_product_data :input:not(#_button_text):not(#_product_url)',
                // inputs : stock
                '#inventory_product_data :input',
                // inputs : shipping
                '#shipping_product_data :input',
                // inputs : related
                '#linked_product_data :input',
                // inputs : attributes
                '#product_attributes :input',
                // inputs : advanced setting
                '#comment_status',
                '#menu_order'
            ];

            // fire event to allow other plugins to extend the list
            this.$('body').trigger('getDisabledItems.Hyyan_WPI', [items]);

            return items;
        }

        /**
         * Get array of selectors to delete
         * 
         * @returns {Array}
         */
        , getDeletedItems: function () {
            // default items
            var items = [
                '#general_product_data .delete'
            ];

            // fire event to allow other plugins to extend the list
            this.$('body').trigger('getDeletedItems.Hyyan_WPI', [items]);

            return items;
        }
    };

    // bootstrap 
    $(document).ready(function ($) {
        new FieldsLocker($).init();
    });

})(window.jQuery, document);
