/**
 * @file
 * JS code for Order information page.
 */

(function ($, Drupal) {
    Drupal.behaviors.fleurOrderInformation = {
        attach: function (context, settings) {

            // Order pane information.
            $('.checkout-pane', context).once().each(function () {
                var $container = $(this);

                var $address_type = $('.field--name-field-address-type', $container);
                var $address = $('.field--name-address', $container);
                var $wrapper_contact = $('<div class="fields-contacts"></div>');

                // Contact information.
                $('.label-to-whom', $container).insertBefore($address_type);
                $('.address-container-inline', $container).insertBefore($address_type);
                $('.field--name-field-telephone', $container).appendTo($wrapper_contact);
                $('.field--name-field-email', $container).appendTo($wrapper_contact);
                $wrapper_contact.insertBefore($address_type);
                $('.label-where', $container).insertBefore($address_type);
                $('.label-when', $container).insertBefore($('div[data-drupal-selector="edit-fleur-shipping-information-shipments"]'));

                // Address.
                $('.form-type-textfield', $address)
                    .addClass('col-sm-6')
                    .parent()
                    .addClass('row');
                $('input.locality', $address)
                    .parent()
                    .insertAfter($('input.organization', $address).parent());
                $('input.address-line2', $address)
                    .parent()
                    .find('.control-label')
                    .removeClass('sr-only');
                $('div[class*="-country-code"]', $address)
                    .parent()
                    .addClass('col-sm-6')
                    .insertBefore($('input.locality', $address).parent());
            });

            // Order summary.
            $('.layout-region-checkout-secondary', context).once().each(function () {
                var $summary = $(this).addClass('fleur-order-summary').addClass('hidden-xs').clone();
                $summary.removeClass()
                    .addClass('fleur-order-summary')
                    .addClass('visible-xs')
                    .prependTo($('.layout-region-checkout-main', context));
            })
        }
    }
})(jQuery, Drupal);
