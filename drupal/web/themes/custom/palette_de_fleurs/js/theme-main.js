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
        if ($(window).scrollTop() > 0) {
          $header.addClass('scrolling');
        }
        else {
          $header.removeClass('scrolling');
        }
      };

      $(document).once().ready(set_background);

      $(window).once().scroll(set_background);

      // Show popup.
      $('.call-icon').once().click(function () {
        $('#call-block-popup').toggleClass('show');
      });

      // Show mobile menu.
      $('.mobile_toggle').once().click(function () {
        $('#navbar-collapse').toggleClass('show');
      });

      // Config slick slideshow.
      $('.slick-customers-reviews').once().each(function (e) {
        var $this = $(this);
        $this.slick({
          dots: true,
          lazyLoad: 'progressive',
          cssEase: 'ease-in',
          infinite: false,
          accessibility: false,
          responsive: [
            {
              breakpoint: 768,
              settings: {
                arrows: false,
              }
            }
          ]
        });
      });

      // Add active class to the size wrapper.
      $('.commerce-order-item-add-to-cart-form input[type="radio"]', context).once().each(function (e) {
        var $this = $(this);

        var toggleActiveClass = function ($element) {
          var checked = $element.is(':checked');

          var $input_wrapper = $element.closest('.form-item');

          // $input_wrapper.find('input[type="radio"]').removeClass('active');

          if (checked) {
            $input_wrapper.addClass('active');
          }
          else {
            $input_wrapper.removeClass('active');
          }
        };

        toggleActiveClass($this);
        $this.once('set-size-active-class').on('change', toggleActiveClass($this));
      })
    }
  };

})(jQuery, Drupal, window);
