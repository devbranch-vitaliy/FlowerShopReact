/**
 * @file
 * JS code for Order information page.
 */

(function ($, Drupal, window) {
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
                $('input.organization', $address)
                    .parent()
                    .clone()
                    .addClass('hidden-field')
                    .addClass('hidden-xs')
                    .insertAfter($('input.organization', $address).parent());
                $('input.dependent-locality', $address)
                    .parent()
                    .insertAfter($('input.locality', $address).parent());
                $('input.address-line2', $address)
                    .parent()
                    .find('.control-label')
                    .removeClass('sr-only');
                $('div[class*="-country-code"]', $address)
                    .parent()
                    .addClass('col-sm-6')
                    .insertBefore($('input.locality', $address).parent());
            });

            // Change Email.
            $('.checkout-pane-fleur-payment-information input[type="email"]', context).once('payment-email').change(function () {
                $('.checkout-pane-contact-information input[type="email"]').val($(this).val());
            });

            // Accordion panel.
            $('.panel-heading', context).once('panel-accordion').click(function () {
                var $this = $(this);
                $this.next().slideToggle(350).toggleClass('show');
                $this.toggleClass('icon-up');
            });

            // Auto open of the panel.
            var open_panel = function () {
                $('.panel-heading', context).each(function () {
                    var $this = $(this);
                    var positionFromTop = $this.position().top;
                    var windowScrollPosition = Math.round(window.scrollY) + Math.round($(window).height() / 2);
                    if (windowScrollPosition > positionFromTop) {
                        $this
                            .once('panel-open')
                            .addClass('icon-up')
                            .next()
                            .slideToggle(350)
                            .addClass('show');
                    }
                });
            };

            open_panel();
            $(window).once('panel-open-on-scroll').scroll(open_panel);
            $(window).once('panel-open-on-resize').resize(open_panel);
        }
    }
})(jQuery, Drupal, window);
