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

      $(document).once().ready(function () {
        set_background();
      });

      $(window).once().scroll(function () {
        set_background();
      });

      // Show popup.
      $('.call-block').once().click(function () {
        document.getElementById('call-block-popup').classList.toggle('show');
      })
    }
  };

})(jQuery, Drupal, window);
