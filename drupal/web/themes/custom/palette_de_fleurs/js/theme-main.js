/**
 * @file
 * Main js code for our theme.
 */

(function ($, Drupal, window, Bootstrap) {

  Drupal.behaviors.fleurSizeActiveClass = {
    attach: function (context, settings) {
      // Add active class to the size wrapper.
      $('.commerce-order-item-add-to-cart-form input[type="radio"]', context).once().each(function (e) {
        var $this = $(this);

        var toggleActiveClass = function ($element) {
          var checked = $element.is(':checked');

          var $input_wrapper = $element.closest('.form-item');

          if (checked) {
            $input_wrapper.addClass('active');
          }
          else {
            $input_wrapper.removeClass('active');
          }
        };

        toggleActiveClass($this);
        $this.once('set-size-active-class').on('change', toggleActiveClass($this));
      });
    }
  };

  Drupal.behaviors.fleurAddToCart = {
    attach: function (context, settings) {
      // Change variation action price to the bottom.
      $(".commerce-order-item-add-to-cart-form", context).once('change-price-position').each(function () {
        var $action_block = $('.path-product .commerce-order-item-add-to-cart-form .form-actions');
        var $price = $('.path-product .field--name-price').clone();

        $action_block.addClass('product-action').append($price);
      });
    }
  };

  Drupal.behaviors.fleurAddToCartDialogElements = {
    attach: function (context, settings) {
      // Change variation action price to the bottom.
      $(".modal-dialog", context).once('change-price-position-modal').each(function () {
        var $price = $('.modal-dialog .field--name-price').clone();

        $('.modal-dialog .modal-buttons').before($price);

      });

      // Change dialog height.
      $(window).resize(function () {
        if ($(window).height() > $('.modal-dialog .modal-content').height()) {
          $('.modal-dialog').height('100%');
        }
        else {
          $('.modal-dialog').height('auto');
        }
      });
    }
  };

  Drupal.behaviors.fleurAddToCartDialog = {
    attach: function () {
      $(window).once('dialog-behavior').bind("dialog:aftercreate", function () {
          Drupal.attachBehaviors();
       });
    }
  };

  Drupal.behaviors.fleurTheme = {
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

    }
  };

  Drupal.behaviors.fleurSlick = {
    attach: function (context, settings) {
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
    }
  };

})(jQuery, Drupal, window);
