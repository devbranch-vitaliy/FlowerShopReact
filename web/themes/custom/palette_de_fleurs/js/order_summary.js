/**
 * @file
 * JS code for Order information page.
 */

(function ($, Drupal) {
    Drupal.behaviors.fleurOrderSummary = {
        attach: function (context, settings) {
            // Order summary.
            $('.layout-region-checkout-secondary', context).once().each(function () {
                var $summary = $(this).addClass('fleur-order-summary').addClass('hidden-xs').clone();
                $summary.removeClass()
                    .addClass('fleur-order-summary')
                    .addClass('visible-xs')
                    .prependTo($('.layout-region-checkout-main', context));
            });
        }
    }
})(jQuery, Drupal);
