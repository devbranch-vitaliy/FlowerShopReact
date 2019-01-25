/**
 * @file
 * JS code -/+ buttons near the cart quantity.
 */

(function ($, Drupal) {
    Drupal.behaviors.quantityField = {
        attach: function attach(context, settings) {
            var markup = '<div class="quantity-button quantity-down">\u2013</div>' + '<div class="quantity-button quantity-up">+</div>';

            var $quantity = $('.js-form-type-number', context);
            $quantity.once('quantity-field').each(function () {
                var $this = $(this);
                $this.append(markup);
                var $input = $this.find('input[type="number"]');
                var $btnUp = $this.find('.quantity-up');
                var $btnDown = $this.find('.quantity-down');
                var min = $input.attr('min');
                var max = $input.attr('max');
                $btnUp.on('click', function () {
                    var oldValue = parseFloat($input.val());
                    if (oldValue >= max) {
                        var newVal = oldValue;
                    }
                    else {
                        var newVal = oldValue + 1;
                    }
                    $this.find('input').val(newVal);
                    $this.find('input').trigger('change');
                });
                $btnDown.on('click', function () {
                    var oldValue = parseFloat($input.val());
                    if (oldValue <= min) {
                        var newVal = oldValue;
                    }
                    else {
                        var newVal = oldValue - 1;
                    }
                    $this.find('input').val(newVal);
                    $this.find('input').trigger('change');
                });
            });
        }
    };
})(jQuery, Drupal);
