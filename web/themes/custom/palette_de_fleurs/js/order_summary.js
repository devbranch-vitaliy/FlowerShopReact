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
                // Mobile view.
                $summary.removeClass()
                    .addClass('fleur-order-summary')
                    .addClass('visible-xs')
                    .prependTo($('.layout-region-checkout-main', context));

                // Floating order summary.
                var $floating = $(this);
                $(window).scroll(function () {
                    var offset = jQuery(window).scrollTop() - $floating.position().top;
                    offset = Math.round(offset);
                    if (offset < 0) {
                        offset = 0;
                    }
                    $floating.css('margin-top', offset + 'px');
                })
            });
        }
    }
})(jQuery, Drupal);
