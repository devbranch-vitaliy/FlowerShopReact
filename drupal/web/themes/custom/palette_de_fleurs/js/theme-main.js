/**
 * @file
 * Main js code for our theme.
 */

(function ($, Drupal, window) {

  Drupal.behaviors.fleur_theme = {
    attach: function (context, settings) {
      // Header background color.
      var set_background = function () {
        var $header = $('#header-background');
        if ($(window).scrollTop() > $header.height()) {
          $header.addClass('scrolling');
        }
        else {
          $header.removeClass('scrolling');
        }
      };

      $(document).once().ready(set_background);

      $(window).once().scroll(set_background);

      // Show popup.
      $('.call-block').once().click(function () {
        $('#call-block-popup').toggleClass('show');
      })
    }
  };

})(jQuery, Drupal, window);
