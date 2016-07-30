/*
 * This file is part of the hyyan/woo-poly-integration plugin.
 * (c) Hyyan Abo Fakher <hyyanaf@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

(function ($, document, HYYAN_WPI_VARIABLES) {

    /**
     * Construct variables
     * 
     * @param {jQuery} $
     * @param {document} document
     * @param {object} data
     * 
     * @returns {Variables}
     */
    var Variables = function ($, document, data) {
        this.$ = $;
        this.document = document;
        this.data = data;
        this._dialog = this._createDialog();
    };

    Variables.prototype = {
        /** Constructor */
        constructor: Variables,
        init: function () {

            var self = this;
            $('#post_lang_choice').change(function () {
                self.shouldAlert();
            });
            $('#product-type').change(function () {
                self.shouldAlert();
            });

        }
        /**
         * Check if we should alert the user
         * 
         * @returns {Boolean}
         */
        , shouldAlert: function () {
            var type = this.getCurrentProduct();
            var lang = this.getCurrentLanguage();
            if (type === 'variable' && lang !== this.data['defaultLang']) {
                this._dialog.dialog('open');
                /* Disable Saving */
                this._changeSaveBoxState(true);
                return true;
            }
            this._changeSaveBoxState(false);
            return false;
        }
        /**
         * Get current product language
         * 
         * @returns {String}
         */
        , getCurrentLanguage: function () {
            return this.$('#post_lang_choice').val();
        }
        /**
         * Get current product type
         * 
         * @returns {string}
         */
        , getCurrentProduct: function () {
            return this.$('#product-type').val();
        }
        /**
         * Change the state of save box from disable to enable and vs
         * 
         * @param {boolean} enable
         */
        , _changeSaveBoxState: function (enable) {
            this.$('#submitdiv *').attr('disabled', enable);
        }
        /**
         * Create dialog
         * 
         * @returns {objet}
         */
        , _createDialog: function () {

            /* This variable */
            var self = this;

            /* Fix the z-index */
            this.$('<style>.ui-dialog { z-index: 1000 !important ;}</style>')
                    .appendTo($('head'));

            /* Create dialog */
            var dialog = this.$("<div id='woo-poly-variables-dialog'/>")
                    .html('<p>' + self.data['content'] + '</p>')
                    .attr('title', self.data['title'])
                    .appendTo("body");


            dialog.dialog({
                dialogClass: "wp-dialog",
                autoOpen: false,
                modal: true,
                width: 400,
                height: 250,
                position: {
                    my: "center",
                    at: "center"
                },
                buttons: [
                    {
                        text: 'Got It',
                        click: function () {
                            $(this).dialog('close');
                        }
                    }
                ]
            });

            return dialog;
        }
    };

    // bootstrap 
    $(document).ready(function ($) {
        new Variables($, document, HYYAN_WPI_VARIABLES).init();
    });


})(jQuery, document, HYYAN_WPI_VARIABLES);