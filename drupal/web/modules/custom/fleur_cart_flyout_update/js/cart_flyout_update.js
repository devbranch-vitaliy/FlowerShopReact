/**
 * Change cart flyout function
 * @preserve
 */

(function ($, Drupal, debounce) {
  Drupal.behaviors.cartFlyoutUpdate = {
    attach: function attach(context, settings) {

      $('.cart-block--offcanvas-cart-table__quantity input[type="number"]').once().on('change', debounce(function () {
        $(this).blur();
      }, 2000));
    }
  };
})(jQuery, Drupal, Drupal.debounce);
