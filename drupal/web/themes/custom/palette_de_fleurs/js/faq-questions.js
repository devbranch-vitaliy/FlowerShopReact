/**
 * @file
 * JS code for FAQ page.
 */

(function ($, Drupal) {
    Drupal.behaviors.fleurMobileFilter = {
        attach: function (context, settings) {
            $('.question', context).once().each(function () {
                var $container = $(this);

                $('.views-field-field-question', $container).click(function (e) {
                    var $this = $(this);

                    $this.next().slideToggle(350);
                    $this.next().toggleClass('show');
                    $this.toggleClass('icon-up');

                });
            })
        }
    }
})(jQuery, Drupal);
